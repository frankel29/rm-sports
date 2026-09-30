<?php

namespace Database\Seeders;

use App\Domain\Catalogo\Enums\CategoriaPrenda;
use App\Domain\Catalogo\Enums\Genero;
use App\Domain\Catalogo\Enums\TipoAbastecimiento;
use App\Domain\Catalogo\Enums\TipoTalla;
use App\Domain\Catalogo\Models\Colegio;
use App\Domain\Catalogo\Models\Kit;
use App\Domain\Catalogo\Models\KitComponente;
use App\Domain\Catalogo\Models\Modelo;
use App\Domain\Catalogo\Models\Prenda;
use App\Domain\Catalogo\Models\Responsable;
use App\Domain\Catalogo\Models\Talla;
use App\Domain\Catalogo\Services\ConfiguracionIvaService;
use App\Domain\Catalogo\Services\PrecioVentaService;
use App\Domain\Catalogo\Services\SkuGeneratorService;
use App\Domain\Materiales\Models\Proveedor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CatalogoDemoSeeder extends Seeder
{
    public function __construct(
        private readonly SkuGeneratorService $skuGeneratorService,
        private readonly PrecioVentaService $precioVentaService,
        private readonly ConfiguracionIvaService $configuracionIvaService,
    ) {}

    public function run(): void
    {
        $rm = Responsable::query()->where('codigo', 'RM')->firstOrFail();
        $gl = Responsable::query()->where('codigo', 'GL')->firstOrFail();

        $colegio = Colegio::query()->updateOrCreate(
            ['codigo' => 'COL01'],
            ['nombre' => 'Unidad Educativa Hermano Miguel', 'contacto' => 'Rectorado', 'telefono' => '032800111'],
        );

        $generico = Colegio::query()->updateOrCreate(
            ['codigo' => 'GENERICO'],
            ['nombre' => 'Sin colegio (línea deportiva)', 'es_generico' => true],
        );

        $prendas = [
            'CHOMPA' => ['nombre' => 'Chompa deportiva', 'categoria' => CategoriaPrenda::DEPORTIVO],
            'PANTALON' => ['nombre' => 'Pantalón deportivo', 'categoria' => CategoriaPrenda::DEPORTIVO],
            'CAMISETA_EXT' => ['nombre' => 'Camiseta exterior', 'categoria' => CategoriaPrenda::DEPORTIVO],
            'CAMISETA_INT' => ['nombre' => 'Camiseta interior', 'categoria' => CategoriaPrenda::DEPORTIVO],
        ];

        foreach ($prendas as $codigo => $datos) {
            Prenda::query()->updateOrCreate(['codigo' => $codigo], $datos);
        }

        $numericas = [4, 6, 8, 10, 12, 14, 16];
        foreach ($numericas as $i => $codigo) {
            Talla::query()->updateOrCreate(
                ['codigo' => (string) $codigo],
                ['orden' => $i + 1, 'tipo' => TipoTalla::NUMERICA],
            );
        }

        $letras = ['S', 'M', 'L', 'XL'];
        foreach ($letras as $i => $codigo) {
            Talla::query()->updateOrCreate(
                ['codigo' => $codigo],
                ['orden' => count($numericas) + $i + 1, 'tipo' => TipoTalla::LETRA],
            );
        }

        $talla4 = Talla::query()->where('codigo', '4')->firstOrFail();
        $tallaXL = Talla::query()->where('codigo', 'XL')->firstOrFail();
        $tallaS = Talla::query()->where('codigo', 'S')->firstOrFail();
        $talla8 = Talla::query()->where('codigo', '8')->firstOrFail();
        $tallaM = Talla::query()->where('codigo', 'M')->firstOrFail();

        $chompa = Modelo::query()->updateOrCreate(
            ['codigo' => 'COL01-CHOMPA'],
            [
                'colegio_id' => $colegio->id,
                'prenda_id' => Prenda::query()->where('codigo', 'CHOMPA')->value('id'),
                'genero' => Genero::UNISEX,
                'color' => 'Azul marino',
                'tipo_abastecimiento' => TipoAbastecimiento::FABRICADO,
                'responsable_id' => $rm->id,
                'talla_desde_id' => $talla4->id,
                'talla_hasta_id' => $tallaXL->id,
            ],
        );
        $this->skuGeneratorService->generarParaModelo($chompa, $talla4, $tallaXL);

        $pantalon = Modelo::query()->updateOrCreate(
            ['codigo' => 'COL01-PANTALON'],
            [
                'colegio_id' => $colegio->id,
                'prenda_id' => Prenda::query()->where('codigo', 'PANTALON')->value('id'),
                'genero' => Genero::UNISEX,
                'color' => 'Azul marino',
                'tipo_abastecimiento' => TipoAbastecimiento::FABRICADO,
                'responsable_id' => $rm->id,
                'talla_desde_id' => $talla4->id,
                'talla_hasta_id' => $tallaXL->id,
            ],
        );
        $this->skuGeneratorService->generarParaModelo($pantalon, $talla4, $tallaXL);

        $camisetaExt = Modelo::query()->updateOrCreate(
            ['codigo' => 'GENERICO-CAMISETA_EXT'],
            [
                'colegio_id' => $generico->id,
                'prenda_id' => Prenda::query()->where('codigo', 'CAMISETA_EXT')->value('id'),
                'genero' => Genero::UNISEX,
                'color' => 'Blanco',
                'tipo_abastecimiento' => TipoAbastecimiento::FABRICADO,
                'responsable_id' => $rm->id,
                'talla_desde_id' => $tallaS->id,
                'talla_hasta_id' => $tallaXL->id,
            ],
        );
        $this->skuGeneratorService->generarParaModelo($camisetaExt, $tallaS, $tallaXL);

        $proveedorTextilandia = Proveedor::query()->where('codigo', 'TEXTILANDIA')->firstOrFail();

        $camisetaInt = Modelo::query()->updateOrCreate(
            ['codigo' => 'GENERICO-CAMISETA_INT'],
            [
                'colegio_id' => $generico->id,
                'prenda_id' => Prenda::query()->where('codigo', 'CAMISETA_INT')->value('id'),
                'genero' => Genero::UNISEX,
                'color' => 'Blanco',
                'tipo_abastecimiento' => TipoAbastecimiento::COMPRADO,
                'proveedor_id' => $proveedorTextilandia->id,
                'responsable_id' => $gl->id,
                'talla_desde_id' => $tallaS->id,
                'talla_hasta_id' => $tallaXL->id,
            ],
        );
        $this->skuGeneratorService->generarParaModelo($camisetaInt, $tallaS, $tallaXL);

        $kit = Kit::query()->updateOrCreate(
            ['codigo' => 'COL01-EXTERIOR'],
            ['nombre' => 'Exterior (chompa + pantalón)', 'responsable_id' => $rm->id],
        );
        KitComponente::query()->updateOrCreate(['kit_id' => $kit->id, 'modelo_id' => $chompa->id], ['cantidad' => 1]);
        KitComponente::query()->updateOrCreate(['kit_id' => $kit->id, 'modelo_id' => $pantalon->id], ['cantidad' => 1]);

        $this->configuracionIvaService->registrar(15, Carbon::create(2025, 1, 1));

        $skuChompa8 = $chompa->skus()->where('talla_id', $talla8->id)->firstOrFail();
        $skuPantalon8 = $pantalon->skus()->where('talla_id', $talla8->id)->firstOrFail();
        $skuCamisetaIntM = $camisetaInt->skus()->where('talla_id', $tallaM->id)->firstOrFail();

        $this->precioVentaService->registrar($skuChompa8, 15.00, true, Carbon::create(2026, 9, 1), $rm);
        $this->precioVentaService->registrar($skuPantalon8, 12.00, true, Carbon::create(2026, 9, 1), $rm);
        $this->precioVentaService->registrar($skuCamisetaIntM, 6.00, true, Carbon::create(2026, 9, 1), $gl);
        $this->precioVentaService->registrar($kit, 25.00, true, Carbon::create(2026, 9, 1), $rm);
    }
}
