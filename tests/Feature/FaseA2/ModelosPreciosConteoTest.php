<?php

use App\Domain\Catalogo\Models\Colegio;
use App\Domain\Catalogo\Models\Modelo;
use App\Domain\Catalogo\Models\PrecioVenta;
use App\Domain\Catalogo\Models\Prenda;
use App\Domain\Catalogo\Models\Responsable;
use App\Domain\Catalogo\Models\Talla;
use App\Domain\Inventario\Enums\TipoMovimientoInventario;
use App\Domain\Inventario\Services\InventarioService;
use App\Enums\RolUsuario;
use App\Filament\Resources\Modelos\Pages\CreateModelo;
use App\Filament\Resources\Modelos\Pages\EditModelo;
use App\Filament\Resources\Modelos\Pages\ListModelos;
use App\Models\User;
use Filament\Forms\Components\Repeater;
use Livewire\Livewire;

beforeEach(function () {
    Repeater::fake();
    $this->rm = Responsable::query()->create(['codigo' => 'RM', 'nombre' => 'RM']);
    $this->colegio = Colegio::query()->create(['codigo' => 'UE_HM', 'nombre' => 'Hermano Miguel']);
    $this->prenda = Prenda::query()->create(['codigo' => 'RM_CH', 'nombre' => 'Chompa', 'categoria' => 'DEPORTIVO']);
    foreach (['28', '30', '32', '34'] as $i => $codigo) {
        Talla::query()->create(['codigo' => $codigo, 'orden' => $i + 1, 'tipo' => 'NUMERICA']);
    }
    $this->talla = fn (string $codigo) => Talla::query()->where('codigo', $codigo)->value('id');
    $this->admin = User::factory()->conRol(RolUsuario::ADMIN)->create();
    $this->actingAs($this->admin);
});

function datosModelo(array $extra = []): array
{
    return array_merge([
        'codigo' => 'HMCH',
        'colegio_id' => test()->colegio->id,
        'prenda_id' => test()->prenda->id,
        'tipo_abastecimiento' => 'FABRICADO',
        'responsable_id' => test()->rm->id,
        'talla_desde_id' => (test()->talla)('28'),
        'talla_hasta_id' => (test()->talla)('32'),
    ], $extra);
}

function modeloConSkus(): Modelo
{
    Livewire::test(CreateModelo::class)->fillForm(datosModelo())->call('create')->assertHasNoFormErrors();

    return Modelo::query()->where('codigo', 'HMCH')->firstOrFail();
}

it('al crear un modelo con rango de tallas crea un SKU por talla', function () {
    $modelo = modeloConSkus();

    expect($modelo->skus()->count())->toBe(3); // 28, 30, 32
});

it('al ampliar el rango al editar agrega solo los SKUs que faltan', function () {
    $modelo = modeloConSkus();

    Livewire::test(EditModelo::class, ['record' => $modelo->getRouteKey()])
        ->fillForm(['talla_hasta_id' => (test()->talla)('34')])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($modelo->skus()->count())->toBe(4);
});

it('no deja guardar un rango con la talla hasta antes que la talla desde', function () {
    Livewire::test(CreateModelo::class)
        ->fillForm(datosModelo(['talla_desde_id' => (test()->talla)('32'), 'talla_hasta_id' => (test()->talla)('28')]))
        ->call('create')
        ->assertHasFormErrors(['talla_hasta_id']);

    expect(Modelo::query()->count())->toBe(0);
});

it('registra precios de varias tallas en una sola acción y deja igual las vacías', function () {
    $modelo = modeloConSkus();
    $skus = $modelo->skus()->with('talla')->get()->sortBy('talla.orden')->values();

    Livewire::test(ListModelos::class)
        ->callTableAction('precios', $modelo, data: [
            'filas' => [
                ['sku_id' => $skus[0]->id, 'nuevo' => 15],
                ['sku_id' => $skus[1]->id, 'nuevo' => 15],
                ['sku_id' => $skus[2]->id, 'nuevo' => null],
            ],
            'incluye_iva' => true,
            'vigente_desde' => '2026-10-01',
            'responsable_id' => test()->rm->id,
        ])
        ->assertHasNoTableActionErrors();

    expect(PrecioVenta::query()->count())->toBe(2)
        ->and((float) $skus[0]->preciosVenta()->first()->precio)->toBe(15.0);
});

it('si un precio choca con la fecha de otro, no guarda ninguno', function () {
    $modelo = modeloConSkus();
    $skus = $modelo->skus()->with('talla')->get()->sortBy('talla.orden')->values();
    $datos = fn (array $filas) => ['filas' => $filas, 'incluye_iva' => true, 'vigente_desde' => '2026-10-01', 'responsable_id' => test()->rm->id];

    Livewire::test(ListModelos::class)->callTableAction('precios', $modelo, data: $datos([['sku_id' => $skus[0]->id, 'nuevo' => 15]]));

    // Segunda vez, misma fecha: la talla 30 sería válida, pero la 28 choca → nada se guarda.
    Livewire::test(ListModelos::class)->callTableAction('precios', $modelo, data: $datos([
        ['sku_id' => $skus[1]->id, 'nuevo' => 16],
        ['sku_id' => $skus[0]->id, 'nuevo' => 17],
    ]));

    expect(PrecioVenta::query()->count())->toBe(1)
        ->and($skus[1]->preciosVenta()->count())->toBe(0);
});

it('registra el conteo inicial por talla y no permite contar dos veces la misma talla', function () {
    $modelo = modeloConSkus();
    $skus = $modelo->skus()->with('talla')->get()->sortBy('talla.orden')->values();
    $inventario = app(InventarioService::class);

    Livewire::test(ListModelos::class)
        ->callTableAction('conteoInicial', $modelo, data: [
            'fecha_conteo' => '2026-10-09',
            'ubicacion' => 'LOCAL',
            'filas' => [
                ['sku_id' => $skus[0]->id, 'cantidad' => 12],
                ['sku_id' => $skus[1]->id, 'cantidad' => 8],
                ['sku_id' => $skus[2]->id, 'cantidad' => null],
            ],
        ])
        ->assertHasNoTableActionErrors();

    expect($inventario->stock($skus[0]))->toBe(12.0)
        ->and($inventario->stock($skus[1]))->toBe(8.0)
        ->and($inventario->tieneInventarioInicial($skus[2]))->toBeFalse();

    // Repetir el conteo de la talla 28 no suma: se rechaza y nada cambia.
    Livewire::test(ListModelos::class)->callTableAction('conteoInicial', $modelo, data: [
        'fecha_conteo' => '2026-10-10',
        'ubicacion' => 'LOCAL',
        'filas' => [['sku_id' => $skus[0]->id, 'cantidad' => 12]],
    ]);

    expect($inventario->stock($skus[0]))->toBe(12.0);
});

it('un inventario inicial reversado permite volver a cargarlo', function () {
    $modelo = modeloConSkus();
    $sku = $modelo->skus()->first();
    $inventario = app(InventarioService::class);

    $mal = $inventario->registrar($sku, TipoMovimientoInventario::INVENTARIO_INICIAL, 100, now(), $this->admin);
    expect(fn () => $inventario->registrar($sku, TipoMovimientoInventario::INVENTARIO_INICIAL, 10, now(), $this->admin))
        ->toThrow(InvalidArgumentException::class);

    $inventario->reversar($mal, $this->admin, 'Se contó mal');
    $inventario->registrar($sku, TipoMovimientoInventario::INVENTARIO_INICIAL, 10, now(), $this->admin);

    expect($inventario->stock($sku))->toBe(10.0);
});
