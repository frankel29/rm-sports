<?php

namespace App\Domain\Importacion\Support;

use DateTimeInterface;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use Throwable;

/**
 * Helpers de parseo de celdas compartidos por todos los importadores de hoja.
 */
trait ConvierteCeldas
{
    protected function texto(mixed $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $texto = trim((string) $valor);

        return $texto === '' ? null : $texto;
    }

    protected function textoObligatorio(mixed $valor, string $campo): string
    {
        $texto = $this->texto($valor);

        if ($texto === null) {
            throw new InvalidArgumentException("El campo \"{$campo}\" es obligatorio.");
        }

        return $texto;
    }

    protected function numero(mixed $valor): ?float
    {
        $texto = $this->texto($valor);

        return $texto === null ? null : (float) str_replace(',', '.', $texto);
    }

    protected function numeroObligatorio(mixed $valor, string $campo): float
    {
        $numero = $this->numero($valor);

        if ($numero === null) {
            throw new InvalidArgumentException("El campo \"{$campo}\" es obligatorio y debe ser numérico.");
        }

        return $numero;
    }

    protected function entero(mixed $valor): ?int
    {
        $numero = $this->numero($valor);

        return $numero === null ? null : (int) $numero;
    }

    protected function booleano(mixed $valor): bool
    {
        return strtoupper((string) $this->texto($valor)) === 'SI';
    }

    protected function fecha(mixed $valor, string $campo): Carbon
    {
        if ($valor instanceof DateTimeInterface) {
            return Carbon::instance($valor)->startOfDay();
        }

        $texto = $this->textoObligatorio($valor, $campo);

        try {
            return Carbon::createFromFormat('d/m/Y', $texto)->startOfDay();
        } catch (Throwable) {
            throw new InvalidArgumentException("El campo \"{$campo}\" no tiene el formato de fecha dd/mm/aaaa: \"{$texto}\".");
        }
    }

    protected function fechaOpcional(mixed $valor, string $campo): ?Carbon
    {
        if (! ($valor instanceof DateTimeInterface) && $this->texto($valor) === null) {
            return null;
        }

        return $this->fecha($valor, $campo);
    }
}
