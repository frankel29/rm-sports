<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ResponsablesSeeder::class,
            UsuariosDemoSeeder::class,
            ProveedoresDemoSeeder::class,
            CatalogoDemoSeeder::class,
            MaterialesDemoSeeder::class,
            CosteoDemoSeeder::class,
            InventarioInicialDemoSeeder::class,
        ]);
    }
}
