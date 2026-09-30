<?php

namespace App\Filament\Resources\ConfiguracionIvas\Pages;

use App\Filament\Resources\ConfiguracionIvas\ConfiguracionIvaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListConfiguracionIvas extends ListRecords
{
    protected static string $resource = ConfiguracionIvaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
