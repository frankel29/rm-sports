<?php

namespace App\Filament\Resources\Prendas\Schemas;

use App\Domain\Catalogo\Enums\CategoriaPrenda;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PrendaForm
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
                Select::make('categoria')
                    ->label('Categoría')
                    ->options(CategoriaPrenda::class)
                    ->required(),
                Textarea::make('notas')
                    ->label('Notas')
                    ->columnSpanFull(),
            ]);
    }
}
