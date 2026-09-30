# Prompt inicial para Claude Code

Pega esto en Claude Code desde la raíz del repositorio, con CLAUDE.md ya creado.

---

Lee CLAUDE.md completo. Vamos a construir la base del sistema (Fase A). No
construyas producción por lotes, portal web ni ML todavía.

Objetivo de esta sesión: dejar funcionando el esqueleto del proyecto, el
catálogo, los materiales/BOM y el motor de inventario (ledger), con tests.

Antes de escribir código, muéstrame un plan con:
1. Estructura de carpetas por módulo (app/Domain/...).
2. Lista de migraciones con columnas y tipos.
3. Servicios de dominio y sus métodos públicos.
4. Recursos de Filament que crearás.
Espera mi aprobación antes de implementar.

Alcance de la Fase A:

1. Setup
   - Proyecto Laravel + Filament (versiones estables actuales; verifícalas).
   - PostgreSQL, Pest, Laravel Pint.
   - Autenticación y roles simples: ADMIN, VENDEDOR, TALLER.
   - Idioma y zona horaria: es / America/Guayaquil. Moneda USD.
   - Tabla responsables (RM, GL) según la regla 14 de CLAUDE.md, con filtro
     en Filament.

2. Catálogo
   - colegios (incluye uno genérico para prendas deportivas/sin colegio).
   - prendas (chompa, pantalón, camiseta exterior, camiseta interior, etc.).
   - tallas (con campo orden para ordenar 4, 6, 8... S, M, L).
   - modelos: colegio × prenda (+ género, color), tipo_abastecimiento
     (FABRICADO | COMPRADO), proveedor opcional, activo.
   - skus: modelo × talla, código único legible.
   - kits (conjuntos) y kit_componentes (modelos componentes y cantidad).
   - precios_venta con vigencia para SKU o kit, con incluye_iva.
   - configuración de IVA con vigencia.
   - Generador masivo: elegir modelo + rango de tallas y crear todos los SKUs.

3. Materiales y BOM
   - proveedores.
   - materiales: tipo, unidad, atributos (composición, color, ancho).
   - precios_material con vigencia.
   - bom_lineas: modelo, material, cantidad estándar, talla nullable (NULL =
     todas las tallas; talla específica la sobrescribe).
   - costos_compra con vigencia para modelos COMPRADOS.
   - Validación: un modelo COMPRADO no puede tener BOM.

4. Motor de inventario (lo más importante; con tests exhaustivos)
   - movimientos_inventario (SKUs) y movimientos_material, inmutables:
     tipo, cantidad con signo (DECIMAL), fecha_hecho, user_id, referencia
     polimórfica, costo_unitario, origen, nota, movimiento_reversado_id.
   - InventarioService: registrar(), reversar(), stock(sku, fecha?),
     stockKit(kit, tallasPorComponente), kardex(sku, desde, hasta).
   - Venta de kit = una salida por componente, todas con la misma referencia.
   - Stock negativo permitido pero reportado como alerta.
   - Impedir UPDATE/DELETE de movimientos a nivel de modelo (y, si es posible,
     con trigger en BD).
   - Tests: suma de movimientos, reverso, kit con tallas mixtas, stock a una
     fecha, stock negativo, inmutabilidad.

5. Importadores (para cargar lo que la familia envíe)
   - Leer directamente docs/plantilla_datos_iniciales.xlsx (y también CSV por hoja).
     Hojas y columnas: usar exactamente los nombres de la fila 1 de cada hoja
     (Colegios, Prendas, Tallas, Proveedores, Modelos, Conjuntos, Precios,
     Materiales, Consumo_por_prenda, Costo_reventa, Mano_de_obra,
     Gastos_mensuales, Inventario_prendas, Inventario_materiales).
     Ignorar filas cuyo código empiece con "EJ". Fechas dd/mm/aaaa.
   - Importar en el orden de las hojas, resolviendo códigos a IDs.
   - Modelos con talla_desde/talla_hasta generan sus SKUs automáticamente.
   - inventario inicial por conteo (genera movimientos INVENTARIO_INICIAL con
     la fecha_conteo), para piezas y materiales.
   - Previsualización con errores por fila antes de confirmar.
   - Guardar el campo responsable (RM | GL) donde venga.

6. Seeders con datos de ejemplo realistas (2 colegios, 4 prendas, tallas 4–16
   y S–XL, 1 producto COMPRADO, 1 kit "exterior", materiales y BOM).

Criterios de terminado: migraciones corren desde cero, seeders cargan, todos
los tests pasan, y desde Filament puedo crear un modelo, generar sus SKUs,
definir su BOM, cargar la plantilla Excel con inventario inicial y ver el
kardex de un SKU, filtrando por responsable.

Trabaja en commits pequeños con mensajes descriptivos en español.
