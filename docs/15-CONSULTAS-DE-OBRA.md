# Consultas de obra

## Flujo

Cada ficha de obra incorpora un formulario con referencia automatica a la obra, artista y SKU. El visitante completa nombre, correo, telefono opcional, mensaje y consentimiento.

Al aceptar una consulta:

1. Se valida el producto, nonce, tiempo minimo, honeypot, correo y consentimiento.
2. Se limita un envio por correo y obra cada cinco minutos.
3. Se crea un registro privado `gmr_inquiry` en WordPress.
4. Se envia un correo a la direccion configurada en Ajustes > Generales.
5. Se muestra una confirmacion sin exponer los datos enviados en la URL.

## Privacidad

No se almacena la direccion IP. El registro conserva solamente la referencia de la obra, datos proporcionados, usuario autenticado cuando exista y fecha de consentimiento. Las consultas no son publicas ni se exponen en REST.

## Operacion

El equipo consulta los registros desde **Consultas de obra** en el escritorio. El destinatario puede cambiarse en **Ajustes > Generales > Correo para consultas de obra**.

Antes del lanzamiento debe realizarse una prueba real de entrega y revisar SPF, DKIM y el servicio SMTP del dominio.
