# Mapa del sitio y URLs

## Galeria Mayari Rojas

Estado: propuesta para aprobacion  
Version: 1.0

## 1. Objetivo

Definir la arquitectura de informacion, las rutas canonicas y la navegacion del nuevo sitio. Las URLs deben ser legibles, estables, indexables cuando corresponda y compatibles con los filtros de privacidad.

## 2. Navegacion principal publica

1. La galeria.
2. Elmar Rojas.
3. Artistas.
4. Catalogo.
5. Colecciones.
6. Agenda.
7. Noticias.
8. Visitanos.
9. Coleccionistas.

En movil se conservara la misma jerarquia, agrupando subniveles sin ocultar Catalogo ni Coleccionistas.

## 3. Arbol general

```text
/
├── la-galeria/
│   ├── historia/
│   └── visita/
├── elmar-rojas/
│   ├── biografia/
│   ├── cronologia/
│   ├── premios/
│   ├── documentos/
│   ├── archivo/
│   ├── pintura/
│   ├── escultura/
│   ├── obra-grafica/
│   └── joyeria/
├── artistas/
│   └── {artista}/
├── catalogo/
│   ├── pintura/
│   ├── escultura/
│   ├── obra-grafica/
│   └── joyeria/
├── obra/{obra}/
├── colecciones/
│   └── {coleccion}/
├── agenda/
│   └── {evento}/
├── actividades/
├── exposiciones/
├── noticias/
│   └── {noticia}/
├── archivo-multimedia/
│   └── {galeria}/
├── contacto/
├── coleccionistas/
│   ├── acceso/
│   ├── catalogo/
│   ├── obras-disponibles/
│   ├── nuevas-adquisiciones/
│   ├── colecciones/
│   ├── documentos/
│   └── perfil/
├── privacidad/
├── terminos/
└── 404
```

## 4. Rutas canonicas

| Contenido | Ruta | Indexable | Fuente |
|---|---|---:|---|
| Inicio | `/` | Si | Pagina/patrones dinamicos |
| La galeria | `/la-galeria/` | Si | Pagina |
| Visita | `/la-galeria/visita/` | Si | Pagina |
| Directorio de artistas | `/artistas/` | Si | Archivo `gmr_artist` |
| Artista generico | `/artistas/{slug}/` | Si | Termino `gmr_artist` |
| Elmar Rojas | `/elmar-rojas/` | Si | Plantilla especial |
| Catalogo | `/catalogo/` | Si | Archivo de productos personalizado |
| Disciplina | `/catalogo/{disciplina}/` | Si | `product_cat` con rewrite propio |
| Obra | `/obra/{slug}/` | Si si publica | Producto WooCommerce |
| Colecciones | `/colecciones/` | Si | Indice curatorial |
| Coleccion | `/colecciones/{slug}/` | Segun visibilidad | `gmr_collection` |
| Agenda | `/agenda/` | Si | Archivo `gmr_event` |
| Evento | `/agenda/{slug}/` | Segun visibilidad | `gmr_event` |
| Actividades | `/actividades/` | Si | Vista filtrada de Agenda |
| Exposiciones | `/exposiciones/` | Si | Vista filtrada de Agenda |
| Noticias | `/noticias/` | Si | Archivo editorial |
| Noticia | `/noticias/{slug}/` | Si | Entrada |
| Multimedia | `/archivo-multimedia/` | Si | Archivo `gmr_media_gallery` |
| Galeria tematica | `/archivo-multimedia/{slug}/` | Segun visibilidad | `gmr_media_gallery` |
| Contacto | `/contacto/` | Si | Pagina/formulario |
| Coleccionistas | `/coleccionistas/` | No | Dashboard privado |
| Acceso | `/coleccionistas/acceso/` | No | Login personalizado |

## 5. Elmar Rojas

`/elmar-rojas/` sera una experiencia editorial especial y no un segundo artista desconectado del catalogo.

Las vistas de disciplina aplicaran automaticamente:

```text
artista = Elmar Rojas
disciplina = ruta solicitada
visibilidad = permitida para usuario actual
```

Rutas:

- `/elmar-rojas/pintura/`.
- `/elmar-rojas/escultura/`.
- `/elmar-rojas/obra-grafica/`.
- `/elmar-rojas/joyeria/`.

Estas rutas usaran canonicas propias por su valor editorial, pero las fichas individuales conservaran `/obra/{slug}/`.

## 6. Catalogo y filtros

La URL base sera `/catalogo/`. Los filtros combinados usaran parametros de consulta para evitar crear miles de paginas indexables.

Ejemplos:

```text
/catalogo/pintura/
/catalogo/?artista=irene-carlos
/catalogo/obra-grafica/?artista=elmar-rojas&estado=disponible
```

Reglas:

- La disciplina principal puede tener URL indexable.
- Artistas tienen su propia pagina canonica y no necesitan un archivo indexable duplicado por parametro.
- Filtros combinados llevaran `noindex, follow` y canonica al archivo relevante.
- El precio no se usara como filtro publico en el MVP.
- La paginacion sera rastreable sin generar duplicados.
- Los filtros nunca revelaran conteos de obras privadas a visitantes.

## 7. Perfil de artista

Estructura recomendada:

