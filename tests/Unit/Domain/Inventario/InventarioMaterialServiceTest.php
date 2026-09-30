<?php

use App\Domain\Catalogo\Models\Responsable;
use App\Domain\Inventario\Enums\TipoMovimientoMaterial;
use App\Domain\Inventario\Exceptions\MovimientoInmutableException;
use App\Domain\Inventario\Models\MovimientoMaterial;
use App\Domain\Inventario\Services\InventarioMaterialService;
use App\Domain\Materiales\Models\Material;
use App\Models\User;
use Illuminate\Support\Carbon;

function crearMaterialDePrueba(string $codigo = 'TELA-1'): Material
{
    $responsable = Responsable::query()->firstOrCreate(['codigo' => 'RM'], ['nombre' => 'RM']);

    return Material::query()->create([
        'codigo' => $codigo,
        'nombre' => 'Material de prueba',
        'tipo' => 'TELA',
        'unidad' => 'M',
        'responsable_id' => $responsable->id,
    ]);
}

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->inventarioMaterial = app(InventarioMaterialService::class);
});

it('el stock de materiales es la suma de los movimientos', function () {
    $material = crearMaterialDePrueba();

    $this->inventarioMaterial->registrar($material, TipoMovimientoMaterial::INVENTARIO_INICIAL, 50, now(), $this->user);
    $this->inventarioMaterial->registrar($material, TipoMovimientoMaterial::CONSUMO, -12.5, now(), $this->user);

    expect($this->inventarioMaterial->stock($material))->toBe(37.5);
});

it('permite reversar un movimiento de material', function () {
    $material = crearMaterialDePrueba();
    $consumo = $this->inventarioMaterial->registrar($material, TipoMovimientoMaterial::CONSUMO, -10, now(), $this->user);

    $reverso = $this->inventarioMaterial->reversar($consumo, $this->user);

    expect((float) $reverso->cantidad)->toBe(10.0)
        ->and($this->inventarioMaterial->stock($material))->toBe(0.0);

    expect(fn () => $this->inventarioMaterial->reversar($consumo, $this->user))
        ->toThrow(InvalidArgumentException::class);
});

it('calcula el stock de materiales a una fecha determinada', function () {
    $material = crearMaterialDePrueba();

    $this->inventarioMaterial->registrar($material, TipoMovimientoMaterial::INVENTARIO_INICIAL, 100, Carbon::parse('2026-01-01'), $this->user);
    $this->inventarioMaterial->registrar($material, TipoMovimientoMaterial::CONSUMO, -30, Carbon::parse('2026-02-01'), $this->user);

    expect($this->inventarioMaterial->stock($material, Carbon::parse('2026-01-15')))->toBe(100.0)
        ->and($this->inventarioMaterial->stock($material))->toBe(70.0);
});

it('permite stock negativo de materiales y lo reporta como alerta', function () {
    $material = crearMaterialDePrueba();
    $this->inventarioMaterial->registrar($material, TipoMovimientoMaterial::CONSUMO, -5, now(), $this->user);

    expect($this->inventarioMaterial->tieneStockNegativo($material))->toBeTrue();
});

it('los movimientos de material son inmutables a nivel de modelo y de base de datos', function () {
    $material = crearMaterialDePrueba();
    $movimiento = $this->inventarioMaterial->registrar($material, TipoMovimientoMaterial::AJUSTE, 1, now(), $this->user);

    expect(fn () => $movimiento->delete())->toThrow(MovimientoInmutableException::class);

    expect(fn () => MovimientoMaterial::query()->where('id', $movimiento->id)->delete())
        ->toThrow(Exception::class);
});
