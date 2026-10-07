<?php

use App\Domain\Catalogo\Models\Responsable;
use App\Enums\RolUsuario;
use App\Models\User;

it('carga todas las páginas de listado del panel para un administrador', function (string $ruta) {
    $admin = User::factory()->conRol(RolUsuario::ADMIN)->create();

    $this->actingAs($admin)
        ->get($ruta)
        ->assertSuccessful();
})->with([
    '/admin',
    '/admin/responsables',
    '/admin/colegios',
    '/admin/prendas',
    '/admin/tallas',
    '/admin/modelos',
    '/admin/skus',
    '/admin/kits',
    '/admin/configuracion-ivas',
    '/admin/proveedors',
    '/admin/materials',
    '/admin/tarifa-mano-obras',
    '/admin/gasto-mensuals',
    '/admin/users',
    '/admin/importar-plantilla',
    '/admin/responsables/create',
    '/admin/colegios/create',
    '/admin/prendas/create',
    '/admin/tallas/create',
    '/admin/modelos/create',
    '/admin/skus/create',
    '/admin/kits/create',
    '/admin/configuracion-ivas/create',
    '/admin/proveedors/create',
    '/admin/materials/create',
    '/admin/tarifa-mano-obras/create',
    '/admin/gasto-mensuals/create',
    '/admin/users/create',
]);

it('bloquea la importación masiva y la gestión de usuarios a roles que no son ADMIN', function (string $ruta) {
    $vendedor = User::factory()->conRol(RolUsuario::VENDEDOR)->create();

    $this->actingAs($vendedor)
        ->get($ruta)
        ->assertForbidden();
})->with([
    '/admin/importar-plantilla',
    '/admin/users',
]);

it('vendedor y taller no entran al backoffice (usarán el POS y el registro de lotes)', function (RolUsuario $rol) {
    $usuario = User::factory()->conRol($rol)->create();

    $this->actingAs($usuario)->get('/admin')->assertForbidden();
})->with([RolUsuario::VENDEDOR, RolUsuario::TALLER]);

it('un administrador de línea sí entra al backoffice', function () {
    $gl = Responsable::query()->create(['codigo' => 'GL', 'nombre' => 'GL']);
    $adminGl = User::factory()->conRol(RolUsuario::ADMIN)->create(['responsable_id' => $gl->id]);

    $this->actingAs($adminGl)->get('/admin/modelos')->assertSuccessful();
});

it('bloquea la gestión de usuarios a un administrador de línea (no general)', function () {
    $rm = Responsable::query()->create(['codigo' => 'RM', 'nombre' => 'RM']);
    $adminDeLinea = User::factory()->conRol(RolUsuario::ADMIN)->create(['responsable_id' => $rm->id]);

    $this->actingAs($adminDeLinea)
        ->get('/admin/users')
        ->assertForbidden();
});
