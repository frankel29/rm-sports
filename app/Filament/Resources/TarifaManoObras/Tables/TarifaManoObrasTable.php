<?php

namespace App\Filament\Resources\TarifaManoObras\Tables;

use App\Domain\Catalogo\Models\Responsable;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TarifaManoObrasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('prenda.nombre')
                    ->label('Prenda')
                    ->searchable(),
                TextColumn::make('operacion')
                    ->label('Operación')
                    ->searchable(),
                TextColumn::make('forma_pago')
                    ->label('Forma de pago')
                    ->badge(),
                TextColumn::make('valor')
                    ->label('Valor')
                    ->money('USD'),
                TextColumn::make('responsable.codigo')
                    ->label('Responsable')
                    ->badge(),
                TextColumn::make('vigente_desde')
                    ->label('Vigente desde')
                    ->date(),
                TextColumn::make('vigente_hasta')
                    ->label('Vigente hasta')
                    ->date()
                    ->placeholder('Vigente'),
            ])
            ->filters([
                SelectFilter::make('responsable_id')
                    ->label('Responsable')
                    ->options(fn () => Responsable::query()->pluck('codigo', 'id')),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
