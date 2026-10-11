<?php

namespace App\Filament\Resources\Modelos\Pages;

use App\Domain\Catalogo\Services\SkuGeneratorService;
use App\Filament\Resources\Modelos\ModeloResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditModelo extends EditRecord
{
    protected static string $resource = ModeloResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $nuevos = app(SkuGeneratorService::class)->sincronizarRango($this->record);

        if ($nuevos > 0) {
            Notification::make()->title("Se agregaron {$nuevos} SKU(s) por el nuevo rango de tallas")->success()->send();
        }
    }
}
