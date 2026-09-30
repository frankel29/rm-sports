<?php

namespace App\Filament\Resources\GastoMensuals\Pages;

use App\Filament\Resources\GastoMensuals\GastoMensualResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGastoMensual extends EditRecord
{
    protected static string $resource = GastoMensualResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
