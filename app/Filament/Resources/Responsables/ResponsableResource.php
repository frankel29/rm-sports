<?php

namespace App\Filament\Resources\Responsables;

use App\Domain\Catalogo\Models\Responsable;
use App\Filament\Resources\Responsables\Pages\CreateResponsable;
use App\Filament\Resources\Responsables\Pages\EditResponsable;
use App\Filament\Resources\Responsables\Pages\ListResponsables;
use App\Filament\Resources\Responsables\Schemas\ResponsableForm;
use App\Filament\Resources\Responsables\Tables\ResponsablesTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ResponsableResource extends Resource
{
    protected static ?string $model = Responsable::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nombre';

    protected static UnitEnum|string|null $navigationGroup = 'Configuración';

    protected static ?string $modelLabel = 'responsable';

    protected static ?string $pluralModelLabel = 'responsables';

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->esAdminGeneral();
    }

    public static function form(Schema $schema): Schema
    {
        return ResponsableForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ResponsablesTable::configure($table);
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
            'index' => ListResponsables::route('/'),
            'create' => CreateResponsable::route('/create'),
            'edit' => EditResponsable::route('/{record}/edit'),
        ];
    }
}
