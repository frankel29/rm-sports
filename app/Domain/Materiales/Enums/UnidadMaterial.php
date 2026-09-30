<?php

namespace App\Domain\Materiales\Enums;

use Filament\Support\Contracts\HasLabel;

enum UnidadMaterial: string implements HasLabel
{
    case M = 'M';
    case UNIDAD = 'UNIDAD';
    case KG = 'KG';
    case ROLLO = 'ROLLO';

    public function getLabel(): string
    {
        return match ($this) {
            self::M => 'Metros',
            self::UNIDAD => 'Unidad',
            self::KG => 'Kilogramos',
            self::ROLLO => 'Rollo',
        };
    }
}
