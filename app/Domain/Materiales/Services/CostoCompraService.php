<?php

namespace App\Domain\Materiales\Services;

use App\Domain\Catalogo\Models\Modelo;
use App\Domain\Catalogo\Models\Responsable;
use App\Domain\Catalogo\Models\Talla;
use App\Domain\Materiales\Models\CostoCompra;
use App\Domain\Materiales\Models\Proveedor;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class CostoCompraService
{
    /**
     * Busca el costo vigente para la talla específica; si no existe, cae al
     * costo con talla NULL (aplica a todas las tallas del modelo).
     */
    public function vigente(Modelo $modelo, ?Talla $talla = null, ?CarbonInterface $fecha = null): ?CostoCompra
    {
        $fecha ??= Carbon::now();

        if ($talla) {
            $especifico = $this->buscar($modelo, $talla->id, $fecha);
            if ($especifico) {
                return $especifico;
            }
        }

        return $this->buscar($modelo, null, $fecha);
    }

    /**
     * Registra un nuevo costo de compra vigente, cerrando la vigencia del
     * costo anterior para la misma talla (o talla NULL) del modelo.
     */
    public function registrar(
        Modelo $modelo,
        float $costo,
        CarbonInterface $vigenteDesde,
        Responsable $responsable,
        ?Talla $talla = null,
        ?Proveedor $proveedor = null,
        string $origen = 'CAPTURA',
        ?string $notas = null,
    ): CostoCompra {
        if ($modelo->esFabricado()) {
            throw new InvalidArgumentException('Un modelo FABRICADO no tiene costo de compra; use el BOM.');
        }

        $anterior = $this->buscar($modelo, $talla?->id, $vigenteDesde);

        if ($anterior && $anterior->vigente_hasta === null) {
            $anterior->update(['vigente_hasta' => $vigenteDesde->copy()->subDay()]);
        }

        return CostoCompra::create([
            'modelo_id' => $modelo->id,
            'talla_id' => $talla?->id,
            'costo' => $costo,
            'proveedor_id' => $proveedor?->id,
            'vigente_desde' => $vigenteDesde,
            'responsable_id' => $responsable->id,
            'origen' => $origen,
            'notas' => $notas,
        ]);
    }

    private function buscar(Modelo $modelo, ?int $tallaId, CarbonInterface $fecha): ?CostoCompra
    {
        return CostoCompra::query()
            ->where('modelo_id', $modelo->id)
            ->when($tallaId === null, fn ($q) => $q->whereNull('talla_id'), fn ($q) => $q->where('talla_id', $tallaId))
            ->whereDate('vigente_desde', '<=', $fecha)
            ->where(function ($query) use ($fecha) {
                $query->whereNull('vigente_hasta')->orWhereDate('vigente_hasta', '>=', $fecha);
            })
            ->orderByDesc('vigente_desde')
            ->first();
    }
}
