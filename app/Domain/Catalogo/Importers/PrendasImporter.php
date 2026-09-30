<?php

namespace App\Domain\Catalogo\Importers;

use App\Domain\Catalogo\Enums\CategoriaPrenda;
use App\Domain\Catalogo\Models\Prenda;
use App\Domain\Importacion\Support\ConvierteCeldas;
use App\Domain\Importacion\Support\HojaImporter;
use App\Models\User;
use InvalidArgumentException;

class PrendasImporter extends HojaImporter
{
    use ConvierteCeldas;

    public function nombreHoja(): string
    {
        return 'Prendas';
    }

    protected function importarFila(array $fila, User $user): void
    {
        $codigo = $this->textoObligatorio($fila['codigo_prenda'] ?? null, 'codigo_prenda');
        $categoria = CategoriaPrenda::tryFrom($this->textoObligatorio($fila['categoria'] ?? null, 'categoria'));

        if (! $categoria) {
            throw new InvalidArgumentException("categoria inválida: \"{$fila['categoria']}\". Use ESCOLAR, DEPORTIVO u OTRO.");
        }

        Prenda::query()->updateOrCreate(
            ['codigo' => $codigo],
            [
                'nombre' => $this->textoObligatorio($fila['nombre'] ?? null, 'nombre'),
                'categoria' => $categoria,
                'notas' => $this->texto($fila['notas'] ?? null),
            ],
        );
    }
}
