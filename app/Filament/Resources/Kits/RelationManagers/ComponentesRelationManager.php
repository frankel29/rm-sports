<?php

namespace App\Filament\Resources\Kits\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ComponentesRelationManager extends RelationManager
{
    protected static string $relationship = 'componentes';

    protected static ?string $title = 'Componentes del kit';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('modelo_id')
                    ->label('Modelo')
                    ->relationship('modelo', 'codigo')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('cantidad')
                    ->label('Cantidad')
                    ->numeric()
                    ->default(1)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('modelo.codigo')
            ->columns([
                TextColumn::make('modelo.codigo')
                    ->label('Modelo'),
                TextColumn::make('modelo.colegio.nombre')
                    ->label('Colegio'),
                TextColumn::make('modelo.prenda.nombre')
                    ->label('Prenda'),
                TextColumn::make('cantidad')
                    ->label('Cantidad'),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
