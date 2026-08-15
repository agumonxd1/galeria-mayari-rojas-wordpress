# Experiencia visual de Elmar Rojas

## Narrativa

- Hero museográfico con obra de fondo, título editorial y acceso directo al catálogo.
- Navegación interna persistente para biografía, cronología, disciplinas, Voces, archivo y obra.
- Biografía y trayectoria mantienen bloques amplios de lectura con fondos diferenciados.
- Cronología convertida en una retícula de etapas.
- Disciplinas presentadas sobre el color de acento global.
- Premios transformados en tarjetas cronológicas.
- Voces, archivo fotográfico y catálogo conservan sus fuentes dinámicas.

## Catálogo y privacidad

- La página carga el sistema visual de tarjetas del catálogo.
- Las nueve obras aplican `gmr_theme_visibility_meta_query()`.
- Los precios continúan sujetos a `gmr_theme_can_view_price()`.

## Responsive

- Cronología: tres columnas en escritorio, dos en tableta y una en móvil.
- Premios: cuatro columnas en escritorio, dos en tableta y una en móvil.
- Disciplinas y catálogo: una columna en móvil.
- Validación sin imágenes rotas ni desbordamiento horizontal.
