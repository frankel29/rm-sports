<?php

namespace App\Filament\Resources\Modelos\Tables;

use App\Domain\Catalogo\Enums\TipoAbastecimiento;
use App\Domain\Catalogo\Models\Modelo;
use App\Domain\Catalogo\Models\Responsable;
use App\Domain\Catalogo\Models\Sku;
use App\Domain\Catalogo\Models\Talla;
use App\Domain\Catalogo\Services\PrecioVentaService;
use App\Domain\Catalogo\Services\SkuGeneratorService;
use App\Domain\Inventario\Enums\TipoMovimientoInventario;
use App\Domain\Inventario\Enums\Ubicacion;
use App\Domain\Inventario\Services\InventarioService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ModelosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo')
                    ->label('Código')
                    ->searchable(),
                TextColumn::make('colegio.nombre')
                    ->label('Colegio')
                    ->searchable(),
                TextColumn::make('prenda.nombre')
                    ->label('Prenda')
                    ->searchable(),
                TextColumn::make('genero')
                    ->label('Género')
                    ->badge()
                    ->searchable(),
                TextColumn::make('color')
                    ->label('Color')
                    ->searchable(),
                TextColumn::make('tipo_abastecimiento')
                    ->label('Abastecimiento')
                    ->badge()
                    ->searchable(),
                TextColumn::make('responsable.codigo')
                    ->label('Responsable')
                    ->badge(),
                IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('responsable_id')
                    ->label('Responsable')
                    ->options(fn () => Responsable::query()->pluck('codigo', 'id')),
                SelectFilter::make('tipo_abastecimiento')
                    ->label('Abastecimiento')
                    ->options(TipoAbastecimiento::class),
            ])
            ->recordActions([
                self::accionPrecios(),
                self::accionConteoInicial(),
                self::accionGenerarSkus(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * @return Collection<int, Sku>
     */
    private static function skusOrdenados(Modelo $modelo): Collection
    {
        return $modelo->skus()->with('talla')->get()->sortBy('talla.orden')->values();
    }

    private static function accionPrecios(): Action
    {
        return Action::make('precios')
            ->label('Precios')
            ->icon('heroicon-o-currency-dollar')
            ->modalHeading(fn (Modelo $record) => "Precios de {$record->codigo}")
            ->fillForm(fn (Modelo $record) => [
                'responsable_id' => $record->responsable_id,
                'incluye_iva' => true,
                'vigente_desde' => now()->toDateString(),
                'filas' => self::skusOrdenados($record)->map(fn (Sku $sku) => [
                    'sku_id' => $sku->id,
                    'talla' => $sku->talla->codigo,
                    'actual' => app(PrecioVentaService::class)->vigente($sku)?->precio,
                    'nuevo' => null,
                ])->all(),
            ])
            ->schema([
                TextInput::make('precio_todas')
                    ->label('Mismo precio para todas (opcional)')
                    ->numeric()
                    ->prefix('$')
                    ->live(onBlur: true)
                    ->dehydrated(false)
                    ->helperText('Llena todas las tallas; luego cambia las que tengan otro precio.')
                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                        $filas = $get('filas') ?? [];
                        foreach (array_keys($filas) as $clave) {
                            $filas[$clave]['nuevo'] = $state;
                        }
                        $set('filas', $filas);
                    }),
                Repeater::make('filas')
                    ->label('Tallas')
                    ->schema([
                        Hidden::make('sku_id'),
                        TextInput::make('talla')->label('Talla')->disabled(),
                        TextInput::make('actual')->label('Precio actual')->prefix('$')->placeholder('Sin precio')->disabled(),
                        TextInput::make('nuevo')->label('Precio nuevo')->numeric()->minValue(0.01)->prefix('$'),
                    ])
                    ->columns(3)
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false),
                Toggle::make('incluye_iva')->label('Incluye IVA'),
                DatePicker::make('vigente_desde')->label('Vigente desde')->required(),
                Select::make('responsable_id')
                    ->label('Línea')
                    ->options(fn () => Responsable::query()->pluck('codigo', 'id'))
                    ->required(),
            ])
            ->action(function (array $data, Modelo $record, Action $action) {
                $filas = collect($data['filas'] ?? [])->filter(fn (array $fila) => filled($fila['nuevo'] ?? null));

                if ($filas->isEmpty()) {
                    Notification::make()->title('No escribiste ningún precio nuevo')->warning()->send();
                    $action->halt();
                }

                try {
                    DB::transaction(function () use ($filas, $data, $record) {
                        $servicio = app(PrecioVentaService::class);
                        $desde = Carbon::parse($data['vigente_desde']);
                        $responsable = Responsable::query()->findOrFail($data['responsable_id']);

                        foreach ($filas as $fila) {
                            $sku = $record->skus()->findOrFail($fila['sku_id']);
                            $servicio->registrar($sku, (float) $fila['nuevo'], (bool) $data['incluye_iva'], $desde, $responsable);
                        }
                    });
                } catch (InvalidArgumentException $e) {
                    Notification::make()->title('No se guardó ningún precio')->body($e->getMessage())->danger()->send();
                    $action->halt();
                }

                Notification::make()->title("{$filas->count()} precio(s) registrado(s)")->success()->send();
            });
    }

    private static function accionConteoInicial(): Action
    {
        return Action::make('conteoInicial')
            ->label('Conteo inicial')
            ->icon('heroicon-o-clipboard-document-check')
            ->modalHeading(fn (Modelo $record) => "Conteo inicial de {$record->codigo}")
            ->fillForm(function (Modelo $record) {
                $inventario = app(InventarioService::class);

                return [
                    'fecha_conteo' => now()->toDateString(),
                    'ubicacion' => Ubicacion::LOCAL->value,
                    'filas' => self::skusOrdenados($record)->map(fn (Sku $sku) => [
                        'sku_id' => $sku->id,
                        'talla' => $sku->talla->codigo,
                        'ya_contado' => $inventario->tieneInventarioInicial($sku),
                        'cantidad' => null,
                    ])->all(),
                ];
            })
            ->schema([
                DatePicker::make('fecha_conteo')->label('Fecha del conteo')->required(),
                Select::make('ubicacion')->label('Ubicación')->options(Ubicacion::class)->required(),
                TextInput::make('contado_por')->label('Contado por'),
                Repeater::make('filas')
                    ->label('Cantidad por talla')
                    ->schema([
                        Hidden::make('sku_id'),
                        Hidden::make('ya_contado'),
                        TextInput::make('talla')->label('Talla')->disabled(),
                        TextInput::make('cantidad')
                            ->label('Cantidad')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->disabled(fn (Get $get) => (bool) $get('ya_contado'))
                            ->placeholder(fn (Get $get) => $get('ya_contado') ? 'Ya contado: usa Ajuste' : null),
                    ])
                    ->columns(2)
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false),
            ])
            ->action(function (array $data, Modelo $record, Action $action) {
                $filas = collect($data['filas'] ?? [])->filter(fn (array $fila) => (float) ($fila['cantidad'] ?? 0) > 0);

                if ($filas->isEmpty()) {
                    Notification::make()->title('No escribiste ninguna cantidad')->warning()->send();
                    $action->halt();
                }

                $ubicacion = $data['ubicacion'] instanceof Ubicacion ? $data['ubicacion']->value : $data['ubicacion'];
                $nota = filled($data['contado_por'] ?? null) ? "Contado por: {$data['contado_por']}" : null;

                try {
                    DB::transaction(function () use ($filas, $data, $record, $ubicacion, $nota) {
                        $inventario = app(InventarioService::class);
                        $fecha = Carbon::parse($data['fecha_conteo']);

                        foreach ($filas as $fila) {
                            $inventario->registrar(
                                sku: $record->skus()->findOrFail($fila['sku_id']),
                                tipo: TipoMovimientoInventario::INVENTARIO_INICIAL,
                                cantidad: (float) $fila['cantidad'],
                                fechaHecho: $fecha,
                                user: auth()->user(),
                                ubicacion: $ubicacion,
                                nota: $nota,
                            );
                        }
                    });
                } catch (InvalidArgumentException $e) {
                    Notification::make()->title('No se guardó el conteo')->body($e->getMessage())->danger()->send();
                    $action->halt();
                }

                Notification::make()->title("Conteo inicial registrado en {$filas->count()} talla(s)")->success()->send();
            });
    }

    private static function accionGenerarSkus(): Action
    {
        return Action::make('generarSkus')
            ->label('Generar SKUs')
            ->icon('heroicon-o-squares-plus')
            ->schema([
                Select::make('talla_desde_id')
                    ->label('Talla desde')
                    ->options(fn () => Talla::query()->orderBy('orden')->pluck('codigo', 'id'))
                    ->required(),
                Select::make('talla_hasta_id')
                    ->label('Talla hasta')
                    ->options(fn () => Talla::query()->orderBy('orden')->pluck('codigo', 'id'))
                    ->required(),
            ])
            ->action(function (array $data, Modelo $record) {
                $tallaDesde = Talla::query()->findOrFail($data['talla_desde_id']);
                $tallaHasta = Talla::query()->findOrFail($data['talla_hasta_id']);

                $skus = app(SkuGeneratorService::class)->generarParaModelo($record, $tallaDesde, $tallaHasta);

                Notification::make()
                    ->title("{$skus->count()} SKU(s) generados o ya existentes")
                    ->success()
                    ->send();
            });
    }
}
