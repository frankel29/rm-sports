<?php

namespace App\Filament\Resources\Modelos\Schemas;

use App\Domain\Catalogo\Enums\Genero;
use App\Domain\Catalogo\Enums\TipoAbastecimiento;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ModeloForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('codigo')
                    ->label('Código')
                    ->required(),
                Select::make('colegio_id')
                    ->label('Colegio')
                    ->relationship('colegio', 'nombre')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('prenda_id')
                    ->label('Prenda')
                    ->relationship('prenda', 'nombre')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('genero')
                    ->label('Género')
                    ->options(Genero::class),
                TextInput::make('color')
                    ->label('Color'),
                Select::make('tipo_abastecimiento')
                    ->label('Tipo de abastecimiento')
                    ->options(TipoAbastecimiento::class)
                    ->required(),
                Select::make('proveedor_id')
                    ->label('Proveedor')
                    ->relationship('proveedor', 'nombre')
                    ->searchable()
                    ->preload(),
                Select::make('responsable_id')
                    ->label('Responsable')
                    ->relationship('responsable', 'codigo')
                    ->required(),
                Select::make('talla_desde_id')
                    ->label('Talla desde')
                    ->relationship('tallaDesde', 'codigo', fn ($query) => $query->orderBy('orden'))
                    ->searchable()
                    ->preload(),
                Select::make('talla_hasta_id')
                    ->label('Talla hasta')
                    ->relationship('tallaHasta', 'codigo', fn ($query) => $query->orderBy('orden'))
                    ->searchable()
                    ->preload(),
                Toggle::make('activo')
                    ->label('Activo')
                    ->default(true),
                TextInput::make('revision')
                    ->label('Revisión'),
                Textarea::make('notas')
                    ->label('Notas')
                    ->columnSpanFull(),
            ]);
    }
}
