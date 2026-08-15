<?php
/**
 * Read-only inventory audit for migration planning.
 * Run on staging: wp eval-file tests/audit-inventory.php
 *
 * @package MayariCore
 */

defined( 'ABSPATH' ) || exit( 1 );

global $wpdb;

$product_ids = get_posts( array(
	'post_type'      => 'product',
	'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
	'posts_per_page' => -1,
	'fields'         => 'ids',
) );

$status_counts = array_count_values( array_map( static fn( $id ) => get_post_status( $id ), $product_ids ) );
$coverage      = array(
	'featured_image' => 0,
	'gallery'        => 0,
	'price'          => 0,
	'sku'            => 0,
	'excerpt'        => 0,
	'content'        => 0,
	'elementor_data' => 0,
);
$category_usage = array();
$quality = array(
	'no_discipline_category'       => 0,
	'multiple_discipline_category' => 0,
	'no_artist_category'           => 0,
	'multiple_artist_category'     => 0,
	'missing_featured_image_ids'   => array(),
	'missing_price_ids'            => array(),
	'missing_sku_ids'              => array(),
	'no_discipline_ids'            => array(),
	'multiple_discipline_ids'      => array(),
	'no_artist_ids'                => array(),
	'multiple_artist_ids'          => array(),
);
$discipline_slugs = array( 'pintura', 'escultura', 'obra-grafica', 'joyeria' );
$artist_slugs = array( 'elmar-rojas', 'irene-carlos', 'rodolfo-abularach', 'milton-bautista', 'miguel-hernandez', 'ramon-avila', 'rudy-cotton', 'armando-lara', 'hector-tadeo', 'bernard-dreyfus', 'ednard-dreyfus', 'elsie-wunderlich', 'juan-navipop' );
$attribute_coverage = array();

foreach ( $product_ids as $product_id ) {
	$coverage['featured_image'] += has_post_thumbnail( $product_id ) ? 1 : 0;
	$coverage['gallery']        += '' !== (string) get_post_meta( $product_id, '_product_image_gallery', true ) ? 1 : 0;
	$coverage['price']          += '' !== (string) get_post_meta( $product_id, '_price', true ) ? 1 : 0;
	$coverage['sku']            += '' !== (string) get_post_meta( $product_id, '_sku', true ) ? 1 : 0;
	$post = get_post( $product_id );
	$coverage['excerpt']        += $post && '' !== trim( $post->post_excerpt ) ? 1 : 0;
	$coverage['content']        += $post && '' !== trim( $post->post_content ) ? 1 : 0;
	$coverage['elementor_data'] += '' !== (string) get_post_meta( $product_id, '_elementor_data', true ) ? 1 : 0;
	$terms = wp_get_post_terms( $product_id, 'product_cat' );
	$slugs = wp_list_pluck( $terms, 'slug' );
	$disciplines = array_intersect( $slugs, $discipline_slugs );
	$artists = array_intersect( $slugs, $artist_slugs );
	$quality['no_discipline_category'] += 0 === count( $disciplines ) ? 1 : 0;
	$quality['multiple_discipline_category'] += count( $disciplines ) > 1 ? 1 : 0;
	$quality['no_artist_category'] += 0 === count( $artists ) ? 1 : 0;
	$quality['multiple_artist_category'] += count( $artists ) > 1 ? 1 : 0;
	if ( 0 === count( $disciplines ) ) $quality['no_discipline_ids'][] = $product_id;
	if ( count( $disciplines ) > 1 ) $quality['multiple_discipline_ids'][ $product_id ] = array_values( $disciplines );
	if ( 0 === count( $artists ) ) $quality['no_artist_ids'][] = $product_id;
	if ( count( $artists ) > 1 ) $quality['multiple_artist_ids'][ $product_id ] = array_values( $artists );
	if ( ! has_post_thumbnail( $product_id ) ) $quality['missing_featured_image_ids'][] = $product_id;
	if ( '' === (string) get_post_meta( $product_id, '_price', true ) ) $quality['missing_price_ids'][] = $product_id;
	if ( '' === (string) get_post_meta( $product_id, '_sku', true ) ) $quality['missing_sku_ids'][] = $product_id;
	foreach ( $terms as $term ) {
		$category_usage[ $term->slug ] = ( $category_usage[ $term->slug ] ?? 0 ) + 1;
	}
	foreach ( (array) get_post_meta( $product_id, '_product_attributes', true ) as $name => $definition ) {
		$attribute_coverage[ $name ] = ( $attribute_coverage[ $name ] ?? 0 ) + 1;
	}
}
arsort( $category_usage );
arsort( $attribute_coverage );

$placeholders = implode( ',', array_fill( 0, count( $product_ids ), '%d' ) );
$meta_rows = $product_ids ? $wpdb->get_results( $wpdb->prepare(
	"SELECT meta_key, COUNT(*) rows_count, COUNT(DISTINCT post_id) product_count,
	SUM(CASE WHEN meta_value <> '' THEN 1 ELSE 0 END) nonempty_count
	FROM {$wpdb->postmeta}
	WHERE post_id IN ($placeholders)
	GROUP BY meta_key ORDER BY product_count DESC, meta_key ASC",
	...$product_ids
), ARRAY_A ) : array(); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

$attributes = array();
foreach ( wc_get_attribute_taxonomies() as $attribute ) {
	$taxonomy = wc_attribute_taxonomy_name( $attribute->attribute_name );
	$attributes[ $taxonomy ] = array_map( static fn( $term ) => array(
		'name'  => $term->name,
		'slug'  => $term->slug,
		'count' => $term->count,
	), get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) ) );
}

$duplicate_titles = $wpdb->get_results( "SELECT post_title, COUNT(*) count, GROUP_CONCAT(ID ORDER BY ID) ids FROM {$wpdb->posts} WHERE post_type='product' AND post_status IN ('publish','draft','pending','private','future') GROUP BY post_title HAVING COUNT(*) > 1 ORDER BY count DESC, post_title", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
$duplicate_skus = $wpdb->get_results( "SELECT meta_value sku, COUNT(DISTINCT post_id) count, GROUP_CONCAT(DISTINCT post_id ORDER BY post_id) ids FROM {$wpdb->postmeta} WHERE meta_key='_sku' AND meta_value <> '' GROUP BY meta_value HAVING COUNT(DISTINCT post_id) > 1 ORDER BY count DESC, sku", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
$variable_products = array();
foreach ( $product_ids as $product_id ) {
	if ( has_term( 'variable', 'product_type', $product_id ) ) {
		$variable_products[] = array( 'id' => $product_id, 'title' => get_the_title( $product_id ), 'variations' => count( get_children( array( 'post_parent' => $product_id, 'post_type' => 'product_variation', 'fields' => 'ids' ) ) ) );
	}
}

$report = array(
	'generated_at'       => gmdate( 'c' ),
	'total_products'     => count( $product_ids ),
	'status_counts'      => $status_counts,
	'coverage'           => $coverage,
	'category_usage'     => $category_usage,
	'quality'            => $quality,
	'attribute_coverage' => $attribute_coverage,
	'global_attributes'  => $attributes,
	'product_meta_usage' => $meta_rows,
	'variation_count'    => (int) wp_count_posts( 'product_variation' )->publish,
	'duplicate_titles'   => $duplicate_titles,
	'duplicate_skus'     => $duplicate_skus,
	'variable_products'  => $variable_products,
);

if ( getenv( 'GMR_AUDIT_COMPACT' ) ) {
	unset( $report['global_attributes'], $report['product_meta_usage'] );
}

echo wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . PHP_EOL;
