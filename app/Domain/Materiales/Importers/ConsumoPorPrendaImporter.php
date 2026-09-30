<?php

namespace App\Domain\Materiales\Importers;

use App\Domain\Catalogo\Models\Modelo;
use App\Domain\Catalogo\Models\Talla;
use App\Domain\Importacion\Support\ConvierteCeldas;
use App\Domain\Importacion\Support\HojaImporter;
use App\Domain\Materiales\Models\Material;
use App\Domain\Materiales\Services\BomService;
use App\Models\User;
use InvalidArgumentException;

class ConsumoPorPrendaImporter extends HojaImporter
{
    use ConvierteCeldas;

    public function __construct(
        private readonly BomService $bomService,
    ) {}

    public function nombreHoja(): string
    {
        return 'Consumo_por_prenda';
    }

    protected function importarFila(array $fila, User $user): void
    {
        $codigoModelo = $this->textoObligatorio($fila['codigo_modelo'] ?? null, 'codigo_modelo');
        $modelo = Modelo::query()->where('codigo', $codigoModelo)->first();
        if (! $modelo) {
            throw new InvalidArgumentException("codigo_modelo \"{$codigoModelo}\" no existe.");
        }

        $codigoMaterial = $this->textoObligatorio($fila['codigo_material'] ?? null, 'codigo_material');
        $material = Material::query()->where('codigo', $codigoMaterial)->first();
        if (! $material) {
            throw new InvalidArgumentException("codigo_material \"{$codigoMaterial}\" no existe.");
        }

        $talla = null;
        $codigoTalla = $this->texto($fila['talla'] ?? null);
        if ($codigoTalla) {
            $talla = Talla::query()->where('codigo', $codigoTalla)->first();
            if (! $talla) {
                throw new InvalidArgumentException("talla \"{$codigoTalla}\" no existe.");
            }
        }

        $this->bomService->agregarLinea(
            modelo: $modelo,
            material: $material,
            cantidad: $this->numeroObligatorio($fila['cantidad'] ?? null, 'cantidad'),
            talla: $talla,
            notas: $this->texto($fila['notas'] ?? null),
        );
    }
}
