# Auditoria del inventario y mapa de migracion

Fecha de corte: 15 de agosto de 2026. Fuente: staging aislado de Duplicator. La auditoria fue de solo lectura.

## Resumen ejecutivo

El inventario contiene 131 productos: 86 publicados y 45 borradores. La base visual esta bien conservada, pero la clasificacion necesita normalizacion antes de construir las plantillas definitivas.

| Indicador | Cobertura |
|---|---:|
| Imagen destacada | 130 / 131 |
| Galeria adicional | 17 / 131 |
| Precio | 129 / 131 |
| SKU | 129 / 131 |
| Ano (`pa_ano`) | 82 / 131 |
| Medidas (`pa_medidas`) | 126 / 131 |
| Tecnica (`pa_tecnica`) | 130 / 131 |
| Descripcion corta | 5 / 131 |
| Descripcion larga | 4 / 131 |
| Datos Elementor dentro del producto | 0 / 131 |

La ausencia de datos Elementor en los productos es favorable: las fichas no dependen de maquetacion individual y pueden migrarse a una plantilla central del tema.

## Diagnostico de categorias

`product_cat` mezcla actualmente disciplinas, artistas y colecciones. La migracion debe separar esos conceptos sin cambiar inicialmente los slugs de las cuatro disciplinas canonicas.

### Disciplinas que permanecen en `product_cat`

| Slug | Productos asignados |
|---|---:|
| `obra-grafica` | 25 |
| `escultura` | 24 |
| `pintura` | 17 |
| `joyeria` | 12 |

Hay 65 productos sin disciplina y 12 con dos disciplinas. Los 12 casos dobles tienen simultaneamente `escultura` y `joyeria`; deben revisarse porque probablemente representan joyeria escultorica, variantes o una clasificacion heredada demasiado amplia.

### Categorias que migran a `gmr_artist`

| Slug actual | Asignaciones detectadas | Destino |
|---|---:|---|
| `elmar-rojas` | 55 | Artista Elmar Rojas, perfil especial |
| `irene-carlos` | 42 | Artista Irene Carlos |
| `rodolfo-abularach` | 5 | Artista Rodolfo Abularach |
| `milton-bautista` | 4 | Artista Milton Bautista |
| `miguel-hernandez` | 3 | Artista Miguel Hernandez |
| `ramon-avila` | 2 | Artista Ramon Avila |
| `rudy-cotton` | 2 | Artista Rudy Cotton |
| `armando-lara` | 2 | Artista Armando Lara |
| `hector-tadeo` | 2 | Artista Hector Tadeo |
| `bernard-dreyfus` | 1 | Requiere validar nombre |
| `ednard-dreyfus` | 1 | Requiere validar si es error tipografico |

Tambien existen categorias sin asignaciones actuales para Elsie Wunderlich y Juan Navipop. Deben conservarse como candidatos editoriales, pero no asociarse automaticamente a obras.

Trece productos no tienen categoria de artista. Doce corresponden al conjunto que mezcla Escultura y Joyeria; el restante es el producto 5472. El producto 6335 tiene simultaneamente `bernard-dreyfus` y `ednard-dreyfus`, por lo que queda bloqueado para revision manual.

### Categorias que migran a `gmr_collection`

- `coleccion-exclusiva-2015`
- `coleccion-exclusiva-2016`
- `coleccion-exclusiva-gran-formato-2016`
- `de-las-alegrias-poeticas`
- `de-las-doncellas`
- `de-las-doncellas-del-campo`
- `de-las-poesias`
- `de-las-tradiciones`

Las categorias genericas heredadas del demo (`Art`, `Clothing`, `Design`, `Media`, `New`, `Popular`, `Uncategorized` y equivalentes) no tienen uso real y se consideran residuo. No se eliminaran en la primera migracion; solo se excluiran del nuevo formulario y de la navegacion.

## Atributos heredados

### Ano

`pa_ano` se convertira a `gmr_year_start` y `gmr_year_end`. Los valores simples pasan directamente; rangos como `2003 - 2004` se dividen. Los productos sin ano quedan marcados para revision, no se inventa una fecha.

### Medidas

`pa_medidas` contiene 126 fichas y debe convertirse a campos numericos en centimetros:

