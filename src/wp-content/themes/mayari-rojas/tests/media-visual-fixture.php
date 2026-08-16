<?php
/** Temporary visual fixture for the multimedia templates. */
defined( 'ABSPATH' ) || exit;
$slug = 'gmr-prueba-visual-multimedia';
$existing = get_page_by_path( $slug, OBJECT, 'gmr_media_gallery' );
if ( isset( $args[0] ) && 'cleanup' === $args[0] ) {
	if ( $existing ) wp_delete_post( $existing->ID, true );
	echo "clean\n";
	return;
}
if ( $existing ) wp_delete_post( $existing->ID, true );
$products = get_posts( array( 'post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => 6, 'fields' => 'ids' ) );
$images = array_values( array_unique( array_filter( array_map( 'get_post_thumbnail_id', $products ) ) ) );
if ( count( $images ) < 3 ) throw new RuntimeException( 'Not enough existing images for multimedia fixture.' );
$post_id = wp_insert_post( array(
	'post_type' => 'gmr_media_gallery', 'post_status' => 'publish', 'post_name' => $slug,
	'post_title' => 'Memoria de la galería',
	'post_excerpt' => 'Un recorrido temporal para validar la experiencia visual del archivo multimedia.',
	'post_content' => '<p>Este recorrido reúne imágenes del acervo para comprobar la lectura editorial, las relaciones y la composición del archivo.</p>',
) );
set_post_thumbnail( $post_id, $images[0] );
update_post_meta( $post_id, 'gmr_media_ids', implode( ',', array_slice( $images, 0, 6 ) ) );
update_post_meta( $post_id, 'gmr_media_date_label', 'Archivo histórico' );
update_post_meta( $post_id, 'gmr_media_credits', 'Galería Mayarí Rojas' );
update_post_meta( $post_id, 'gmr_visibility', 'public' );
$topic = get_term_by( 'slug', 'historia-de-la-galeria', 'gmr_media_topic' );
if ( $topic ) wp_set_object_terms( $post_id, array( $topic->term_id ), 'gmr_media_topic' );
$event = get_posts( array( 'post_type' => 'gmr_event', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids' ) );
if ( $event ) update_post_meta( $post_id, 'gmr_media_events', (string) $event[0] );
echo wp_json_encode( array( 'id' => $post_id, 'url' => get_permalink( $post_id ), 'images' => count( array_slice( $images, 0, 6 ) ) ) ) . "\n";
