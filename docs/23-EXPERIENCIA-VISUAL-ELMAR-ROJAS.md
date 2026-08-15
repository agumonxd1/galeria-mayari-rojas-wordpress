# Experiencia visual de Elmar Rojas

## Narrativa

- Hero museográfico con obra de fondo, título editorial y acceso directo al catálogo.
- Navegación interna persistente para biografía, cronología, disciplinas, Voces, archivo y obra.
- Biografía y trayectoria mantienen bloques amplios de lectura con fondos diferenciados.
- Cronología convertida en una retícula de etapas.
- Disciplinas presentadas como tarjetas fotográficas; cada imagen se obtiene de una obra pública de Elmar y dispone de respaldo visual si el catálogo aún no tiene una pieza asignada.
- Premios transformados en tarjetas cronológicas.
- Voces usa el tipo de contenido independiente `gmr_tribute`: autor, cargo o semblanza, fecha, extracto, texto completo, fuente y visibilidad. La portada muestra extractos breves y enlaza al testimonio completo.
- El documento reservado se integra como una franja horizontal oscura y sólo aparece a usuarios autorizados.
- Archivo fotográfico y catálogo conservan sus fuentes dinámicas.

## Catálogo y privacidad

- La página carga el sistema visual de tarjetas del catálogo.
- Las nueve obras aplican `gmr_theme_visibility_meta_query()`.
- Los precios continúan sujetos a `gmr_theme_can_view_price()`.

## Responsive

- Cronología: tres columnas en escritorio, dos en tableta y una en móvil.
- Premios: cuatro columnas en escritorio, dos en tableta y una en móvil.
- Catálogo: cuatro columnas en pantallas grandes y una columna en móvil.
- Disciplinas: cuatro columnas en escritorio y una columna en móvil.
- Validación sin imágenes rotas ni desbordamiento horizontal.
