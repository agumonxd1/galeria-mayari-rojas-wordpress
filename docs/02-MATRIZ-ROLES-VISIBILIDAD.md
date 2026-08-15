# Matriz de roles y visibilidad

## Galeria Mayari Rojas

Estado: propuesta para aprobacion  
Version: 1.0

## 1. Objetivo

Definir quien puede ver, crear, editar, publicar y administrar cada tipo de contenido. Las reglas se aplicaran en servidor y cubriran paginas, consultas, REST, busqueda, feeds, sitemap, bloques y archivos protegidos.

## 2. Roles del sistema

### Visitante

Usuario no autenticado. Solo accede a contenido publico.

### Coleccionista (`gmr_collector`)

Cuenta creada por la galeria. Accede a catalogo y contenido privado autorizado, sin permisos de edicion.

### Gestor de galeria (`gmr_gallery_manager`)

Administra el contenido y las cuentas de Coleccionistas. No modifica plugins, temas, codigo ni configuracion critica.

### Administrador (`administrator`)

Control tecnico y editorial completo.

Roles heredados de WordPress y WooCommerce se mantendran cuando los requieran plugins, pero no seran el modelo principal de acceso del sitio.

## 3. Acciones por rol

| Accion | Visitante | Coleccionista | Gestor | Administrador |
|---|---:|---:|---:|---:|
| Ver contenido publico | Si | Si | Si | Si |
| Ver obras para Coleccionistas | No | Si | Si | Si |
| Ver contenido oculto/borrador | No | No | Si | Si |
| Ver precio `public` | Si | Si | Si | Si |
| Ver precio `collectors` | No | Si | Si | Si |
| Ver precio `admins` | No | No | Si | Si |
| Descargar documento publico | Si | Si | Si | Si |
| Descargar documento de Coleccionistas | No | Si | Si | Si |
| Consultar una obra | Si, si es publica | Si | Si | Si |
| Editar perfil propio | No | Limitado | Si | Si |
| Crear/editar obras | No | No | Si | Si |
| Publicar obras | No | No | Si | Si |
| Cambiar precios | No | No | Si | Si |
| Cambiar visibilidad | No | No | Si | Si |
| Crear artistas/colecciones | No | No | Si | Si |
| Gestionar agenda/noticias | No | No | Si | Si |
| Crear Coleccionistas | No | No | Si | Si |
| Asignar roles administrativos | No | No | No | Si |
| Instalar plugins/temas | No | No | No | Si |
| Editar codigo/configuracion | No | No | No | Si |
| Exportar datos sensibles | No | No | Restringido | Si |

## 4. Matriz de visibilidad de obra

| `gmr_visibility` | Visitante | Coleccionista | Gestor | Administrador |
|---|---:|---:|---:|---:|
| `public` | Si | Si | Si | Si |
| `collectors` | No | Si | Si | Si |
| `hidden` | No | No | Si | Si |

Reglas:

- `public` controla la obra, no el precio.
- `collectors` exige sesion y capacidad `gmr_view_collector_catalog`.
- `hidden` no se trata como contenido publicado para usuarios externos.
- Un enlace directo nunca omite la verificacion.
- Las vistas previas administrativas usan capacidades editoriales, no excepciones publicas.

## 5. Matriz de visibilidad de precio

| `gmr_price_visibility` | Visitante | Coleccionista | Gestor | Administrador |
|---|---:|---:|---:|---:|
| `public` | Si, si ve la obra | Si | Si | Si |
| `collectors` | No | Si | Si | Si |
| `admins` | No | No | Si | Si |

Si no existe precio:

- Mostrar `Consultar` o el texto editorial configurado.
- No inferir disponibilidad a partir de la ausencia del precio.
- No incluir valores vacios o privados en HTML, datos estructurados o JavaScript.

## 6. Estado comercial y presentacion

| Estado | Aparicion predeterminada | Accion publica |
|---|---|---|
| Disponible | Catalogos permitidos | Consultar obra |
| Reservada | Catalogos permitidos | Consultar disponibilidad |
| Vendida | Decision editorial pendiente | Sin accion comercial o consultar similares |
| No disponible | Perfil/archivo segun configuracion | Consultar similares |
| En exposicion | Catalogos y exposicion | Informacion/visita |
| Archivo historico | Archivo, no catalogo comercial | Ninguna o contexto editorial |

## 7. Contenido editorial

| Contenido | Publico | Coleccionistas | Oculto |
|---|---:|---:|---:|
| Artista | Si | Si | Gestor/Admin |
| Coleccion | Si | Si | Gestor/Admin |
| Evento/actividad | Si | Si | Gestor/Admin |
| Noticia | Si | Si | Gestor/Admin |
| Galeria multimedia | Si | Si | Gestor/Admin |
| Pagina | Si | Si | Gestor/Admin |

