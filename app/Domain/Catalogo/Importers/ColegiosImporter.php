<?php

namespace App\Domain\Catalogo\Importers;

use App\Domain\Catalogo\Models\Colegio;
use App\Domain\Importacion\Support\ConvierteCeldas;
use App\Domain\Importacion\Support\HojaImporter;
use App\Models\User;

class ColegiosImporter extends HojaImporter
{
    use ConvierteCeldas;

    public function nombreHoja(): string
    {
        return 'Colegios';
    }

    protected function importarFila(array $fila, User $user): void
    {
        $codigo = $this->textoObligatorio($fila['codigo_colegio'] ?? null, 'codigo_colegio');

        Colegio::query()->updateOrCreate(
            ['codigo' => $codigo],
            [
                'nombre' => $this->textoObligatorio($fila['nombre'] ?? null, 'nombre'),
                'contacto' => $this->texto($fila['contacto'] ?? null),
                'telefono' => $this->texto($fila['telefono'] ?? null),
                'notas' => $this->texto($fila['notas'] ?? null),
            ],
        );
    }
}
