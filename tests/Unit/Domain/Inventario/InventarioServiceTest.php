<?php

use App\Domain\Catalogo\Models\Colegio;
use App\Domain\Catalogo\Models\Kit;
use App\Domain\Catalogo\Models\KitComponente;
use App\Domain\Catalogo\Models\Modelo;
use App\Domain\Catalogo\Models\Prenda;
use App\Domain\Catalogo\Models\Responsable;
use App\Domain\Catalogo\Models\Sku;
use App\Domain\Catalogo\Models\Talla;
use App\Domain\Inventario\Enums\TipoMovimientoInventario;
use App\Domain\Inventario\Exceptions\MovimientoInmutableException;
use App\Domain\Inventario\Models\MovimientoInventario;
use App\Domain\Inventario\Services\InventarioService;
use App\Models\User;
use Illuminate\Support\Carbon;

function crearSkuDePrueba(string $codigo = 'MOD-1'): Sku
{
    $responsable = Responsable::query()->firstOrCreate(['codigo' => 'RM'], ['nombre' => 'RM']);
    $colegio = Colegio::query()->firstOrCreate(['codigo' => 'COL'], ['nombre' => 'Colegio de prueba']);
    $prenda = Prenda::query()->firstOrCreate(['codigo' => 'PRENDA'], ['nombre' => 'Prenda', 'categoria' => 'DEPORTIVO']);
    $talla = Talla::query()->firstOrCreate(['codigo' => 'U'], ['orden' => 1, 'tipo' => 'LETRA']);

    $modelo = Modelo::query()->create([
        'codigo' => $codigo,
        'colegio_id' => $colegio->id,
        'prenda_id' => $prenda->id,
        'tipo_abastecimiento' => 'FABRICADO',
        'responsable_id' => $responsable->id,
    ]);

    return Sku::query()->create([
        'modelo_id' => $modelo->id,
        'talla_id' => $talla->id,
        'codigo' => "{$codigo}-{$talla->codigo}",
    ]);
}

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->inventario = app(InventarioService::class);
});

it('el stock es la suma de los movimientos', function () {
    $sku = crearSkuDePrueba();

    $this->inventario->registrar($sku, TipoMovimientoInventario::INVENTARIO_INICIAL, 10, now(), $this->user);
    $this->inventario->registrar($sku, TipoMovimientoInventario::VENTA, -3, now(), $this->user);
    $this->inventario->registrar($sku, TipoMovimientoInventario::PRODUCCION, 5, now(), $this->user);

    expect($this->inventario->stock($sku))->toBe(12.0);
});

it('permite reversar un movimiento generando su opuesto', function () {
    $sku = crearSkuDePrueba();

    $venta = $this->inventario->registrar($sku, TipoMovimientoInventario::VENTA, -4, now(), $this->user);
    expect($this->inventario->stock($sku))->toBe(-4.0);

    $reverso = $this->inventario->reversar($venta, $this->user, 'Error de digitación');

    expect($reverso->tipo)->toBe(TipoMovimientoInventario::REVERSO)
        ->and((float) $reverso->cantidad)->toBe(4.0)
        ->and($reverso->movimiento_reversado_id)->toBe($venta->id)
        ->and($this->inventario->stock($sku))->toBe(0.0);
});

it('no permite reversar dos veces el mismo movimiento', function () {
    $sku = crearSkuDePrueba();
    $venta = $this->inventario->registrar($sku, TipoMovimientoInventario::VENTA, -2, now(), $this->user);

    $this->inventario->reversar($venta, $this->user);

    expect(fn () => $this->inventario->reversar($venta, $this->user))
        ->toThrow(InvalidArgumentException::class);
});

it('no permite reversar un reverso', function () {
    $sku = crearSkuDePrueba();
    $venta = $this->inventario->registrar($sku, TipoMovimientoInventario::VENTA, -2, now(), $this->user);
    $reverso = $this->inventario->reversar($venta, $this->user);

    expect(fn () => $this->inventario->reversar($reverso, $this->user))
        ->toThrow(InvalidArgumentException::class);
});

