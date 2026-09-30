<?php

namespace App\Filament\Resources\ConfiguracionIvas;

use App\Domain\Catalogo\Models\ConfiguracionIva;
use App\Filament\Resources\ConfiguracionIvas\Pages\CreateConfiguracionIva;
use App\Filament\Resources\ConfiguracionIvas\Pages\EditConfiguracionIva;
use App\Filament\Resources\ConfiguracionIvas\Pages\ListConfiguracionIvas;
use App\Filament\Resources\ConfiguracionIvas\Schemas\ConfiguracionIvaForm;
use App\Filament\Resources\ConfiguracionIvas\Tables\ConfiguracionIvasTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ConfiguracionIvaResource extends Resource
{
    protected static ?string $model = ConfiguracionIva::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static UnitEnum|string|null $navigationGroup = 'Configuración';

    protected static ?string $modelLabel = 'IVA';

    protected static ?string $pluralModelLabel = 'Configuración de IVA';

    public static function form(Schema $schema): Schema
    {
        return ConfiguracionIvaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ConfiguracionIvasTable::configure($table);
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
            'index' => ListConfiguracionIvas::route('/'),
            'create' => CreateConfiguracionIva::route('/create'),
            'edit' => EditConfiguracionIva::route('/{record}/edit'),
        ];
    }
}
