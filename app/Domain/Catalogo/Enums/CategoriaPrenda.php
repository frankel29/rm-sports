<?php

namespace App\Domain\Catalogo\Enums;

use Filament\Support\Contracts\HasLabel;

enum CategoriaPrenda: string implements HasLabel
{
    case ESCOLAR = 'ESCOLAR';
    case DEPORTIVO = 'DEPORTIVO';
    case OTRO = 'OTRO';

    public function getLabel(): string
    {
        return match ($this) {
            self::ESCOLAR => 'Escolar',
            self::DEPORTIVO => 'Deportivo',
            self::OTRO => 'Otro',
        };
    }
}
