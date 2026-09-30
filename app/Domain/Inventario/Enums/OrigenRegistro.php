<?php

namespace App\Domain\Inventario\Enums;

use Filament\Support\Contracts\HasLabel;

enum OrigenRegistro: string implements HasLabel
{
    case CAPTURA = 'CAPTURA';
    case SRI_HISTORICO = 'SRI_HISTORICO';
    case IMPORTACION_MANUAL = 'IMPORTACION_MANUAL';

    public function getLabel(): string
    {
        return match ($this) {
            self::CAPTURA => 'Captura',
            self::SRI_HISTORICO => 'Histórico SRI',
            self::IMPORTACION_MANUAL => 'Importación manual',
        };
    }
}