- dos cifras: alto y ancho;
- tres cifras: alto, ancho y profundidad;
- expresiones con `diametro`: diametro;
- textos como `Coleccion` o `Individual`: conservar en `gmr_dimensions_notes` y revisar;
- valores defectuosos como `22` o `70 x 70 cms}`: revision manual.

El atributo original se conserva durante la verificacion y se retira solo cuando las conversiones sean aprobadas.

### Tecnica, soporte y materiales

`pa_tecnica` tiene informacion en 130 productos, pero combina tecnica, soporte, materiales y acabados en una sola frase. La migracion automatica sera conservadora:

1. guardar siempre la frase original en `gmr_technique_notes`;
2. asociar tecnicas inequívocas a `gmr_technique` (oleo, acrilico, serigrafia, litografia, grabado, mezotinta, mixografia, piezografia, tecnica mixta);
3. asociar soportes inequívocos a `gmr_support` (tela, lienzo, papel, papel Fabriano, madera);
4. asociar materiales inequívocos a `gmr_material` (bronce, plata, jade, resina, piedra volcanica, oro, carboncillo, tinta, pastel);
5. enviar frases ambiguas o inconsistentes a revision manual.

Existe un valor de medida (`127 x 101 cms`) dentro de `pa_tecnica`; no se debe migrar como tecnica.

## Productos variables

Se detectaron 8 productos variables y 28 variaciones:

- Coleccion Elmar Rojas (2)
- Personajes magicos del campo (2)
- De los paseos del Pequeno Itzul (3)
- Reiki (Coleccion) (7)
- Andasolos (3)
- De las ternuras del campo (4)
- Itzul de Jade (3)
- Pequenos Itzules (4)

Como no habra compra en linea, las variaciones no deben conservarse automaticamente como opciones comerciales. Antes de migrarlas hay que decidir por conjunto si representan obras fisicas distintas, ediciones o solo opciones antiguas. Cuando sean piezas distintas, cada variacion debe convertirse en una obra independiente con SKU propio.

## Excepciones prioritarias

- Producto 5472: sin imagen, precio, SKU, artista ni disciplina.
- Producto 6351: sin precio.
- Producto 6380: sin SKU y sin disciplina.
- Producto 6335: dos posibles artistas (`bernard-dreyfus` y `ednard-dreyfus`).
- Nueve grupos de titulos repetidos; no son duplicados confirmados porque los SKU no se repiten.
- 62 productos usan `qodef_product_list_image`, una imagen auxiliar de Lekker que debe conservarse como referencia hasta comparar su calidad con la imagen destacada.

## Mapa de transformacion

| Origen | Destino canonico | Estrategia |
|---|---|---|
| Categoria de artista | `gmr_artist` | Crear termino y copiar relacion |
| Categoria de coleccion | `gmr_collection` | Crear termino y copiar relacion |
| Categoria de disciplina | `product_cat` | Mantener relacion |
| `pa_ano` | `gmr_year_start`, `gmr_year_end`, `gmr_undated` | Parseo controlado |
| `pa_medidas` | campos `gmr_height`, `gmr_width`, `gmr_depth`, `gmr_diameter` | Parseo en cm mas excepciones |
| `pa_tecnica` | `gmr_technique`, `gmr_support`, `gmr_material`, notas | Diccionario mas frase original |
| `_price` | precio WooCommerce | Conservar; visibilidad inicial `collectors` |
| `_sku` | SKU WooCommerce | Conservar sin cambios |
| `_thumbnail_id` | imagen destacada | Conservar |
| `_product_image_gallery` | galeria de producto | Conservar |
| `qodef_product_list_image` | referencia temporal | Comparar antes de retirar Lekker |

## Plan de ejecucion segura

1. Crear un comando de migracion con modo simulacion obligatorio por defecto.
2. Generar artistas y colecciones sin borrar categorias antiguas.
3. Copiar relaciones y metadatos a los campos canonicos.
4. Exportar un informe por producto con cambios propuestos, advertencias y excepciones.
5. Revisar manualmente los casos ambiguos y los productos variables.
6. Ejecutar la migracion real solo en staging y repetir la auditoria.
7. Comparar conteos, imágenes, SKU y precios antes/despues.
8. Mantener una operacion reversible que elimine exclusivamente datos creados por nuestra migracion.

La primera ejecucion no eliminara taxonomias, atributos ni metadatos heredados. Esa limpieza sera una fase posterior, cuando el nuevo tema ya haya sido validado visualmente.
