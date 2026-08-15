# Catálogo y ficha de obra

## Alcance

- Archivo editorial del catálogo con navegación por disciplinas.
- Filtros combinables por artista y estado comercial.
- Tarjetas con artista, año, técnica, disponibilidad y precio condicionado por permisos.
- Ficha individual con galería, datos técnicos, edición, firma, certificado, SKU y consulta privada.
- Obras relacionadas priorizadas por artista.

## Privacidad

- Los filtros conservan `gmr_theme_visibility_meta_query()` y agregan el estado comercial con una relación AND.
- El precio sólo se imprime cuando `gmr_theme_can_view_price()` lo autoriza.
- Las consultas relacionadas aplican la misma condición de visibilidad.

## Estados comerciales

`available`, `reserved`, `sold`, `not_available`, `on_exhibition` y `archive` se presentan en español mediante helpers del tema.

## Validación de staging

- Catálogo público: 86 obras visibles, 18 por página.
- Precio público: no presente.
- Ficha probada: galería, ficha técnica, formulario y tres obras relacionadas.
- Escritorio y móvil: sin imágenes rotas ni desbordamiento horizontal.
