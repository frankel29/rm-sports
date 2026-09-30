<?php

namespace App\Domain\Inventario\Enums;

use Filament\Support\Contracts\HasLabel;

enum TipoMovimientoInventario: string implements HasLabel
{
    case INVENTARIO_INICIAL = 'INVENTARIO_INICIAL';
    case PRODUCCION = 'PRODUCCION';
    case COMPRA = 'COMPRA';
    case VENTA = 'VENTA';
    case DEVOLUCION = 'DEVOLUCION';
    case CAMBIO_TALLA = 'CAMBIO_TALLA';
    case AJUSTE = 'AJUSTE';
    case REVERSO = 'REVERSO';

    public function getLabel(): string
    {
        return match ($this) {
            self::INVENTARIO_INICIAL => 'Inventario inicial',
            self::PRODUCCION => 'Producción',
            self::COMPRA => 'Compra',
            self::VENTA => 'Venta',
            self::DEVOLUCION => 'Devolución',
            self::CAMBIO_TALLA => 'Cambio de talla',
            self::AJUSTE => 'Ajuste',
            self::REVERSO => 'Reverso',
        };
    }
}
