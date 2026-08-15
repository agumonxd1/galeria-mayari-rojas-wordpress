# Migracion desde EventON

## Alcance

Se migran cinco eventos de `ajde_events` a `gmr_event` conservando ID, slug, titulo, contenido, imagen destacada y todos los metadatos originales de EventON.

## Mapeo

- Relatos Escondidos: exposicion.
- El realismo magico de Elmar Rojas: exposicion.
- Concurso de Pintura Infantil: actividad.
- B'UKB'AL de Juan Navichoc: exposicion.
- Antes del amor: exposicion.

Las fechas Unix de EventON se convierten explicitamente a `America/Guatemala`, porque la configuracion general heredada de WordPress no coincide con la zona guardada por EventON. Los eventos se marcan como presenciales, publicos y finalizados cuando su fecha final ya paso.

No existen registros `evo-rsvp`, por lo que los complementos RSVP no contienen inscripciones que deban conservarse.

## Resultado en staging

- Cinco de cinco eventos completos y publicados en Agenda.
- Cuatro exposiciones y una actividad.
- Imágenes destacadas, contenido y metadatos históricos conservados.
- EventON, EventON RSVP y EventON RSVP Invitees desactivados y eliminados del staging después de validar el frontend sin ellos.

## Compatibilidad

Mayari Core reconoce las rutas antiguas `/event/{slug}/` y `/events/{slug}/`. Los metadatos originales permanecen en cada registro como respaldo y trazabilidad.
