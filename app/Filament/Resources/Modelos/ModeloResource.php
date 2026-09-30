<?php

namespace App\Filament\Resources\Modelos;

use App\Domain\Catalogo\Models\Modelo;
use App\Filament\Resources\Modelos\Pages\CreateModelo;
use App\Filament\Resources\Modelos\Pages\EditModelo;
use App\Filament\Resources\Modelos\Pages\ListModelos;
use App\Filament\Resources\Modelos\RelationManagers\BomLineasRelationManager;
use App\Filament\Resources\Modelos\RelationManagers\CostosCompraRelationManager;
use App\Filament\Resources\Modelos\RelationManagers\SkusRelationManager;
use App\Filament\Resources\Modelos\Schemas\ModeloForm;
use App\Filament\Resources\Modelos\Tables\ModelosTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ModeloResource extends Resource
{
    protected static ?string $model = Modelo::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'codigo';

    protected static UnitEnum|string|null $navigationGroup = 'Catálogo';

    protected static ?string $modelLabel = 'modelo';

    protected static ?string $pluralModelLabel = 'modelos';

    public static function form(Schema $schema): Schema
    {
        return ModeloForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ModelosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            SkusRelationManager::class,
            BomLineasRelationManager::class,
            CostosCompraRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListModelos::route('/'),
            'create' => CreateModelo::route('/create'),
            'edit' => EditModelo::route('/{record}/edit'),
        ];
    }
}
