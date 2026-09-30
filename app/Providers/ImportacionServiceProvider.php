<?php

namespace App\Providers;

use App\Domain\Catalogo\Importers\ColegiosImporter;
use App\Domain\Catalogo\Importers\ConjuntosImporter;
use App\Domain\Catalogo\Importers\ModelosImporter;
use App\Domain\Catalogo\Importers\PreciosImporter;
use App\Domain\Catalogo\Importers\PrendasImporter;
use App\Domain\Catalogo\Importers\TallasImporter;
use App\Domain\Costeo\Importers\GastosMensualesImporter;
use App\Domain\Costeo\Importers\ManoDeObraImporter;
use App\Domain\Importacion\ImportarPlantillaService;
use App\Domain\Importacion\Support\HojaImporter;
use App\Domain\Importacion\Support\LectorExcel;
use App\Domain\Inventario\Importers\InventarioMaterialesImporter;
use App\Domain\Inventario\Importers\InventarioPrendasImporter;
use App\Domain\Materiales\Importers\ConsumoPorPrendaImporter;
use App\Domain\Materiales\Importers\CostoReventaImporter;
use App\Domain\Materiales\Importers\MaterialesImporter;
use App\Domain\Materiales\Importers\ProveedoresImporter;
use Illuminate\Support\ServiceProvider;

class ImportacionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ImportarPlantillaService::class, function ($app) {
            $importadores = [];

            foreach ($this->clasesImportadores() as $clase) {
                /** @var HojaImporter $importador */
                $importador = $app->make($clase);
                $importadores[$importador->nombreHoja()] = $importador;
            }

            return new ImportarPlantillaService($app->make(LectorExcel::class), $importadores);
        });
    }

    /**
     * @return array<int, class-string<HojaImporter>>
     */
    private function clasesImportadores(): array
    {
        return [
            ColegiosImporter::class,
            PrendasImporter::class,
            TallasImporter::class,
            ProveedoresImporter::class,
            ModelosImporter::class,
            ConjuntosImporter::class,
            PreciosImporter::class,
            MaterialesImporter::class,
            ConsumoPorPrendaImporter::class,
            CostoReventaImporter::class,
            ManoDeObraImporter::class,
            GastosMensualesImporter::class,
            InventarioPrendasImporter::class,
            InventarioMaterialesImporter::class,
        ];
    }
}
