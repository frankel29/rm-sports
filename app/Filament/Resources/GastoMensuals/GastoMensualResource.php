<?php

namespace App\Filament\Resources\GastoMensuals;

use App\Domain\Costeo\Models\GastoMensual;
use App\Filament\Resources\GastoMensuals\Pages\CreateGastoMensual;
use App\Filament\Resources\GastoMensuals\Pages\EditGastoMensual;
use App\Filament\Resources\GastoMensuals\Pages\ListGastoMensuals;
use App\Filament\Resources\GastoMensuals\Schemas\GastoMensualForm;
use App\Filament\Resources\GastoMensuals\Tables\GastoMensualsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class GastoMensualResource extends Resource
{
    protected static ?string $model = GastoMensual::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'concepto';

    protected static UnitEnum|string|null $navigationGroup = 'Materiales';

    protected static ?string $modelLabel = 'gasto mensual';

    protected static ?string $pluralModelLabel = 'gastos mensuales';

    public static function form(Schema $schema): Schema
    {
        return GastoMensualForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GastoMensualsTable::configure($table);
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
            'index' => ListGastoMensuals::route('/'),
            'create' => CreateGastoMensual::route('/create'),
            'edit' => EditGastoMensual::route('/{record}/edit'),
        ];
    }
}
