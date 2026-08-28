<?php
/**
 * Curate a small public selection per artist. Dry-run unless explicitly confirmed.
 *
 * Run: wp eval-file tests/curate-public-catalog.php
 * Apply: GMR_CURATION_CONFIRM=1 wp eval-file tests/curate-public-catalog.php
 *
 * @package MayariCore
 */

defined( 'ABSPATH' ) || exit( 1 );

$published_ids = get_posts( array(
	'post_type'      => 'product',
	'post_status'    => 'publish',
	'posts_per_page' => -1,
	'fields'         => 'ids',
	'orderby'        => 'date',
	'order'          => 'DESC',
) );

$groups = array();
foreach ( $published_ids as $product_id ) {
	$status = get_post_meta( $product_id, 'gmr_commercial_status', true ) ?: 'available';
	if ( ! has_post_thumbnail( $product_id ) || ! in_array( $status, array( 'available', 'on_exhibition' ), true ) ) continue;
	$artists = wp_get_post_terms( $product_id, 'gmr_artist' );
	if ( is_wp_error( $artists ) || ! $artists ) $artists = array( (object) array( 'term_id' => 0, 'name' => 'Sin artista', 'slug' => 'sin-artista' ) );
	foreach ( $artists as $artist ) {
		$groups[ $artist->term_id ]['artist'] = $artist;
		$groups[ $artist->term_id ]['products'][] = $product_id;
	}
}

$selected_ids = array();
$report       = array();
foreach ( $groups as $group ) {
	$candidates = array_values( array_unique( $group['products'] ) );
	usort( $candidates, static function( int $a, int $b ): int {
		$a_featured = (int) (bool) get_post_meta( $a, 'gmr_featured', true );
		$b_featured = (int) (bool) get_post_meta( $b, 'gmr_featured', true );
		if ( $a_featured !== $b_featured ) return $b_featured <=> $a_featured;
		return strcmp( get_post_field( 'post_date', $b ), get_post_field( 'post_date', $a ) );
	} );

	$chosen      = array();
	$disciplines = array();
	foreach ( $candidates as $product_id ) {
		$terms = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'slugs' ) );
		$discipline = is_wp_error( $terms ) ? 'sin-disciplina' : ( array_values( array_intersect( $terms, array( 'pintura', 'escultura', 'obra-grafica', 'joyeria' ) ) )[0] ?? 'sin-disciplina' );
		if ( isset( $disciplines[ $discipline ] ) ) continue;
		$chosen[] = $product_id;
		$disciplines[ $discipline ] = true;
		if ( 3 === count( $chosen ) ) break;
	}
	foreach ( $candidates as $product_id ) {
		if ( 3 === count( $chosen ) ) break;
		if ( ! in_array( $product_id, $chosen, true ) ) $chosen[] = $product_id;
	}

	$selected_ids = array_merge( $selected_ids, $chosen );
	$report[] = array(
		'artist'         => $group['artist']->name,
		'eligible_count' => count( $candidates ),
		'selected'       => array_map( static fn( $id ) => array( 'id' => $id, 'title' => get_the_title( $id ) ), $chosen ),
	);
}

$selected_ids = array_values( array_unique( $selected_ids ) );
usort( $report, static fn( $a, $b ) => strcasecmp( $a['artist'], $b['artist'] ) );
$result = array( 'mode'=>getenv('GMR_CURATION_CONFIRM')?'apply':'dry-run', 'published_total'=>count($published_ids), 'public_selected'=>count($selected_ids), 'collectors_selected'=>count($published_ids)-count($selected_ids), 'groups'=>$report );

if ( getenv( 'GMR_CURATION_CONFIRM' ) ) {
	$snapshot = array();
	foreach ( $published_ids as $product_id ) $snapshot[ $product_id ] = get_post_meta( $product_id, 'gmr_visibility', true );
	$snapshot_key = 'gmr_public_curation_snapshot_' . gmdate( 'Ymd_His' );
	update_option( $snapshot_key, $snapshot, false );
	foreach ( $published_ids as $product_id ) update_post_meta( $product_id, 'gmr_visibility', in_array( $product_id, $selected_ids, true ) ? 'public' : 'collectors' );
	$result['snapshot_key'] = $snapshot_key;
}

echo wp_json_encode( $result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . PHP_EOL;
