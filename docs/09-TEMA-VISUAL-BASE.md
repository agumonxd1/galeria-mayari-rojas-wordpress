# Tema visual base Mayari Rojas

Version 0.1.0 activada exclusivamente en staging el 15 de agosto de 2026.

## Alcance

- Tema propio sin dependencia de Lekker para renderizar el catalogo.
- Sistema editorial marfil, tinta y bronce con Cormorant Garamond e Inter.
- Encabezado responsive y navegacion publica aprobada.
- Portada con hero y obras destacadas filtradas por visibilidad.
- Archivo general, disciplinas, artistas, colecciones y fichas de obra.
- Datos tecnicos, dimensiones, estado, precio autorizado y consulta.
- Compatibilidad WooCommerce en modo catalogo.

## Validacion

- Portada: H1 correcto y seis obras destacadas.
- Catalogo: 18 obras por pagina y filtros de disciplina.
- Ficha: imagen, artista, titulo, ano, tecnica, soporte, medidas y precio para administrador.
- Todas las plantillas pasaron el lint PHP.

El staging de Duplicator vive en una ruta fisica anidada que no resuelve correctamente enlaces bonitos. Se usan enlaces simples solo en staging; produccion conservara las rutas canonicas aprobadas.

## Plugins desactivados solo en staging

- LiteSpeed Cache, para evitar respuestas antiguas y fugas entre niveles de acceso durante desarrollo.
- Lekker Core, porque inyectaba JavaScript dependiente del tema anterior.

Produccion no fue modificada. Antes del lanzamiento se definira una cache compatible con privacidad.
