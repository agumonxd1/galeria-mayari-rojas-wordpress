# Plan maestro - Galeria Mayari Rojas

## 1. Objetivo

Construir un tema propio de WordPress para Galeria Mayari Rojas, compatible con WooCommerce y orientado a catalogo de arte, junto con un plugin complementario que gestione obras, artistas, colecciones, contenido editorial, permisos y el area privada Coleccionistas.

El sistema no vendera en linea durante la primera etapa. WooCommerce se utilizara como motor de inventario y catalogo, dejando preparada una posible habilitacion futura de comercio electronico.

## 2. Principios del proyecto

- Una sola fuente de datos para cada obra.
- No duplicar obras en paginas de artistas, categorias o colecciones.
- Separar presentacion y logica: el tema controla el diseño; el plugin controla datos, relaciones y permisos.
- Aplicar la privacidad en el servidor, no solo ocultar elementos visualmente.
- Diseñar primero para contenido real: obras verticales, horizontales, esculturas, joyeria, documentos y video.
- Facilitar el trabajo diario del equipo de la galeria con formularios claros y pocos campos irrelevantes.
- Mantener importacion, exportacion y portabilidad de datos.
- Cumplir accesibilidad, rendimiento, SEO y diseño adaptable desde el inicio.

## 3. Arquitectura general

### Tema `mayari-rojas`

Responsable de:

- Sistema visual y `theme.json`.
- Header, navegacion y footer.
- Plantillas publicas y privadas.
- Componentes y patrones editoriales.
- Integracion visual con WooCommerce.
- Pagina especial de Elmar Rojas.
- Interfaz de Coleccionistas.

### Plugin `mayari-core`

Responsable de:

- Modo catalogo de WooCommerce.
- Campos especializados de las obras.
- Artistas y sus perfiles.
- Colecciones y relaciones.
- Estados comerciales.
- Niveles de visibilidad.
- Roles y capacidades.
- Eventos, actividades y archivo multimedia.
- Formularios administrativos simplificados.
- Reglas para precios, documentos y contenido privado.
- Importacion, exportacion y herramientas de mantenimiento.

## 4. Modelo de contenido inicial

### Obra

Se almacenara como producto simple de WooCommerce.

Campos principales:

- Titulo.
- Codigo o SKU.
- Artista.
- Disciplina.
- Coleccion o serie.
- Año.
- Tecnica.
- Soporte y materiales.
- Alto, ancho y profundidad.
- Unidad de medida.
- Peso opcional.
- Tiraje o numero de edicion.
- Firma.
- Certificado de autenticidad.
- Procedencia.
- Condicion.
- Descripcion corta y texto curatorial.
- Imagen principal, galeria y documentos.
- Precio y moneda.
- Estado comercial.
- Visibilidad de la obra.
- Visibilidad del precio.
- Obra destacada.

Estados comerciales:

- Disponible.
- Reservada.
- Vendida.
- No disponible.
- En exposicion.
- Archivo historico.

Visibilidad de la obra:

- Publica.
- Solo Coleccionistas.
- Oculta o archivada.

Visibilidad del precio:

- Solo administradores.
- Coleccionistas y administradores.
- Publico.

### Artista

Entidad relacionada con las obras, con:

- Nombre y slug.
- Retrato.
- Portada.
- Biografia corta y completa.
- Historia o trayectoria.
- Cronologia.
- Premios.
- Exposiciones.
- Documentos y publicaciones.
- Galeria multimedia.
- Datos SEO.
- Orden de aparicion.
- Indicador de artista destacado.

### Elmar Rojas

Usara el mismo inventario y relacion de artista que los demas, pero tendra plantillas editoriales exclusivas:

- Portada general.
- Biografia e historia.
- Cronologia y premios.
- Documentos.
- Archivo multimedia.
- Pintura.
- Escultura.
- Obra grafica.
- Joyeria.

Las paginas de disciplina consultaran el catalogo central con los filtros `artista = Elmar Rojas` y `disciplina = correspondiente`.

### Coleccion

- Nombre.
- Artista o artistas relacionados.
- Año o periodo.
- Portada.
- Texto curatorial.
- Obras relacionadas.
- Documentos y multimedia.
- Visibilidad publica o privada.

### Agenda

Un tipo de contenido comun con clasificacion por tipo:

- Evento.
- Actividad.
- Exposicion.
- Conversatorio.
- Taller.

