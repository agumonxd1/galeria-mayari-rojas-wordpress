<?php
/**
 * WP-CLI smoke test: wp eval-file tests/smoke.php
 *
 * @package MayariCore
 */

defined( 'ABSPATH' ) || exit( 1 );

$checks = array(
	'plugin_version'      => defined( 'GMR_CORE_VERSION' ) ? GMR_CORE_VERSION : false,
	'artist_taxonomy'    => taxonomy_exists( 'gmr_artist' ),
	'collection_taxonomy'=> taxonomy_exists( 'gmr_collection' ),
	'event_post_type'    => post_type_exists( 'gmr_event' ),
	'gallery_post_type'  => post_type_exists( 'gmr_media_gallery' ),
	'collector_role'     => null !== get_role( 'gmr_collector' ),
	'manager_role'       => null !== get_role( 'gmr_gallery_manager' ),
);

$product_ids = get_posts(
	array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'fields'         => 'ids',
	)
);

if ( $product_ids && function_exists( 'wc_get_product' ) ) {
	$product = wc_get_product( $product_ids[0] );
	$checks['sample_product']       = $product instanceof WC_Product;
	$checks['product_purchasable']  = $product ? $product->is_purchasable() : null;
	$checks['anonymous_price_html'] = $product ? wp_strip_all_tags( $product->get_price_html() ) : null;
}

$failed = array_filter(
	$checks,
	static function ( $value, $key ) {
		if ( 'product_purchasable' === $key ) {
			return false !== $value;
		}

		if ( 'anonymous_price_html' === $key ) {
			return 'Consultar' !== $value;
		}

		return ! $value;
	},
	ARRAY_FILTER_USE_BOTH
);

echo wp_json_encode(
	array(
		'ok'     => empty( $failed ),
		'checks' => $checks,
	),
	JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
);
echo PHP_EOL;

if ( $failed ) {
	exit( 1 );
}
