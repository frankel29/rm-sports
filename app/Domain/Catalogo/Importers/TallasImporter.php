<?php

namespace App\Domain\Catalogo\Importers;

use App\Domain\Catalogo\Enums\TipoTalla;
use App\Domain\Catalogo\Models\Talla;
use App\Domain\Importacion\Support\ConvierteCeldas;
use App\Domain\Importacion\Support\HojaImporter;
use App\Models\User;
use InvalidArgumentException;

class TallasImporter extends HojaImporter
{
    use ConvierteCeldas;

    public function nombreHoja(): string
    {
        return 'Tallas';
    }

    protected function importarFila(array $fila, User $user): void
    {
        $codigo = $this->textoObligatorio($fila['talla'] ?? null, 'talla');
        $tipo = TipoTalla::tryFrom($this->textoObligatorio($fila['tipo'] ?? null, 'tipo'));

        if (! $tipo) {
            throw new InvalidArgumentException("tipo inválido: \"{$fila['tipo']}\". Use NUMERICA o LETRA.");
        }

        Talla::query()->updateOrCreate(
            ['codigo' => $codigo],
            [
                'orden' => (int) $this->numeroObligatorio($fila['orden'] ?? null, 'orden'),
                'tipo' => $tipo,
            ],
        );
    }
}
