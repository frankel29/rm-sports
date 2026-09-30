<?php

use App\Domain\Catalogo\Models\Colegio;
use App\Domain\Catalogo\Models\Modelo;
use App\Domain\Catalogo\Models\Prenda;
use App\Domain\Catalogo\Models\Responsable;
use App\Domain\Catalogo\Models\Sku;
use App\Domain\Catalogo\Models\Talla;
use App\Domain\Inventario\Services\InventarioService;
use App\Enums\RolUsuario;
use App\Filament\Resources\Skus\Pages\ListSkus;
use App\Models\User;
use Livewire\Livewire;

it('registra un ajuste de inventario desde la acción de tabla del SKU', function () {
    $admin = User::factory()->conRol(RolUsuario::ADMIN)->create();

    $responsable = Responsable::query()->create(['codigo' => 'RM', 'nombre' => 'RM']);
    $colegio = Colegio::query()->create(['codigo' => 'COL', 'nombre' => 'Colegio']);
    $prenda = Prenda::query()->create(['codigo' => 'PRENDA', 'nombre' => 'Prenda', 'categoria' => 'DEPORTIVO']);
    $talla = Talla::query()->create(['codigo' => 'U', 'orden' => 1, 'tipo' => 'LETRA']);
    $modelo = Modelo::query()->create([
        'codigo' => 'MOD-1',
        'colegio_id' => $colegio->id,
        'prenda_id' => $prenda->id,
        'tipo_abastecimiento' => 'FABRICADO',
        'responsable_id' => $responsable->id,
    ]);
    $sku = Sku::query()->create(['modelo_id' => $modelo->id, 'talla_id' => $talla->id, 'codigo' => 'MOD-1-U']);

    Livewire::actingAs($admin)
        ->test(ListSkus::class)
        ->callTableAction('registrarAjuste', $sku, data: [
            'cantidad' => -3,
            'fecha_hecho' => now()->toDateString(),
            'nota' => 'Prenda dañada en bodega',
        ])
        ->assertHasNoTableActionErrors();

    expect(app(InventarioService::class)->stock($sku->fresh()))->toBe(-3.0);
});
