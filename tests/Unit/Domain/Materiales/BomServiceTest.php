<?php

use App\Domain\Catalogo\Models\Colegio;
use App\Domain\Catalogo\Models\Modelo;
use App\Domain\Catalogo\Models\Prenda;
use App\Domain\Catalogo\Models\Responsable;
use App\Domain\Catalogo\Models\Talla;
use App\Domain\Materiales\Models\BomLinea;
use App\Domain\Materiales\Models\Material;
use App\Domain\Materiales\Services\BomService;
use App\Domain\Materiales\Services\PrecioMaterialService;

function crearModeloDePrueba(string $tipoAbastecimiento = 'FABRICADO'): Modelo
{
    $responsable = Responsable::query()->firstOrCreate(['codigo' => 'RM'], ['nombre' => 'RM']);
    $colegio = Colegio::query()->firstOrCreate(['codigo' => 'COL'], ['nombre' => 'Colegio']);
    $prenda = Prenda::query()->firstOrCreate(['codigo' => 'PRENDA'], ['nombre' => 'Prenda', 'categoria' => 'DEPORTIVO']);

    return Modelo::query()->create([
        'codigo' => 'MOD-'.uniqid(),
        'colegio_id' => $colegio->id,
        'prenda_id' => $prenda->id,
        'tipo_abastecimiento' => $tipoAbastecimiento,
        'responsable_id' => $responsable->id,
    ]);
}

beforeEach(function () {
    $this->bom = app(BomService::class);
    $this->responsable = Responsable::query()->firstOrCreate(['codigo' => 'RM'], ['nombre' => 'RM']);
    $this->material = Material::query()->create([
        'codigo' => 'TELA-'.uniqid(),
        'nombre' => 'Tela',
        'tipo' => 'TELA',
        'unidad' => 'M',
        'responsable_id' => $this->responsable->id,
    ]);
});

it('no permite agregar una línea de BOM a un modelo COMPRADO', function () {
    $modelo = crearModeloDePrueba('COMPRADO');

    expect(fn () => $this->bom->agregarLinea($modelo, $this->material, 1.5))
        ->toThrow(InvalidArgumentException::class);
});

it('una línea con talla específica reemplaza a la línea NULL para esa talla', function () {
    $modelo = crearModeloDePrueba();
    $tallaS = Talla::query()->create(['codigo' => 'S', 'orden' => 1, 'tipo' => 'LETRA']);
    $tallaM = Talla::query()->create(['codigo' => 'M', 'orden' => 2, 'tipo' => 'LETRA']);

    $this->bom->agregarLinea($modelo, $this->material, 1.0); // NULL: todas las tallas
    $this->bom->agregarLinea($modelo, $this->material, 1.5, $tallaM); // override solo talla M

    $consumoS = $this->bom->consumoPara($modelo, $tallaS);
    $consumoM = $this->bom->consumoPara($modelo, $tallaM);

    expect((float) $consumoS->first()->cantidad)->toBe(1.0)
        ->and((float) $consumoM->first()->cantidad)->toBe(1.5);
});

it('agregarLinea es idempotente: repetir la misma talla actualiza en vez de duplicar', function () {
    $modelo = crearModeloDePrueba();

    $this->bom->agregarLinea($modelo, $this->material, 1.0);
    $this->bom->agregarLinea($modelo, $this->material, 2.0);

    expect(BomLinea::query()->where('modelo_id', $modelo->id)->count())->toBe(1)
        ->and((float) BomLinea::query()->where('modelo_id', $modelo->id)->first()->cantidad)->toBe(2.0);
});

it('costoMateriales suma cantidad por precio vigente de cada material', function () {
    $modelo = crearModeloDePrueba();
    app(PrecioMaterialService::class)->registrar($this->material, 4.0, now()->subDay());

    $material2 = Material::query()->create([
        'codigo' => 'TELA2-'.uniqid(),
        'nombre' => 'Tela 2',
        'tipo' => 'TELA',
        'unidad' => 'M',
        'responsable_id' => $this->responsable->id,
    ]);
    app(PrecioMaterialService::class)->registrar($material2, 2.0, now()->subDay());

    $talla = Talla::query()->create(['codigo' => 'U', 'orden' => 1, 'tipo' => 'LETRA']);
    $this->bom->agregarLinea($modelo, $this->material, 1.5, $talla);
    $this->bom->agregarLinea($modelo, $material2, 2.0, $talla);

    // 1.5 * 4.0 + 2.0 * 2.0 = 6 + 4 = 10
    expect($this->bom->costoMateriales($modelo, $talla))->toBe(10.0);
});
