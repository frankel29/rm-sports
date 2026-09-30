<?php

namespace App\Filament\Resources\TarifaManoObras\Schemas;

use App\Domain\Costeo\Enums\FormaPago;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TarifaManoObraForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('prenda_id')
                    ->label('Prenda')
                    ->relationship('prenda', 'nombre')
                    ->searchable()
                    ->required(),
                TextInput::make('operacion')
                    ->label('Operación')
                    ->required(),
                Select::make('forma_pago')
                    ->label('Forma de pago')
                    ->options(FormaPago::class)
                    ->required(),
                TextInput::make('valor')
                    ->label('Valor')
                    ->numeric()
                    ->required(),
                Select::make('responsable_id')
                    ->label('Responsable')
                    ->relationship('responsable', 'codigo')
                    ->required(),
                DatePicker::make('vigente_desde')
                    ->label('Vigente desde')
                    ->required(),
                DatePicker::make('vigente_hasta')
                    ->label('Vigente hasta'),
                Textarea::make('notas')
                    ->label('Notas')
                    ->columnSpanFull(),
            ]);
    }
}
