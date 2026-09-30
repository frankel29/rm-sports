<?php

namespace App\Filament\Resources\Skus;

use App\Domain\Catalogo\Models\Sku;
use App\Filament\RelationManagers\PreciosVentaRelationManager;
use App\Filament\Resources\Skus\Pages\CreateSku;
use App\Filament\Resources\Skus\Pages\EditSku;
use App\Filament\Resources\Skus\Pages\ListSkus;
use App\Filament\Resources\Skus\RelationManagers\MovimientosRelationManager;
use App\Filament\Resources\Skus\Schemas\SkuForm;
use App\Filament\Resources\Skus\Tables\SkusTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SkuResource extends Resource
{
    protected static ?string $model = Sku::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'codigo';

    protected static UnitEnum|string|null $navigationGroup = 'Catálogo';

    protected static ?string $modelLabel = 'SKU';

    protected static ?string $pluralModelLabel = 'SKUs';

    public static function form(Schema $schema): Schema
    {
        return SkuForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SkusTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PreciosVentaRelationManager::class,
            MovimientosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSkus::route('/'),
            'create' => CreateSku::route('/create'),
            'edit' => EditSku::route('/{record}/edit'),
        ];
    }
}
