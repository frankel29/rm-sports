<?php

namespace App\Domain\Materiales\Importers;

use App\Domain\Catalogo\Models\Modelo;
use App\Domain\Catalogo\Models\Responsable;
use App\Domain\Catalogo\Models\Talla;
use App\Domain\Importacion\Support\ConvierteCeldas;
use App\Domain\Importacion\Support\HojaImporter;
use App\Domain\Materiales\Models\Proveedor;
use App\Domain\Materiales\Services\CostoCompraService;
use App\Models\User;
use InvalidArgumentException;

class CostoReventaImporter extends HojaImporter
{
    use ConvierteCeldas;

    public function __construct(
        private readonly CostoCompraService $costoCompraService,
    ) {}

    public function nombreHoja(): string
    {
        return 'Costo_reventa';
    }

    protected function importarFila(array $fila, User $user): void
    {
        $codigoModelo = $this->textoObligatorio($fila['codigo_modelo'] ?? null, 'codigo_modelo');
        $modelo = Modelo::query()->where('codigo', $codigoModelo)->first();
        if (! $modelo) {
            throw new InvalidArgumentException("codigo_modelo \"{$codigoModelo}\" no existe.");
        }

        $talla = null;
        $codigoTalla = $this->texto($fila['talla'] ?? null);
        if ($codigoTalla) {
            $talla = Talla::query()->where('codigo', $codigoTalla)->first();
            if (! $talla) {
                throw new InvalidArgumentException("talla \"{$codigoTalla}\" no existe.");
            }
        }

        $proveedor = null;
        $codigoProveedor = $this->texto($fila['codigo_proveedor'] ?? null);
        if ($codigoProveedor) {
            $proveedor = Proveedor::query()->where('codigo', $codigoProveedor)->first();
            if (! $proveedor) {
                throw new InvalidArgumentException("codigo_proveedor \"{$codigoProveedor}\" no existe.");
            }
        }

        $codigoResponsable = $this->textoObligatorio($fila['responsable'] ?? null, 'responsable');
        $responsable = Responsable::query()->where('codigo', $codigoResponsable)->first();
        if (! $responsable) {
            throw new InvalidArgumentException("responsable \"{$codigoResponsable}\" no existe.");
        }

        $this->costoCompraService->registrar(
            modelo: $modelo,
            costo: $this->numeroObligatorio($fila['costo_unitario'] ?? null, 'costo_unitario'),
            vigenteDesde: $this->fecha($fila['fecha'] ?? null, 'fecha'),
            responsable: $responsable,
            talla: $talla,
            proveedor: $proveedor,
            origen: 'IMPORTACION_MANUAL',
            notas: $this->texto($fila['notas'] ?? null),
        );
    }
}
