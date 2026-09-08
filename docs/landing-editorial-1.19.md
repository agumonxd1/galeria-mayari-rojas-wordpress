# Portada editorial — versión 1.19.0

## Administración

En Apariencia → Opciones Mayarí → Secciones de la página principal se editan los cuatro textos de disciplinas de Elmar Rojas y los encabezados de Colección, Agenda cultural y Voces sobre Elmar. También están disponibles en el Personalizador.

Valores iniciales: Pintura → Patrimonio; Obra gráfica → Memoria y tradición; Escultura → Legado; Joyería → Historia.

Los encabezados son «Obras destacadas del mes», «Exposiciones y eventos» y «Críticas artísticas». El pie utiliza «Arte, legado y patrimonio.».

En Opciones Mayarí → Redes sociales del pie se pueden configurar Instagram, Facebook, YouTube, TikTok, LinkedIn y X. Solo se muestra un icono si tiene un enlace HTTP o HTTPS válido. No se incorporan perfiles de ejemplo.

## Agenda destacada

Agenda → editar evento → Datos de agenda incluye «Evento destacado en la página principal». El metadato booleano es gmr_event_featured.

La portada reserva hasta cuatro posiciones. Primero selecciona destacados por fecha de inicio descendente y completa las posiciones con otros eventos, sin duplicar. Los eventos existentes sin ese metadato siguen siendo elegibles. Se conservan las reglas de visibilidad para visitantes y coleccionistas en ambas consultas. Los destacados muestran un badge.

## Críticas

Las tarjetas conservan la rotación existente y enlazan al texto completo. Priorizan el extracto editorial cuando existe y usan el contenido como alternativa. La vista previa se limita a 32 palabras y seis líneas, con tipografía más pequeña y altura natural. Los grupos ocultos ya no fuerzan la altura del grupo visible.

## Verificación en staging

- PHP: sintaxis válida en todos los archivos modificados.
- Comprobados guardado y desmarcado del destacado desde el formulario editorial.
- Comprobado que un destacado antiguo aparece antes que un evento reciente sin el metadato.
- Comprobado que un destacado privado no aparece al visitante.
- Comprobados badge y campos nuevos de Opciones Mayarí.
- Comprobado que las redes vacías no generan enlaces y una red configurada genera icono y nombre accesible.
- Revisada la portada en escritorio y a 390 px; sin desbordamiento horizontal en móvil.
- Los tres eventos temporales de prueba se eliminaron al terminar; no se modificaron eventos editoriales existentes.

Los cambios se desplegaron únicamente en staging. Se conservó una copia local de los archivos anteriores en tmp/landing-backup-20260907-230319.
