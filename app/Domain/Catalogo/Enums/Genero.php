<?php

namespace App\Domain\Catalogo\Enums;

use Filament\Support\Contracts\HasLabel;

enum Genero: string implements HasLabel
{
    case HOMBRE = 'HOMBRE';
    case MUJER = 'MUJER';
    case UNISEX = 'UNISEX';

    public function getLabel(): string
    {
        return match ($this) {
            self::HOMBRE => 'Hombre',
            self::MUJER => 'Mujer',
            self::UNISEX => 'Unisex',
        };
    }
}
