<?php

namespace App\Filament\Resources\Modelos\Schemas;

use App\Domain\Catalogo\Enums\CategoriaPrenda;
use App\Domain\Catalogo\Enums\Genero;
use App\Domain\Catalogo\Enums\TipoAbastecimiento;
use App\Domain\Catalogo\Enums\TipoTalla;
use App\Domain\Catalogo\Models\Talla;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
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
                    ->required()
                    ->createOptionForm([
                        TextInput::make('codigo')->label('Código')->required()->unique('colegios', 'codigo'),
                        TextInput::make('nombre')->label('Nombre')->required(),
                    ]),
                Select::make('prenda_id')
                    ->label('Prenda')
                    ->relationship('prenda', 'nombre')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        TextInput::make('codigo')->label('Código')->required()->unique('prendas', 'codigo'),
                        TextInput::make('nombre')->label('Nombre')->required(),
                        Select::make('categoria')->label('Categoría')->options(CategoriaPrenda::class)->required(),
                    ]),
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
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('codigo')->label('Código')->unique('proveedores', 'codigo'),
                        TextInput::make('nombre')->label('Nombre')->required(),
                        TextInput::make('telefono')->label('Teléfono')->tel(),
                    ]),
                Select::make('responsable_id')
                    ->label('Responsable')
                    ->relationship('responsable', 'codigo')
                    ->required(),
                Select::make('talla_desde_id')
                    ->label('Talla desde')
                    ->relationship('tallaDesde', 'codigo', fn ($query) => $query->orderBy('orden'))
                    ->searchable()
                    ->preload()
                    ->createOptionForm(self::formularioTalla()),
                Select::make('talla_hasta_id')
                    ->label('Talla hasta')
                    ->relationship('tallaHasta', 'codigo', fn ($query) => $query->orderBy('orden'))
                    ->searchable()
                    ->preload()
                    ->createOptionForm(self::formularioTalla())
                    ->helperText('Al guardar se crea un SKU por cada talla del rango.')
                    ->rule(fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                        $desde = Talla::query()->find($get('talla_desde_id'));
                        $hasta = Talla::query()->find($value);

                        if ($desde && $hasta && $desde->orden > $hasta->orden) {
                            $fail("La talla hasta ({$hasta->codigo}) va antes que la talla desde ({$desde->codigo}).");
                        }
                    }),
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

    private static function formularioTalla(): array
    {
        return [
            TextInput::make('codigo')->label('Código')->required()->unique('tallas', 'codigo'),
            TextInput::make('orden')->label('Orden')->numeric()->required()
                ->helperText('Posición en la secuencia de tallas: 28 = 1, 30 = 2…'),
            Select::make('tipo')->label('Tipo')->options(TipoTalla::class)->required(),
        ];
    }
}
