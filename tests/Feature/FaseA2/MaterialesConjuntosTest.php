<?php

use App\Domain\Catalogo\Models\Colegio;
use App\Domain\Catalogo\Models\Kit;
use App\Domain\Catalogo\Models\Modelo;
use App\Domain\Catalogo\Models\PrecioVenta;
use App\Domain\Catalogo\Models\Prenda;
use App\Domain\Catalogo\Models\Responsable;
use App\Domain\Catalogo\Models\Sku;
use App\Domain\Catalogo\Models\Talla;
use App\Domain\Inventario\Services\InventarioMaterialService;
use App\Domain\Materiales\Models\Material;
use App\Enums\RolUsuario;
use App\Filament\Resources\Kits\Pages\CreateKit;
use App\Filament\Resources\Materials\Pages\CreateMaterial;
use App\Filament\Resources\Materials\Pages\ListMaterials;
use App\Filament\Resources\Modelos\Pages\CreateModelo;
use App\Filament\Resources\Modelos\Pages\ListModelos;
use App\Models\User;
use Filament\Forms\Components\Repeater;
use Livewire\Livewire;

beforeEach(function () {
    Repeater::fake();
    $this->rm = Responsable::query()->create(['codigo' => 'RM', 'nombre' => 'RM']);
    $this->gl = Responsable::query()->create(['codigo' => 'GL', 'nombre' => 'GL']);
    $this->admin = User::factory()->conRol(RolUsuario::ADMIN)->create();
    $this->actingAs($this->admin);
});

function materialDePrueba(): Material
{
    return Material::query()->create([
        'codigo' => 'TELA-CAL', 'nombre' => 'Calentador', 'tipo' => 'TELA', 'unidad' => 'M',
        'responsable_id' => test()->rm->id,
    ]);
}

function modeloDePrueba(string $codigo, Responsable $responsable): Modelo
{
    $colegio = Colegio::query()->firstOrCreate(['codigo' => 'UE_HM'], ['nombre' => 'Hermano Miguel']);
    $prenda = Prenda::query()->firstOrCreate(['codigo' => 'RM_CH'], ['nombre' => 'Chompa', 'categoria' => 'DEPORTIVO']);

    return Modelo::query()->create([
        'codigo' => $codigo, 'colegio_id' => $colegio->id, 'prenda_id' => $prenda->id,
        'tipo_abastecimiento' => 'FABRICADO', 'responsable_id' => $responsable->id,
    ]);
}

it('registra el conteo inicial de un material una sola vez y luego se corrige con ajuste', function () {
    $material = materialDePrueba();
    $inventario = app(InventarioMaterialService::class);

    Livewire::test(ListMaterials::class)
        ->callTableAction('conteoInicial', $material, data: ['cantidad' => 35.5, 'fecha_conteo' => '2026-10-09', 'ubicacion' => 'TALLER'])
        ->assertHasNoTableActionErrors()
        ->assertTableActionHidden('conteoInicial', $material);

    Livewire::test(ListMaterials::class)
        ->callTableAction('registrarAjuste', $material, data: ['cantidad' => -5.5, 'fecha_conteo' => '2026-10-10', 'nota' => 'Retazos dañados'])
        ->assertHasNoTableActionErrors();

    expect($inventario->stock($material))->toBe(30.0)
        ->and($inventario->kardex($material))->toHaveCount(2);
});

it('el ajuste de material exige un motivo', function () {
    $material = materialDePrueba();

    Livewire::test(ListMaterials::class)
        ->callTableAction('registrarAjuste', $material, data: ['cantidad' => -1, 'fecha_conteo' => '2026-10-10', 'nota' => null])
        ->assertHasTableActionErrors(['nota']);
});

it('acepta la unidad CONO', function () {
    Livewire::test(CreateMaterial::class)
        ->fillForm(['codigo' => 'HILO-1', 'nombre' => 'Hilo azul', 'tipo' => 'INSUMO', 'unidad' => 'CONO', 'responsable_id' => test()->rm->id])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Material::query()->where('codigo', 'HILO-1')->value('unidad')->value)->toBe('CONO');
});

it('crea un conjunto con sus piezas en un solo formulario', function () {
    $chompa = modeloDePrueba('HMCH', test()->rm);
    $pantalon = modeloDePrueba('HMPAN', test()->rm);

    Livewire::test(CreateKit::class)
        ->fillForm([
            'codigo' => 'HMEXT', 'nombre' => 'Exterior Hermano Miguel', 'responsable_id' => test()->rm->id,
            'componentes' => [
                ['modelo_id' => $chompa->id, 'cantidad' => 1],
                ['modelo_id' => $pantalon->id, 'cantidad' => 1],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Kit::query()->where('codigo', 'HMEXT')->firstOrFail()->componentes()->count())->toBe(2);
});

it('crea el colegio desde el mismo formulario del modelo', function () {
    Livewire::test(CreateModelo::class)
        ->callFormComponentAction('colegio_id', 'createOption', data: ['codigo' => 'UE_LS', 'nombre' => 'La Salle'])
        ->assertHasNoFormComponentActionErrors();

    expect(Colegio::query()->where('codigo', 'UE_LS')->exists())->toBeTrue();
});

it('un admin de línea que pone precios queda en su línea aunque elija la otra', function () {
    $modeloGl = modeloDePrueba('GLCH', test()->gl);
    $talla = Talla::query()->create(['codigo' => 'U', 'orden' => 1, 'tipo' => 'LETRA']);
    $sku = Sku::query()->create(['modelo_id' => $modeloGl->id, 'talla_id' => $talla->id, 'codigo' => 'GLCH-U']);

    $this->actingAs(User::factory()->conRol(RolUsuario::ADMIN)->create(['responsable_id' => test()->gl->id]));

    Livewire::test(ListModelos::class)
        ->callTableAction('precios', $modeloGl, data: [
            'filas' => [['sku_id' => $sku->id, 'nuevo' => 9]],
            'incluye_iva' => true, 'vigente_desde' => '2026-10-01', 'responsable_id' => test()->rm->id,
        ])
        ->assertHasNoTableActionErrors();

    expect(PrecioVenta::query()->withoutGlobalScopes()->first()->responsable_id)->toBe(test()->gl->id);
});
