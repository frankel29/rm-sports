<?php

namespace App\Filament\Resources\TarifaManoObras;

use App\Domain\Costeo\Models\TarifaManoObra;
use App\Filament\Resources\TarifaManoObras\Pages\CreateTarifaManoObra;
use App\Filament\Resources\TarifaManoObras\Pages\EditTarifaManoObra;
use App\Filament\Resources\TarifaManoObras\Pages\ListTarifaManoObras;
use App\Filament\Resources\TarifaManoObras\Schemas\TarifaManoObraForm;
use App\Filament\Resources\TarifaManoObras\Tables\TarifaManoObrasTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TarifaManoObraResource extends Resource
{
    protected static ?string $model = TarifaManoObra::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static UnitEnum|string|null $navigationGroup = 'Materiales';

    protected static ?string $modelLabel = 'tarifa de mano de obra';

    protected static ?string $pluralModelLabel = 'tarifas de mano de obra';

    public static function form(Schema $schema): Schema
    {
        return TarifaManoObraForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TarifaManoObrasTable::configure($table);
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
            'index' => ListTarifaManoObras::route('/'),
            'create' => CreateTarifaManoObra::route('/create'),
            'edit' => EditTarifaManoObra::route('/{record}/edit'),
        ];
    }
}
