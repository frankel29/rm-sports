<?php

namespace App\Domain\Inventario\Importers;

use App\Domain\Importacion\Support\ConvierteCeldas;
use App\Domain\Importacion\Support\HojaImporter;
use App\Domain\Inventario\Enums\TipoMovimientoMaterial;
use App\Domain\Inventario\Enums\Ubicacion;
use App\Domain\Inventario\Services\InventarioMaterialService;
use App\Domain\Materiales\Models\Material;
use App\Models\User;
use InvalidArgumentException;

class InventarioMaterialesImporter extends HojaImporter
{
    use ConvierteCeldas;

    public function __construct(
        private readonly InventarioMaterialService $inventarioMaterialService,
    ) {}

    public function nombreHoja(): string
    {
        return 'Inventario_materiales';
    }

    protected function importarFila(array $fila, User $user): void
    {
        $codigoMaterial = $this->textoObligatorio($fila['codigo_material'] ?? null, 'codigo_material');
        $material = Material::query()->where('codigo', $codigoMaterial)->first();
        if (! $material) {
            throw new InvalidArgumentException("codigo_material \"{$codigoMaterial}\" no existe.");
        }

        $codigoResponsable = $this->textoObligatorio($fila['responsable'] ?? null, 'responsable');
        if ($material->responsable->codigo !== $codigoResponsable) {
            throw new InvalidArgumentException("responsable \"{$codigoResponsable}\" no coincide con el responsable del material ({$material->responsable->codigo}).");
        }

        $ubicacionTexto = $this->texto($fila['ubicacion'] ?? null);
        $ubicacion = $ubicacionTexto ? Ubicacion::tryFrom($ubicacionTexto) : null;
        if ($ubicacionTexto && ! $ubicacion) {
            throw new InvalidArgumentException("ubicacion inválida: \"{$ubicacionTexto}\".");
        }

        $contadoPor = $this->texto($fila['contado_por'] ?? null);
        $notas = $this->texto($fila['notas'] ?? null);
        $nota = trim(collect([
            $contadoPor ? "Contado por: {$contadoPor}." : null,
            $notas,
        ])->filter()->implode(' '));

        $this->inventarioMaterialService->registrar(
            material: $material,
            tipo: TipoMovimientoMaterial::INVENTARIO_INICIAL,
            cantidad: $this->numeroObligatorio($fila['cantidad'] ?? null, 'cantidad'),
            fechaHecho: $this->fecha($fila['fecha_conteo'] ?? null, 'fecha_conteo'),
            user: $user,
            ubicacion: $ubicacion?->value,
            origen: 'IMPORTACION_MANUAL',
            nota: $nota === '' ? null : $nota,
        );
    }
}
