# Experiencia visual del Archivo Multimedia

## Archivo temático

- Encabezado compacto consistente con Artistas, Colecciones y Agenda.
- Índice bento preparado para galerías de distintos tamaños y proporciones.
- Tarjetas con portada, tema, periodo, cantidad de imágenes, extracto y acceso al recorrido.
- Filtros dinámicos por `gmr_media_topic`, con actualización de título, contador, URL y estado activo sin recargar la página.
- Los enlaces taxonómicos continúan funcionando como respaldo sin JavaScript.
- El estado sin publicaciones se presenta como una sección editorial intencional, sin inventar contenido.

## Galería individual

- Hero inmersivo con portada, temas, periodo, título, extracto y cantidad de imágenes.
- Bloque contextual construido desde el editor nativo de WordPress.
- Relaciones opcionales con artistas, colecciones y eventos.
- Recorrido fotográfico masonry de tres columnas en escritorio, dos en tableta y una en móvil.
- Créditos y derechos al final del recorrido.
- Videos y otros contenidos enriquecidos pueden insertarse desde el editor dentro del contexto de la galería.

## Administración

El formulario **Archivo multimedia > Añadir nueva** incluye:

- título, extracto, texto editorial y portada mediante campos nativos;
- fecha o periodo;
- créditos y derechos;
- selector visual y ordenable de imágenes;
- temas multimedia;
- artistas y colecciones relacionados;
- selección múltiple de eventos relacionados;
- visibilidad pública, para coleccionistas u oculta.

## Validación

- El archivo real permanece vacío y muestra su estado editorial de preparación.
- Se creó y eliminó una galería temporal usando exclusivamente imágenes existentes.
- La prueba verificó portada, tema, filtro, seis imágenes, créditos y relación con Agenda.
- Masonry validado en tres columnas de escritorio y una columna móvil.
- Sin imágenes rotas, errores PHP ni desbordamiento horizontal.
