<?php

namespace App\Filament\Resources\ConfiguracionIvas\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ConfiguracionIvaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('porcentaje')
                    ->label('Porcentaje de IVA')
                    ->numeric()
                    ->suffix('%')
                    ->required(),
                DatePicker::make('vigente_desde')
                    ->label('Vigente desde')
                    ->required(),
                DatePicker::make('vigente_hasta')
                    ->label('Vigente hasta'),
            ]);
    }
}
