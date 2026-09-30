<?php

namespace App\Filament\Resources\Skus\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SkuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('modelo_id')
                    ->label('Modelo')
                    ->relationship('modelo', 'codigo')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('talla_id')
                    ->label('Talla')
                    ->relationship('talla', 'codigo', fn ($query) => $query->orderBy('orden'))
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('codigo')
                    ->label('Código')
                    ->required(),
                Toggle::make('activo')
                    ->label('Activo')
                    ->default(true),
            ]);
    }
}
