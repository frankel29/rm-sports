<?php

namespace App\Domain\Catalogo\Services;

use App\Domain\Catalogo\Models\ConfiguracionIva;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class ConfiguracionIvaService
{
    public function vigente(?CarbonInterface $fecha = null): ?ConfiguracionIva
    {
        $fecha ??= Carbon::now();

        return ConfiguracionIva::query()
            ->whereDate('vigente_desde', '<=', $fecha)
            ->where(function ($query) use ($fecha) {
                $query->whereNull('vigente_hasta')->orWhereDate('vigente_hasta', '>=', $fecha);
            })
            ->orderByDesc('vigente_desde')
            ->first();
    }

    /**
     * Registra un nuevo porcentaje de IVA vigente desde $vigenteDesde, cerrando
     * la vigencia del porcentaje anterior (nunca se sobrescribe un registro).
     */
    public function registrar(float $porcentaje, CarbonInterface $vigenteDesde): ConfiguracionIva
    {
        $anterior = $this->vigente($vigenteDesde);

        if ($anterior && $anterior->vigente_hasta === null) {
            $anterior->update(['vigente_hasta' => $vigenteDesde->copy()->subDay()]);
        }

        return ConfiguracionIva::create([
            'porcentaje' => $porcentaje,
            'vigente_desde' => $vigenteDesde,
        ]);
    }
}
