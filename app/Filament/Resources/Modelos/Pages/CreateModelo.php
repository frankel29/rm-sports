<?php

namespace App\Filament\Resources\Modelos\Pages;

use App\Domain\Catalogo\Services\SkuGeneratorService;
use App\Filament\Resources\Modelos\ModeloResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateModelo extends CreateRecord
{
    protected static string $resource = ModeloResource::class;

    protected function afterCreate(): void
    {
        $nuevos = app(SkuGeneratorService::class)->sincronizarRango($this->record);

        if ($nuevos > 0) {
            Notification::make()->title("Se crearon {$nuevos} SKU(s), uno por talla")->success()->send();
        }
    }
}
