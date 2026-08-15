# Area de Coleccionistas

## Alcance de la iteracion 1.2.0

- Cuentas creadas exclusivamente por Administradores o Gestores de galeria.
- Roles editables restringidos a `Coleccionista` para impedir elevacion de privilegios.
- Estados de cuenta `active`, `suspended` y `expired`.
- Cierre inmediato de sesiones al suspender o expirar una cuenta.
- Registro de ultimo acceso; no se registra la navegacion del usuario.
- Catalogo compartido para todos los Coleccionistas durante el MVP.
- Precios privados visibles solo con la capacidad correspondiente.
- Exclusiones de cache e indexacion para respuestas autenticadas y la pagina privada.
- Filtro de visibilidad en consultas REST de productos.
- Store API de productos cerrada para visitantes, ya que el tema no depende de ella y su esquema incluye precios crudos.

## Operacion administrativa

1. Ir a Usuarios > Anadir nuevo.
2. Registrar nombre y correo unico del Coleccionista.
3. Asignar exclusivamente el rol Coleccionista.
4. Enviar el enlace nativo de WordPress para establecer contrasena.
5. Editar la cuenta para cambiar su estado cuando sea necesario.

Un Gestor de galeria puede crear y editar Coleccionistas, pero no modificar Administradores ni asignar roles superiores. Un Coleccionista nunca accede al escritorio de WordPress.

## Privacidad

La pagina privada incluye cabeceras `no-cache`, `noindex` y variacion por cookie. Las obras `collectors` se omiten para visitantes en catalogos, busqueda y APIs. Las obras `hidden` siguen reservadas para Gestores y Administradores.

## Pendiente posterior al MVP

- Selecciones privadas distintas para cada Coleccionista.
- Descargas documentales mediante un controlador protegido cuando se carguen documentos comerciales reales.
- Integracion con el cache/CDN definitivo antes del lanzamiento a produccion.
