# Diccionario de datos

## Galeria Mayari Rojas

Estado: propuesta para aprobacion  
Version: 1.0  
Alcance: primer hito funcional

## 1. Objetivo

Definir una fuente unica y consistente para obras, artistas, disciplinas, colecciones y contenido editorial. Este documento sera la referencia para construir `mayari-core`, los formularios administrativos, la migracion y las plantillas del tema.

## 2. Evidencia del sitio actual

La auditoria del staging encontro:

- 131 productos de WooCommerce.
- 5 eventos de EventON.
- 45 entradas.
- 99 paginas.
- 4 elementos de portafolio Lekker.
- Artistas, disciplinas y colecciones mezclados en `product_cat`.
- Atributos globales existentes para año, medidas y tecnica.
- Medidas almacenadas como textos completos, lo que impide ordenar y filtrar numericamente.
- Tecnicas con duplicados ortograficos y valores incorrectos.
- Dependencia editorial de Lekker, Elementor, EventON y WooCommerce.

## 3. Convenciones

- Todos los identificadores internos nuevos usaran el prefijo `gmr_`.
- Los slugs y claves tecnicas estaran en ingles simple; las etiquetas administrativas estaran en español.
- Las fechas se almacenaran en formato WordPress/ISO y se mostraran segun la zona horaria del sitio.
- Las dimensiones se almacenaran numericamente y por separado.
- Los valores que deban filtrar el catalogo seran taxonomias o campos normalizados, no texto libre.
- Los textos curatoriales conservaran formato editorial seguro.
- Los campos privados no se expondran en REST, feeds, sitemap, busqueda ni HTML publico.

## 4. Obra

Entidad canonica: producto simple de WooCommerce (`product`).

### 4.1 Identificacion

| Campo | Clave | Tipo | Obligatorio | Publico | Notas |
|---|---|---:|---:|---:|---|
| Titulo | `post_title` | texto | Si | Segun visibilidad | Nombre oficial de la obra. |
| Slug | `post_name` | slug | Automatico | Segun visibilidad | Unico dentro de productos. |
| Codigo de inventario | `_sku` | texto unico | Si | Configurable | Ejemplo: `ART-MR-142`. |
| Artista | `gmr_artist` | taxonomia | Si | Si | Un artista principal en MVP. |
| Disciplina | `product_cat` | taxonomia jerarquica | Si | Si | Solo disciplinas y subdisciplinas. |
| Coleccion o serie | `gmr_collection` | taxonomia | No | Si | Puede pertenecer a varias. |
| Año inicial | `gmr_year_start` | entero | No | Si | Cuatro digitos. |
| Año final | `gmr_year_end` | entero | No | Si | Para rangos; igual al inicial si no aplica. |
| Sin fecha | `gmr_undated` | booleano | No | Si | Muestra `Sin fecha`. |
| Numero interno heredado | `gmr_legacy_id` | texto | Migracion | No | Referencia al sistema anterior. |

Reglas:

- El SKU debe ser unico.
- Una obra no puede tener simultaneamente año y `Sin fecha`.
- En un rango, el año final no puede ser menor al inicial.
- Las categorias de producto dejaran de almacenar artistas y colecciones.

### 4.2 Descripcion

| Campo | Clave | Tipo | Obligatorio | Publico | Notas |
|---|---|---:|---:|---:|---|
| Descripcion breve | `post_excerpt` | texto enriquecido corto | No | Segun visibilidad | Para tarjetas y encabezado. |
| Texto curatorial | `post_content` | bloques seguros | No | Segun visibilidad | Contenido principal. |
| Historia o notas | `gmr_history` | texto enriquecido | No | Configurable | Contexto adicional. |
| Observaciones internas | `gmr_internal_notes` | texto | No | Nunca | Solo gestores y administradores. |

### 4.3 Tecnica y materiales

| Campo | Clave | Tipo | Obligatorio | Publico | Notas |
|---|---|---:|---:|---:|---|
| Tecnica | `gmr_technique` | taxonomia | Segun disciplina | Si | Valores normalizados. |
| Soporte | `gmr_support` | taxonomia | No | Si | Lienzo, papel, madera, etc. |
| Materiales | `gmr_material` | taxonomia multiple | No | Si | Bronce, jade, plata, resina, etc. |
| Detalle tecnico | `gmr_technique_notes` | texto | No | Si | Matices no cubiertos por taxonomias. |

Ejemplo de normalizacion:

- Tecnica: `Oleo`.
- Soporte: `Lienzo`.
- Materiales: opcional.
- Presentacion publica: `Oleo sobre lienzo`.

