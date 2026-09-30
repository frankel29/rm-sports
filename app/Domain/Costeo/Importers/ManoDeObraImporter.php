<?php

namespace App\Domain\Costeo\Importers;

use App\Domain\Catalogo\Models\Prenda;
use App\Domain\Catalogo\Models\Responsable;
use App\Domain\Costeo\Enums\FormaPago;
use App\Domain\Costeo\Models\TarifaManoObra;
use App\Domain\Importacion\Support\ConvierteCeldas;
use App\Domain\Importacion\Support\HojaImporter;
use App\Models\User;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class ManoDeObraImporter extends HojaImporter
{
    use ConvierteCeldas;

    public function nombreHoja(): string
    {
        return 'Mano_de_obra';
    }

    protected function importarFila(array $fila, User $user): void
    {
        $codigoPrenda = $this->textoObligatorio($fila['codigo_prenda'] ?? null, 'codigo_prenda');
        $prenda = Prenda::query()->where('codigo', $codigoPrenda)->first();
        if (! $prenda) {
            throw new InvalidArgumentException("codigo_prenda \"{$codigoPrenda}\" no existe.");
        }

        $operacion = $this->textoObligatorio($fila['operacion'] ?? null, 'operacion');

        $formaPago = FormaPago::tryFrom($this->textoObligatorio($fila['como_se_paga'] ?? null, 'como_se_paga'));
        if (! $formaPago) {
            throw new InvalidArgumentException("como_se_paga inválido: \"{$fila['como_se_paga']}\". Use POR_PRENDA o SUELDO_MENSUAL.");
        }

        $codigoResponsable = $this->textoObligatorio($fila['responsable'] ?? null, 'responsable');
        $responsable = Responsable::query()->where('codigo', $codigoResponsable)->first();
        if (! $responsable) {
            throw new InvalidArgumentException("responsable \"{$codigoResponsable}\" no existe.");
        }

        // La vigencia de las tarifas de mano de obra inicia en la fecha de
        // importación (no viene una fecha en la plantilla).
        $ahora = Carbon::now()->startOfDay();

        $anterior = TarifaManoObra::query()
            ->where('prenda_id', $prenda->id)
            ->where('operacion', $operacion)
            ->where('responsable_id', $responsable->id)
            ->whereNull('vigente_hasta')
            ->first();

        if ($anterior) {
            $anterior->update(['vigente_hasta' => $ahora->copy()->subDay()]);
        }

        TarifaManoObra::create([
            'prenda_id' => $prenda->id,
            'operacion' => $operacion,
            'forma_pago' => $formaPago,
            'valor' => $this->numeroObligatorio($fila['valor'] ?? null, 'valor'),
            'responsable_id' => $responsable->id,
            'vigente_desde' => $ahora,
            'notas' => $this->texto($fila['notas'] ?? null),
        ]);
    }
}
