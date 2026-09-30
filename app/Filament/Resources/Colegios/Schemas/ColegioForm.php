<?php

namespace App\Filament\Resources\Colegios\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ColegioForm
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
                TextInput::make('contacto')
                    ->label('Contacto'),
                TextInput::make('telefono')
                    ->label('Teléfono')
                    ->tel(),
                Toggle::make('es_generico')
                    ->label('Es genérico (sin colegio)')
                    ->default(false),
                Toggle::make('activo')
                    ->label('Activo')
                    ->default(true),
                Textarea::make('notas')
                    ->label('Notas')
                    ->columnSpanFull(),
            ]);
    }
}
