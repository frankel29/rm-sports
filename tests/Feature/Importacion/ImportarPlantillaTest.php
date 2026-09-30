<?php

use App\Domain\Catalogo\Models\Colegio;
use App\Domain\Catalogo\Models\Modelo;
use App\Domain\Catalogo\Models\Responsable;
use App\Domain\Catalogo\Models\Sku;
use App\Domain\Importacion\ImportarPlantillaService;
use App\Domain\Inventario\Services\InventarioMaterialService;
use App\Domain\Inventario\Services\InventarioService;
use App\Domain\Materiales\Models\Material;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * @param  array<string, array<int, array<string, mixed>>>  $hojas  nombre de hoja => filas (cada fila: columna => valor)
 */
function crearPlantillaDePrueba(array $hojas): string
{
    $spreadsheet = new Spreadsheet;
    $spreadsheet->removeSheetByIndex(0);

    foreach ($hojas as $nombreHoja => $filas) {
        $hoja = $spreadsheet->createSheet();
        $hoja->setTitle($nombreHoja);

        if (empty($filas)) {
            continue;
        }

        $encabezados = array_keys($filas[0]);
        foreach ($encabezados as $col => $nombreColumna) {
            $hoja->setCellValue([$col + 1, 1], $nombreColumna);
        }

        foreach ($filas as $numeroFila => $fila) {
            foreach (array_values($fila) as $col => $valor) {
                $hoja->setCellValue([$col + 1, $numeroFila + 2], $valor);
            }
        }
    }

    $ruta = storage_path('app/test-plantilla-'.uniqid().'.xlsx');
    (new Xlsx($spreadsheet))->save($ruta);

    return $ruta;
}

function filasDePruebaValidas(): array
{
    return [
        'Colegios' => [
            ['codigo_colegio' => 'T-COL', 'nombre' => 'Colegio Test', 'contacto' => null, 'telefono' => null, 'notas' => null],
        ],
        'Prendas' => [
            ['codigo_prenda' => 'T-CHOMPA', 'nombre' => 'Chompa Test', 'categoria' => 'DEPORTIVO', 'notas' => null],
        ],
        'Tallas' => [
            ['talla' => '8', 'orden' => 1, 'tipo' => 'NUMERICA'],
            ['talla' => '10', 'orden' => 2, 'tipo' => 'NUMERICA'],
        ],
        'Proveedores' => [
            ['codigo_proveedor' => 'T-PROV', 'nombre' => 'Proveedor Test', 'ruc' => null, 'telefono' => null, 'que_provee' => 'Telas', 'responsable' => 'RM', 'notas' => null],
        ],
        'Modelos' => [
            ['codigo_modelo' => 'T-COL-CHOMPA', 'codigo_colegio' => 'T-COL', 'codigo_prenda' => 'T-CHOMPA', 'genero' => 'UNISEX', 'color' => 'Azul', 'tipo_abastecimiento' => 'FABRICADO', 'codigo_proveedor' => null, 'talla_desde' => '8', 'talla_hasta' => '10', 'responsable' => 'RM', 'notas' => null, 'revision' => 'OK'],
        ],
        'Conjuntos' => [],
        'Precios' => [
            ['codigo_producto' => 'T-COL-CHOMPA', 'talla' => '8', 'precio_venta' => 15, 'incluye_iva' => 'SI', 'vigente_desde' => '01/09/2026', 'responsable' => 'RM', 'notas' => null],
        ],
        'Materiales' => [
            ['codigo_material' => 'T-TELA', 'nombre' => 'Tela Test', 'tipo' => 'TELA', 'unidad' => 'M', 'composicion' => null, 'color' => null, 'ancho_m' => 1.5, 'codigo_proveedor' => 'T-PROV', 'precio_unitario' => 4.5, 'fecha_precio' => '15/08/2026', 'responsable' => 'RM'],
        ],
        'Consumo_por_prenda' => [
            ['codigo_modelo' => 'T-COL-CHOMPA', 'codigo_material' => 'T-TELA', 'talla' => null, 'cantidad' => 1.4, 'unidad' => 'M', 'notas' => null],
        ],
        'Costo_reventa' => [],
        'Mano_de_obra' => [
            ['codigo_prenda' => 'T-CHOMPA', 'operacion' => 'CONFECCION', 'como_se_paga' => 'POR_PRENDA', 'valor' => 1.5, 'responsable' => 'RM', 'notas' => null],
        ],
        'Gastos_mensuales' => [
            ['concepto' => 'Arriendo test', 'monto_mensual' => 100, 'responsable' => 'RM', 'notas' => null],
        ],
        'Inventario_prendas' => [
            ['codigo_modelo' => 'T-COL-CHOMPA', 'talla' => '8', 'cantidad' => 12, 'ubicacion' => 'LOCAL', 'responsable' => 'RM', 'fecha_conteo' => '15/10/2026', 'contado_por' => 'Juan', 'notas' => null],
        ],
        'Inventario_materiales' => [
            ['codigo_material' => 'T-TELA', 'cantidad' => 35.5, 'unidad' => 'M', 'ubicacion' => 'TALLER', 'responsable' => 'RM', 'fecha_conteo' => '15/10/2026', 'contado_por' => 'Juan', 'notas' => null],
        ],
    ];
}

