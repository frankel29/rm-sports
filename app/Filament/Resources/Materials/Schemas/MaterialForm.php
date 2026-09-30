<?php

namespace App\Filament\Resources\Materials\Schemas;

use App\Domain\Materiales\Enums\TipoMaterial;
use App\Domain\Materiales\Enums\UnidadMaterial;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MaterialForm
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
                Select::make('tipo')
                    ->label('Tipo')
                    ->options(TipoMaterial::class)
                    ->required(),
                Select::make('unidad')
                    ->label('Unidad')
                    ->options(UnidadMaterial::class)
                    ->required(),
                TextInput::make('composicion')
                    ->label('Composición'),
                TextInput::make('color')
                    ->label('Color'),
                TextInput::make('ancho_m')
                    ->label('Ancho (m)')
                    ->numeric(),
                Select::make('responsable_id')
                    ->label('Responsable')
                    ->relationship('responsable', 'codigo')
                    ->required(),
                Toggle::make('activo')
                    ->label('Activo')
                    ->default(true),
                Textarea::make('notas')
                    ->label('Notas')
                    ->columnSpanFull(),
            ]);
    }
}
