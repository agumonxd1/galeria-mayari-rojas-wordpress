# Ejecucion de migracion `catalog-v1`

Fecha: 15 de agosto de 2026. Entorno: staging aislado de Duplicator. Mayari Core 0.5.0.

## Seguridad y reversibilidad

Antes de la corrida completa se migro una sola obra bajo `rollback-test`, se verifico el cambio y se restauro. El conteo de marcadores volvio a cero.

La corrida real utiliza el identificador `catalog-v1`. Cada producto tiene un snapshot de metadatos y relaciones taxonomicas anterior a su escritura. El proceso es reanudable y omite las obras que ya tienen marcador de migracion.

Estado:

```json
{"run":"catalog-v1","status":"complete","processed":131,"remaining":0}
```

El rollback de emergencia se ejecuta exclusivamente en staging con:

```bash
wp gmr migrate-rollback --run=catalog-v1 --confirm=ROLLBACK-STAGING
```

No debe ejecutarse en produccion ni despues de ediciones editoriales nuevas sin preparar antes otro respaldo.

## Resultado auditado

| Comprobacion | Resultado |
|---|---:|
| Productos migrados | 131 / 131 |
| Con artista | 130 / 131 |
| Asignados a Anonimo | 13 |
| Sin artista por ambiguedad aprobada | 1 |
| Con disciplina canonica | 131 / 131 |
| Con tecnica canonica o Sin tecnica | 131 / 131 |
| Con ano o marca sin fecha | 131 / 131 |
| Con imagen | 131 / 131 |
| Con SKU | 131 / 131 |
| Con precio | 129 / 131 |
| Categorias heredadas de artista asignadas | 0 |
| Categorias heredadas de coleccion asignadas | 0 |
| Productos variables conservados | 8 |
| Variaciones conservadas | 28 |

Los dos productos sin precio son validos en modo catalogo y muestran `Consultar`.

## Ajustes durante la ejecucion

El servidor ralentizo las actualizaciones por los hooks de LiteSpeed Cache. La migracion se reanudo desde su checkpoint y se limitaron las escrituras masivas de metadatos a operaciones directas controladas. Las taxonomias continuaron usando las APIs de WordPress. Para los SKU generados tambien se sincronizo la tabla de busqueda de WooCommerce.

Una verificacion final corrigio 27 obras que contenian materiales o soportes pero ninguna tecnica estricta, asignandoles `Sin tecnica`. La obra 5877 recupero su relacion correcta con Irene Carlos despues de la reanudacion.

## Siguiente fase

Con el inventario normalizado, el siguiente trabajo puede concentrarse en el tema: consultas de catalogo, archivos de disciplina, perfiles de artista, colecciones, ficha de obra y controles de visibilidad para Coleccionistas.
