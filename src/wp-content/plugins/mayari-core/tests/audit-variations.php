<?php
/** Read-only variation audit. @package MayariCore */
defined( 'ABSPATH' ) || exit( 1 );

$parents = get_posts( array( 'post_type' => 'product', 'post_status' => array( 'publish', 'draft' ), 'posts_per_page' => -1, 'fields' => 'ids', 'tax_query' => array( array( 'taxonomy' => 'product_type', 'field' => 'slug', 'terms' => 'variable' ) ) ) ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
$report = array();
foreach ( $parents as $parent_id ) {
	$rows = array();
	foreach ( wc_get_product( $parent_id )->get_children() as $variation_id ) {
		$variation = wc_get_product( $variation_id );
		$rows[] = array(
			'id'         => $variation_id,
			'sku'        => $variation->get_sku(),
			'price'      => $variation->get_price(),
			'image_id'   => $variation->get_image_id(),
			'attributes' => $variation->get_attributes(),
			'status'     => $variation->get_status(),
		);
	}
	$report[] = array( 'id' => $parent_id, 'title' => get_the_title( $parent_id ), 'status' => get_post_status( $parent_id ), 'variations' => $rows );
}
echo wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . PHP_EOL;
