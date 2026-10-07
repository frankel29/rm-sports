<?php

namespace App\Domain\Importacion;

use App\Domain\Importacion\DTO\ImportacionResultado;
use App\Domain\Importacion\Support\HojaImporter;
use App\Domain\Importacion\Support\LectorExcel;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ImportarPlantillaService
{
    /**
     * Orden fijo de hojas: cada una puede referenciar códigos definidos en
     * una hoja anterior (ver Instrucciones de la plantilla).
     */
    private const ORDEN_HOJAS = [
        'Colegios',
        'Prendas',
        'Tallas',
        'Proveedores',
        'Modelos',
        'Conjuntos',
        'Precios',
        'Materiales',
        'Consumo_por_prenda',
        'Costo_reventa',
        'Mano_de_obra',
        'Gastos_mensuales',
        'Inventario_prendas',
        'Inventario_materiales',
    ];

    /**
     * @param  array<string, HojaImporter>  $importadores  nombre de hoja => importador
     */
    public function __construct(
        private readonly LectorExcel $lector,
        private readonly array $importadores,
    ) {}

    /**
     * Ejecuta la importación completa dentro de una transacción que siempre
     * se revierte, para mostrar los errores por fila sin persistir nada.
     *
     * @return array<string, ImportacionResultado>
     */
    public function previsualizarTodo(string $rutaArchivo, User $user): array
    {
        DB::beginTransaction();

        try {
            return $this->ejecutarTodos($rutaArchivo, $user);
        } finally {
            DB::rollBack();
        }
    }

    /**
     * Ejecuta la importación completa y la confirma solo si ninguna hoja
     * tuvo errores; si alguna falló, revierte todo (todo o nada).
     *
     * @return array<string, ImportacionResultado>
     */
    public function confirmarTodo(string $rutaArchivo, User $user): array
    {
        DB::beginTransaction();

        $resultados = $this->ejecutarTodos($rutaArchivo, $user);

        $huboErrores = collect($resultados)->contains(fn (ImportacionResultado $r) => $r->conError());

        if ($huboErrores) {
            DB::rollBack();
        } else {
            DB::commit();
        }

        return $resultados;
    }

    /**
     * @return array<string, ImportacionResultado>
     */
    private function ejecutarTodos(string $rutaArchivo, User $user): array
    {
        $spreadsheet = $this->lector->cargar($rutaArchivo);
        $resultados = [];

        foreach (self::ORDEN_HOJAS as $hoja) {
            $importador = $this->importadores[$hoja] ?? null;

            if (! $importador) {
                continue;
            }

            $filas = $this->lector->extraerHoja($spreadsheet, $hoja);
            $resultados[$hoja] = $importador->importar($filas, $user);
        }

        return $resultados;
    }
}
