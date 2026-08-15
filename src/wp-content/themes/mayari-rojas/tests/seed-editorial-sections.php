<?php
defined( 'ABSPATH' ) || exit;
if ( 'cli' !== PHP_SAPI ) exit( "CLI only\n" );
foreach ( array( 'actividades' => 'Actividades', 'noticias' => 'Noticias' ) as $slug => $title ) {
	if ( ! get_page_by_path( $slug ) ) wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $title, 'post_name' => $slug ) );
}
foreach ( array( 'evento' => 'Evento', 'actividad' => 'Actividad', 'exposicion' => 'Exposicion', 'conversatorio' => 'Conversatorio', 'taller' => 'Taller' ) as $slug => $name ) {
	if ( ! term_exists( $slug, 'gmr_event_type' ) ) wp_insert_term( $name, 'gmr_event_type', array( 'slug' => $slug ) );
}
foreach ( array( 'exposiciones' => 'Exposiciones', 'artistas' => 'Artistas', 'historia-de-la-galeria' => 'Historia de la galeria', 'actividades' => 'Actividades' ) as $slug => $name ) {
	if ( ! term_exists( $slug, 'gmr_media_topic' ) ) wp_insert_term( $name, 'gmr_media_topic', array( 'slug' => $slug ) );
}
echo wp_json_encode( array( 'pages' => array( 'actividades', 'noticias' ), 'event_types' => 5, 'media_topics' => 4 ) ) . "\n";
