# Auditoria integral de visibilidad

## Superficies protegidas

- URL individual de obra y contenido editorial.
- Catalogo, taxonomias, busqueda y feeds.
- Colecciones privadas y ocultas.
- Consultas personalizadas del tema.
- REST de productos, agenda, multimedia y Voces sobre Elmar.
- Store API cerrada para visitantes.
- Sitemaps de obras y colecciones filtrados.
- Sitemap de usuarios desactivado.
- Conteos de obras por artista y coleccion calculados por sesion.
- Precios privados ausentes del HTML y de datos estructurados WooCommerce.
- Documentos privados servidos desde una boveda externa.

## Matriz automatizada

`tests/visibility-matrix.php` crea obras publica, para Coleccionistas y oculta; una coleccion privada; y un usuario Coleccionista. Verifica permisos como visitante, Coleccionista y Administrador, y elimina todos los fixtures en un bloque `finally`.

## Limite de archivos de imagen

WordPress almacena las imagenes de producto existentes en `uploads`, que Nginx sirve sin arrancar PHP. Las vistas y APIs ya no publican URLs de obras privadas, pero una URL estatica conocida con anterioridad no puede revocarse desde el plugin. La proteccion criptografica de imagenes privadas requiere migrarlas a la boveda o configurar una ubicacion Nginx privada. Se mantiene como bloque explicito antes de produccion; no afecta documentos, que ya residen fuera de `htdocs`.
