<?php

namespace App\Filament\Resources\Proveedors\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProveedorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('codigo')
                    ->label('Código'),
                TextInput::make('nombre')
                    ->label('Nombre')
                    ->required(),
                TextInput::make('ruc')
                    ->label('RUC'),
                TextInput::make('telefono')
                    ->label('Teléfono')
                    ->tel(),
                Textarea::make('que_provee')
                    ->label('Qué provee')
                    ->columnSpanFull(),
                Select::make('responsable_id')
                    ->label('Responsable')
                    ->relationship('responsable', 'codigo'),
                Toggle::make('activo')
                    ->label('Activo')
                    ->default(true),
                Textarea::make('notas')
                    ->label('Notas')
                    ->columnSpanFull(),
            ]);
    }
}
