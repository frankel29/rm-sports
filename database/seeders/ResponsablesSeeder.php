<?php

namespace Database\Seeders;

use App\Domain\Catalogo\Models\Responsable;
use Illuminate\Database\Seeder;

class ResponsablesSeeder extends Seeder
{
    public function run(): void
    {
        Responsable::query()->updateOrCreate(['codigo' => 'RM'], ['nombre' => 'RM - Línea deportiva']);
        Responsable::query()->updateOrCreate(['codigo' => 'GL'], ['nombre' => 'GL - Línea no deportiva']);
    }
}
