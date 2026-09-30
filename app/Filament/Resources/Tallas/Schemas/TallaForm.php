<?php

namespace App\Filament\Resources\Tallas\Schemas;

use App\Domain\Catalogo\Enums\TipoTalla;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TallaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('codigo')
                    ->label('Código')
                    ->required(),
                TextInput::make('orden')
                    ->label('Orden')
                    ->required()
                    ->numeric(),
                Select::make('tipo')
                    ->label('Tipo')
                    ->options(TipoTalla::class)
                    ->required(),
            ]);
    }
}