No se creara un termino distinto por cada frase completa si puede componerse con tecnica, soporte y materiales.

### 4.4 Dimensiones

| Campo | Clave | Tipo | Obligatorio | Publico | Notas |
|---|---|---:|---:|---:|---|
| Alto | `gmr_height` | decimal | Segun disciplina | Si | Valor sin unidad. |
| Ancho | `gmr_width` | decimal | Segun disciplina | Si | Valor sin unidad. |
| Profundidad | `gmr_depth` | decimal | No | Si | Escultura, objetos y joyeria. |
| Diametro | `gmr_diameter` | decimal | No | Si | Alternativo cuando corresponda. |
| Unidad | `gmr_dimension_unit` | enum | Si si hay medidas | Si | `cm` en MVP. |
| Peso | `gmr_weight` | decimal | No | Configurable | Valor sin unidad. |
| Unidad de peso | `gmr_weight_unit` | enum | Si si hay peso | Configurable | `kg` o `g`. |
| Medidas variables | `gmr_dimensions_variable` | booleano | No | Si | Instalaciones o conjuntos. |
| Nota de dimensiones | `gmr_dimensions_notes` | texto | No | Si | Marco, base, conjunto, etc. |

Reglas:

- Pintura y obra grafica requieren alto y ancho salvo excepcion documentada.
- Escultura requiere alto, ancho y profundidad salvo excepcion documentada.
- Los decimales se almacenan con punto y se muestran segun idioma.
- `pa_medidas` se conservara solo durante la migracion y luego quedara obsoleto.

### 4.5 Edicion, firma y autenticidad

| Campo | Clave | Tipo | Obligatorio | Publico | Notas |
|---|---|---:|---:|---:|---|
| Pieza unica | `gmr_unique_piece` | booleano | No | Si | Valor predeterminado segun disciplina. |
| Numero de edicion | `gmr_edition_number` | texto | No | Si | Ejemplo `3/25`, `PA`, `HC`. |
| Tamaño del tiraje | `gmr_edition_size` | entero | No | Si | Cuando sea conocido. |
| Firma | `gmr_signature_status` | enum | No | Si | Firmada, no firmada, atribuida, desconocida. |
| Ubicacion de firma | `gmr_signature_location` | texto | No | Si | Reverso, frente inferior derecho, etc. |
| Certificado | `gmr_certificate_status` | enum | No | Configurable | Incluido, disponible, no disponible. |
| Procedencia | `gmr_provenance` | texto enriquecido | No | Configurable | Puede contener datos privados. |

### 4.6 Estado fisico y comercial

| Campo | Clave | Tipo | Obligatorio | Publico | Notas |
|---|---|---:|---:|---:|---|
| Estado comercial | `gmr_commercial_status` | enum | Si | Si | No depende del stock de WooCommerce. |
| Condicion | `gmr_condition` | enum | No | Configurable | Excelente, buena, restaurada, revisar. |
| Nota de condicion | `gmr_condition_notes` | texto | No | Privado por defecto | Detalles internos. |
| Ubicacion fisica | `gmr_physical_location` | texto | No | Nunca | Bodega, sala, consignacion, etc. |
| Propietario/consignante | `gmr_consignor` | referencia/texto | No | Nunca | Acceso administrativo. |

Valores de `gmr_commercial_status`:

- `available`: Disponible.
- `reserved`: Reservada.
- `sold`: Vendida.
- `not_available`: No disponible.
- `on_exhibition`: En exposicion.
- `archive`: Archivo historico.

WooCommerce mantendra el producto como no comprable en MVP. `_stock_status` no sera la fuente editorial del estado comercial.

### 4.7 Precio

| Campo | Clave | Tipo | Obligatorio | Publico | Notas |
|---|---|---:|---:|---:|---|
| Precio | `_regular_price` | decimal monetario | No | Segun regla | Fuente canonica de WooCommerce. |
| Precio activo | `_price` | decimal monetario | Automatico | Segun regla | Gestionado por WooCommerce. |
| Texto alternativo | `gmr_price_label` | texto | No | Segun regla | Ejemplo: `Consultar`. |
| Visibilidad de precio | `gmr_price_visibility` | enum | Si | Controlado | Ver reglas de acceso. |
| Precio negociable | `gmr_price_negotiable` | booleano | No | Coleccionistas | Informativo. |

Valores de `gmr_price_visibility`:

- `admins`: Administradores y gestores.
- `collectors`: Coleccionistas, gestores y administradores.
- `public`: Todos los visitantes autorizados a ver la obra.

