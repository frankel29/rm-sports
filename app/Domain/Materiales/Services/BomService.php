<?php

namespace App\Domain\Materiales\Services;

use App\Domain\Catalogo\Models\Modelo;
use App\Domain\Catalogo\Models\Talla;
use App\Domain\Materiales\Models\BomLinea;
use App\Domain\Materiales\Models\Material;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class BomService
{
    public function __construct(
        private readonly PrecioMaterialService $precioMaterialService,
    ) {}

    /**
     * Crea o reemplaza la línea de BOM para (modelo, material, talla).
     * $talla = null significa "aplica a todas las tallas".
     */
    public function agregarLinea(
        Modelo $modelo,
        Material $material,
        float $cantidad,
        ?Talla $talla = null,
        ?string $notas = null,
    ): BomLinea {
        if ($modelo->esComprado()) {
            throw new InvalidArgumentException('Un modelo COMPRADO no puede tener líneas de BOM.');
        }

        $linea = BomLinea::query()
            ->where('modelo_id', $modelo->id)
            ->where('material_id', $material->id)
            ->when($talla, fn ($q) => $q->where('talla_id', $talla->id), fn ($q) => $q->whereNull('talla_id'))
            ->first();

        $atributos = [
            'cantidad' => $cantidad,
            'unidad' => $material->unidad->value,
            'notas' => $notas,
        ];

        if ($linea) {
            $linea->update($atributos);

            return $linea;
        }

        return BomLinea::create([
            'modelo_id' => $modelo->id,
            'material_id' => $material->id,
            'talla_id' => $talla?->id,
            ...$atributos,
        ]);
    }

    /**
     * Resuelve una línea por material para la talla dada: prefiere la línea
     * específica de esa talla y cae a la línea NULL (todas las tallas).
     *
     * @return Collection<int, BomLinea>
     */
    public function consumoPara(Modelo $modelo, Talla $talla): Collection
    {
        return BomLinea::query()
            ->where('modelo_id', $modelo->id)
            ->where(function ($query) use ($talla) {
                $query->where('talla_id', $talla->id)->orWhereNull('talla_id');
            })
            ->get()
            ->groupBy('material_id')
            ->map(fn (Collection $porMaterial) => $porMaterial->first(fn (BomLinea $linea) => $linea->talla_id === $talla->id)
                ?? $porMaterial->first())
            ->values();
    }

    public function costoMateriales(Modelo $modelo, Talla $talla, ?CarbonInterface $fecha = null): float
    {
        return $this->consumoPara($modelo, $talla)
            ->sum(function (BomLinea $linea) use ($fecha) {
                $precio = $this->precioMaterialService->vigente($linea->material, $fecha);

                return (float) $linea->cantidad * ($precio ? (float) $precio->precio : 0.0);
            });
    }
}
