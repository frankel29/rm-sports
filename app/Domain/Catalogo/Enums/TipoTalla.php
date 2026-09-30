<?php

namespace App\Domain\Catalogo\Enums;

use Filament\Support\Contracts\HasLabel;

enum TipoTalla: string implements HasLabel
{
    case NUMERICA = 'NUMERICA';
    case LETRA = 'LETRA';

    public function getLabel(): string
    {
        return match ($this) {
            self::NUMERICA => 'Numérica',
            self::LETRA => 'Letra',
        };
    }
}