Decision pendiente: moneda canonica del catalogo. Los datos actuales muestran precios con simbolo `$`; se debe confirmar si representan USD y si existira una unica moneda.

### 4.8 Visibilidad y destacamiento

| Campo | Clave | Tipo | Obligatorio | Publico | Notas |
|---|---|---:|---:|---:|---|
| Visibilidad | `gmr_visibility` | enum | Si | Controlado | Seguridad propia de Mayari. |
| Destacada | `gmr_featured` | booleano | No | Si | Seleccion editorial. |
| Orden editorial | `menu_order` | entero | No | No | Orden dentro de selecciones. |
| Fecha de publicacion | `post_date` | fecha/hora | Si | Si | Flujo editorial normal. |

Valores de `gmr_visibility`:

- `public`: visible para visitantes y usuarios.
- `collectors`: solo Coleccionistas, gestores y administradores.
- `hidden`: solo gestores y administradores; no se publica en catalogos.

`product_visibility` de WooCommerce puede apoyar la presentacion, pero no se usara como mecanismo de seguridad.

### 4.9 Imagenes y documentos

| Campo | Clave | Tipo | Obligatorio | Publico | Notas |
|---|---|---:|---:|---:|---|
| Imagen principal | `_thumbnail_id` | adjunto | Si | Segun obra | Requiere texto alternativo. |
| Galeria | `_product_image_gallery` | lista de adjuntos | No | Segun obra | Detalles, reverso, escala, ambiente. |
| Punto focal | `gmr_focal_point` | coordenadas | No | No | Para recortes responsivos. |
| Documentos | `gmr_documents` | lista estructurada | No | Controlado | Certificados, fichas, publicaciones. |

Cada documento tendra:

- Titulo.
- Archivo o URL.
- Tipo.
- Visibilidad: administradores, Coleccionistas o publico.
- Permiso de descarga.
- Fecha y notas internas opcionales.

Los documentos privados no deben tener una URL publica directa sin validacion de permisos.

## 5. Disciplina

Entidad canonica: `product_cat` de WooCommerce.

Valores iniciales:

- Pintura (`pintura`).
- Escultura (`escultura`).
- Obra grafica (`obra-grafica`).
- Joyeria (`joyeria`).

Se podran crear subdisciplinas cuando exista necesidad real. Artistas y colecciones actuales se retiraran de `product_cat` durante la migracion.

## 6. Artista

Entidad canonica: taxonomia enriquecida `gmr_artist`, asociada a productos y contenido editorial relevante.

| Campo | Clave | Tipo | Obligatorio | Visibilidad |
|---|---|---:|---:|---:|
| Nombre | nombre de termino | texto | Si | Publica |
| Slug | slug de termino | slug | Si | Publica |
| Biografia breve | descripcion | texto enriquecido corto | Si | Publica |
| Biografia completa | `gmr_artist_biography` | bloques/HTML seguro | No | Publica |
| Historia/trayectoria | `gmr_artist_history` | texto enriquecido | No | Publica |
| Retrato | `gmr_artist_portrait_id` | adjunto | Si | Publica |
| Portada | `gmr_artist_cover_id` | adjunto | No | Publica |
| Cronologia | `gmr_artist_timeline` | lista estructurada | No | Publica |
| Premios | `gmr_artist_awards` | lista estructurada | No | Publica |
| Publicaciones | `gmr_artist_publications` | lista estructurada | No | Configurable |
| Destacado | `gmr_artist_featured` | booleano | No | Publica |
| Tratamiento especial | `gmr_artist_special_template` | enum | No | No |
| Orden | `gmr_artist_order` | entero | No | No |
| SEO | campos SEO compatibles | estructura | No | Publica |

Elmar Rojas usara el valor `elmar` en `gmr_artist_special_template`. Sus obras seguiran perteneciendo al mismo catalogo.

## 7. Coleccion o serie

Entidad canonica: taxonomia enriquecida `gmr_collection`, asociada a productos.

| Campo | Clave | Tipo | Obligatorio | Visibilidad |
|---|---|---:|---:|---:|
| Nombre | nombre de termino | texto | Si | Segun coleccion |
| Slug | slug de termino | slug | Si | Segun coleccion |
| Subtitulo | `gmr_collection_subtitle` | texto | No | Segun coleccion |
| Periodo inicial/final | metadatos de año | enteros | No | Segun coleccion |
| Texto curatorial | `gmr_collection_text` | texto enriquecido | No | Segun coleccion |
| Portada | `gmr_collection_cover_id` | adjunto | No | Segun coleccion |
| Artistas relacionados | `gmr_collection_artists` | lista de terminos | No | Segun coleccion |
| Visibilidad | `gmr_visibility` | enum | Si | Controlado |
| Orden | `gmr_collection_order` | entero | No | No |

