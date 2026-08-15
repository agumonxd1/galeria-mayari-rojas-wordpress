<?php
/** Idempotent staging seed for editorial index pages and artist imagery. */
defined( 'ABSPATH' ) || exit( 1 );

$pages = array( 'colecciones' => 'Colecciones', 'coleccionistas' => 'Coleccionistas' );
foreach ( $pages as $slug => $title ) {
	if ( ! get_page_by_path( $slug ) ) wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $title, 'post_name' => $slug ) );
}

$artists = array(
	'elmar-rojas' => array( 'Elmar Rojas', 6235, 6237, 1 ),
	'irene-carlos' => array( 'Irene Carlos', 6145, 0, 20 ),
	'hector-tadeo' => array( 'Hector Tadeo', 6085, 0, 30 ),
	'juan-navipop' => array( 'Juan Navipop', 6159, 6166, 40 ),
	'elsie-wunderlich' => array( 'Elsie Wunderlich', 6185, 6207, 50 ),
	'rodolfo-abularach' => array( 'Rodolfo Abularach', 6203, 6199, 60 ),
	'milton-bautista' => array( 'Milton Bautista', 6189, 0, 70 ),
	'miguel-hernandez' => array( 'Miguel Hernandez', 6295, 0, 80 ),
	'armando-lara' => array( 'Armando Lara', 6314, 0, 90 ),
	'bernard-dreyfus' => array( 'Bernard Dreyfus', 6328, 0, 100 ),
	'rudy-cotton' => array( 'Rudy Cotton', 6341, 0, 110 ),
	'ramon-avila' => array( 'Ramon Avila', 6357, 0, 120 ),
);
foreach ( $artists as $slug => $data ) {
	$term = get_term_by( 'slug', $slug, 'gmr_artist' );
	if ( ! $term ) { $created = wp_insert_term( $data[0], 'gmr_artist', array( 'slug' => $slug ) ); $term = is_wp_error( $created ) ? null : get_term( $created['term_id'] ); }
	if ( ! $term ) continue;
	if ( $data[1] && ! get_term_meta( $term->term_id, 'gmr_artist_portrait_id', true ) ) update_term_meta( $term->term_id, 'gmr_artist_portrait_id', $data[1] );
	if ( $data[2] && ! get_term_meta( $term->term_id, 'gmr_artist_cover_id', true ) ) update_term_meta( $term->term_id, 'gmr_artist_cover_id', $data[2] );
	update_term_meta( $term->term_id, 'gmr_artist_order', $data[3] );
}

echo wp_json_encode( array( 'pages' => array_keys( $pages ), 'artists' => count( $artists ) ) ) . PHP_EOL;
