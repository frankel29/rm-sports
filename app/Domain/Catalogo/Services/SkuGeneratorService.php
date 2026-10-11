<?php

namespace App\Domain\Catalogo\Services;

use App\Domain\Catalogo\Models\Modelo;
use App\Domain\Catalogo\Models\Sku;
use App\Domain\Catalogo\Models\Talla;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;

class SkuGeneratorService
{
    /**
     * Crea (si no existen) los SKUs de un modelo para todas las tallas entre
     * $tallaDesde y $tallaHasta (inclusive, según el campo "orden" de tallas).
     * Idempotente: si un SKU ya existe para esa combinación, se reutiliza.
     *
     * @return Collection<int, Sku>
     */
    public function generarParaModelo(Modelo $modelo, Talla $tallaDesde, Talla $tallaHasta): Collection
    {
        if ($tallaDesde->orden > $tallaHasta->orden) {
            throw new InvalidArgumentException('La talla desde no puede tener un orden mayor que la talla hasta.');
        }

        $tallas = Talla::query()
            ->whereBetween('orden', [$tallaDesde->orden, $tallaHasta->orden])
            ->orderBy('orden')
            ->get();

        return $tallas->map(fn (Talla $talla) => Sku::query()->firstOrCreate(
            ['modelo_id' => $modelo->id, 'talla_id' => $talla->id],
            ['codigo' => $this->generarCodigo($modelo, $talla)],
        ));
    }

    /**
     * Crea los SKUs que falten según el rango talla desde/hasta guardado en el
     * modelo. Nunca borra: si el rango se achica, los SKUs sobrantes quedan
     * (pueden tener movimientos). Devuelve cuántos SKUs nuevos creó.
     */
    public function sincronizarRango(Modelo $modelo): int
    {
        if (! $modelo->talla_desde_id || ! $modelo->talla_hasta_id) {
            return 0;
        }

        $desde = Talla::query()->findOrFail($modelo->talla_desde_id);
        $hasta = Talla::query()->findOrFail($modelo->talla_hasta_id);

        return $this->generarParaModelo($modelo, $desde, $hasta)
            ->filter(fn (Sku $sku) => $sku->wasRecentlyCreated)
            ->count();
    }

    public function generarCodigo(Modelo $modelo, Talla $talla): string
    {
        return sprintf('%s-%s', $modelo->codigo, $talla->codigo);
    }
}
