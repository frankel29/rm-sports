<?php

namespace App\Filament\Resources\ConfiguracionIvas\Pages;

use App\Filament\Resources\ConfiguracionIvas\ConfiguracionIvaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditConfiguracionIva extends EditRecord
{
    protected static string $resource = ConfiguracionIvaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