it('la venta de un kit genera una salida por cada componente con tallas mixtas', function () {
    $skuChompa = crearSkuDePrueba('CHOMPA');
    $skuPantalon = crearSkuDePrueba('PANTALON');

    $this->inventario->registrar($skuChompa, TipoMovimientoInventario::INVENTARIO_INICIAL, 5, now(), $this->user);
    $this->inventario->registrar($skuPantalon, TipoMovimientoInventario::INVENTARIO_INICIAL, 5, now(), $this->user);

    $kit = Kit::query()->create([
        'codigo' => 'KIT-EXTERIOR',
        'nombre' => 'Exterior',
        'responsable_id' => Responsable::query()->where('codigo', 'RM')->value('id'),
    ]);
    KitComponente::query()->create(['kit_id' => $kit->id, 'modelo_id' => $skuChompa->modelo_id, 'cantidad' => 1]);
    KitComponente::query()->create(['kit_id' => $kit->id, 'modelo_id' => $skuPantalon->modelo_id, 'cantidad' => 1]);

    // Talla mixta: chompa en una talla, pantalón en otra (SKUs distintos, mismo kit).
    $tallaOtra = Talla::query()->create(['codigo' => 'U2', 'orden' => 2, 'tipo' => 'LETRA']);
    $skuPantalonOtraTalla = Sku::query()->create([
        'modelo_id' => $skuPantalon->modelo_id,
        'talla_id' => $tallaOtra->id,
        'codigo' => 'PANTALON-U2',
    ]);
    $this->inventario->registrar($skuPantalonOtraTalla, TipoMovimientoInventario::INVENTARIO_INICIAL, 3, now(), $this->user);

    $movimientos = $this->inventario->registrarSalidaKit(
        kit: $kit,
        tallaPorComponente: [
            $skuChompa->modelo_id => $skuChompa->talla_id,
            $skuPantalon->modelo_id => $tallaOtra->id,
        ],
        tipo: TipoMovimientoInventario::VENTA,
        fechaHecho: now(),
        user: $this->user,
        referencia: $kit,
    );

    expect($movimientos)->toHaveCount(2)
        ->and($this->inventario->stock($skuChompa))->toBe(4.0)
        ->and($this->inventario->stock($skuPantalonOtraTalla))->toBe(2.0)
        ->and($this->inventario->stock($skuPantalon))->toBe(5.0); // talla original intacta

    // Ambas salidas comparten la misma referencia (el mismo kit vendido).
    expect($movimientos->pluck('referencia_id')->unique()->all())->toBe([$kit->id])
        ->and($movimientos->pluck('referencia_type')->unique()->first())->toBe($kit->getMorphClass());
});

it('calcula el stock a una fecha determinada, ignorando movimientos posteriores', function () {
    $sku = crearSkuDePrueba();

    $this->inventario->registrar($sku, TipoMovimientoInventario::INVENTARIO_INICIAL, 10, Carbon::parse('2026-01-01'), $this->user);
    $this->inventario->registrar($sku, TipoMovimientoInventario::VENTA, -3, Carbon::parse('2026-01-10'), $this->user);
    $this->inventario->registrar($sku, TipoMovimientoInventario::VENTA, -2, Carbon::parse('2026-02-01'), $this->user);

    expect($this->inventario->stock($sku, Carbon::parse('2026-01-15')))->toBe(7.0)
        ->and($this->inventario->stock($sku))->toBe(5.0);
});

it('permite stock negativo y lo reporta como alerta', function () {
    $sku = crearSkuDePrueba();

    $this->inventario->registrar($sku, TipoMovimientoInventario::VENTA, -5, now(), $this->user);

    expect($this->inventario->stock($sku))->toBe(-5.0)
        ->and($this->inventario->tieneStockNegativo($sku))->toBeTrue();
});

it('el kardex trae saldo acumulado en orden cronológico', function () {
    $sku = crearSkuDePrueba();

    $this->inventario->registrar($sku, TipoMovimientoInventario::INVENTARIO_INICIAL, 10, Carbon::parse('2026-01-01'), $this->user);
    $this->inventario->registrar($sku, TipoMovimientoInventario::VENTA, -3, Carbon::parse('2026-01-05'), $this->user);

    $kardex = $this->inventario->kardex($sku);

    expect($kardex)->toHaveCount(2)
        ->and((float) $kardex[0]->saldo)->toBe(10.0)
        ->and((float) $kardex[1]->saldo)->toBe(7.0);
});

it('los movimientos son inmutables a nivel de modelo Eloquent', function () {
    $sku = crearSkuDePrueba();
    $movimiento = $this->inventario->registrar($sku, TipoMovimientoInventario::AJUSTE, 1, now(), $this->user);

    expect(fn () => $movimiento->update(['cantidad' => 99]))
        ->toThrow(MovimientoInmutableException::class);

    expect(fn () => $movimiento->delete())
        ->toThrow(MovimientoInmutableException::class);

    expect((float) $movimiento->fresh()->cantidad)->toBe(1.0);
});

it('los movimientos son inmutables también a nivel de base de datos (trigger)', function () {
    $sku = crearSkuDePrueba();
    $movimiento = $this->inventario->registrar($sku, TipoMovimientoInventario::AJUSTE, 1, now(), $this->user);

    expect(fn () => MovimientoInventario::query()->where('id', $movimiento->id)->update(['cantidad' => 99]))
        ->toThrow(Exception::class);
});