Campos: fechas, horario, lugar, modalidad, estado, portada, galeria, artistas y obras relacionadas.

### Noticias

Entradas editoriales con categorias y relaciones opcionales con artistas, obras, colecciones y eventos.

### Archivo multimedia

Galerias tematicas de fotografia, video y documentos, relacionadas opcionalmente con artistas, colecciones o eventos.

## 5. Roles y acceso

### Visitante

- Ve paginas y obras publicas.
- No ve precios privados.
- No ve documentos ni secciones restringidas.
- Puede solicitar informacion.

### Coleccionista

- Cuenta creada por el equipo de la galeria.
- Ve obras publicas y privadas autorizadas.
- Ve precios permitidos.
- Accede a contenido y documentos privados.
- No puede editar contenido.

### Gestor de galeria

- Crea y edita obras, artistas, colecciones, agenda, noticias y galerias.
- Crea y administra cuentas de Coleccionistas.
- No modifica configuracion tecnica sensible.

### Administrador

- Control total de WordPress, tema, plugin y usuarios.

## 6. Fases de construccion

### Fase 0 - Preparacion y respaldo

Objetivo: trabajar sin poner en riesgo el sitio existente.

Tareas:

- Crear repositorio Git y convenciones de ramas/versiones.
- Preparar ambiente local o staging.
- Registrar versiones objetivo de WordPress, PHP y WooCommerce.
- Inventariar plugins activos y dependencias de Lekker/Elementor.
- Obtener respaldo de archivos, base de datos y medios.
- Exportar productos, entradas, paginas, usuarios y taxonomias actuales.
- Definir estrategia de migracion y reversa.

Criterio de aceptacion:

- Existe un staging reproducible y un respaldo restaurable antes de modificar produccion.

### Fase 1 - Auditoria de contenido y especificacion funcional

Objetivo: cerrar el modelo de datos antes de programar formularios y plantillas.

Tareas:

- Inventariar todas las secciones actuales.
- Revisar una muestra representativa de pinturas, esculturas, obra grafica y joyeria.
- Crear el diccionario definitivo de campos.
- Definir campos obligatorios por disciplina.
- Definir taxonomias y valores controlados.
- Acordar estados comerciales y reglas de precio.
- Definir que contenido privado vera todo Coleccionista y que contenido podria asignarse individualmente.
- Preparar mapa de URLs y redirecciones.
- Definir formulario y destino de las consultas.

Criterio de aceptacion:

- Diccionario de datos, matriz de permisos y mapa del sitio aprobados.

### Fase 2 - Sistema de diseño y prototipos

Objetivo: convertir la portada aprobada en un lenguaje visual reutilizable.

Tareas:

- Definir colores, tipografias, escalas, espaciado y radios.
- Diseñar header, footer, botones, formularios, avisos y estados.
- Diseñar tarjetas de obra, artista, coleccion, evento y noticia.
- Diseñar desktop, tablet y movil.
- Prototipar las pantallas criticas:
  - Inicio.
  - Catalogo general.
  - Disciplina.
  - Ficha de obra.
  - Directorio y perfil de artista.
  - Pagina especial de Elmar.
  - Coleccion.
  - Agenda.
  - Archivo multimedia.
  - Inicio de sesion y panel Coleccionistas.
  - Formularios administrativos.
- Validar contraste, tamaños y navegacion por teclado.

Criterio de aceptacion:

- Todas las plantillas criticas estan aprobadas antes de su implementacion completa.

### Fase 3 - Base del plugin `mayari-core`

Objetivo: construir el modelo de negocio independiente del tema.

Tareas:

- Crear estructura y versionado del plugin.
- Verificar dependencias y activacion de WooCommerce.
- Registrar tipos de contenido y taxonomias.
- Registrar metadatos con esquemas, sanitizacion y permisos.
- Implementar relaciones entre obras, artistas, colecciones y contenido editorial.
- Añadir estados comerciales.
- Crear datos de demostracion controlados.
- Añadir pruebas de registro, guardado y consulta.

Criterio de aceptacion:

- Los datos se crean, editan, consultan y conservan aunque se cambie de tema.

### Fase 4 - Modo catalogo WooCommerce

Objetivo: eliminar la experiencia de compra sin perder las capacidades de inventario.

Tareas:

