<?php

namespace App\Domain\Costeo\Importers;

use App\Domain\Catalogo\Models\Responsable;
use App\Domain\Costeo\Models\GastoMensual;
use App\Domain\Importacion\Support\ConvierteCeldas;
use App\Domain\Importacion\Support\HojaImporter;
use App\Models\User;
use InvalidArgumentException;

class GastosMensualesImporter extends HojaImporter
{
    use ConvierteCeldas;

    public function nombreHoja(): string
    {
        return 'Gastos_mensuales';
    }

    protected function importarFila(array $fila, User $user): void
    {
        $concepto = $this->textoObligatorio($fila['concepto'] ?? null, 'concepto');

        $responsable = null;
        $codigoResponsable = $this->texto($fila['responsable'] ?? null);
        if ($codigoResponsable) {
            $responsable = Responsable::query()->where('codigo', $codigoResponsable)->first();
            if (! $responsable) {
                throw new InvalidArgumentException("responsable \"{$codigoResponsable}\" no existe.");
            }
        }

        GastoMensual::query()->updateOrCreate(
            ['concepto' => $concepto, 'responsable_id' => $responsable?->id],
            [
                'monto_mensual' => $this->numero($fila['monto_mensual'] ?? null),
                'notas' => $this->texto($fila['notas'] ?? null),
            ],
        );
    }
}
