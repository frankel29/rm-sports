<?php

namespace App\Filament\Resources\Skus\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MovimientosRelationManager extends RelationManager
{
    protected static string $relationship = 'movimientos';

    protected static ?string $title = 'Kardex';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fecha_hecho')
                    ->label('Fecha')
                    ->date(),
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge(),
                TextColumn::make('cantidad')
                    ->label('Cantidad')
                    ->color(fn ($state) => $state < 0 ? 'danger' : 'success'),
                TextColumn::make('ubicacion')
                    ->label('Ubicación'),
                TextColumn::make('origen')
                    ->label('Origen'),
                TextColumn::make('user.name')
                    ->label('Usuario'),
                TextColumn::make('nota')
                    ->label('Nota')
                    ->limit(40),
            ])
            ->defaultSort('fecha_hecho', 'desc')
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
