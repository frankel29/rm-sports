<?php

namespace Database\Seeders;

use App\Domain\Catalogo\Models\Responsable;
use App\Domain\Materiales\Models\Proveedor;
use Illuminate\Database\Seeder;

class ProveedoresDemoSeeder extends Seeder
{
    public function run(): void
    {
        $rm = Responsable::query()->where('codigo', 'RM')->firstOrFail();
        $gl = Responsable::query()->where('codigo', 'GL')->firstOrFail();

        Proveedor::query()->updateOrCreate(
            ['codigo' => 'NILOTEX'],
            [
                'nombre' => 'Nilotex',
                'telefono' => '0988888888',
                'que_provee' => 'Telas: calentador, gabardina, jersey, piqué, polar',
                'responsable_id' => $rm->id,
            ],
        );

        Proveedor::query()->updateOrCreate(
            ['codigo' => 'TEXTILANDIA'],
            [
                'nombre' => 'Textilandia',
                'telefono' => '0987777777',
                'que_provee' => 'Camisetas interiores lisas para reventa',
                'responsable_id' => $gl->id,
            ],
        );
    }
}
