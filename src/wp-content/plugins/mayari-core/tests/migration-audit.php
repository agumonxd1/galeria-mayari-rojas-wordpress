<?php
/** Post-migration read-only audit. @package MayariCore */
defined( 'ABSPATH' ) || exit( 1 );

$ids = get_posts( array( 'post_type' => 'product', 'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future' ), 'posts_per_page' => -1, 'fields' => 'ids' ) );
$counts = array_fill_keys( array( 'products', 'migrated', 'artist', 'anonymous', 'discipline', 'technique', 'year_or_undated', 'image', 'sku', 'price', 'legacy_artist_categories', 'legacy_collection_categories' ), 0 );
$counts['products'] = count( $ids );
$artist_slugs = GMR_Core_Migration_Preview::legacy_artist_slugs();
$collection_slugs = GMR_Core_Migration_Preview::legacy_collection_slugs();
foreach ( $ids as $id ) {
	$counts['migrated'] += 'catalog-v1' === get_post_meta( $id, 'gmr_migration_run', true ) ? 1 : 0;
	$counts['artist'] += has_term( '', 'gmr_artist', $id ) ? 1 : 0;
	$counts['anonymous'] += has_term( 'anonimo', 'gmr_artist', $id ) ? 1 : 0;
	$counts['discipline'] += (bool) array_intersect( wp_get_post_terms( $id, 'product_cat', array( 'fields' => 'slugs' ) ), array( 'pintura', 'escultura', 'obra-grafica', 'joyeria', 'sin-disciplina' ) ) ? 1 : 0;
	$counts['technique'] += has_term( '', 'gmr_technique', $id ) ? 1 : 0;
	$counts['year_or_undated'] += metadata_exists( 'post', $id, 'gmr_year_start' ) || (bool) get_post_meta( $id, 'gmr_undated', true ) ? 1 : 0;
	$counts['image'] += has_post_thumbnail( $id ) ? 1 : 0;
	$counts['sku'] += '' !== (string) get_post_meta( $id, '_sku', true ) ? 1 : 0;
	$counts['price'] += '' !== (string) get_post_meta( $id, '_price', true ) ? 1 : 0;
	$categories = wp_get_post_terms( $id, 'product_cat', array( 'fields' => 'slugs' ) );
	$counts['legacy_artist_categories'] += (bool) array_intersect( $categories, $artist_slugs ) ? 1 : 0;
	$counts['legacy_collection_categories'] += (bool) array_intersect( $categories, $collection_slugs ) ? 1 : 0;
}
$counts['variable_products'] = count(
	get_posts(
		array(
			'post_type'      => 'product',
			'post_status'    => array( 'publish', 'draft' ),
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'tax_query'      => array( array( 'taxonomy' => 'product_type', 'field' => 'slug', 'terms' => 'variable' ) ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		)
	)
);
$counts['variations'] = (int) wp_count_posts( 'product_variation' )->publish;
$checks = array(
	'all_migrated' => 131 === $counts['migrated'],
	'all_disciplined' => 131 === $counts['discipline'],
	'all_technique_resolved' => 131 === $counts['technique'],
	'all_year_resolved' => 131 === $counts['year_or_undated'],
	'all_have_image' => 131 === $counts['image'],
	'all_have_sku' => 131 === $counts['sku'],
	'artist_rule' => 130 === $counts['artist'] && 13 === $counts['anonymous'],
	'legacy_categories_removed' => 0 === $counts['legacy_artist_categories'] && 0 === $counts['legacy_collection_categories'],
	'variables_preserved' => 8 === $counts['variable_products'] && 28 === $counts['variations'],
);
echo wp_json_encode( array( 'ok' => ! in_array( false, $checks, true ), 'counts' => $counts, 'checks' => $checks ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) . PHP_EOL;
if ( in_array( false, $checks, true ) ) throw new RuntimeException( 'Post-migration audit failed.' );
