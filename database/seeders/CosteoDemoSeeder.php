<?php

namespace Database\Seeders;

use App\Domain\Catalogo\Models\Prenda;
use App\Domain\Catalogo\Models\Responsable;
use App\Domain\Costeo\Enums\FormaPago;
use App\Domain\Costeo\Models\GastoMensual;
use App\Domain\Costeo\Models\TarifaManoObra;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CosteoDemoSeeder extends Seeder
{
    public function run(): void
    {
        $rm = Responsable::query()->where('codigo', 'RM')->firstOrFail();
        $chompa = Prenda::query()->where('codigo', 'CHOMPA')->firstOrFail();
        $pantalon = Prenda::query()->where('codigo', 'PANTALON')->firstOrFail();

        TarifaManoObra::query()->updateOrCreate(
            ['prenda_id' => $chompa->id, 'operacion' => 'CONFECCION', 'responsable_id' => $rm->id],
            ['forma_pago' => FormaPago::SUELDO_MENSUAL, 'valor' => 2.50, 'vigente_desde' => Carbon::create(2026, 8, 1)],
        );

        TarifaManoObra::query()->updateOrCreate(
            ['prenda_id' => $pantalon->id, 'operacion' => 'CONFECCION', 'responsable_id' => $rm->id],
            ['forma_pago' => FormaPago::POR_PRENDA, 'valor' => 1.50, 'vigente_desde' => Carbon::create(2026, 8, 1)],
        );

        GastoMensual::query()->updateOrCreate(
            ['concepto' => 'Arriendo del local', 'responsable_id' => null],
            ['monto_mensual' => 150],
        );

        GastoMensual::query()->updateOrCreate(
            ['concepto' => 'Arriendo del taller', 'responsable_id' => $rm->id],
            ['monto_mensual' => 100],
        );
    }
}
