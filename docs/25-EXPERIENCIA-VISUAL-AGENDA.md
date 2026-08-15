# Experiencia visual de Agenda

## Modelo editorial

- `gmr_event` continúa como fuente única para exposiciones, actividades, talleres, conversatorios y eventos.
- `gmr_event_type` alimenta filtros visibles sólo cuando existen publicaciones relacionadas.
- Los filtros actúan dinámicamente sobre el conjunto ya cargado: no recargan la página, actualizan título, contador, URL y estado activo, y conservan enlaces funcionales sin JavaScript.
- El sistema clasifica automáticamente cada contenido como próximo, en curso, finalizado o cancelado.
- La visibilidad pública, para coleccionistas u oculta se conserva en todos los listados y fichas.

## Archivo de Agenda

- Cuando existen fechas próximas, se muestran primero bajo **Próximamente**.
- Cuando no existen fechas futuras, la página se presenta como **Memoria cultural**, evitando una agenda aparentemente desactualizada.
- La primera tarjeta del archivo recibe un formato panorámico destacado; las demás forman una retícula editorial de dos columnas.
- Cada tarjeta contiene imagen, estado, tipo, fecha, título, extracto opcional y acceso a la ficha.
- Al cambiar de filtro, las tarjetas entran y salen con una transición breve compatible con `prefers-reduced-motion`.

## Ficha individual

- Hero inmersivo construido a partir de la imagen destacada.
- Tipo, estado, título, extracto y fecha permanecen legibles sobre la imagen.
- Hora, lugar, dirección y modalidad se imprimen sólo cuando están disponibles.
- El botón de registro sólo aparece en eventos vigentes.
- El editor nativo de WordPress contiene el cuerpo narrativo del evento.

## Actividades

La página Actividades reutiliza el mismo sistema visual y consulta los tipos actividad, taller y conversatorio. Agenda conserva el archivo completo y Exposición continúa disponible como filtro taxonómico.

## Validación

- Cinco eventos históricos migrados y visibles.
- Cinco imágenes de portada cargadas correctamente.
- Filtros públicos reducidos a tipos con contenido: Actividad y Exposición.
- Todo: cinco títulos únicos; Actividad: un contenido; Exposición: cuatro contenidos.
- Ficha de `Relatos Escondidos` probada con fecha, tres bloques informativos y contenido editorial.
- Escritorio y móvil sin desbordamiento horizontal.
