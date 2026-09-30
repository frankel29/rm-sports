<?php

namespace App\Domain\Inventario\Services;

use App\Domain\Catalogo\Models\Kit;
use App\Domain\Catalogo\Models\Sku;
use App\Domain\Inventario\Enums\TipoMovimientoInventario;
use App\Domain\Inventario\Models\MovimientoInventario;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class InventarioService
{
    public function registrar(
        Sku $sku,
        TipoMovimientoInventario $tipo,
        float $cantidad,
        CarbonInterface $fechaHecho,
        User $user,
        ?Model $referencia = null,
        ?float $costoUnitario = null,
        ?string $ubicacion = null,
        string $origen = 'CAPTURA',
        ?string $nota = null,
    ): MovimientoInventario {
        return MovimientoInventario::create([
            'sku_id' => $sku->id,
            'tipo' => $tipo,
            'cantidad' => $cantidad,
            'costo_unitario' => $costoUnitario,
            'fecha_hecho' => $fechaHecho,
            'referencia_type' => $referencia?->getMorphClass(),
            'referencia_id' => $referencia?->getKey(),
            'responsable_id' => $sku->modelo->responsable_id,
            'ubicacion' => $ubicacion,
            'origen' => $origen,
            'user_id' => $user->id,
            'nota' => $nota,
        ]);
    }

    /**
     * Registra una salida por cada componente del kit (misma referencia para las
     * tres). $tallaPorComponente mapea modelo_id => talla_id elegida al vender.
     *
     * @param  array<int, int>  $tallaPorComponente
     * @return Collection<int, MovimientoInventario>
     */
    public function registrarSalidaKit(
        Kit $kit,
        array $tallaPorComponente,
        TipoMovimientoInventario $tipo,
        CarbonInterface $fechaHecho,
        User $user,
        ?Model $referencia = null,
        string $origen = 'CAPTURA',
        ?string $nota = null,
    ): Collection {
        $componentes = $kit->componentes()->get();

        $faltantes = $componentes->pluck('modelo_id')->diff(array_keys($tallaPorComponente));
        if ($faltantes->isNotEmpty()) {
            throw new InvalidArgumentException('Falta indicar la talla para todos los componentes del kit.');
        }

        return $componentes->map(function ($componente) use ($tallaPorComponente, $tipo, $fechaHecho, $user, $referencia, $origen, $nota) {
            $tallaId = $tallaPorComponente[$componente->modelo_id];

            $sku = Sku::query()
                ->where('modelo_id', $componente->modelo_id)
                ->where('talla_id', $tallaId)
                ->firstOrFail();

            return $this->registrar(
                sku: $sku,
                tipo: $tipo,
                cantidad: -1 * (float) $componente->cantidad,
                fechaHecho: $fechaHecho,
                user: $user,
                referencia: $referencia,
                origen: $origen,
                nota: $nota,
            );
        });
    }

    public function reversar(MovimientoInventario $movimiento, User $user, ?string $nota = null): MovimientoInventario
    {
        if ($movimiento->tipo === TipoMovimientoInventario::REVERSO) {
            throw new InvalidArgumentException('No se puede reversar un movimiento que ya es un reverso.');
        }

        $yaReversado = MovimientoInventario::query()
            ->where('movimiento_reversado_id', $movimiento->id)
            ->exists();

        if ($yaReversado) {
            throw new InvalidArgumentException('Este movimiento ya fue reversado anteriormente.');
        }

        return MovimientoInventario::create([
            'sku_id' => $movimiento->sku_id,
            'tipo' => TipoMovimientoInventario::REVERSO,
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

    public function stock(Sku $sku, ?CarbonInterface $fecha = null): float
    {
        return (float) MovimientoInventario::query()
            ->where('sku_id', $sku->id)
            ->when($fecha, fn ($query) => $query->whereDate('fecha_hecho', '<=', $fecha))
            ->sum('cantidad');
    }

    /**
     * Disponibilidad del kit = mínimo, entre sus componentes, de cuántos kits
     * completos permite el stock actual de cada componente en la talla elegida.
     *
     * @param  array<int, int>  $tallaPorComponente  modelo_id => talla_id
     */
    public function stockKit(Kit $kit, array $tallaPorComponente, ?CarbonInterface $fecha = null): float
    {
        $disponibilidades = $kit->componentes()->get()->map(function ($componente) use ($tallaPorComponente, $fecha) {
            $tallaId = $tallaPorComponente[$componente->modelo_id] ?? null;

            if (! $tallaId) {
                return 0.0;
            }

            $sku = Sku::query()
                ->where('modelo_id', $componente->modelo_id)
                ->where('talla_id', $tallaId)
                ->first();

            if (! $sku) {
                return 0.0;
            }

            return floor($this->stock($sku, $fecha) / (float) $componente->cantidad);
        });

        return (float) ($disponibilidades->min() ?? 0.0);
    }

    /**
     * Kardex ordenado cronológicamente con saldo acumulado.
     *
     * @return Collection<int, MovimientoInventario>
     */
    public function kardex(Sku $sku, ?CarbonInterface $desde = null, ?CarbonInterface $hasta = null): Collection
    {
        $movimientos = MovimientoInventario::query()
            ->where('sku_id', $sku->id)
            ->when($desde, fn ($query) => $query->whereDate('fecha_hecho', '>=', $desde))
            ->when($hasta, fn ($query) => $query->whereDate('fecha_hecho', '<=', $hasta))
            ->orderBy('fecha_hecho')
            ->orderBy('id')
            ->get();

        $saldo = $desde ? $this->stock($sku, $desde->copy()->subDay()) : 0.0;

        return $movimientos->map(function (MovimientoInventario $movimiento) use (&$saldo) {
            $saldo += (float) $movimiento->cantidad;
            $movimiento->setAttribute('saldo', $saldo);

            return $movimiento;
        });
    }

    public function tieneStockNegativo(Sku $sku): bool
    {
        return $this->stock($sku) < 0;
    }
}