1. Portada y retrato.
2. Biografia breve.
3. Obras destacadas.
4. Obras en catalogo.
5. Trayectoria o historia.
6. Exposiciones/premios cuando existan.
7. Noticias y eventos relacionados.
8. Archivo multimedia.

Para visitantes se consultan obras publicas; para Coleccionistas se agregan las autorizadas sin cambiar la URL.

## 8. Ficha de obra

Ruta canonica: `/obra/{slug}/`.

Estructura:

1. Imagen principal y galeria.
2. Titulo, artista y año.
3. Tecnica y dimensiones.
4. Estado comercial.
5. Precio, si el usuario tiene permiso.
6. Texto curatorial.
7. Coleccion o serie.
8. Documentos autorizados.
9. Solicitud de informacion.
10. Obras relacionadas permitidas.

Una obra `collectors` devuelve 404 a visitantes. No redirige a una ficha publica incompleta.

## 9. Coleccionistas

Navegacion privada:

```text
Coleccionistas
├── Resumen
├── Catalogo completo
├── Obras disponibles
├── Nuevas adquisiciones
├── Colecciones privadas
├── Documentos
├── Mi perfil
└── Cerrar sesion
```

Reglas de ruta:

- Toda la seccion usa `noindex, nofollow`.
- No se cachea publicamente.
- Si no hay sesion, `/coleccionistas/` redirige a `/coleccionistas/acceso/`.
- Tras iniciar sesion se valida un destino interno seguro.
- La recuperacion de contraseña se integra visualmente, manteniendo tokens nativos de WordPress.

## 10. Agenda, actividades y exposiciones

Se administran como un solo tipo de contenido, pero se presentan mediante rutas editoriales distintas:

- `/agenda/`: todo lo relevante por fecha.
- `/actividades/`: talleres, conversatorios y actividades.
- `/exposiciones/`: exposiciones actuales, proximas y archivo.

Los eventos finalizados permanecen disponibles cuando tengan valor documental. La URL individual no cambia al finalizar.

## 11. Noticias y multimedia

Noticias:

- Archivo `/noticias/`.
- Individual `/noticias/{slug}/`.
- Categorias editoriales solo cuando tengan suficiente contenido.

Multimedia:

- Archivo `/archivo-multimedia/`.
- Individual `/archivo-multimedia/{slug}/`.
- Filtros por artista, evento, periodo o tema sin crear duplicados indexables.

## 12. Navegacion secundaria y footer

Footer publico:

- Direccion y horarios.
- Contacto.
- Redes sociales.
- La galeria.
- Artistas.
- Catalogo.
- Agenda.
- Noticias.
- Coleccionistas.
- Privacidad y terminos.

La navegacion contextual aparecera en perfiles extensos, especialmente Elmar Rojas, para evitar paginas interminables sin orientacion.

## 13. Busqueda

Ruta: `/buscar/?q={termino}` o integracion equivalente con la busqueda nativa.

Resultados agrupados:

- Obras.
- Artistas.
- Colecciones.
- Noticias.
- Eventos.
- Galerias multimedia.

La consulta se filtra por permisos antes de calcular resultados y conteos.

## 14. Mapa inicial de redirecciones

La tabla definitiva se generara al inventariar las 99 paginas y URLs actuales. Reglas conceptuales:

| Origen actual | Destino nuevo |
|---|---|
| `/tienda/` o catalogo actual | `/catalogo/` |
| `/product-category/pintura/` | `/catalogo/pintura/` |
| `/product-category/escultura/` | `/catalogo/escultura/` |
| `/product-category/obra-grafica/` | `/catalogo/obra-grafica/` |
| `/product-category/joyeria/` | `/catalogo/joyeria/` |
| Categoria que representa artista | `/artistas/{artista}/` |
| Categoria que representa coleccion | `/colecciones/{coleccion}/` |
| Producto actual | `/obra/{slug}/` |
| Pagina antigua de artistas | `/artistas/` |
| Pagina antigua de eventos | `/agenda/` o ruta equivalente |

Todas las redirecciones permanentes se probaran sin cadenas ni ciclos.

## 15. SEO e indexacion

Indexables:

- Contenido publico canonico.
- Disciplinas principales.
- Artistas con contenido suficiente.
- Colecciones publicas.
- Noticias, eventos y galerias publicas.

No indexables:

- Coleccionistas y login.
- Filtros combinados.
- Busqueda interna.
- Contenido privado u oculto.
- Documentos privados.
- Staging.

Los sitemaps se filtraran con las mismas reglas de visibilidad.

## 16. Errores y estados vacios

- 404 editorial coherente con la galeria.
- Catalogo sin resultados con opcion de limpiar filtros.
- Artista sin obras publicas con biografia y mensaje neutral.
- Coleccion privada inexistente para visitantes.
- Evento cancelado claramente identificado, sin eliminar su archivo.
- Error de formulario preservando datos no sensibles y explicando el siguiente paso.

## 17. Criterios de aceptacion

- Cada entidad tiene una unica URL canonica.
- Elmar posee rutas especiales sin duplicar fichas de obra.
- Las subtiendas son filtros del mismo inventario.
- Las URLs privadas no aparecen en sitemap ni busqueda publica.
- Las redirecciones antiguas conservan trafico y no forman cadenas.
- La navegacion permite llegar a cualquier seccion principal en dos o tres acciones.
- Las rutas funcionan con y sin enlaces permanentes regenerados durante despliegue.

