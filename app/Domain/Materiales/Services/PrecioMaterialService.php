<?php

namespace App\Domain\Materiales\Services;

use App\Domain\Materiales\Models\Material;
use App\Domain\Materiales\Models\PrecioMaterial;
use App\Domain\Materiales\Models\Proveedor;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class PrecioMaterialService
{
    public function vigente(Material $material, ?CarbonInterface $fecha = null): ?PrecioMaterial
    {
        $fecha ??= Carbon::now();

        return $material->precios()
            ->whereDate('vigente_desde', '<=', $fecha)
            ->where(function ($query) use ($fecha) {
                $query->whereNull('vigente_hasta')->orWhereDate('vigente_hasta', '>=', $fecha);
            })
            ->orderByDesc('vigente_desde')
            ->first();
    }

    /**
     * Registra un nuevo precio vigente para el material, cerrando la vigencia
     * del precio anterior (nunca se sobrescribe).
     */
    public function registrar(
        Material $material,
        float $precio,
        CarbonInterface $vigenteDesde,
        ?Proveedor $proveedor = null,
        string $origen = 'CAPTURA',
        ?string $notas = null,
    ): PrecioMaterial {
        $anterior = $this->vigente($material, $vigenteDesde);

        if ($anterior && $anterior->vigente_hasta === null) {
            $anterior->update(['vigente_hasta' => $vigenteDesde->copy()->subDay()]);
        }

        return $material->precios()->create([
            'precio' => $precio,
            'proveedor_id' => $proveedor?->id,
            'vigente_desde' => $vigenteDesde,
            'origen' => $origen,
            'notas' => $notas,
        ]);
    }
}
