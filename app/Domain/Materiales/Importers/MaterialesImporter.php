<?php

namespace App\Domain\Materiales\Importers;

use App\Domain\Catalogo\Models\Responsable;
use App\Domain\Importacion\Support\ConvierteCeldas;
use App\Domain\Importacion\Support\HojaImporter;
use App\Domain\Materiales\Enums\TipoMaterial;
use App\Domain\Materiales\Enums\UnidadMaterial;
use App\Domain\Materiales\Models\Material;
use App\Domain\Materiales\Models\Proveedor;
use App\Domain\Materiales\Services\PrecioMaterialService;
use App\Models\User;
use InvalidArgumentException;

class MaterialesImporter extends HojaImporter
{
    use ConvierteCeldas;

    public function __construct(
        private readonly PrecioMaterialService $precioMaterialService,
    ) {}

    public function nombreHoja(): string
    {
        return 'Materiales';
    }

    protected function importarFila(array $fila, User $user): void
    {
        $codigo = $this->textoObligatorio($fila['codigo_material'] ?? null, 'codigo_material');

        $tipo = TipoMaterial::tryFrom($this->textoObligatorio($fila['tipo'] ?? null, 'tipo'));
        if (! $tipo) {
            throw new InvalidArgumentException("tipo inválido: \"{$fila['tipo']}\".");
        }

        $unidad = UnidadMaterial::tryFrom($this->textoObligatorio($fila['unidad'] ?? null, 'unidad'));
        if (! $unidad) {
            throw new InvalidArgumentException("unidad inválida: \"{$fila['unidad']}\".");
        }

        $codigoResponsable = $this->textoObligatorio($fila['responsable'] ?? null, 'responsable');
        $responsable = Responsable::query()->where('codigo', $codigoResponsable)->first();
        if (! $responsable) {
            throw new InvalidArgumentException("responsable \"{$codigoResponsable}\" no existe.");
        }

        $material = Material::query()->updateOrCreate(
            ['codigo' => $codigo],
            [
                'nombre' => $this->textoObligatorio($fila['nombre'] ?? null, 'nombre'),
                'tipo' => $tipo,
                'unidad' => $unidad,
                'composicion' => $this->texto($fila['composicion'] ?? null),
                'color' => $this->texto($fila['color'] ?? null),
                'ancho_m' => $this->numero($fila['ancho_m'] ?? null),
                'responsable_id' => $responsable->id,
            ],
        );

        $precioUnitario = $this->numero($fila['precio_unitario'] ?? null);
        $fechaPrecio = $this->texto($fila['fecha_precio'] ?? null);

        if ($precioUnitario === null && $fechaPrecio === null) {
            return;
        }

        $proveedor = null;
        $codigoProveedor = $this->texto($fila['codigo_proveedor'] ?? null);
        if ($codigoProveedor) {
            $proveedor = Proveedor::query()->where('codigo', $codigoProveedor)->first();
            if (! $proveedor) {
                throw new InvalidArgumentException("codigo_proveedor \"{$codigoProveedor}\" no existe.");
            }
        }

        $this->precioMaterialService->registrar(
            material: $material,
            precio: $this->numeroObligatorio($fila['precio_unitario'] ?? null, 'precio_unitario'),
            vigenteDesde: $this->fecha($fila['fecha_precio'] ?? null, 'fecha_precio'),
            proveedor: $proveedor,
            origen: 'IMPORTACION_MANUAL',
        );
    }
}