beforeEach(function () {
    Responsable::query()->create(['codigo' => 'RM', 'nombre' => 'RM']);
    Responsable::query()->create(['codigo' => 'GL', 'nombre' => 'GL']);
    $this->user = User::factory()->create();
    $this->servicio = app(ImportarPlantillaService::class);
});

it('previsualiza sin persistir nada y sin errores para un archivo válido', function () {
    $ruta = crearPlantillaDePrueba(filasDePruebaValidas());

    $resultados = $this->servicio->previsualizarTodo($ruta, $this->user);

    foreach ($resultados as $hoja => $resultado) {
        expect($resultado->filasConError)->toBe(0, "La hoja {$hoja} no debería tener errores en la previsualización");
    }

    expect(Colegio::query()->count())->toBe(0)
        ->and(Modelo::query()->count())->toBe(0)
        ->and(Material::query()->count())->toBe(0);
});

it('confirma la importación completa: catálogo, SKUs automáticos, BOM, precios e inventario inicial', function () {
    $ruta = crearPlantillaDePrueba(filasDePruebaValidas());

    $resultados = $this->servicio->confirmarTodo($ruta, $this->user);

    foreach ($resultados as $hoja => $resultado) {
        expect($resultado->filasConError)->toBe(0, "La hoja {$hoja} no debería tener errores al confirmar");
    }

    $modelo = Modelo::query()->where('codigo', 'T-COL-CHOMPA')->first();
    expect($modelo)->not->toBeNull();

    // Modelos con talla_desde/talla_hasta generan sus SKUs automáticamente.
    expect(Sku::query()->where('modelo_id', $modelo->id)->count())->toBe(2);

    $sku8 = Sku::query()->where('modelo_id', $modelo->id)->where('codigo', 'T-COL-CHOMPA-8')->firstOrFail();
    expect(app(InventarioService::class)->stock($sku8))->toBe(12.0);

    $material = Material::query()->where('codigo', 'T-TELA')->firstOrFail();
    expect(app(InventarioMaterialService::class)->stock($material))->toBe(35.5)
        ->and((float) $modelo->bomLineas()->first()->cantidad)->toBe(1.4);
});

it('una fila inválida se reporta sin bloquear otras filas u hojas, pero no se confirma nada (todo o nada)', function () {
    $filas = filasDePruebaValidas();
    // Prenda inválida: categoria fuera del catálogo permitido.
    $filas['Prendas'][] = ['codigo_prenda' => 'T-MALA', 'nombre' => 'Prenda mala', 'categoria' => 'NO_EXISTE', 'notas' => null];

    $ruta = crearPlantillaDePrueba($filas);

    $resultados = $this->servicio->confirmarTodo($ruta, $this->user);

    expect($resultados['Prendas']->filasValidas)->toBe(1)
        ->and($resultados['Prendas']->filasConError)->toBe(1)
        // Las hojas independientes (Tallas, Proveedores) no se ven afectadas por el error en Prendas.
        ->and($resultados['Tallas']->filasConError)->toBe(0)
        ->and($resultados['Proveedores']->filasConError)->toBe(0);

    // Pero como hubo un error en alguna hoja, no se confirma NADA.
    expect(Colegio::query()->count())->toBe(0)
        ->and(Modelo::query()->count())->toBe(0);
});
