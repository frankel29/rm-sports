<?php

namespace App\Domain\Inventario\Enums;

use Filament\Support\Contracts\HasLabel;

enum TipoMovimientoMaterial: string implements HasLabel
{
    case INVENTARIO_INICIAL = 'INVENTARIO_INICIAL';
    case CONSUMO = 'CONSUMO';
    case COMPRA = 'COMPRA';
    case DEVOLUCION = 'DEVOLUCION';
    case AJUSTE = 'AJUSTE';
    case REVERSO = 'REVERSO';

    public function getLabel(): string
    {
        return match ($this) {
            self::INVENTARIO_INICIAL => 'Inventario inicial',
            self::CONSUMO => 'Consumo',
            self::COMPRA => 'Compra',
            self::DEVOLUCION => 'Devolución',
            self::AJUSTE => 'Ajuste',
            self::REVERSO => 'Reverso',
        };
    }
}
