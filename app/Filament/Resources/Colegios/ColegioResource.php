<?php

namespace App\Filament\Resources\Colegios;

use App\Domain\Catalogo\Models\Colegio;
use App\Filament\Resources\Colegios\Pages\CreateColegio;
use App\Filament\Resources\Colegios\Pages\EditColegio;
use App\Filament\Resources\Colegios\Pages\ListColegios;
use App\Filament\Resources\Colegios\Schemas\ColegioForm;
use App\Filament\Resources\Colegios\Tables\ColegiosTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ColegioResource extends Resource
{
    protected static ?string $model = Colegio::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nombre';

    protected static UnitEnum|string|null $navigationGroup = 'Catálogo';

    protected static ?string $modelLabel = 'colegio';

    protected static ?string $pluralModelLabel = 'colegios';

    public static function form(Schema $schema): Schema
    {
        return ColegioForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ColegiosTable::configure($table);
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
            'index' => ListColegios::route('/'),
            'create' => CreateColegio::route('/create'),
            'edit' => EditColegio::route('/{record}/edit'),
        ];
    }
}
