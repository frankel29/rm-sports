<?php

namespace App\Filament\Resources\Tallas\Pages;

use App\Filament\Resources\Tallas\TallaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTalla extends EditRecord
{
    protected static string $resource = TallaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
