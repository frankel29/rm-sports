<?php

namespace App\Filament\Resources\GastoMensuals\Tables;

use App\Domain\Catalogo\Models\Responsable;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GastoMensualsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('concepto')
                    ->label('Concepto')
                    ->searchable(),
                TextColumn::make('monto_mensual')
                    ->label('Monto mensual')
                    ->money('USD')
                    ->placeholder('Sin definir'),
                TextColumn::make('responsable.codigo')
                    ->label('Responsable')
                    ->badge()
                    ->placeholder('Compartido'),
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
