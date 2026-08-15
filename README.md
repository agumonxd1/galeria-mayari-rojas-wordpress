# Galeria Mayari Rojas

Repositorio de desarrollo del tema y del plugin funcional para el nuevo sitio de Galeria Mayari Rojas.

## Componentes

- `src/wp-content/plugins/mayari-core/`: datos, relaciones, permisos, catalogo privado y funciones administrativas.
- `src/wp-content/themes/mayari-rojas/`: sistema visual y plantillas del sitio.
- `PLAN-MAESTRO.md`: fases, alcance y criterios de aceptacion.
- `docs/01-DICCIONARIO-DATOS.md`: modelo canonico de obras y contenido relacionado.
- `docs/02-MATRIZ-ROLES-VISIBILIDAD.md`: roles, capacidades y reglas de privacidad.
- `docs/03-MAPA-SITIO-URLS.md`: arquitectura de informacion y rutas canonicas.

## Flujo de trabajo

1. Desarrollar y verificar localmente.
2. Subir una version identificada al staging.
3. Probar catalogo, roles, privacidad y presentacion.
4. Desplegar en produccion solamente una version aprobada.

Los respaldos, credenciales, archivos multimedia originales y dependencias generadas no deben almacenarse en Git.
