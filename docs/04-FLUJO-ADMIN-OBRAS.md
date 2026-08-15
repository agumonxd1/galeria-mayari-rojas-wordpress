# Flujo administrativo de obras

## Version 1.0

La edicion de una obra se realiza desde WooCommerce > Productos. `Mayari Core` agrega una caja principal llamada **Ficha artistica Mayari** y mantiene las funciones de inventario, SKU, precio e imagenes de WooCommerce.

## Alta de una obra

1. Crear un producto nuevo.
2. Escribir el titulo oficial.
3. Asignar el SKU en Datos del producto.
4. Completar Artista y Disciplina.
5. Añadir año, tecnica, materiales y dimensiones.
6. Completar edicion, firma y certificado cuando corresponda.
7. Definir estado comercial, visibilidad de obra y visibilidad de precio.
8. Añadir imagen principal y galeria.
9. Guardar como borrador o publicar.

## Secciones del formulario

- Identificacion.
- Tecnica y materiales.
- Dimensiones.
- Edicion, firma y autenticidad.
- Estado y visibilidad.
- Informacion interna.

Los campos de profundidad y peso se muestran para Escultura y Joyeria. La seccion de edicion se muestra para Obra grafica, Escultura y Joyeria. Estas condiciones ayudan al usuario, pero el servidor sigue validando y sanitizando todos los valores recibidos.

## Campos minimos

- Titulo.
- SKU.
- Artista.
- Disciplina.
- Estado comercial.
- Visibilidad de obra.
- Visibilidad de precio.
- Imagen principal antes de la publicacion definitiva.

Los productos heredados pueden seguir editandose sin perder automaticamente sus categorias antiguas. El formulario conserva temporalmente las categorias que todavia no hayan sido migradas.

## Listado de productos

Se agregan columnas para:

- Artista.
- Disciplina.
- Estado comercial.
- Visibilidad.

Tambien se agregan filtros por artista, estado comercial y visibilidad. Los filtros de categoria propios de WooCommerce permanecen disponibles.

## Seguridad de guardado

- Nonce por formulario.
- Verificacion de autosave y revisiones.
- Capacidades de Gestor o WooCommerce.
- Sanitizacion por tipo de campo.
- Enumeraciones cerradas para estados y visibilidad.
- Terminos guardados por identificador numerico.

## Limitaciones de esta iteracion

- La creacion visual de artistas y colecciones enriquecidos se implementara en la siguiente iteracion.
- La migracion automatica de atributos antiguos aun no se ejecuta.
- El formulario mantiene el panel estandar de precio e inventario de WooCommerce.
- La validacion avisa sobre campos faltantes pero no bloquea todavia la publicacion de contenido heredado.

