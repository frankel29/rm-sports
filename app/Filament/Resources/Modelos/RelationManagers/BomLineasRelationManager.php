<?php

namespace App\Filament\Resources\Modelos\RelationManagers;

use App\Domain\Catalogo\Models\Modelo;
use App\Domain\Materiales\Models\Material;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class BomLineasRelationManager extends RelationManager
{
    protected static string $relationship = 'bomLineas';

    protected static ?string $title = 'BOM (consumo de materiales)';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord instanceof Modelo && $ownerRecord->esFabricado();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('material_id')
                    ->label('Material')
                    ->options(fn () => Material::query()->pluck('nombre', 'id'))
                    ->searchable()
                    ->required(),
                Select::make('talla_id')
                    ->label('Talla (vacío = todas)')
                    ->relationship('talla', 'codigo', fn ($query) => $query->orderBy('orden')),
                TextInput::make('cantidad')
                    ->label('Cantidad')
                    ->numeric()
                    ->required(),
                TextInput::make('unidad')
                    ->label('Unidad')
                    ->required(),
                Textarea::make('notas')
                    ->label('Notas')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('material.nombre')
            ->columns([
                TextColumn::make('material.nombre')
                    ->label('Material'),
                TextColumn::make('talla.codigo')
                    ->label('Talla')
                    ->placeholder('Todas'),
                TextColumn::make('cantidad')
                    ->label('Cantidad'),
                TextColumn::make('unidad')
                    ->label('Unidad'),
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
