<?php

namespace Database\Seeders;

use App\Domain\Catalogo\Models\Modelo;
use App\Domain\Catalogo\Models\Talla;
use App\Domain\Inventario\Enums\TipoMovimientoInventario;
use App\Domain\Inventario\Enums\TipoMovimientoMaterial;
use App\Domain\Inventario\Enums\Ubicacion;
use App\Domain\Inventario\Services\InventarioMaterialService;
use App\Domain\Inventario\Services\InventarioService;
use App\Domain\Materiales\Models\Material;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class InventarioInicialDemoSeeder extends Seeder
{
    public function __construct(
        private readonly InventarioService $inventarioService,
        private readonly InventarioMaterialService $inventarioMaterialService,
    ) {}

    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@rmsports.test')->firstOrFail();
        $fechaConteo = Carbon::create(2026, 9, 15);

        $chompa = Modelo::query()->where('codigo', 'COL01-CHOMPA')->firstOrFail();
        $pantalon = Modelo::query()->where('codigo', 'COL01-PANTALON')->firstOrFail();
        $camisetaInt = Modelo::query()->where('codigo', 'GENERICO-CAMISETA_INT')->firstOrFail();

        $talla8 = Talla::query()->where('codigo', '8')->firstOrFail();
        $tallaM = Talla::query()->where('codigo', 'M')->firstOrFail();

        $skuChompa8 = $chompa->skus()->where('talla_id', $talla8->id)->firstOrFail();
        $skuPantalon8 = $pantalon->skus()->where('talla_id', $talla8->id)->firstOrFail();
        $skuCamisetaIntM = $camisetaInt->skus()->where('talla_id', $tallaM->id)->firstOrFail();

        foreach ([[$skuChompa8, 12], [$skuPantalon8, 10], [$skuCamisetaIntM, 20]] as [$sku, $cantidad]) {
            if ($this->inventarioService->stock($sku) > 0) {
                continue;
            }

            $this->inventarioService->registrar(
                sku: $sku,
                tipo: TipoMovimientoInventario::INVENTARIO_INICIAL,
                cantidad: $cantidad,
                fechaHecho: $fechaConteo,
                user: $admin,
                ubicacion: Ubicacion::LOCAL->value,
                nota: 'Conteo inicial de datos de ejemplo.',
            );
        }

        $calentador = Material::query()->where('codigo', 'CALENTADOR')->firstOrFail();
        $gabardina = Material::query()->where('codigo', 'GABARDINA')->firstOrFail();

        foreach ([[$calentador, 35.5], [$gabardina, 20]] as [$material, $cantidad]) {
            if ($this->inventarioMaterialService->stock($material) > 0) {
                continue;
            }

            $this->inventarioMaterialService->registrar(
                material: $material,
                tipo: TipoMovimientoMaterial::INVENTARIO_INICIAL,
                cantidad: $cantidad,
                fechaHecho: $fechaConteo,
                user: $admin,
                ubicacion: Ubicacion::TALLER->value,
                nota: 'Conteo inicial de datos de ejemplo.',
            );
        }
    }
}
