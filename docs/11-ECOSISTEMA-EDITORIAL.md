# Ecosistema editorial

## Arquitectura

- `gmr_event`: agenda unificada para eventos, actividades, exposiciones, conversatorios y talleres.
- `gmr_event_type`: clasifica la agenda sin duplicar publicaciones.
- Entradas de WordPress: fuente canonica de Noticias.
- `gmr_media_gallery`: galerias tematicas de fotografia y memoria documental.
- `gmr_media_topic`: temas jerarquicos del archivo multimedia.

## Formulario de agenda

El bloque **Datos de agenda** permite completar inicio, final, dia completo, lugar, direccion, modalidad, estado, enlace de registro y visibilidad. El editor nativo contiene la historia del evento; el resumen y la imagen destacada alimentan las tarjetas editoriales.

## Formulario multimedia

El bloque **Datos multimedia** permite indicar periodo, creditos, IDs ordenados de imagenes y visibilidad. La imagen destacada funciona como portada. Los artistas y colecciones relacionados se asignan con las taxonomias compartidas.

## Vistas

- Agenda: archivo cronologico y filtros por tipo.
- Actividades: seleccion de actividades, talleres y conversatorios.
- Noticias: archivo editorial de entradas existentes.
- Archivo multimedia: indice tematico y pagina individual con composicion tipo masonry.

Todas las vistas comparten controles de visibilidad y estilos responsivos. El despliegue actual esta limitado al staging de Duplicator.

## Versiones

- Mayari Core `0.6.0`.
- Tema Mayari Rojas `0.3.0`.
