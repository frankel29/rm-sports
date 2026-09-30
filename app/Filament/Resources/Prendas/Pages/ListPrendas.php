<?php

namespace App\Filament\Resources\Prendas\Pages;

use App\Filament\Resources\Prendas\PrendaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPrendas extends ListRecords
{
    protected static string $resource = PrendaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
