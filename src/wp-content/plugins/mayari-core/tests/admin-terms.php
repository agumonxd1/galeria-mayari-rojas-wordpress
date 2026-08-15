<?php
/**
 * Non-destructive artist and collection admin smoke test.
 * Run on staging: wp eval-file tests/admin-terms.php
 *
 * @package MayariCore
 */

defined( 'ABSPATH' ) || exit( 1 );

ob_start();
GMR_Core_Admin_Terms::render_add_fields( 'gmr_artist' );
$artist_form = ob_get_clean();

ob_start();
GMR_Core_Admin_Terms::render_add_fields( 'gmr_collection' );
$collection_form = ob_get_clean();

$artist_meta     = get_registered_meta_keys( 'term', 'gmr_artist' );
$collection_meta = get_registered_meta_keys( 'term', 'gmr_collection' );
$artist_keys     = array( 'gmr_artist_biography', 'gmr_artist_portrait_id', 'gmr_artist_cover_id', 'gmr_artist_special_template' );
$collection_keys = array( 'gmr_collection_subtitle', 'gmr_collection_text', 'gmr_collection_cover_id', 'gmr_collection_artists', 'gmr_visibility' );

$checks = array(
	'class_loaded'               => class_exists( 'GMR_Core_Admin_Terms' ),
	'artist_schema_registered'   => ! array_diff( $artist_keys, array_keys( $artist_meta ) ),
	'collection_schema_registered'=> ! array_diff( $collection_keys, array_keys( $collection_meta ) ),
	'artist_nonce_rendered'      => str_contains( $artist_form, 'name="gmr_term_nonce"' ),
	'artist_media_rendered'      => str_contains( $artist_form, 'name="gmr_artist_portrait_id"' ),
	'elmar_option_rendered'      => str_contains( $artist_form, 'value="elmar"' ),
	'collection_nonce_rendered'  => str_contains( $collection_form, 'name="gmr_term_nonce"' ),
	'collection_media_rendered'  => str_contains( $collection_form, 'name="gmr_collection_cover_id"' ),
	'artist_relation_rendered'   => str_contains( $collection_form, 'name="gmr_collection_artists[]"' ),
	'visibility_rendered'        => str_contains( $collection_form, 'name="gmr_visibility"' ),
);

$failed = array_filter( $checks, static fn( $value ) => ! $value );
echo wp_json_encode( array( 'ok' => empty( $failed ), 'checks' => $checks ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) . PHP_EOL;

if ( $failed ) {
	throw new RuntimeException( 'Artist and collection admin smoke test failed.' );
}
