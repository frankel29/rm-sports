<?php

namespace App\Filament\Resources\Kits;

use App\Domain\Catalogo\Models\Kit;
use App\Filament\RelationManagers\PreciosVentaRelationManager;
use App\Filament\Resources\Kits\Pages\CreateKit;
use App\Filament\Resources\Kits\Pages\EditKit;
use App\Filament\Resources\Kits\Pages\ListKits;
use App\Filament\Resources\Kits\RelationManagers\ComponentesRelationManager;
use App\Filament\Resources\Kits\Schemas\KitForm;
use App\Filament\Resources\Kits\Tables\KitsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class KitResource extends Resource
{
    protected static ?string $model = Kit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nombre';

    protected static UnitEnum|string|null $navigationGroup = 'Catálogo';

    protected static ?string $modelLabel = 'conjunto';

    protected static ?string $pluralModelLabel = 'conjuntos';

    public static function form(Schema $schema): Schema
    {
        return KitForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KitsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ComponentesRelationManager::class,
            PreciosVentaRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKits::route('/'),
            'create' => CreateKit::route('/create'),
            'edit' => EditKit::route('/{record}/edit'),
        ];
    }
}