Cada entidad que admita contenido privado reutilizara `gmr_visibility`.

## 8. Documentos y medios privados

- Los archivos privados se entregaran mediante un controlador que valide sesion y capacidad.
- No se enlazaran directamente desde la biblioteca publica.
- Se enviaran cabeceras que eviten cache publico e indexacion.
- Los nombres de archivo no se consideraran una barrera de seguridad.
- Las miniaturas privadas tambien requieren control de acceso cuando revelen la obra.
- Los logs no almacenaran precios, documentos ni datos personales completos.

## 9. Superficies que deben protegerse

La misma regla de acceso debe aplicarse a:

- Catalogo y archivos de taxonomia.
- Ficha individual de obra.
- Perfil del artista.
- Paginas especiales de Elmar Rojas.
- Colecciones y obras relacionadas.
- Busqueda nativa y sugerencias.
- API REST de WordPress y WooCommerce.
- Store API de WooCommerce.
- Feeds RSS.
- Sitemaps XML.
- Datos estructurados y Open Graph.
- Shortcodes, widgets, bloques y consultas AJAX.
- Endpoint de consulta.
- Exportaciones y archivos descargables.
- Cache de pagina, objetos y CDN.

## 10. Comportamiento ante acceso denegado

| Caso | Respuesta |
|---|---|
| Visitante abre obra `collectors` | 404 por defecto, sin confirmar existencia. |
| Coleccionista abre obra `hidden` | 404. |
| Visitante abre pagina privada conocida | Redirigir a login solo si la pagina es explicitamente de Coleccionistas. |
| Sesion expirada dentro de Coleccionistas | Redirigir a login con retorno seguro. |
| Documento privado sin permiso | 404 o 403 sin URL alternativa publica. |
| REST/Store API sin permiso | Omitir registro o devolver error autorizado. |

## 11. Cuentas de Coleccionistas

- No existe registro publico.
- Gestor o Administrador crea la cuenta.
- Se exige correo unico.
- WordPress genera un enlace seguro para establecer contraseña.
- La galeria puede desactivar la cuenta sin borrarla.
- No se comparte una cuenta entre personas.
- Recuperacion de contraseña usa el flujo de WordPress.
- Se registran ultimo acceso y estado de cuenta, sin vigilancia invasiva.

Estados propuestos:

- Activa.
- Suspendida.
- Expirada.

Decision MVP: todos los Coleccionistas ven el mismo catalogo privado. Las selecciones individuales se reservan para una fase posterior.

## 12. Capacidades propuestas

Capacidades de lectura:

- `gmr_view_collector_area`.
- `gmr_view_collector_catalog`.
- `gmr_view_private_prices`.
- `gmr_download_collector_documents`.

Capacidades de gestion:

- `gmr_manage_artworks`.
- `gmr_manage_artists`.
- `gmr_manage_collections`.
- `gmr_manage_agenda`.
- `gmr_manage_media_galleries`.
- `gmr_manage_collectors`.
- `gmr_export_catalog`.

El plugin asignara capacidades de forma explicita durante activacion y las retirara solo mediante una desinstalacion confirmada.

## 13. Reglas de cache

- No almacenar en cache publico paginas de Coleccionistas.
- Variar o excluir cache cuando la respuesta dependa de autenticacion.
- No incluir precios privados en fragmentos compartidos.
- Purgar las vistas relacionadas al cambiar visibilidad, precio, artista, disciplina o coleccion.
- En staging, mantener correos bloqueados e indexacion desactivada.

## 14. Pruebas obligatorias

Para cada nivel de visibilidad se probara:

1. URL directa.
2. Catalogo.
3. Busqueda.
4. Perfil de artista.
5. Coleccion.
6. Productos relacionados.
7. REST y Store API.
8. Feed y sitemap.
9. Cache con y sin sesion.
10. Documento adjunto.

La prueba se ejecutara como visitante, Coleccionista, Gestor y Administrador.

## 15. Criterios de aceptacion

- Un visitante nunca recibe el titulo, imagen, precio ni identificador de una obra privada.
- Un Coleccionista no puede acceder al panel editorial.
- Un Gestor no puede instalar plugins, editar codigo ni elevar privilegios.
- Los precios se filtran antes de generar HTML o JSON.
- Los documentos privados no son accesibles con solo conocer la URL.
- El cache no mezcla respuestas publicas y privadas.
- Crear o desactivar una cuenta no requiere intervencion tecnica.

