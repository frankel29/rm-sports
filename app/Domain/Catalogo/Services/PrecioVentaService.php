<?php

namespace App\Domain\Catalogo\Services;

use App\Domain\Catalogo\Models\Kit;
use App\Domain\Catalogo\Models\PrecioVenta;
use App\Domain\Catalogo\Models\Responsable;
use App\Domain\Catalogo\Models\Sku;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class PrecioVentaService
{
    public function __construct(
        private readonly ConfiguracionIvaService $configuracionIvaService,
    ) {}

    public function vigente(Sku|Kit $vendible, ?CarbonInterface $fecha = null): ?PrecioVenta
    {
        $fecha ??= Carbon::now();

        return $vendible->preciosVenta()
            ->whereDate('vigente_desde', '<=', $fecha)
            ->where(function ($query) use ($fecha) {
                $query->whereNull('vigente_hasta')->orWhereDate('vigente_hasta', '>=', $fecha);
            })
            ->orderByDesc('vigente_desde')
            ->first();
    }

    /**
     * Registra un nuevo precio vigente desde $vigenteDesde, cerrando la
     * vigencia del precio anterior del mismo SKU o kit (nunca se sobrescribe).
     */
    public function registrar(
        Sku|Kit $vendible,
        float $precio,
        bool $incluyeIva,
        CarbonInterface $vigenteDesde,
        Responsable $responsable,
        string $origen = 'CAPTURA',
        ?string $notas = null,
    ): PrecioVenta {
        $anterior = $this->vigente($vendible, $vigenteDesde);

        if ($anterior && $anterior->vigente_hasta === null) {
            $anterior->update(['vigente_hasta' => $vigenteDesde->copy()->subDay()]);
        }

        return $vendible->preciosVenta()->create([
            'precio' => $precio,
            'incluye_iva' => $incluyeIva,
            'vigente_desde' => $vigenteDesde,
            'responsable_id' => $responsable->id,
            'origen' => $origen,
            'notas' => $notas,
        ]);
    }

    public function calcularConIva(PrecioVenta $precio, ?CarbonInterface $fecha = null): float
    {
        if ($precio->incluye_iva) {
            return (float) $precio->precio;
        }

        $iva = $this->configuracionIvaService->vigente($fecha ?? $precio->vigente_desde);
        $porcentaje = $iva ? (float) $iva->porcentaje : 0.0;

        return round((float) $precio->precio * (1 + $porcentaje / 100), 2);
    }
}
