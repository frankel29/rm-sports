<?php

use App\Domain\Catalogo\Models\Colegio;
use App\Domain\Catalogo\Models\Modelo;
use App\Domain\Catalogo\Models\Prenda;
use App\Domain\Catalogo\Models\Responsable;
use App\Domain\Catalogo\Models\Sku;
use App\Domain\Catalogo\Models\Talla;
use App\Domain\Catalogo\Services\SkuGeneratorService;

beforeEach(function () {
    $this->skuGenerator = app(SkuGeneratorService::class);

    $responsable = Responsable::query()->create(['codigo' => 'RM', 'nombre' => 'RM']);
    $colegio = Colegio::query()->create(['codigo' => 'COL', 'nombre' => 'Colegio']);
    $prenda = Prenda::query()->create(['codigo' => 'PRENDA', 'nombre' => 'Prenda', 'categoria' => 'DEPORTIVO']);

    $this->modelo = Modelo::query()->create([
        'codigo' => 'MOD-1',
        'colegio_id' => $colegio->id,
        'prenda_id' => $prenda->id,
        'tipo_abastecimiento' => 'FABRICADO',
        'responsable_id' => $responsable->id,
    ]);

    foreach ([4, 6, 8, 10, 12] as $i => $codigo) {
        Talla::query()->create(['codigo' => (string) $codigo, 'orden' => $i + 1, 'tipo' => 'NUMERICA']);
    }
});

it('genera un SKU por cada talla dentro del rango, inclusive', function () {
    $desde = Talla::query()->where('codigo', '6')->first();
    $hasta = Talla::query()->where('codigo', '10')->first();

    $skus = $this->skuGenerator->generarParaModelo($this->modelo, $desde, $hasta);

    expect($skus)->toHaveCount(3) // 6, 8, 10
        ->and(Sku::query()->where('modelo_id', $this->modelo->id)->pluck('codigo')->sort()->values()->all())
        ->toBe(['MOD-1-10', 'MOD-1-6', 'MOD-1-8']);
});

it('es idempotente: generar dos veces no duplica SKUs', function () {
    $desde = Talla::query()->where('codigo', '4')->first();
    $hasta = Talla::query()->where('codigo', '8')->first();

    $this->skuGenerator->generarParaModelo($this->modelo, $desde, $hasta);
    $this->skuGenerator->generarParaModelo($this->modelo, $desde, $hasta);

    expect(Sku::query()->where('modelo_id', $this->modelo->id)->count())->toBe(3);
});

it('lanza una excepción si la talla desde tiene un orden mayor que la talla hasta', function () {
    $desde = Talla::query()->where('codigo', '10')->first();
    $hasta = Talla::query()->where('codigo', '6')->first();

    expect(fn () => $this->skuGenerator->generarParaModelo($this->modelo, $desde, $hasta))
        ->toThrow(InvalidArgumentException::class);
});
