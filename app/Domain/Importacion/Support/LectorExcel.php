<?php

namespace App\Domain\Importacion\Support;

use PhpOffice\PhpSpreadsheet\IOFactory;

class LectorExcel
{
    /**
     * Lee una hoja del Excel y la devuelve como filas asociativas por el
     * nombre de columna de la fila 1. Ignora filas totalmente vacías y filas
     * de ejemplo (donde la primera columna empieza con "EJ").
     *
     * @return array<int, array<string, mixed>> número de fila real del Excel => fila
     */
    public function leerHoja(string $rutaArchivo, string $nombreHoja): array
    {
        $spreadsheet = IOFactory::load($rutaArchivo);

        if (! $spreadsheet->sheetNameExists($nombreHoja)) {
            return [];
        }

        $hoja = $spreadsheet->getSheetByName($nombreHoja);
        $filas = $hoja->toArray(null, true, true, true);

        $encabezados = $filas[1] ?? [];
        unset($filas[1]);

        $resultado = [];

        foreach ($filas as $numeroFila => $fila) {
            $registro = [];

            foreach ($encabezados as $columna => $nombreColumna) {
                if ($nombreColumna === null || $nombreColumna === '') {
                    continue;
                }

                $registro[$nombreColumna] = $fila[$columna] ?? null;
            }

            if ($this->filaVacia($registro) || $this->esFilaDeEjemplo($registro)) {
                continue;
            }

            $resultado[$numeroFila] = $registro;
        }

        return $resultado;
    }

    private function filaVacia(array $registro): bool
    {
        foreach ($registro as $valor) {
            if ($valor !== null && $valor !== '') {
                return false;
            }
        }

        return true;
    }

    private function esFilaDeEjemplo(array $registro): bool
    {
        $primerValor = array_values($registro)[0] ?? null;

        return is_string($primerValor) && str_starts_with($primerValor, 'EJ');
    }
}
