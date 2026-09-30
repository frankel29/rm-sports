<?php

namespace App\Filament\Resources\Materials\RelationManagers;

use App\Domain\Materiales\Models\Proveedor;
use App\Domain\Materiales\Services\PrecioMaterialService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

class PreciosRelationManager extends RelationManager
{
    protected static string $relationship = 'precios';

    protected static ?string $title = 'Precios';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('precio')
                    ->label('Precio')
                    ->numeric()
                    ->required(),
                Textarea::make('notas')
                    ->label('Notas')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('precio')
            ->columns([
                TextColumn::make('precio')
                    ->label('Precio')
                    ->money('USD', decimals: 4),
                TextColumn::make('proveedor.nombre')
                    ->label('Proveedor'),
                TextColumn::make('vigente_desde')
                    ->label('Vigente desde')
                    ->date(),
                TextColumn::make('vigente_hasta')
                    ->label('Vigente hasta')
                    ->date()
                    ->placeholder('Vigente'),
            ])
            ->headerActions([
                Action::make('registrarPrecio')
                    ->label('Registrar nuevo precio')
                    ->schema([
                        TextInput::make('precio')
                            ->label('Precio')
                            ->numeric()
                            ->required(),
                        DatePicker::make('vigente_desde')
                            ->label('Vigente desde')
                            ->default(now())
                            ->required(),
                        Select::make('proveedor_id')
                            ->label('Proveedor')
                            ->options(fn () => Proveedor::query()->pluck('nombre', 'id'))
                            ->searchable(),
                        Textarea::make('notas')
                            ->label('Notas')
                            ->columnSpanFull(),
                    ])
                    ->action(function (array $data) {
                        app(PrecioMaterialService::class)->registrar(
                            material: $this->getOwnerRecord(),
                            precio: (float) $data['precio'],
                            vigenteDesde: Carbon::parse($data['vigente_desde']),
                            proveedor: isset($data['proveedor_id']) ? Proveedor::query()->find($data['proveedor_id']) : null,
                            notas: $data['notas'] ?? null,
                        );
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
