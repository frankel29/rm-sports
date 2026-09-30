<?php

namespace App\Domain\Materiales\Importers;

use App\Domain\Catalogo\Models\Responsable;
use App\Domain\Importacion\Support\ConvierteCeldas;
use App\Domain\Importacion\Support\HojaImporter;
use App\Domain\Materiales\Models\Proveedor;
use App\Models\User;
use InvalidArgumentException;

class ProveedoresImporter extends HojaImporter
{
    use ConvierteCeldas;

    public function nombreHoja(): string
    {
        return 'Proveedores';
    }

    protected function importarFila(array $fila, User $user): void
    {
        $codigo = $this->textoObligatorio($fila['codigo_proveedor'] ?? null, 'codigo_proveedor');

        $responsable = null;
        $codigoResponsable = $this->texto($fila['responsable'] ?? null);
        if ($codigoResponsable) {
            $responsable = Responsable::query()->where('codigo', $codigoResponsable)->first();
            if (! $responsable) {
                throw new InvalidArgumentException("responsable \"{$codigoResponsable}\" no existe.");
            }
        }

        Proveedor::query()->updateOrCreate(
            ['codigo' => $codigo],
            [
                'nombre' => $this->textoObligatorio($fila['nombre'] ?? null, 'nombre'),
                'ruc' => $this->texto($fila['ruc'] ?? null),
                'telefono' => $this->texto($fila['telefono'] ?? null),
                'que_provee' => $this->texto($fila['que_provee'] ?? null),
                'responsable_id' => $responsable?->id,
                'notas' => $this->texto($fila['notas'] ?? null),
            ],
        );
    }
}
