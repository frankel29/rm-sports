<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum RolUsuario: string implements HasLabel
{
    case ADMIN = 'ADMIN';
    case VENDEDOR = 'VENDEDOR';
    case TALLER = 'TALLER';

    public function getLabel(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrador',
            self::VENDEDOR => 'Vendedor',
            self::TALLER => 'Taller',
        };
    }
}