- Desactivar carrito y checkout en el frontend.
- Eliminar botones de compra y cantidades.
- Reemplazar compra por solicitud de informacion.
- Mantener precios almacenados en WooCommerce.
- Aplicar reglas de visibilidad del precio.
- Personalizar estados y etiquetas de disponibilidad.
- Evitar accesos accidentales a endpoints comerciales.
- Mantener una opcion de activacion futura de ventas.

Criterio de aceptacion:

- Ningun visitante puede completar una compra y el inventario sigue siendo administrable.

### Fase 5 - Privacidad y Coleccionistas

Objetivo: implementar acceso privado seguro.

Tareas:

- Crear roles y capacidades.
- Bloquear el registro publico.
- Crear flujo administrativo de alta de Coleccionistas.
- Construir inicio de sesion, recuperacion y cierre de sesion.
- Proteger consultas, URLs directas, busqueda, feeds, API y contenido relacionado.
- Ocultar precios y documentos segun permisos.
- Crear panel privado y navegacion de Coleccionistas.
- Registrar accesos y errores relevantes sin almacenar informacion innecesaria.
- Preparar expiracion o desactivacion de cuentas.

Criterio de aceptacion:

- Una prueba anonima no puede descubrir obras, precios ni documentos privados por ningun punto de entrada evaluado.

### Fase 6 - Administracion simplificada

Objetivo: hacer que el equipo pueda cargar contenido sin conocimientos tecnicos.

Tareas:

- Crear formulario de Nueva obra por secciones.
- Mostrar campos condicionales por disciplina.
- Añadir validaciones y ayudas contextuales.
- Simplificar las columnas del listado de obras.
- Añadir filtros por artista, disciplina, disponibilidad y visibilidad.
- Implementar acciones masivas seguras.
- Mejorar carga y orden de imagenes.
- Añadir formularios claros para artistas, colecciones y agenda.
- Preparar importacion/exportacion CSV.

Criterio de aceptacion:

- Un gestor puede publicar correctamente una obra de cada disciplina siguiendo solamente las instrucciones de pantalla.

### Fase 7 - Base del tema `mayari-rojas`

Objetivo: implementar el sistema visual aprobado.

Tareas:

- Crear estructura del tema de bloques.
- Configurar `theme.json`.
- Añadir tipografias locales o estrategia de carga optimizada.
- Construir header, navegacion movil y footer.
- Crear patrones y componentes compartidos.
- Implementar estados de foco, hover, carga y vacio.
- Crear configuraciones editoriales seguras y limitar opciones que rompan el diseño.

Criterio de aceptacion:

- El tema puede activarse, conserva la identidad visual y presenta correctamente los componentes base en todos los tamaños.

### Fase 8 - Catalogo y artistas

Objetivo: construir la experiencia principal de descubrimiento de obras.

Tareas:

- Catalogo general con paginacion.
- Subcatalogos por disciplina.
- Busqueda y filtros relevantes.
- Tarjeta de obra adaptable.
- Ficha detallada de obra.
- Obras relacionadas.
- Directorio de artistas.
- Perfil generico de artista.
- Obras publicas y privadas segun sesion.
- Colecciones y series.
- Formularios de consulta con referencia automatica a la obra.

Criterio de aceptacion:

- Una obra aparece automaticamente en su artista, disciplina y colecciones, respetando privacidad y estado comercial.

### Fase 9 - Experiencia especial de Elmar Rojas

Objetivo: preservar su posicion central sin duplicar datos.

Tareas:

- Portada editorial de Elmar.
- Navegacion interna o indice fijo.
- Biografia, historia y cronologia.
- Premios y documentos.
- Archivo fotografico y multimedia.
- Paginas filtradas de pintura, escultura, obra grafica y joyeria.
- Selecciones curatoriales configurables.
- Revision especial de rendimiento por la longitud de la pagina.

Criterio de aceptacion:

- Elmar tiene una experiencia diferenciada y todas sus obras provienen del catalogo central.

### Fase 10 - Contenido institucional y editorial

Objetivo: completar el ecosistema del sitio.

Tareas:

- Inicio.
- La galeria.
- Agenda, eventos y actividades.
- Noticias.
- Archivo multimedia tematico.
- Contacto y visita.
- Paginas legales y privacidad.
- Relaciones entre noticias, eventos, artistas y obras.

Criterio de aceptacion:

- Todas las secciones actuales tienen un destino nuevo y no quedan enlaces huerfanos.

### Fase 11 - Migracion de contenido

