<?php

namespace App\Domain\Catalogo\Enums;

use Filament\Support\Contracts\HasLabel;

enum TipoAbastecimiento: string implements HasLabel
{
    case FABRICADO = 'FABRICADO';
    case COMPRADO = 'COMPRADO';

    public function getLabel(): string
    {
        return match ($this) {
            self::FABRICADO => 'Fabricado',
            self::COMPRADO => 'Comprado (reventa)',
        };
    }
}
