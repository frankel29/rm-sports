<?php

namespace App\Domain\Catalogo\Importers;

use App\Domain\Catalogo\Models\Kit;
use App\Domain\Catalogo\Models\KitComponente;
use App\Domain\Catalogo\Models\Modelo;
use App\Domain\Catalogo\Models\Responsable;
use App\Domain\Importacion\Support\ConvierteCeldas;
use App\Domain\Importacion\Support\HojaImporter;
use App\Models\User;
use InvalidArgumentException;

class ConjuntosImporter extends HojaImporter
{
    use ConvierteCeldas;

    public function nombreHoja(): string
    {
        return 'Conjuntos';
    }

    protected function importarFila(array $fila, User $user): void
    {
        $codigoKit = $this->textoObligatorio($fila['codigo_conjunto'] ?? null, 'codigo_conjunto');
        $codigoResponsable = $this->textoObligatorio($fila['responsable'] ?? null, 'responsable');

        $responsable = Responsable::query()->where('codigo', $codigoResponsable)->first();
        if (! $responsable) {
            throw new InvalidArgumentException("responsable \"{$codigoResponsable}\" no existe.");
        }

        $kit = Kit::query()->updateOrCreate(
            ['codigo' => $codigoKit],
            [
                'nombre' => $this->textoObligatorio($fila['nombre_conjunto'] ?? null, 'nombre_conjunto'),
                'responsable_id' => $responsable->id,
                'notas' => $this->texto($fila['notas'] ?? null),
            ],
        );

        $codigoModelo = $this->textoObligatorio($fila['codigo_modelo_pieza'] ?? null, 'codigo_modelo_pieza');
        $modelo = Modelo::query()->where('codigo', $codigoModelo)->first();
        if (! $modelo) {
            throw new InvalidArgumentException("codigo_modelo_pieza \"{$codigoModelo}\" no existe. Impórtelo primero en Modelos.");
        }

        KitComponente::query()->updateOrCreate(
            ['kit_id' => $kit->id, 'modelo_id' => $modelo->id],
            ['cantidad' => $this->numeroObligatorio($fila['cantidad'] ?? null, 'cantidad')],
        );
    }
}
