<?php

namespace App\Domain\Materiales\Enums;

use Filament\Support\Contracts\HasLabel;

enum TipoMaterial: string implements HasLabel
{
    case TELA = 'TELA';
    case INSUMO = 'INSUMO';
    case PRENDA_BASE = 'PRENDA_BASE';
    case OTRO = 'OTRO';

    public function getLabel(): string
    {
        return match ($this) {
            self::TELA => 'Tela',
            self::INSUMO => 'Insumo',
            self::PRENDA_BASE => 'Prenda base',
            self::OTRO => 'Otro',
        };
    }
}
