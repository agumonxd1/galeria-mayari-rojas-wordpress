<?php
/**
 * Visibility helpers and frontend protection.
 *
 * @package MayariCore
 */

defined( 'ABSPATH' ) || exit;

final class GMR_Core_Access {

	public static function register_hooks(): void {
		add_action( 'template_redirect', array( self::class, 'protect_singular_content' ), 1 );
		add_action( 'template_redirect', array( self::class, 'protect_taxonomy_content' ), 1 );
		add_action( 'pre_get_posts', array( self::class, 'filter_public_queries' ), 20 );
		foreach ( array( 'gmr_event', 'gmr_media_gallery', 'gmr_tribute' ) as $post_type ) add_filter( 'rest_' . $post_type . '_query', array( self::class, 'filter_rest_query' ) );
		add_filter( 'wp_sitemaps_posts_query_args', array( self::class, 'filter_sitemap_posts' ), 10, 2 );
		add_filter( 'wp_sitemaps_taxonomies_query_args', array( self::class, 'filter_sitemap_terms' ), 10, 2 );
		add_filter( 'wp_sitemaps_add_provider', array( self::class, 'remove_user_sitemap' ), 10, 2 );
	}

	public static function allowed_visibilities(): array {
		if ( current_user_can( 'gmr_manage_artworks' ) ) return array( 'public', 'collectors', 'hidden' );
		return current_user_can( 'gmr_view_collector_catalog' ) ? array( 'public', 'collectors' ) : array( 'public' );
	}

	public static function visibility_meta_query(): array {
		return array( 'relation'=>'OR', array( 'key'=>'gmr_visibility', 'value'=>self::allowed_visibilities(), 'compare'=>'IN' ), array( 'key'=>'gmr_visibility', 'compare'=>'NOT EXISTS' ) );
	}

	public static function can_view_term( int $term_id ): bool {
		$visibility = get_term_meta( $term_id, 'gmr_visibility', true ) ?: 'public';
		return in_array( $visibility, self::allowed_visibilities(), true );
	}

	public static function can_view( int $post_id ): bool {
		$visibility = get_post_meta( $post_id, 'gmr_visibility', true ) ?: 'public';

		if ( current_user_can( 'gmr_manage_artworks' ) || current_user_can( 'edit_post', $post_id ) ) {
			return true;
		}

		if ( 'public' === $visibility ) {
			return true;
		}

		return 'collectors' === $visibility && current_user_can( 'gmr_view_collector_catalog' );
	}

	public static function can_view_price( int $product_id ): bool {
		if ( current_user_can( 'gmr_manage_artworks' ) || current_user_can( 'manage_woocommerce' ) ) {
			return true;
		}

		$visibility = get_post_meta( $product_id, 'gmr_price_visibility', true ) ?: 'collectors';

		if ( 'public' === $visibility ) {
			return self::can_view( $product_id );
		}

		return 'collectors' === $visibility && current_user_can( 'gmr_view_private_prices' );
	}

	public static function protect_singular_content(): void {
		if ( ! is_singular( array( 'product', 'gmr_event', 'gmr_media_gallery', 'gmr_tribute' ) ) ) {
			return;
		}

		$post_id = get_queried_object_id();
		if ( ! $post_id || self::can_view( $post_id ) ) {
			return;
		}

		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}

	public static function protect_taxonomy_content(): void {
		if ( ! is_tax( 'gmr_collection' ) ) return;
		$term = get_queried_object();
		if ( $term instanceof WP_Term && ! self::can_view_term( $term->term_id ) ) self::make_404();
	}

	private static function make_404(): void {
		global $wp_query; $wp_query->set_404(); status_header( 404 ); nocache_headers();
	}

	public static function filter_public_queries( WP_Query $query ): void {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		$post_type = $query->get( 'post_type' );
		$is_protected_archive = $query->is_search() || is_post_type_archive( array( 'product', 'gmr_event', 'gmr_media_gallery', 'gmr_tribute' ) ) || is_tax( array( 'product_cat', 'gmr_artist', 'gmr_collection' ) );
		if ( ! $is_protected_archive && ! in_array( $post_type, array( 'product', 'gmr_event', 'gmr_media_gallery', 'gmr_tribute' ), true ) ) {
			return;
		}

		$meta_query = (array) $query->get( 'meta_query', array() );
		$meta_query[] = self::visibility_meta_query();

		$query->set( 'meta_query', $meta_query );
	}

	public static function filter_rest_query( array $args ): array {
		$args['meta_query'] = isset( $args['meta_query'] ) && is_array( $args['meta_query'] ) ? $args['meta_query'] : array();
		$args['meta_query'][] = self::visibility_meta_query(); return $args;
	}

	public static function filter_sitemap_posts( array $args, string $post_type ): array {
		if ( in_array( $post_type, array( 'product','gmr_event','gmr_media_gallery','gmr_tribute' ), true ) ) {
			$args['meta_query'] = isset( $args['meta_query'] ) ? (array) $args['meta_query'] : array(); $args['meta_query'][] = self::visibility_meta_query();
		}
		return $args;
	}

	public static function filter_sitemap_terms( array $args, string $taxonomy ): array {
		if ( 'gmr_collection' === $taxonomy ) { $args['meta_query'] = isset( $args['meta_query'] ) ? (array) $args['meta_query'] : array(); $args['meta_query'][] = self::visibility_meta_query(); }
		return $args;
	}

	public static function remove_user_sitemap( mixed $provider, string $name ): mixed { return 'users' === $name ? false : $provider; }
}
