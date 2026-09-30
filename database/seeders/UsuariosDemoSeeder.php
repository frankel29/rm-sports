<?php

namespace Database\Seeders;

use App\Enums\RolUsuario;
use App\Models\User;
use Illuminate\Database\Seeder;

class UsuariosDemoSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@rmsports.test'],
            ['name' => 'Administradora', 'rol' => RolUsuario::ADMIN, 'password' => 'password'],
        );

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
