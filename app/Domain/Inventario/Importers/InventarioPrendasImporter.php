<?php

namespace App\Domain\Inventario\Importers;

use App\Domain\Catalogo\Models\Modelo;
use App\Domain\Catalogo\Models\Sku;
use App\Domain\Catalogo\Models\Talla;
use App\Domain\Importacion\Support\ConvierteCeldas;
use App\Domain\Importacion\Support\HojaImporter;
use App\Domain\Inventario\Enums\TipoMovimientoInventario;
use App\Domain\Inventario\Enums\Ubicacion;
use App\Domain\Inventario\Services\InventarioService;
use App\Models\User;
use InvalidArgumentException;

class InventarioPrendasImporter extends HojaImporter
{
    use ConvierteCeldas;

    public function __construct(
        private readonly InventarioService $inventarioService,
    ) {}

    public function nombreHoja(): string
    {
        return 'Inventario_prendas';
    }

    protected function importarFila(array $fila, User $user): void
    {
        $codigoModelo = $this->textoObligatorio($fila['codigo_modelo'] ?? null, 'codigo_modelo');
        $modelo = Modelo::query()->where('codigo', $codigoModelo)->first();
        if (! $modelo) {
            throw new InvalidArgumentException("codigo_modelo \"{$codigoModelo}\" no existe.");
        }

        $codigoTalla = $this->textoObligatorio($fila['talla'] ?? null, 'talla');
        $talla = Talla::query()->where('codigo', $codigoTalla)->first();
        if (! $talla) {
            throw new InvalidArgumentException("talla \"{$codigoTalla}\" no existe.");
        }

        $sku = Sku::query()
            ->where('modelo_id', $modelo->id)
            ->where('talla_id', $talla->id)
            ->first();

        if (! $sku) {
            throw new InvalidArgumentException("No existe el SKU {$codigoModelo}-{$codigoTalla}. Genere los SKUs del modelo primero.");
        }

        $codigoResponsable = $this->textoObligatorio($fila['responsable'] ?? null, 'responsable');
        if ($modelo->responsable->codigo !== $codigoResponsable) {
            throw new InvalidArgumentException("responsable \"{$codigoResponsable}\" no coincide con el responsable del modelo ({$modelo->responsable->codigo}).");
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

        $this->inventarioService->registrar(
            sku: $sku,
            tipo: TipoMovimientoInventario::INVENTARIO_INICIAL,
            cantidad: $this->numeroObligatorio($fila['cantidad'] ?? null, 'cantidad'),
            fechaHecho: $this->fecha($fila['fecha_conteo'] ?? null, 'fecha_conteo'),
            user: $user,
            ubicacion: $ubicacion?->value,
            origen: 'IMPORTACION_MANUAL',
            nota: $nota === '' ? null : $nota,
        );
    }
}
