<?php
defined( 'ABSPATH' ) || exit;
if ( 'cli' !== PHP_SAPI ) exit( "CLI only\n" );
$category = get_term_by( 'slug', 'opinion-y-critica', 'category' );
if ( ! $category ) exit( "Category not found\n" );
$ids = get_posts( array( 'post_type' => array( 'post', 'gmr_tribute' ), 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'category' => $category->term_id ) );
$migrated = 0;
foreach ( $ids as $id ) {
	$post = get_post( $id );
	if ( ! $post ) continue;
	if ( 'gmr_tribute' !== $post->post_type ) wp_update_post( array( 'ID' => $id, 'post_type' => 'gmr_tribute' ) );
	$author = preg_replace( '/^Por\s+/u', '', get_the_title( $id ) );
	if ( ! get_post_meta( $id, 'gmr_tribute_author', true ) ) update_post_meta( $id, 'gmr_tribute_author', $author );
	if ( ! metadata_exists( 'post', $id, 'gmr_visibility' ) ) update_post_meta( $id, 'gmr_visibility', 'public' );
	update_post_meta( $id, 'gmr_legacy_category', 'opinion-y-critica' );
	$migrated++;
}
$news = get_page_by_path( 'noticias' );
if ( $news && 'draft' !== $news->post_status ) wp_update_post( array( 'ID' => $news->ID, 'post_status' => 'draft' ) );
echo wp_json_encode( array( 'voices' => $migrated, 'news_page' => $news ? 'draft' : 'missing' ) ) . "\n";
