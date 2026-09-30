<?php

namespace Database\Seeders;

use App\Domain\Catalogo\Models\Modelo;
use App\Domain\Catalogo\Models\Responsable;
use App\Domain\Catalogo\Models\Talla;
use App\Domain\Materiales\Enums\TipoMaterial;
use App\Domain\Materiales\Enums\UnidadMaterial;
use App\Domain\Materiales\Models\Material;
use App\Domain\Materiales\Models\Proveedor;
use App\Domain\Materiales\Services\BomService;
use App\Domain\Materiales\Services\CostoCompraService;
use App\Domain\Materiales\Services\PrecioMaterialService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class MaterialesDemoSeeder extends Seeder
{
    public function __construct(
        private readonly PrecioMaterialService $precioMaterialService,
        private readonly BomService $bomService,
        private readonly CostoCompraService $costoCompraService,
    ) {}

    public function run(): void
    {
        $rm = Responsable::query()->where('codigo', 'RM')->firstOrFail();
        $gl = Responsable::query()->where('codigo', 'GL')->firstOrFail();
        $nilotex = Proveedor::query()->where('codigo', 'NILOTEX')->firstOrFail();
        $textilandia = Proveedor::query()->where('codigo', 'TEXTILANDIA')->firstOrFail();

        $materialesTela = [
            'CALENTADOR' => ['nombre' => 'Calentador azul marino', 'color' => 'Azul marino', 'ancho_m' => 1.6, 'precio' => 4.50],
            'GABARDINA' => ['nombre' => 'Gabardina azul marino', 'color' => 'Azul marino', 'ancho_m' => 1.5, 'precio' => 5.20],
            'JERSEY' => ['nombre' => 'Jersey blanco', 'color' => 'Blanco', 'ancho_m' => 1.6, 'precio' => 3.80],
            'PIQUE' => ['nombre' => 'Piqué blanco', 'color' => 'Blanco', 'ancho_m' => 1.5, 'precio' => 4.10],
            'POLAR' => ['nombre' => 'Polar azul marino', 'color' => 'Azul marino', 'ancho_m' => 1.5, 'precio' => 4.90],
        ];

        $materiales = [];

        foreach ($materialesTela as $codigo => $datos) {
            $material = Material::query()->updateOrCreate(
                ['codigo' => $codigo],
                [
                    'nombre' => $datos['nombre'],
                    'tipo' => TipoMaterial::TELA,
                    'unidad' => UnidadMaterial::M,
                    'composicion' => '100% poliéster',
                    'color' => $datos['color'],
                    'ancho_m' => $datos['ancho_m'],
                    'responsable_id' => $rm->id,
                ],
            );

            $this->precioMaterialService->registrar($material, $datos['precio'], Carbon::create(2026, 8, 15), $nilotex);

            $materiales[$codigo] = $material;
        }

        $chompa = Modelo::query()->where('codigo', 'COL01-CHOMPA')->firstOrFail();
        $pantalon = Modelo::query()->where('codigo', 'COL01-PANTALON')->firstOrFail();
        $camisetaExt = Modelo::query()->where('codigo', 'GENERICO-CAMISETA_EXT')->firstOrFail();
        $camisetaInt = Modelo::query()->where('codigo', 'GENERICO-CAMISETA_INT')->firstOrFail();

        $this->bomService->agregarLinea($chompa, $materiales['CALENTADOR'], 1.4);
        $tallaXL = Talla::query()->where('codigo', 'XL')->firstOrFail();
        $this->bomService->agregarLinea($chompa, $materiales['CALENTADOR'], 1.7, $tallaXL, 'Talla XL consume más tela');

        $this->bomService->agregarLinea($pantalon, $materiales['GABARDINA'], 1.1);
        $this->bomService->agregarLinea($camisetaExt, $materiales['JERSEY'], 0.9);

        $this->costoCompraService->registrar(
            modelo: $camisetaInt,
            costo: 3.50,
            vigenteDesde: Carbon::create(2026, 8, 10),
            responsable: $gl,
            proveedor: $textilandia,
        );
    }
}
