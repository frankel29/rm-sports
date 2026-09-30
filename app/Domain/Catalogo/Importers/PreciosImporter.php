<?php

namespace App\Domain\Catalogo\Importers;

use App\Domain\Catalogo\Models\Kit;
use App\Domain\Catalogo\Models\Modelo;
use App\Domain\Catalogo\Models\Responsable;
use App\Domain\Catalogo\Models\Sku;
use App\Domain\Catalogo\Models\Talla;
use App\Domain\Catalogo\Services\PrecioVentaService;
use App\Domain\Importacion\Support\ConvierteCeldas;
use App\Domain\Importacion\Support\HojaImporter;
use App\Models\User;
use InvalidArgumentException;

class PreciosImporter extends HojaImporter
{
    use ConvierteCeldas;

    public function __construct(
        private readonly PrecioVentaService $precioVentaService,
    ) {}

    public function nombreHoja(): string
    {
        return 'Precios';
    }

    protected function importarFila(array $fila, User $user): void
    {
        $codigoProducto = $this->textoObligatorio($fila['codigo_producto'] ?? null, 'codigo_producto');
        $codigoResponsable = $this->textoObligatorio($fila['responsable'] ?? null, 'responsable');

        $responsable = Responsable::query()->where('codigo', $codigoResponsable)->first();
        if (! $responsable) {
            throw new InvalidArgumentException("responsable \"{$codigoResponsable}\" no existe.");
        }

        $vendible = $this->resolverVendible($codigoProducto, $fila['talla'] ?? null);

        $this->precioVentaService->registrar(
            vendible: $vendible,
            precio: $this->numeroObligatorio($fila['precio_venta'] ?? null, 'precio_venta'),
            incluyeIva: $this->booleano($fila['incluye_iva'] ?? null),
            vigenteDesde: $this->fecha($fila['vigente_desde'] ?? null, 'vigente_desde'),
            responsable: $responsable,
            origen: 'IMPORTACION_MANUAL',
            notas: $this->texto($fila['notas'] ?? null),
        );
    }

    private function resolverVendible(string $codigoProducto, mixed $tallaCelda): Sku|Kit
    {
        $modelo = Modelo::query()->where('codigo', $codigoProducto)->first();

        if ($modelo) {
            $codigoTalla = $this->textoObligatorio($tallaCelda, 'talla');
            $talla = Talla::query()->where('codigo', $codigoTalla)->first();

            if (! $talla) {
                throw new InvalidArgumentException("talla \"{$codigoTalla}\" no existe.");
            }

            $sku = Sku::query()->where('modelo_id', $modelo->id)->where('talla_id', $talla->id)->first();

            if (! $sku) {
                throw new InvalidArgumentException("No existe el SKU {$codigoProducto}-{$codigoTalla}. Genere los SKUs del modelo primero.");
            }

            return $sku;
        }

        $kit = Kit::query()->where('codigo', $codigoProducto)->first();

        if (! $kit) {
            throw new InvalidArgumentException("codigo_producto \"{$codigoProducto}\" no coincide con ningún modelo ni conjunto.");
        }

        return $kit;
    }
}
