<?php

namespace App\Filament\Resources\Kits\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class KitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('codigo')
                    ->label('Código')
                    ->required(),
                TextInput::make('nombre')
                    ->label('Nombre')
                    ->required(),
                Select::make('responsable_id')
                    ->label('Responsable')
                    ->relationship('responsable', 'codigo')
                    ->required(),
                Toggle::make('activo')
                    ->label('Activo')
                    ->default(true),
                Repeater::make('componentes')
                    ->label('Piezas del conjunto')
                    ->relationship()
                    ->schema([
                        Select::make('modelo_id')
                            ->label('Modelo')
                            ->relationship('modelo', 'codigo')
                            ->searchable()
                            ->preload()
                            ->distinct()
                            ->required(),
                        TextInput::make('cantidad')
                            ->label('Cantidad')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->required(),
                    ])
                    ->columns(2)
                    ->minItems(1)
                    ->defaultItems(2)
                    ->addActionLabel('Agregar pieza')
                    ->helperText('La talla de cada pieza se elige al vender.')
                    ->columnSpanFull(),
                Textarea::make('notas')
                    ->label('Notas')
                    ->columnSpanFull(),
            ]);
    }
}
