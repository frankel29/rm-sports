<?php

use App\Domain\Catalogo\Models\Colegio;
use App\Domain\Catalogo\Models\Modelo;
use App\Domain\Catalogo\Models\Prenda;
use App\Domain\Catalogo\Models\Responsable;
use App\Domain\Catalogo\Models\Sku;
use App\Domain\Catalogo\Models\Talla;
use App\Domain\Costeo\Models\GastoMensual;
use App\Enums\RolUsuario;
use App\Models\User;

beforeEach(function () {
    $this->rm = Responsable::query()->create(['codigo' => 'RM', 'nombre' => 'RM']);
    $this->gl = Responsable::query()->create(['codigo' => 'GL', 'nombre' => 'GL']);

    $this->colegio = Colegio::query()->create(['codigo' => 'COL', 'nombre' => 'Colegio']);
    $this->prenda = Prenda::query()->create(['codigo' => 'PRENDA', 'nombre' => 'Prenda', 'categoria' => 'DEPORTIVO']);
    $this->talla = Talla::query()->create(['codigo' => 'U', 'orden' => 1, 'tipo' => 'LETRA']);

    $this->modeloRm = Modelo::query()->create([
        'codigo' => 'MOD-RM', 'colegio_id' => $this->colegio->id, 'prenda_id' => $this->prenda->id,
        'tipo_abastecimiento' => 'FABRICADO', 'responsable_id' => $this->rm->id,
    ]);
    $this->modeloGl = Modelo::query()->create([
        'codigo' => 'MOD-GL', 'colegio_id' => $this->colegio->id, 'prenda_id' => $this->prenda->id,
        'tipo_abastecimiento' => 'FABRICADO', 'responsable_id' => $this->gl->id,
    ]);
});

function usuarioSinLinea(): User
{
    return User::factory()->conRol(RolUsuario::ADMIN)->create(['responsable_id' => null]);
}

function usuarioDeLinea(Responsable $responsable): User
{
    return User::factory()->conRol(RolUsuario::ADMIN)->create(['responsable_id' => $responsable->id]);
}

it('un administrador general ve todos los modelos de ambas líneas', function () {
    $this->actingAs(usuarioSinLinea());

    expect(Modelo::query()->count())->toBe(2);
});

it('un usuario de línea solo ve los modelos de su propia línea', function () {
    $this->actingAs(usuarioDeLinea($this->rm));

    $codigos = Modelo::query()->pluck('codigo')->all();

    expect($codigos)->toBe(['MOD-RM']);
});

it('un usuario de línea no puede crear un registro en la línea contraria aunque lo envíe así', function () {
    $this->actingAs(usuarioDeLinea($this->gl));

    $modelo = Modelo::query()->create([
        'codigo' => 'MOD-NUEVO', 'colegio_id' => $this->colegio->id, 'prenda_id' => $this->prenda->id,
        'tipo_abastecimiento' => 'FABRICADO', 'responsable_id' => $this->rm->id, // intenta forzar RM
    ]);

    expect($modelo->responsable_id)->toBe($this->gl->id);
});

it('un usuario de línea no puede mover un registro propio a la otra línea', function () {
    $usuarioGl = usuarioDeLinea($this->gl);
    $this->actingAs($usuarioGl);

    $modeloPropio = Modelo::query()->create([
        'codigo' => 'MOD-GL-2', 'colegio_id' => $this->colegio->id, 'prenda_id' => $this->prenda->id,
        'tipo_abastecimiento' => 'FABRICADO', 'responsable_id' => $this->gl->id,
    ]);

    $modeloPropio->update(['responsable_id' => $this->rm->id]);

    expect($modeloPropio->fresh()->responsable_id)->toBe($this->gl->id);
});

it('los SKUs se filtran por la línea del modelo, aunque no tengan columna responsable_id propia', function () {
    Sku::query()->create(['modelo_id' => $this->modeloRm->id, 'talla_id' => $this->talla->id, 'codigo' => 'MOD-RM-U']);
    Sku::query()->create(['modelo_id' => $this->modeloGl->id, 'talla_id' => $this->talla->id, 'codigo' => 'MOD-GL-U']);

    $this->actingAs(usuarioDeLinea($this->rm));

    expect(Sku::query()->pluck('codigo')->all())->toBe(['MOD-RM-U']);
});

it('un usuario de línea ve los registros compartidos (sin línea) además de los suyos', function () {
    GastoMensual::query()->create(['concepto' => 'Compartido', 'responsable_id' => null]);
    GastoMensual::query()->create(['concepto' => 'Solo RM', 'responsable_id' => $this->rm->id]);
    GastoMensual::query()->create(['concepto' => 'Solo GL', 'responsable_id' => $this->gl->id]);

    $this->actingAs(usuarioDeLinea($this->rm));

    expect(GastoMensual::query()->pluck('concepto')->sort()->values()->all())
        ->toBe(['Compartido', 'Solo RM']);
});

it('sin usuario autenticado (CLI, seeders) no se filtra nada', function () {
    expect(Modelo::query()->count())->toBe(2);
});
