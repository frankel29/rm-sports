<?php

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
