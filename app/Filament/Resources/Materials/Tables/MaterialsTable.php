<?php

namespace App\Filament\Resources\Materials\Tables;

use App\Domain\Catalogo\Models\Responsable;
use App\Domain\Materiales\Enums\TipoMaterial;
use App\Domain\Materiales\Services\PrecioMaterialService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MaterialsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo')
                    ->label('Código')
                    ->searchable(),
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable(),
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge(),
                TextColumn::make('unidad')
                    ->label('Unidad'),
                TextColumn::make('precioVigente')
                    ->label('Precio vigente')
                    ->state(fn ($record) => app(PrecioMaterialService::class)->vigente($record)?->precio)
                    ->money('USD'),
                TextColumn::make('responsable.codigo')
                    ->label('Responsable')
                    ->badge(),
                IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('tipo')
                    ->label('Tipo')
                    ->options(TipoMaterial::class),
                SelectFilter::make('responsable_id')
                    ->label('Responsable')
                    ->options(fn () => Responsable::query()->pluck('codigo', 'id')),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