Objetivo: trasladar y normalizar la informacion existente.

Tareas:

- Limpiar codificacion UTF-8 y caracteres dañados.
- Normalizar nombres de artistas, disciplinas y tecnicas.
- Mapear productos y SKUs existentes.
- Importar imagenes con metadatos y textos alternativos.
- Migrar biografias, colecciones, eventos y noticias.
- Detectar duplicados.
- Mantener tabla de correspondencia entre URLs antiguas y nuevas.
- Ejecutar migracion de prueba antes de la definitiva.

Criterio de aceptacion:

- Los conteos, relaciones, imagenes, estados y URLs se verifican contra el inventario fuente.

### Fase 12 - Calidad, seguridad y lanzamiento

Objetivo: validar el sistema completo y reemplazar el tema actual con riesgo controlado.

Pruebas:

- Roles y privacidad.
- Busqueda y filtros.
- Formularios y correos.
- Navegadores y dispositivos.
- Accesibilidad por teclado y lector de pantalla.
- SEO, metadatos, sitemap y redirecciones.
- Rendimiento e imagenes.
- Enlaces rotos y paginas 404.
- Copias de seguridad y restauracion.
- Actualizaciones de WordPress y WooCommerce.

Lanzamiento:

- Congelar temporalmente cambios de contenido.
- Realizar respaldo final.
- Ejecutar migracion definitiva.
- Activar plugin y tema.
- Limpiar cache y regenerar enlaces permanentes.
- Revisar recorridos publicos y privados.
- Monitorear errores y formularios durante el periodo de estabilizacion.

Criterio de aceptacion:

- Lista de lanzamiento aprobada, restauracion probada y cero filtraciones de contenido privado.

## 7. Primera version funcional o MVP

El MVP debe incluir:

- Plugin `mayari-core`.
- Tema `mayari-rojas`.
- Obras y disciplinas.
- Artistas y perfiles.
- Pagina especial de Elmar.
- Colecciones.
- Modo catalogo.
- Precios privados.
- Roles Administrador, Gestor y Coleccionista.
- Inicio de sesion y catalogo privado.
- Inicio, La galeria, Agenda, Noticias, Archivo multimedia y Contacto.
- Formularios administrativos basicos.
- Importacion inicial y redirecciones.

Queda para una etapa posterior:

- Selecciones privadas diferentes para cada Coleccionista.
- Favoritos personales.
- Generacion de fichas PDF.
- Integracion con CRM.
- Reservas avanzadas.
- Multidioma.
- Venta en linea.

## 8. Orden recomendado de trabajo por iteraciones

Cada iteracion debe terminar con demostracion y aprobacion.

1. Datos y permisos.
2. Formulario de obra.
3. Catalogo y ficha de obra sin diseño final.
4. Sistema visual y componentes.
5. Catalogo publico terminado.
6. Artistas y colecciones.
7. Coleccionistas y precios privados.
8. Experiencia de Elmar.
9. Agenda, noticias y multimedia.
10. Migracion, calidad y lanzamiento.

## 9. Criterios globales de terminado

Una funcionalidad se considera terminada cuando:

- Tiene estados normales, vacios y de error.
- Funciona en escritorio, tablet y movil.
- Respeta roles y privacidad.
- Es accesible por teclado.
- No introduce errores PHP o JavaScript.
- Incluye pruebas proporcionales a su riesgo.
- Tiene instrucciones de administracion cuando sean necesarias.
- Fue revisada con datos reales o representativos.

## 10. Decisiones que debemos cerrar en la Fase 1

- Lista exacta de disciplinas iniciales.
- Campos obligatorios para cada disciplina.
- Moneda y formato de precios.
- Si todos los Coleccionistas ven lo mismo durante el MVP.
- Si las obras vendidas permanecen visibles publicamente.
- Si los visitantes pueden consultar cualquier obra publica.
- Destinatarios y contenido de los formularios de consulta.
- Documentos descargables y reglas de acceso.
- Idioma unico o preparacion inmediata para multidioma.
- Alcance exacto de la migracion desde el sitio actual.

## 11. Primer hito de ejecucion

Antes de escribir el tema, el primer hito sera producir y aprobar tres documentos operativos:

1. Diccionario de datos de la obra.
2. Matriz de roles y visibilidad.
3. Mapa del sitio y URLs.

Con estos documentos aprobados comenzara la implementacion del plugin `mayari-core`.
