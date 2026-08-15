# Experiencia editorial y privada

## Alcance de la iteracion 0.2.0

- Directorio editorial de artistas con retrato, orden configurable y acceso al archivo de cada artista.
- Pagina dedicada a Elmar Rojas con portada, introduccion, disciplinas y obras seleccionadas.
- Indice de colecciones con portada automatica de respaldo y acceso a cada archivo curatorial.
- Area Coleccionistas con inicio de sesion, control de capacidades, catalogo reservado y precios protegidos.
- Filtros de catalogo por artista y disponibilidad mediante parametros de consulta.
- Navegacion compatible con instalaciones en subdirectorios, incluido el staging de Duplicator.
- Composiciones responsivas para escritorio, tableta y movil.

## Paginas activas en staging

| Seccion | Pagina | Plantilla |
| --- | --- | --- |
| Artistas | `artistas` | `page-artistas.php` |
| Elmar Rojas | `elmar-rojas` | `page-elmar-rojas.php` |
| Colecciones | `colecciones` | `page-colecciones.php` |
| Coleccionistas | `coleccionistas` | `page-coleccionistas.php` |

Las plantillas editoriales se fuerzan desde `template_include` para reemplazar de forma segura las plantillas antiguas asignadas por Elementor.

## Privacidad

Un visitante anonimo solo recibe el formulario de acceso en Coleccionistas. El contenido privado se consulta unicamente cuando la cuenta posee `gmr_view_collector_area` o `gmr_manage_artworks`. Los precios mantienen su control independiente mediante `gmr_price_visibility`.

## Validacion

- Sintaxis PHP validada en todos los archivos del tema desplegado.
- Directorio de artistas, pagina de Elmar e indice de colecciones renderizados por HTTP.
- Formulario de acceso visible sin sesion y contenido privado ausente en la respuesta anonima.
- Los enlaces internos conservan la ruta completa del staging.

El despliegue de esta iteracion esta limitado al staging; produccion no fue modificada.
