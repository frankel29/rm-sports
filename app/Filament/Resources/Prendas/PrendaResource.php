<?php

namespace App\Filament\Resources\Prendas;

use App\Domain\Catalogo\Models\Prenda;
use App\Filament\Resources\Prendas\Pages\CreatePrenda;
use App\Filament\Resources\Prendas\Pages\EditPrenda;
use App\Filament\Resources\Prendas\Pages\ListPrendas;
use App\Filament\Resources\Prendas\Schemas\PrendaForm;
use App\Filament\Resources\Prendas\Tables\PrendasTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PrendaResource extends Resource
{
    protected static ?string $model = Prenda::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nombre';

    protected static UnitEnum|string|null $navigationGroup = 'Catálogo';

    protected static ?string $modelLabel = 'prenda';

    protected static ?string $pluralModelLabel = 'prendas';

    public static function form(Schema $schema): Schema
    {
        return PrendaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PrendasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPrendas::route('/'),
            'create' => CreatePrenda::route('/create'),
            'edit' => EditPrenda::route('/{record}/edit'),
        ];
    }
}
