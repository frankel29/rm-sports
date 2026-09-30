<?php

namespace App\Filament\Resources\Modelos\Tables;

use App\Domain\Catalogo\Enums\TipoAbastecimiento;
use App\Domain\Catalogo\Models\Responsable;
use App\Domain\Catalogo\Models\Talla;
use App\Domain\Catalogo\Services\SkuGeneratorService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

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
                Action::make('generarSkus')
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
                    ->action(function (array $data, $record) {
                        $tallaDesde = Talla::query()->findOrFail($data['talla_desde_id']);
                        $tallaHasta = Talla::query()->findOrFail($data['talla_hasta_id']);

                        $skus = app(SkuGeneratorService::class)->generarParaModelo($record, $tallaDesde, $tallaHasta);

                        Notification::make()
                            ->title("{$skus->count()} SKU(s) generados o ya existentes")
                            ->success()
                            ->send();
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
