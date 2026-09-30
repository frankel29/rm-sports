<?php

namespace App\Filament\Resources\Modelos\RelationManagers;

use App\Domain\Catalogo\Models\Sku;
use App\Domain\Inventario\Services\InventarioService;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SkusRelationManager extends RelationManager
{
    protected static string $relationship = 'skus';

    protected static ?string $title = 'SKUs';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('activo')
                    ->label('Activo'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('codigo')
            ->columns([
                TextColumn::make('codigo')
                    ->label('Código')
                    ->searchable(),
                TextColumn::make('talla.codigo')
                    ->label('Talla'),
                TextColumn::make('stock')
                    ->label('Stock actual')
                    ->state(fn (Sku $record) => app(InventarioService::class)->stock($record))
                    ->color(fn (Sku $record) => app(InventarioService::class)->tieneStockNegativo($record) ? 'danger' : null),
                IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
