<?php

namespace App\Filament\Resources\GastoMensuals\Pages;

use App\Filament\Resources\GastoMensuals\GastoMensualResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGastoMensuals extends ListRecords
{
    protected static string $resource = GastoMensualResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
