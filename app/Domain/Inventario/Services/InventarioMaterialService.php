<?php

namespace App\Domain\Inventario\Services;

use App\Domain\Inventario\Enums\TipoMovimientoMaterial;
use App\Domain\Inventario\Models\MovimientoMaterial;
use App\Domain\Materiales\Models\Material;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class InventarioMaterialService
{
    public function registrar(
        Material $material,
        TipoMovimientoMaterial $tipo,
        float $cantidad,
        CarbonInterface $fechaHecho,
        User $user,
        ?Model $referencia = null,
        ?float $costoUnitario = null,
        ?string $ubicacion = null,
        string $origen = 'CAPTURA',
        ?string $nota = null,
    ): MovimientoMaterial {
        return MovimientoMaterial::create([
            'material_id' => $material->id,
            'tipo' => $tipo,
            'cantidad' => $cantidad,
            'costo_unitario' => $costoUnitario,
            'fecha_hecho' => $fechaHecho,
            'referencia_type' => $referencia?->getMorphClass(),
            'referencia_id' => $referencia?->getKey(),
            'responsable_id' => $material->responsable_id,
            'ubicacion' => $ubicacion,
            'origen' => $origen,
            'user_id' => $user->id,
            'nota' => $nota,
        ]);
    }

    public function reversar(MovimientoMaterial $movimiento, User $user, ?string $nota = null): MovimientoMaterial
    {
        if ($movimiento->tipo === TipoMovimientoMaterial::REVERSO) {
            throw new InvalidArgumentException('No se puede reversar un movimiento que ya es un reverso.');
        }

        $yaReversado = MovimientoMaterial::query()
            ->where('movimiento_reversado_id', $movimiento->id)
            ->exists();

        if ($yaReversado) {
            throw new InvalidArgumentException('Este movimiento ya fue reversado anteriormente.');
        }

        return MovimientoMaterial::create([
            'material_id' => $movimiento->material_id,
            'tipo' => TipoMovimientoMaterial::REVERSO,
            'cantidad' => -1 * (float) $movimiento->cantidad,
            'costo_unitario' => $movimiento->costo_unitario,
            'fecha_hecho' => now(),
            'referencia_type' => $movimiento->referencia_type,
            'referencia_id' => $movimiento->referencia_id,
            'responsable_id' => $movimiento->responsable_id,
            'ubicacion' => $movimiento->ubicacion,
            'origen' => $movimiento->origen,
            'user_id' => $user->id,
            'nota' => $nota,
            'movimiento_reversado_id' => $movimiento->id,
        ]);
    }

    public function stock(Material $material, ?CarbonInterface $fecha = null): float
    {
        return (float) MovimientoMaterial::query()
            ->where('material_id', $material->id)
            ->when($fecha, fn ($query) => $query->whereDate('fecha_hecho', '<=', $fecha))
            ->sum('cantidad');
    }

    /**
     * Kardex ordenado cronológicamente con saldo acumulado.
     *
     * @return Collection<int, MovimientoMaterial>
     */
    public function kardex(Material $material, ?CarbonInterface $desde = null, ?CarbonInterface $hasta = null): Collection
    {
        $movimientos = MovimientoMaterial::query()
            ->where('material_id', $material->id)
            ->when($desde, fn ($query) => $query->whereDate('fecha_hecho', '>=', $desde))
            ->when($hasta, fn ($query) => $query->whereDate('fecha_hecho', '<=', $hasta))
            ->orderBy('fecha_hecho')
            ->orderBy('id')
            ->get();

        $saldo = $desde ? $this->stock($material, $desde->copy()->subDay()) : 0.0;

        return $movimientos->map(function (MovimientoMaterial $movimiento) use (&$saldo) {
            $saldo += (float) $movimiento->cantidad;
            $movimiento->setAttribute('saldo', $saldo);

            return $movimiento;
        });
    }

    public function tieneStockNegativo(Material $material): bool
    {
        return $this->stock($material) < 0;
    }
}
