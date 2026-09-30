<?php

namespace App\Domain\Inventario\Enums;

use Filament\Support\Contracts\HasLabel;

enum Ubicacion: string implements HasLabel
{
    case LOCAL = 'LOCAL';
    case TALLER = 'TALLER';
    case BODEGA = 'BODEGA';
    case OTRO = 'OTRO';

    public function getLabel(): string
    {
        return match ($this) {
            self::LOCAL => 'Local',
            self::TALLER => 'Taller',
            self::BODEGA => 'Bodega',
            self::OTRO => 'Otro',
        };
    }
}
