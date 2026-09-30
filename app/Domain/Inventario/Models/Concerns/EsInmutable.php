<?php

namespace App\Domain\Inventario\Models\Concerns;

use App\Domain\Inventario\Exceptions\MovimientoInmutableException;

trait EsInmutable
{
    public static function bootEsInmutable(): void
    {
        static::updating(function (): void {
            throw MovimientoInmutableException::paraOperacion('actualizar un movimiento');
        });

        static::deleting(function (): void {
            throw MovimientoInmutableException::paraOperacion('eliminar un movimiento');
        });
    }
}
