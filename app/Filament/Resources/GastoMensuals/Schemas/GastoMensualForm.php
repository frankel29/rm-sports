<?php

namespace App\Filament\Resources\GastoMensuals\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class GastoMensualForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('concepto')
                    ->label('Concepto')
                    ->required(),
                TextInput::make('monto_mensual')
                    ->label('Monto mensual')
                    ->numeric(),
                Select::make('responsable_id')
                    ->label('Responsable')
                    ->relationship('responsable', 'codigo'),
                Textarea::make('notas')
                    ->label('Notas')
                    ->columnSpanFull(),
            ]);
    }
}
