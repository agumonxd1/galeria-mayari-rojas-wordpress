# Sistema visual global

## Direccion aprobada

El tema adopta una direccion editorial y museografica basada en la propuesta visual nueva:

- fotografia como protagonista;
- fondos marfil y carbon;
- serif editorial de alto contraste;
- microtexto sans serif en mayusculas;
- composiciones amplias con abundante espacio negativo;
- alternancia de secciones claras y oscuras;
- tarjetas con radios suaves;
- botones capsula y movimiento discreto.

Las capturas antiguas de Lekker se utilizan solo como referencia de contenido y jerarquia heredada, no como modelo visual.

## Tokens

Los tokens viven en `assets/design-system.css`:

- color: `--gmr-ink`, `--gmr-charcoal`, `--gmr-paper`, `--gmr-canvas`, `--gmr-stone`, `--gmr-muted`, `--gmr-bronze`;
- espacio fluido: `--gmr-xs` a `--gmr-xl`;
- radios: `--gmr-radius-sm`, `--gmr-radius`, `--gmr-radius-lg`;
- tipografia: `--serif`, `--sans`;
- ancho editorial: `--wrap`;
- transicion: `--ease`.

## Componentes globales

- Header transparente sobre Inicio y solido al desplazarse.
- Marca tipografica temporal con monograma `MR`.
- Navegacion de escritorio y panel movil.
- Acceso a Coleccionistas como control capsula.
- Titulares, kickers, botones, filtros, tarjetas, formularios y paginacion.
- Encabezados de archivos públicos con la escala compacta aprobada en Artistas; aplicada a Catálogo, Colecciones y Agenda.
- Footer editorial con llamada institucional.
- Estados de foco visibles y enlace para saltar al contenido.
- Respeto de `prefers-reduced-motion`.

## Validacion responsive

- Escritorio: 1920 x 945, sin desbordamiento horizontal.
- Movil: 390 x 844, sin desbordamiento horizontal.
- Header movil: 74 px.
- Menu abre, actualiza `aria-expanded`, bloquea el fondo y cierra con Escape.

La imagen definitiva del hero, el tratamiento de cada bloque y el contenido destacado se resolveran en la iteracion especifica de Inicio.
