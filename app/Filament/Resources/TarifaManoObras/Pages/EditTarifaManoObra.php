<?php

namespace App\Filament\Resources\TarifaManoObras\Pages;

use App\Filament\Resources\TarifaManoObras\TarifaManoObraResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTarifaManoObra extends EditRecord
{
    protected static string $resource = TarifaManoObraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
