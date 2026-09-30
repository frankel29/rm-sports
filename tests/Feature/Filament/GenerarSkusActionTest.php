<?php

use App\Domain\Catalogo\Models\Colegio;
use App\Domain\Catalogo\Models\Modelo;
use App\Domain\Catalogo\Models\Prenda;
use App\Domain\Catalogo\Models\Responsable;
use App\Domain\Catalogo\Models\Sku;
use App\Domain\Catalogo\Models\Talla;
use App\Enums\RolUsuario;
use App\Filament\Resources\Modelos\Pages\ListModelos;
use App\Models\User;
use Livewire\Livewire;

it('genera los SKUs de un modelo desde la acción de tabla', function () {
    $admin = User::factory()->conRol(RolUsuario::ADMIN)->create();

    $responsable = Responsable::query()->create(['codigo' => 'RM', 'nombre' => 'RM']);
    $colegio = Colegio::query()->create(['codigo' => 'COL', 'nombre' => 'Colegio']);
    $prenda = Prenda::query()->create(['codigo' => 'PRENDA', 'nombre' => 'Prenda', 'categoria' => 'DEPORTIVO']);
    $modelo = Modelo::query()->create([
        'codigo' => 'MOD-1',
        'colegio_id' => $colegio->id,
        'prenda_id' => $prenda->id,
        'tipo_abastecimiento' => 'FABRICADO',
        'responsable_id' => $responsable->id,
    ]);

    $tallaS = Talla::query()->create(['codigo' => 'S', 'orden' => 1, 'tipo' => 'LETRA']);
    $tallaM = Talla::query()->create(['codigo' => 'M', 'orden' => 2, 'tipo' => 'LETRA']);
    $tallaL = Talla::query()->create(['codigo' => 'L', 'orden' => 3, 'tipo' => 'LETRA']);

    Livewire::actingAs($admin)
        ->test(ListModelos::class)
        ->callTableAction('generarSkus', $modelo, data: [
            'talla_desde_id' => $tallaS->id,
            'talla_hasta_id' => $tallaL->id,
        ])
        ->assertHasNoTableActionErrors();

    expect(Sku::query()->where('modelo_id', $modelo->id)->count())->toBe(3)
        ->and(Sku::query()->where('modelo_id', $modelo->id)->where('talla_id', $tallaM->id)->exists())->toBeTrue();
});
