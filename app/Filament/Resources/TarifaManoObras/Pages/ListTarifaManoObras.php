<?php

namespace App\Filament\Resources\TarifaManoObras\Pages;

use App\Filament\Resources\TarifaManoObras\TarifaManoObraResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTarifaManoObras extends ListRecords
{
    protected static string $resource = TarifaManoObraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
