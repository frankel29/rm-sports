<?php

namespace App\Filament\Resources\Tallas\Pages;

use App\Filament\Resources\Tallas\TallaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTallas extends ListRecords
{
    protected static string $resource = TallaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
