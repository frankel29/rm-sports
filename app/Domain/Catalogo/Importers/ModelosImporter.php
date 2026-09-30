<?php

namespace App\Domain\Catalogo\Importers;

use App\Domain\Catalogo\Enums\Genero;
use App\Domain\Catalogo\Enums\TipoAbastecimiento;
use App\Domain\Catalogo\Models\Colegio;
use App\Domain\Catalogo\Models\Modelo;
use App\Domain\Catalogo\Models\Prenda;
use App\Domain\Catalogo\Models\Responsable;
use App\Domain\Catalogo\Models\Talla;
use App\Domain\Catalogo\Services\SkuGeneratorService;
use App\Domain\Importacion\Support\ConvierteCeldas;
use App\Domain\Importacion\Support\HojaImporter;
use App\Domain\Materiales\Models\Proveedor;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class ModelosImporter extends HojaImporter
{
    use ConvierteCeldas;

    public function __construct(
        private readonly SkuGeneratorService $skuGeneratorService,
    ) {}

    public function nombreHoja(): string
    {
        return 'Modelos';
    }

    protected function importarFila(array $fila, User $user): void
    {
        $codigo = $this->textoObligatorio($fila['codigo_modelo'] ?? null, 'codigo_modelo');

        $colegio = $this->buscarPorCodigo(Colegio::class, $fila['codigo_colegio'] ?? null, 'codigo_colegio');
        $prenda = $this->buscarPorCodigo(Prenda::class, $fila['codigo_prenda'] ?? null, 'codigo_prenda');
        $responsable = $this->buscarPorCodigo(Responsable::class, $fila['responsable'] ?? null, 'responsable');

        $tipoAbastecimiento = TipoAbastecimiento::tryFrom(
            $this->textoObligatorio($fila['tipo_abastecimiento'] ?? null, 'tipo_abastecimiento')
        );

        if (! $tipoAbastecimiento) {
            throw new InvalidArgumentException("tipo_abastecimiento inválido: \"{$fila['tipo_abastecimiento']}\". Use FABRICADO o COMPRADO.");
        }

        $generoTexto = $this->texto($fila['genero'] ?? null);
        $genero = $generoTexto ? Genero::tryFrom($generoTexto) : null;

        if ($generoTexto && ! $genero) {
            throw new InvalidArgumentException("genero inválido: \"{$generoTexto}\". Use HOMBRE, MUJER o UNISEX.");
        }

        $proveedor = null;
        $codigoProveedor = $this->texto($fila['codigo_proveedor'] ?? null);
        if ($codigoProveedor) {
            $proveedor = $this->buscarPorCodigo(Proveedor::class, $codigoProveedor, 'codigo_proveedor');
        }

        $tallaDesde = null;
        $tallaHasta = null;
        $codigoTallaDesde = $this->texto($fila['talla_desde'] ?? null);
        $codigoTallaHasta = $this->texto($fila['talla_hasta'] ?? null);

        if ($codigoTallaDesde) {
            $tallaDesde = $this->buscarPorCodigo(Talla::class, $codigoTallaDesde, 'talla_desde');
        }

        if ($codigoTallaHasta) {
            $tallaHasta = $this->buscarPorCodigo(Talla::class, $codigoTallaHasta, 'talla_hasta');
        }

        $modelo = Modelo::query()->updateOrCreate(
            ['codigo' => $codigo],
            [
                'colegio_id' => $colegio->id,
                'prenda_id' => $prenda->id,
                'genero' => $genero,
                'color' => $this->texto($fila['color'] ?? null),
                'tipo_abastecimiento' => $tipoAbastecimiento,
                'proveedor_id' => $proveedor?->id,
                'responsable_id' => $responsable->id,
                'talla_desde_id' => $tallaDesde?->id,
                'talla_hasta_id' => $tallaHasta?->id,
                'revision' => $this->texto($fila['revision'] ?? null),
                'notas' => $this->texto($fila['notas'] ?? null),
            ],
        );

        if ($tallaDesde && $tallaHasta) {
            $this->skuGeneratorService->generarParaModelo($modelo, $tallaDesde, $tallaHasta);
        }
    }

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  class-string<TModel>  $modelo
     * @return TModel
     */
    private function buscarPorCodigo(string $modelo, mixed $valor, string $campo): Model
    {
        $codigo = $this->textoObligatorio($valor, $campo);
        $registro = $modelo::query()->where('codigo', $codigo)->first();

        if (! $registro) {
            $nombreCorto = class_basename($modelo);
            throw new InvalidArgumentException("{$campo} \"{$codigo}\" no existe ({$nombreCorto}). Impórtelo primero.");
        }

        return $registro;
    }
}
