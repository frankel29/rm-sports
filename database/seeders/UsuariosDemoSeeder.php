<?php

namespace Database\Seeders;

use App\Domain\Catalogo\Models\Responsable;
use App\Enums\RolUsuario;
use App\Models\User;
use Illuminate\Database\Seeder;

class UsuariosDemoSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@rmsports.test'],
            ['name' => 'Administradora General', 'rol' => RolUsuario::ADMIN, 'responsable_id' => null, 'password' => 'password'],
        );

        $rm = Responsable::query()->where('codigo', 'RM')->first();
        if ($rm) {
            User::query()->updateOrCreate(
                ['email' => 'admin-rm@rmsports.test'],
                ['name' => 'Administradora RM', 'rol' => RolUsuario::ADMIN, 'responsable_id' => $rm->id, 'password' => 'password'],
            );
        }

        $gl = Responsable::query()->where('codigo', 'GL')->first();
        if ($gl) {
            User::query()->updateOrCreate(
                ['email' => 'admin-gl@rmsports.test'],
                ['name' => 'Administradora GL', 'rol' => RolUsuario::ADMIN, 'responsable_id' => $gl->id, 'password' => 'password'],
            );
        }

        User::query()->updateOrCreate(
            ['email' => 'ventas@rmsports.test'],
            ['name' => 'Vendedora Local', 'rol' => RolUsuario::VENDEDOR, 'password' => 'password'],
        );

        User::query()->updateOrCreate(
            ['email' => 'taller@rmsports.test'],
            ['name' => 'Jefe de Taller', 'rol' => RolUsuario::TALLER, 'password' => 'password'],
        );
    }
}
