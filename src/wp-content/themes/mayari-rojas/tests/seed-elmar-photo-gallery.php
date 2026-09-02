<?php
/**
 * Create or update the curated Elmar Rojas photographic archive.
 * Run with: wp eval-file wp-content/themes/mayari-rojas/tests/seed-elmar-photo-gallery.php
 */
defined( 'ABSPATH' ) || exit;

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

$source_dir = WP_CONTENT_DIR . '/uploads/gmr-import-elmar';
$items = array(
	array( 'elmar-rojas-proceso-creativo-01.jpg', 'Elmar Rojas trabajando sobre una obra gráfica' ),
	array( 'elmar-rojas-proceso-creativo-02.jpg', 'Elmar Rojas interviniendo una obra gráfica' ),
	array( 'elmar-rojas-taller-escultura-01.jpg', 'Elmar Rojas trabajando una escultura en su taller' ),
	array( 'elmar-rojas-taller-escultura-02.jpg', 'Elmar Rojas junto a una escultura en proceso' ),
	array( 'elmar-rojas-taller-pintura.jpg', 'Elmar Rojas trabajando en una pintura' ),
	array( 'elmar-rojas-retrato-taller-01.jpg', 'Retrato de Elmar Rojas en su taller' ),
	array( 'elmar-rojas-retrato-taller-02.jpg', 'Elmar Rojas junto a una de sus pinturas' ),
	array( 'elmar-rojas-escultura-metal-01.jpg', 'Elmar Rojas junto a una escultura de metal' ),
	array( 'elmar-rojas-escultura-metal-02.jpg', 'Elmar Rojas en su taller de escultura' ),
	array( 'elmar-rojas-escultura-metal-03.jpg', 'Elmar Rojas junto a una escultura monumental' ),
	array( 'elmar-rojas-ciudad-de-mexico.jpg', 'Elmar Rojas en Ciudad de México' ),
	array( 'elmar-rojas-arquitectura.jpg', 'Elmar Rojas en un espacio de arquitectura contemporánea' ),
	array( 'elmar-rojas-feria-mexico-2012.jpg', 'Elmar Rojas durante una feria de arte en México en 2012' ),
	array( 'elmar-rojas-museo-ixchel-guatemala.jpg', 'Elmar Rojas durante una exposición en el Museo Ixchel de Guatemala' ),
	array( 'elmar-rojas-artistas-guatemaltecos.jpg', 'Elmar Rojas junto a artistas guatemaltecos' ),
	array( 'elmar-rojas-andasolo-avenida-reforma-2015.jpg', 'Escultura Andasolo de Elmar Rojas en Avenida Reforma, 2015' ),
	array( 'elmar-rojas-mayari-rojas-andasolo.jpg', 'Elmar Rojas y Mayarí Rojas junto a la escultura Andasolo' ),
	array( 'elmar-rojas-exposicion.jpg', 'Elmar Rojas en una exposición de arte' ),
	array( 'elmar-rojas-retrato.jpg', 'Retrato de Elmar Rojas' ),
);

$gallery = get_page_by_path( 'elmar-rojas-vida-obra-y-memoria', OBJECT, 'gmr_media_gallery' );
$gallery_id = $gallery ? (int) $gallery->ID : wp_insert_post( array(
	'post_type'    => 'gmr_media_gallery',
	'post_status'  => 'publish',
	'post_name'    => 'elmar-rojas-vida-obra-y-memoria',
	'post_title'   => 'Elmar Rojas: vida, obra y memoria',
	'post_excerpt' => 'Un recorrido fotográfico por el proceso creativo, los talleres, las exposiciones y la dimensión humana de Elmar Rojas.',
	'post_content' => '<p>Este archivo reúne escenas del proceso creativo de Elmar Rojas, su relación con la pintura y la escultura, y algunos de los encuentros y exposiciones que acompañaron su trayectoria.</p><p>Las fotografías se presentan como una memoria visual abierta: un acercamiento al artista trabajando, dialogando con su obra y compartiendo su universo con otras generaciones.</p>',
) );

if ( ! $gallery_id || is_wp_error( $gallery_id ) ) {
	WP_CLI::error( 'No fue posible crear la galería.' );
}

$attachment_ids = array();
foreach ( $items as list( $filename, $alt ) ) {
	$existing = get_posts( array(
		'post_type'      => 'attachment',
		'post_status'    => 'inherit',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_key'       => '_gmr_elmar_source_file',
		'meta_value'     => $filename,
	) );
	if ( $existing ) {
		$attachment_id = (int) $existing[0];
	} else {
		$path = $source_dir . '/' . $filename;
		if ( ! is_readable( $path ) ) {
			WP_CLI::warning( 'No se encontró ' . $filename );
			continue;
		}
		$tmp = wp_tempnam( $filename );
		copy( $path, $tmp );
		$attachment_id = media_handle_sideload( array( 'name' => $filename, 'tmp_name' => $tmp ), $gallery_id, $alt );
		if ( is_wp_error( $attachment_id ) ) {
			@unlink( $tmp );
			WP_CLI::warning( $attachment_id->get_error_message() );
			continue;
		}
		update_post_meta( $attachment_id, '_gmr_elmar_source_file', $filename );
	}
	wp_update_post( array( 'ID' => $attachment_id, 'post_title' => $alt, 'post_excerpt' => $alt, 'post_parent' => $gallery_id ) );
	update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt );
	$attachment_ids[] = $attachment_id;
}

update_post_meta( $gallery_id, 'gmr_media_ids', implode( ',', $attachment_ids ) );
update_post_meta( $gallery_id, 'gmr_media_date_label', 'Archivo de vida y obra' );
update_post_meta( $gallery_id, 'gmr_media_credits', 'Archivo Galería Mayarí Rojas. Todos los derechos reservados.' );
update_post_meta( $gallery_id, 'gmr_visibility', 'public' );
if ( $attachment_ids ) {
	set_post_thumbnail( $gallery_id, $attachment_ids[0] );
	set_theme_mod( 'gmr_home_elmar_image', $attachment_ids[0] );
}

$artist = get_term_by( 'slug', 'elmar-rojas', 'gmr_artist' );
if ( $artist ) {
	wp_set_object_terms( $gallery_id, array( $artist->term_id ), 'gmr_artist' );
}
$topic = term_exists( 'elmar-rojas', 'gmr_media_topic' );
if ( ! $topic ) {
	$topic = wp_insert_term( 'Elmar Rojas', 'gmr_media_topic', array( 'slug' => 'elmar-rojas' ) );
}
if ( ! is_wp_error( $topic ) ) {
	wp_set_object_terms( $gallery_id, array( (int) ( is_array( $topic ) ? $topic['term_id'] : $topic ) ), 'gmr_media_topic' );
}

WP_CLI::success( sprintf( 'Galería %d actualizada con %d fotografías.', $gallery_id, count( $attachment_ids ) ) );
