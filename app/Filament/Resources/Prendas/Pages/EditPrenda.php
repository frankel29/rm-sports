<?php

namespace App\Filament\Resources\Prendas\Pages;

use App\Filament\Resources\Prendas\PrendaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPrenda extends EditRecord
{
    protected static string $resource = PrendaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
