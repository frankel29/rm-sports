<?php

namespace App\Filament\Resources\Modelos\RelationManagers;

use App\Domain\Catalogo\Models\Modelo;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CostosCompraRelationManager extends RelationManager
{
    protected static string $relationship = 'costosCompra';

    protected static ?string $title = 'Costos de compra';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord instanceof Modelo && $ownerRecord->esComprado();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('talla_id')
                    ->label('Talla (vacío = todas)')
                    ->relationship('talla', 'codigo', fn ($query) => $query->orderBy('orden')),
                TextInput::make('costo')
                    ->label('Costo')
                    ->numeric()
                    ->required(),
                Select::make('proveedor_id')
                    ->label('Proveedor')
                    ->relationship('proveedor', 'nombre')
                    ->searchable(),
                DatePicker::make('vigente_desde')
                    ->label('Vigente desde')
                    ->required(),
                DatePicker::make('vigente_hasta')
                    ->label('Vigente hasta'),
                Select::make('responsable_id')
                    ->label('Responsable')
                    ->relationship('responsable', 'codigo')
                    ->required(),
                Textarea::make('notas')
                    ->label('Notas')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('costo')
            ->columns([
                TextColumn::make('talla.codigo')
                    ->label('Talla')
                    ->placeholder('Todas'),
                TextColumn::make('costo')
                    ->label('Costo')
                    ->money('USD'),
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
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
