<?php
/**
 * Non-destructive admin artwork smoke test.
 * Run on staging: wp eval-file tests/admin-artwork.php
 *
 * @package MayariCore
 */

defined( 'ABSPATH' ) || exit( 1 );

$product_ids = get_posts(
	array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'fields'         => 'ids',
	)
);

if ( ! $product_ids ) {
	throw new RuntimeException( 'No product available for admin smoke test.' );
}

$product = get_post( $product_ids[0] );

ob_start();
GMR_Core_Admin_Artwork::render_meta_box( $product );
$rendered = ob_get_clean();

$checks = array(
	'class_loaded'             => class_exists( 'GMR_Core_Admin_Artwork' ),
	'meta_box_callable'        => is_callable( array( 'GMR_Core_Admin_Artwork', 'add_meta_box' ) ),
	'save_callback_callable'   => is_callable( array( 'GMR_Core_Admin_Artwork', 'save' ) ),
	'nonce_rendered'           => str_contains( $rendered, 'name="gmr_artwork_nonce"' ),
	'artist_field_rendered'    => str_contains( $rendered, 'name="gmr_artist"' ),
	'discipline_rendered'      => str_contains( $rendered, 'name="gmr_discipline"' ),
	'commercial_rendered'      => str_contains( $rendered, 'name="gmr_commercial_status"' ),
	'visibility_rendered'      => str_contains( $rendered, 'name="gmr_visibility"' ),
	'conditional_markup'       => str_contains( $rendered, 'data-gmr-disciplines="escultura,joyeria"' ),
);

$failed = array_filter( $checks, static fn( $value ) => ! $value );
echo wp_json_encode( array( 'ok' => empty( $failed ), 'checks' => $checks ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) . PHP_EOL;

if ( $failed ) {
	throw new RuntimeException( 'Artwork admin smoke test failed.' );
}
