<?php

namespace App\Filament\Resources\Tallas;

use App\Domain\Catalogo\Models\Talla;
use App\Filament\Resources\Tallas\Pages\CreateTalla;
use App\Filament\Resources\Tallas\Pages\EditTalla;
use App\Filament\Resources\Tallas\Pages\ListTallas;
use App\Filament\Resources\Tallas\Schemas\TallaForm;
use App\Filament\Resources\Tallas\Tables\TallasTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TallaResource extends Resource
{
    protected static ?string $model = Talla::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'codigo';

    protected static UnitEnum|string|null $navigationGroup = 'Catálogo';

    protected static ?string $modelLabel = 'talla';

    protected static ?string $pluralModelLabel = 'tallas';

    public static function form(Schema $schema): Schema
    {
        return TallaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TallasTable::configure($table);
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
            'index' => ListTallas::route('/'),
            'create' => CreateTalla::route('/create'),
            'edit' => EditTalla::route('/{record}/edit'),
        ];
    }
}
