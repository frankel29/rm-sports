<?php

namespace App\Domain\Importacion\Support;

use App\Domain\Importacion\DTO\ImportacionResultado;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Un importador por hoja del Excel. Cada fila se procesa en su propio
 * savepoint: si una fila falla, no aborta la transacción completa de la
 * importación (necesario en Postgres, donde un error dentro de una
 * transacción la deja abortada hasta el rollback).
 */
abstract class HojaImporter
{
    abstract public function nombreHoja(): string;

    /**
     * Procesa una fila ya parseada (claves = nombres de columna de la hoja).
     * Debe lanzar una excepción con mensaje claro si la fila es inválida.
     *
     * @param  array<string, mixed>  $fila
     */
    abstract protected function importarFila(array $fila, User $user): void;

    /**
     * @param  array<int, array<string, mixed>>  $filas  número de fila del Excel => fila
     */
    public function importar(array $filas, User $user): ImportacionResultado
    {
        $filasValidas = 0;
        $errores = [];

        foreach ($filas as $numeroFila => $fila) {
            try {
                DB::transaction(fn () => $this->importarFila($fila, $user));
                $filasValidas++;
            } catch (Throwable $e) {
                $errores[$numeroFila] = $e->getMessage();
            }
        }

        return new ImportacionResultado(
            hoja: $this->nombreHoja(),
            filasValidas: $filasValidas,
            filasConError: count($errores),
            errores: $errores,
        );
    }
}
