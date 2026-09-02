# Galería Mayarí Rojas — WordPress

Tema y plugin desarrollados a medida para la experiencia digital de una galería de arte guatemalteca. El proyecto convierte WooCommerce en un catálogo curatorial sin venta en línea y combina una vitrina pública con un archivo privado para coleccionistas.

## El proyecto

La plataforma organiza obras, artistas, disciplinas, colecciones, eventos, testimonios y archivos multimedia desde una única fuente de datos. El diseño utiliza composiciones editoriales y bloques bento para dar protagonismo a las imágenes sin perder claridad administrativa.

Entre sus objetivos principales están:

- preservar y presentar el legado de Elmar Rojas;
- administrar un catálogo de arte con información especializada;
- publicar selecciones distintas para visitantes y coleccionistas;
- proteger precios, documentos e imágenes privadas desde el servidor;
- facilitar la carga cotidiana de contenido al equipo de la galería.

## Funcionalidades principales

- Tema WordPress responsive construido desde cero.
- Plugin funcional independiente `mayari-core`.
- WooCommerce operando en modo catálogo.
- Obras relacionadas con artistas, disciplinas y colecciones.
- Estados comerciales y niveles de visibilidad independientes.
- Imagen pública opcional y original privado protegido por obra.
- Precios y documentos visibles según capacidades.
- Área de Coleccionistas con cuentas creadas por la galería.
- Login, recuperación y restablecimiento de contraseña con interfaz propia.
- Perfiles editoriales de artistas y experiencia especial para Elmar Rojas.
- Agenda dinámica de actividades y exposiciones.
- Voces sobre Elmar como contenido editorial independiente.
- Galerías multimedia con mosaico adaptable y visor navegable.
- Formularios administrativos especializados para obras de arte.
- Consultas de obra almacenadas en WordPress.
- Migración reproducible del inventario histórico.

## Arquitectura

```text
src/wp-content/
├── plugins/mayari-core/      Datos, permisos, privacidad y administración
└── themes/mayari-rojas/      Plantillas, componentes y sistema visual

docs/                         Decisiones funcionales y documentación técnica
tools/                        Utilidades reproducibles para preparación de medios
PLAN-MAESTRO.md               Fases y criterios generales del proyecto
```

La separación permite cambiar la presentación visual sin perder obras, relaciones, permisos o metadatos especializados.

## Privacidad

La privacidad no depende únicamente de ocultar elementos con CSS. Las reglas se aplican en consultas, URLs directas, REST API, feeds, documentos e imágenes protegidas.

Los archivos originales privados se almacenan fuera del directorio público y se entregan mediante un controlador que valida sesión, capacidades y un token temporal.

## Tecnologías

- WordPress
- WooCommerce
- PHP 8.1+
- JavaScript nativo
- CSS responsive
- WP-CLI
- Git

## Documentación

El repositorio conserva el proceso completo de diseño e implementación. Los documentos de `docs/` cubren el modelo de datos, la matriz de permisos, migraciones, administración, catálogo, artistas, colecciones, agenda y sistema visual.

## Estado

El desarrollo funcional y visual se realiza primero en un entorno aislado de staging. El paso a producción requiere la auditoría final de seguridad, accesibilidad, rendimiento, SEO, formularios y restauración de respaldos.

## Uso y derechos

Este repositorio se publica como muestra de portafolio. El código fuente se distribuye bajo GPL-2.0-or-later, de acuerdo con el ecosistema WordPress. Las marcas, obras de arte, fotografías, textos curatoriales, archivos de clientes e identidad visual no quedan licenciados para reutilización. Consulte [LICENSE](LICENSE) para conocer el alcance.
