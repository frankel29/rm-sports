<?php

namespace App\Filament\Resources\Skus\Tables;

use App\Domain\Catalogo\Models\Responsable;
use App\Domain\Inventario\Enums\TipoMovimientoInventario;
use App\Domain\Inventario\Enums\Ubicacion;
use App\Domain\Inventario\Services\InventarioService;
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

class SkusTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo')
                    ->label('Código')
                    ->searchable(),
                TextColumn::make('modelo.codigo')
                    ->label('Modelo')
                    ->searchable(),
                TextColumn::make('modelo.colegio.nombre')
                    ->label('Colegio'),
                TextColumn::make('modelo.prenda.nombre')
                    ->label('Prenda'),
                TextColumn::make('talla.codigo')
                    ->label('Talla'),
                TextColumn::make('modelo.responsable.codigo')
                    ->label('Responsable')
                    ->badge(),
                TextColumn::make('stock')
                    ->label('Stock actual')
                    ->state(fn ($record) => app(InventarioService::class)->stock($record))
                    ->color(fn ($record) => app(InventarioService::class)->tieneStockNegativo($record) ? 'danger' : 'success')
                    ->weight('bold'),
                IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('modelo.responsable_id')
                    ->label('Responsable')
                    ->options(fn () => Responsable::query()->pluck('codigo', 'id'))
                    ->query(fn ($query, array $data) => $query->when(
                        $data['value'] ?? null,
                        fn ($query, $value) => $query->whereHas('modelo', fn ($q) => $q->where('responsable_id', $value)),
                    )),
            ])
            ->recordActions([
                Action::make('verKardex')
                    ->label('Kardex')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->color('gray')
                    ->schema([])
                    ->modalContent(fn ($record) => view('filament.sku-kardex', [
                        'movimientos' => app(InventarioService::class)->kardex($record),
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar'),
                Action::make('registrarAjuste')
                    ->label('Ajuste')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->color('warning')
                    ->schema([
                        TextInput::make('cantidad')
                            ->label('Cantidad (use negativo para restar)')
                            ->numeric()
                            ->required(),
                        DatePicker::make('fecha_hecho')
                            ->label('Fecha')
                            ->default(now())
                            ->required(),
                        Select::make('ubicacion')
                            ->label('Ubicación')
                            ->options(Ubicacion::class),
                        Textarea::make('nota')
                            ->label('Motivo del ajuste')
                            ->required(),
                    ])
                    ->action(function (array $data, $record) {
                        app(InventarioService::class)->registrar(
                            sku: $record,
                            tipo: TipoMovimientoInventario::AJUSTE,
                            cantidad: (float) $data['cantidad'],
                            fechaHecho: Carbon::parse($data['fecha_hecho']),
                            user: auth()->user(),
                            ubicacion: $data['ubicacion'] ?? null,
                            nota: $data['nota'],
                        );

                        Notification::make()->title('Ajuste registrado')->success()->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
