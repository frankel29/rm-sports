<?php

namespace App\Domain\Costeo\Enums;

use Filament\Support\Contracts\HasLabel;

enum FormaPago: string implements HasLabel
{
    case POR_PRENDA = 'POR_PRENDA';
    case SUELDO_MENSUAL = 'SUELDO_MENSUAL';

    public function getLabel(): string
    {
        return match ($this) {
            self::POR_PRENDA => 'Por prenda',
            self::SUELDO_MENSUAL => 'Sueldo mensual',
        };
    }
}
