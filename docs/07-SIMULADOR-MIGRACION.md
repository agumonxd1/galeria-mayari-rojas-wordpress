# Simulador de migracion

Mayari Core 0.4.0 incorpora un planificador de migracion estrictamente de solo lectura. No contiene una opcion `--apply` ni llama funciones de escritura.

## Comandos

Resumen general:

```bash
wp gmr migration-preview
```

Tabla completa:

```bash
wp gmr migration-preview --format=table
```

Solo productos con advertencias:

```bash
wp gmr migration-preview --warnings-only --format=table
```

Salida estructurada para revision o archivo:

```bash
wp gmr migration-preview --format=json
wp gmr migration-preview --format=csv
```

## Alcance

Para cada producto propone:

- artista a partir de la categoria heredada;
- disciplina canonica;
- colecciones o series;
- ano inicial y final;
- alto, ancho, profundidad o diametro en centimetros;
- tecnica, soporte y materiales detectados;
- lista de advertencias que bloquean la migracion automatica.

Siempre conserva en el plan los valores originales de ano, medidas y tecnica. Los parsers son deliberadamente conservadores: un dato ambiguo produce una advertencia en lugar de una suposicion.

## Regla de seguridad

Esta version sirve para validar reglas y obtener la linea base. La futura ejecucion real sera otro comando, con respaldo, identificador de corrida, registro por obra y reversion limitada a los datos creados por esa corrida.

## Linea base obtenida en staging

La simulacion sobre las 131 obras produjo:

| Resultado | Cantidad |
|---|---:|
| Listas sin advertencias | 50 |
| Con alguna advertencia | 81 |
| Sin disciplina | 65 |
| Sin ano | 49 |
| Sin artista | 13 |
| Disciplina ambigua | 12 |
| Productos variables | 8 |
| Sin medidas | 5 |
| Medidas no convertibles como una sola obra | 3 |
| Sin SKU | 2 |
| Sin precio | 2 |
| Sin tecnica | 1 |
| Sin imagen | 1 |
| Artista ambiguo | 1 |

Las tres advertencias de medidas no son fallos del parser. Los productos 5833, 6266 y 6278 tienen dos o mas medidas porque representan colecciones o productos variables. Deben descomponerse o revisarse como conjuntos antes de crear campos dimensionales canonicos.

El simulador no modifico productos, terminos ni metadatos; el conteo final permanecio en 131.
