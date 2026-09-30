<?php

namespace App\Filament\Resources\ConfiguracionIvas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ConfiguracionIvasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('porcentaje')
                    ->label('Porcentaje')
                    ->suffix('%'),
                TextColumn::make('vigente_desde')
                    ->label('Vigente desde')
                    ->date(),
                TextColumn::make('vigente_hasta')
                    ->label('Vigente hasta')
                    ->date()
                    ->placeholder('Vigente'),
            ])
            ->defaultSort('vigente_desde', 'desc')
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
