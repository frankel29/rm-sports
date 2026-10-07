<?php

namespace App\Domain\Importacion\Support;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class LectorExcel
{
    /**
     * Carga el archivo una sola vez. Léelo con esto y pase el resultado a
     * extraerHoja() por cada hoja: volver a parsear el archivo completo por
     * cada hoja es muy costoso en archivos grandes (miles de filas).
     *
     * Solo nos interesan los valores de las celdas, no su formato ni las
     * validaciones de datos (menús desplegables de la plantilla); leer en
     * modo "solo datos" evita parsear todo eso y es muchísimo más rápido en
     * archivos grandes.
     */
    public function cargar(string $rutaArchivo): Spreadsheet
    {
        $reader = IOFactory::createReaderForFile($rutaArchivo);
        $reader->setReadDataOnly(true);

        return $reader->load($rutaArchivo);
    }

    /**
     * Extrae una hoja ya cargada como filas asociativas por el nombre de
     * columna de la fila 1. Ignora filas totalmente vacías y filas de
     * ejemplo (donde la primera columna empieza con "EJ").
     *
     * @return array<int, array<string, mixed>> número de fila real del Excel => fila
     */
    public function extraerHoja(Spreadsheet $spreadsheet, string $nombreHoja): array
    {
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

    /**
     * Atajo para leer una sola hoja (carga el archivo completo igual: solo
     * conviene para uso puntual, no dentro de un bucle de varias hojas).
     *
     * @return array<int, array<string, mixed>>
     */
    public function leerHoja(string $rutaArchivo, string $nombreHoja): array
    {
        return $this->extraerHoja($this->cargar($rutaArchivo), $nombreHoja);
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
