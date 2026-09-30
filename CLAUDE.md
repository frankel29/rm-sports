# CLAUDE.md — Sistema de manufactura, inventario y ventas de uniformes

Este archivo es el contexto permanente del proyecto. Léelo completo antes de
proponer o escribir código. Si una decisión contradice este archivo, detente y
pregunta.

## Negocio
Negocio familiar ecuatoriano (Latacunga) que fabrica y vende uniformes escolares
y deportivos, y además revende algunos productos comprados a proveedores.
Operaban sin inventario digital. Las prendas (chompa, pantalón, camiseta
exterior/interior, etc.) se fabrican en lotes consumiendo materia prima; cada
prenda puede usar materiales distintos (gabardina, calentador, jersey, piqué,
polar, etc.). Algunas prendas se venden como conjunto (ej. "exterior" = chompa +
pantalón) y también sueltas. La venta es mayoritariamente presencial y por
pedidos directos (local, WhatsApp, colegios); la web es solo vitrina. La demanda
es fuertemente estacional (inicio de clases). Moneda: USD.

## Objetivo actual (prioridad absoluta)
Sistema transaccional robusto y MUY fácil de usar para capturar datos
estructurados desde el día 1. La IA es fase 2 y depende de estos datos. El mayor
riesgo es la adopción: si registrar es lento, la familia dejará de hacerlo.
Cada pantalla de registro frecuente debe completarse en segundos, desde celular.

## Stack
- Laravel (última versión estable) como monolito modular. Sin microservicios.
- Filament (última versión estable) para el backoffice.
- POS como PWA optimizada para celular (Livewire o Inertia + Vue).
- PostgreSQL (MySQL 8 aceptable). Montos en DECIMAL, nunca float.
- Pest para tests.
- Fase 2: servicio ML en Python (FastAPI) como job programado que escribe
  sugerencias en tablas. NO construir ahora.

## Módulos (namespaces separados en app/Domain/<Modulo>)
Catalogo, Materiales, Costeo, Produccion, Inventario, Compras, Ventas, Pedidos,
Portal, Sugerencias (fase posterior).

## Reglas de modelo de datos (no negociables)
1. Unidad de análisis: SKU = modelo × talla, donde modelo = colegio × prenda
   (× género/color). Nunca el lote.
2. Cada modelo tiene tipo_abastecimiento:
   - FABRICADO: tiene BOM y se produce en lotes.
   - COMPRADO: producto de reventa; sin BOM ni lote; entra por compra a
     proveedor con costo de compra.
   - Una prenda comprada y luego personalizada (ej. camiseta lisa + bordado) es
     FABRICADO cuyo BOM incluye la prenda base como material.
3. Stock solo de piezas (SKUs). Los conjuntos son kits virtuales: definidos por
   modelos componentes; la talla de cada componente se elige al vender (puede
   ser chompa 12 + pantalón 14). Disponibilidad del kit = mínimo de sus
   componentes. Vender un kit genera una salida por cada pieza. Nunca se guarda
   stock de kits.
4. Materiales con catálogo propio: tipo (tela, insumo, prenda_base, etc.),
   unidad (m, unidad, kg, rollo), atributos (composición, color, ancho).
5. BOM por modelo, con consumo estándar opcionalmente por talla (línea con
   talla NULL aplica a todas las tallas; una línea con talla específica la
   reemplaza). Distintos modelos pueden usar materiales distintos. Las
   tarifas de mano de obra se pagan POR_PRENDA o SUELDO_MENSUAL; su vigencia
   inicia en la fecha de importación.
6. Inventario como ledger inmutable (kardex), uno para SKUs y otro para
   materiales. El stock es la suma de movimientos. Los movimientos nunca se
   editan ni se borran; los errores se corrigen con un movimiento de reverso.
   Tipos: INVENTARIO_INICIAL, PRODUCCION, COMPRA, VENTA, DEVOLUCION,
   CAMBIO_TALLA, AJUSTE, CONSUMO (materiales), REVERSO.
7. El stock negativo se PERMITE pero se marca como alerta (no bloquear ventas:
   bloquear frustra la adopción).
8. Todo registro guarda fecha_hecho, fecha_registro (created_at) y usuario.
9. Precios de venta, costos de compra, precios de materiales y tarifas de mano
   de obra con vigencia (vigente_desde / vigente_hasta). Nunca sobrescribir.
10. Lotes con proyectado vs. real en campos separados, etapas con inicio/fin,
    y costo estándar/real guardados como fotografía al cierre.
11. Registro de demanda no atendida (SKU o modelo+talla, cantidad, fecha,
    canal) accesible con un toque desde el POS.
12. Campo canal (LOCAL, WHATSAPP, COLEGIO, WEB, OTRO) en ventas y pedidos.
    Campo origen (CAPTURA, SRI_HISTORICO, IMPORTACION_MANUAL) en datos.
13. IVA configurable con vigencia; precios indican si incluyen IVA.
14. Campo responsable: identifica la línea de negocio (hoy RM = línea
    deportiva, GL = línea no deportiva: sacos de lana, pantalones de tela,
    camisas, etc.). Ambos venden al mismo colegio, por eso NO va en colegios.
    Va en: modelos, kits, precios, proveedores, materiales, costos de compra,
    tarifas de mano de obra, gastos y conteos de inventario. En ventas,
    pedidos, lotes y movimientos se hereda del modelo a nivel de LÍNEA (una
    misma venta puede mezclar productos de RM y GL), no de la cabecera.
    El stock NO se separa por responsable. Modelarlo como catálogo (tabla
    responsables), no como enum fijo, porque la administración del negocio
    puede cambiar. Debe poder filtrarse y reportarse por responsable.
    El negocio vende solo en Latacunga: no hay campo ciudad.
15. Los datos iniciales llegan en la plantilla Excel "plantilla_datos_iniciales"
    (una hoja por entidad, fila 1 = nombres de columna, filas con códigos que
    empiezan con "EJ" son ejemplos y se ignoran). Los importadores deben
    aceptar ese formato tal cual.

## Costeo
Costo unitario fabricado = materiales (BOM × precio vigente) + merma +
bordado/estampado + mano de obra + indirectos prorrateados.
Costo unitario comprado = costo de compra vigente (+ flete si aplica).
Se compara estándar vs. real por lote. Empezar simple.

## Datos históricos
Facturas electrónicas SRI (XML) parciales: usar para estacionalidad y mezcla de
tallas/prendas, NO como volumen total. Importar con origen = SRI_HISTORICO.

## Estrategia de IA (fase 2, no construir aún)
Pregunta central: ¿qué produzco/compro, cuánto y cuándo?
Sugerido = demanda estimada − stock − lotes en curso − compras pendientes
+ pedidos confirmados + stock de seguridad. Se registra si cada sugerencia fue
aceptada, modificada o ignorada.

## Qué NO hacer
- No predecir a nivel lote. No guardar stock de conjuntos.
- No mostrar cantidades exactas en la web.
- No agregar infraestructura compleja (colas externas, microservicios, k8s).
- No editar ni borrar movimientos de inventario.
- No usar float para dinero o cantidades.

## Forma de trabajo
- Antes de cada módulo, presenta un plan corto (tablas, clases, pantallas) y
  espera aprobación.
- Migraciones pequeñas y reversibles. Seeders con datos de ejemplo realistas.
- Toda lógica de inventario y costeo en servicios de dominio con tests Pest.
- Interfaz y textos en español.
