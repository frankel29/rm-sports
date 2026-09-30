<?php

namespace App\Domain\Inventario\Exceptions;

use RuntimeException;

class MovimientoInmutableException extends RuntimeException
{
    public static function paraOperacion(string $operacion): self
    {
        return new self("Los movimientos de inventario son inmutables: no se permite {$operacion}. Registre un movimiento de reverso.");
    }
}