## 8. Agenda

Entidad nueva: tipo de contenido `gmr_event`.

Clasificacion: taxonomia `gmr_event_type`.

Tipos iniciales:

- Evento.
- Actividad.
- Exposicion.
- Conversatorio.
- Taller.

Campos principales:

- Titulo, slug, resumen y contenido.
- Tipo.
- Fecha/hora de inicio y fin.
- Evento de dia completo.
- Zona horaria heredada del sitio.
- Lugar, direccion y modalidad.
- Estado: proximo, en curso, finalizado o cancelado.
- Portada y galeria.
- Artistas, obras y colecciones relacionadas.
- Enlace de registro opcional.
- Visibilidad publica o Coleccionistas.

Los 5 eventos actuales de EventON se mapearan al nuevo tipo tras revisar fechas, recurrencia y RSVP.

## 9. Noticias

Entidad canonica: entrada de WordPress (`post`).

Se mantendran categorias editoriales separadas de las taxonomias del catalogo. Se agregaran relaciones opcionales con artistas, obras, colecciones y eventos.

## 10. Archivo multimedia

Entidad nueva: tipo de contenido `gmr_media_gallery`.

Campos:

- Titulo, slug, resumen y contenido.
- Tema/categoria.
- Portada.
- Lista ordenada de fotografias, videos o documentos.
- Creditos y derechos.
- Fecha o periodo.
- Artistas, colecciones y eventos relacionados.
- Visibilidad publica o Coleccionistas.

## 11. Consulta de obra

Entidad funcional, no publica. En MVP puede almacenarse como entrada segura de formulario y enviarse por correo en produccion.

Datos:

- Obra y SKU.
- Nombre del interesado.
- Correo y telefono opcional.
- Mensaje.
- Usuario autenticado si aplica.
- Fecha, origen y consentimiento.
- Estado interno de seguimiento.

No se almacenaran datos de pago.

## 12. Reglas por disciplina

| Campo | Pintura | Escultura | Obra grafica | Joyeria |
|---|---:|---:|---:|---:|
| Alto y ancho | Requerido | Requerido | Requerido | Segun pieza |
| Profundidad | Opcional | Requerido | Opcional | Opcional |
| Tecnica | Requerido | Requerido | Requerido | Requerido |
| Soporte | Recomendado | Opcional | Requerido | No aplica |
| Materiales | Opcional | Requerido | Opcional | Requerido |
| Edicion/tiraje | Opcional | Opcional | Requerido cuando aplique | Opcional |
| Peso | No | Recomendado | No | Opcional |
| Certificado | Recomendado | Recomendado | Recomendado | Recomendado |

## 13. Migracion del modelo anterior

1. Respaldar tablas y exportar productos.
2. Crear las nuevas taxonomias y metadatos.
3. Clasificar cada `product_cat` actual como disciplina, artista, coleccion o residuo.
4. Crear terminos equivalentes en `gmr_artist` y `gmr_collection`.
5. Relacionar obras sin cambiar inicialmente sus URLs.
6. Separar `pa_tecnica` en tecnica, soporte y materiales.
7. Convertir `pa_medidas` a campos numericos; marcar excepciones para revision manual.
8. Convertir `pa_ano` en año inicial/final.
9. Asignar visibilidad y estado comercial predeterminados.
10. Comparar conteos, SKUs, imagenes y relaciones antes de retirar datos heredados.

## 14. Decisiones pendientes de aprobacion

- Moneda canonica: confirmar USD, GTQ u otra.
- Si una obra puede tener mas de un artista en el MVP.
- Comportamiento publico de obras vendidas.
- Visibilidad predeterminada de procedencia y certificado.
- Si todos los Coleccionistas tendran el mismo catalogo privado inicialmente.
- Si se conservara RSVP de EventON o se reemplazara por un enlace/formulario simple.

## 15. Criterios de aceptacion

- Cada obra tiene SKU unico, artista, disciplina, estado y visibilidad.
- Artistas, disciplinas y colecciones son entidades separadas.
- Las dimensiones pueden ordenarse y validarse numericamente.
- El precio se almacena una sola vez y se muestra solo al rol permitido.
- Una obra privada no aparece en ningun canal publico.
- Elmar Rojas utiliza los mismos datos de catalogo sin duplicaciones.
- La migracion puede comparar el total original de 131 productos con el resultado.

