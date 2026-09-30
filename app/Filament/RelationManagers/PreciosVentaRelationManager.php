<?php

namespace App\Filament\RelationManagers;

use App\Domain\Catalogo\Models\Responsable;
use App\Domain\Catalogo\Services\PrecioVentaService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

class PreciosVentaRelationManager extends RelationManager
{
    protected static string $relationship = 'preciosVenta';

    protected static ?string $title = 'Precios de venta';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('precio')
                    ->label('Precio')
                    ->numeric()
                    ->required(),
                Toggle::make('incluye_iva')
                    ->label('Incluye IVA'),
                DatePicker::make('vigente_hasta')
                    ->label('Vigente hasta'),
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
                    ->money('USD'),
                IconColumn::make('incluye_iva')
                    ->label('Incluye IVA')
                    ->boolean(),
                TextColumn::make('vigente_desde')
                    ->label('Vigente desde')
                    ->date(),
                TextColumn::make('vigente_hasta')
                    ->label('Vigente hasta')
                    ->date()
                    ->placeholder('Vigente'),
                TextColumn::make('responsable.codigo')
                    ->label('Responsable')
                    ->badge(),
            ])
            ->headerActions([
                Action::make('registrarPrecio')
                    ->label('Registrar nuevo precio')
                    ->schema([
                        TextInput::make('precio')
                            ->label('Precio')
                            ->numeric()
                            ->required(),
                        Toggle::make('incluye_iva')
                            ->label('Incluye IVA'),
                        DatePicker::make('vigente_desde')
                            ->label('Vigente desde')
                            ->default(now())
                            ->required(),
                        Select::make('responsable_id')
                            ->label('Responsable')
                            ->options(fn () => Responsable::query()->pluck('codigo', 'id'))
                            ->required(),
                        Textarea::make('notas')
                            ->label('Notas')
                            ->columnSpanFull(),
                    ])
                    ->action(function (array $data) {
                        app(PrecioVentaService::class)->registrar(
                            vendible: $this->getOwnerRecord(),
                            precio: (float) $data['precio'],
                            incluyeIva: (bool) ($data['incluye_iva'] ?? false),
                            vigenteDesde: Carbon::parse($data['vigente_desde']),
                            responsable: Responsable::query()->findOrFail($data['responsable_id']),
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
