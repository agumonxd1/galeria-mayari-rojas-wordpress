# Documentos privados

## Arquitectura

Los documentos reservados no se almacenan en la biblioteca publica de WordPress. Mayari Core utiliza una boveda configurada mediante `gmr_private_documents_path`, obligatoriamente fuera de `ABSPATH`.

Cada archivo tiene un registro privado `gmr_document` que conserva solamente:

- nombre editorial;
- nombre interno aleatorio;
- tipo MIME;
- tipo e ID del propietario (`product` o `artist`).

La ruta fisica nunca se imprime en HTML ni en respuestas REST.

## Acceso

La descarga pasa por un endpoint frontal controlado por Mayari Core y requiere:

- una sesion valida;
- la capacidad `gmr_download_collector_documents` o una capacidad de gestion;
- un token temporal de WordPress;
- un registro y archivo existentes.

Una solicitud anonima, expirada o manipulada devuelve 404. Las respuestas usan `no-cache`, `noindex`, `nosniff` y descarga como adjunto.

## Administracion

- En una obra: Productos > editar > Documentos privados.
- En un artista: Artistas > editar > Documentos privados.
- Se permiten PDF, JPG, PNG, WebP, DOC y DOCX.
- Eliminar el registro elimina tambien el archivo fisico de la boveda.

## Migracion inicial

El PDF `Obra Grafica - Patrimonio Elmar Rojas` se copia a la boveda y se elimina definitivamente de uploads, ya que la auditoria comprobo que solo estaba relacionado con el perfil de Elmar. El enlace publico anterior deja de existir.

Produccion debera configurar su propia ruta externa antes de migrar o cargar documentos.
