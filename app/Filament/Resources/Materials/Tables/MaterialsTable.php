<?php

namespace App\Filament\Resources\Materials\Tables;

use App\Domain\Catalogo\Models\Responsable;
use App\Domain\Inventario\Enums\TipoMovimientoMaterial;
use App\Domain\Inventario\Enums\Ubicacion;
use App\Domain\Inventario\Services\InventarioMaterialService;
use App\Domain\Materiales\Enums\TipoMaterial;
use App\Domain\Materiales\Models\Material;
use App\Domain\Materiales\Services\PrecioMaterialService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class MaterialsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo')
                    ->label('Código')
                    ->searchable(),
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable(),
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge(),
                TextColumn::make('stock')
                    ->label('Stock')
                    ->state(fn (Material $record) => app(InventarioMaterialService::class)->stock($record))
                    ->suffix(fn (Material $record) => ' '.$record->unidad->value)
                    ->color(fn (Material $record) => app(InventarioMaterialService::class)->tieneStockNegativo($record) ? 'danger' : null),
                TextColumn::make('precioVigente')
                    ->label('Precio vigente')
                    ->state(fn ($record) => app(PrecioMaterialService::class)->vigente($record)?->precio)
                    ->money('USD'),
                TextColumn::make('responsable.codigo')
                    ->label('Responsable')
                    ->badge(),
                IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('tipo')
                    ->label('Tipo')
                    ->options(TipoMaterial::class),
                SelectFilter::make('responsable_id')
                    ->label('Responsable')
                    ->options(fn () => Responsable::query()->pluck('codigo', 'id')),
            ])
            ->recordActions([
                Action::make('verKardex')
                    ->label('Kardex')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->color('gray')
                    ->modalContent(fn (Material $record) => view('filament.kardex', [
                        'movimientos' => app(InventarioMaterialService::class)->kardex($record),
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar'),
                self::accionConteoInicial(),
                self::accionAjuste(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function accionConteoInicial(): Action
    {
        return Action::make('conteoInicial')
            ->label('Conteo inicial')
            ->icon('heroicon-o-clipboard-document-check')
            ->visible(fn (Material $record) => ! app(InventarioMaterialService::class)->tieneInventarioInicial($record))
            ->fillForm(['fecha_conteo' => now()->toDateString(), 'ubicacion' => Ubicacion::TALLER->value])
            ->schema([
                TextInput::make('cantidad')
                    ->label('Cantidad')
                    ->numeric()
                    ->minValue(0.01)
                    ->suffix(fn (Material $record) => $record->unidad->value)
                    ->required(),
                DatePicker::make('fecha_conteo')->label('Fecha del conteo')->required(),
                Select::make('ubicacion')->label('Ubicación')->options(Ubicacion::class)->required(),
                TextInput::make('contado_por')->label('Contado por'),
            ])
            ->action(function (array $data, Material $record, Action $action) {
                self::registrar($action, $record, TipoMovimientoMaterial::INVENTARIO_INICIAL, $data, (float) $data['cantidad'],
                    filled($data['contado_por'] ?? null) ? "Contado por: {$data['contado_por']}" : null);
            });
    }

    private static function accionAjuste(): Action
    {
        return Action::make('registrarAjuste')
            ->label('Ajuste')
            ->icon('heroicon-o-adjustments-horizontal')
            ->color('warning')
            ->fillForm(['fecha_conteo' => now()->toDateString()])
            ->schema([
                TextInput::make('cantidad')
                    ->label('Cantidad (negativa para restar)')
                    ->numeric()
                    ->suffix(fn (Material $record) => $record->unidad->value)
                    ->required(),
                DatePicker::make('fecha_conteo')->label('Fecha')->required(),
                Select::make('ubicacion')->label('Ubicación')->options(Ubicacion::class),
                Textarea::make('nota')->label('Motivo del ajuste')->required(),
            ])
            ->action(function (array $data, Material $record, Action $action) {
                self::registrar($action, $record, TipoMovimientoMaterial::AJUSTE, $data, (float) $data['cantidad'], $data['nota']);
            });
    }

    private static function registrar(Action $action, Material $material, TipoMovimientoMaterial $tipo, array $data, float $cantidad, ?string $nota): void
    {
        $ubicacion = $data['ubicacion'] ?? null;

        try {
            app(InventarioMaterialService::class)->registrar(
                material: $material,
                tipo: $tipo,
                cantidad: $cantidad,
                fechaHecho: Carbon::parse($data['fecha_conteo']),
                user: auth()->user(),
                ubicacion: $ubicacion instanceof Ubicacion ? $ubicacion->value : $ubicacion,
                nota: $nota,
            );
        } catch (InvalidArgumentException $e) {
            Notification::make()->title('No se registró')->body($e->getMessage())->danger()->send();
            $action->halt();
        }

        Notification::make()->title('Movimiento registrado')->success()->send();
    }
}
