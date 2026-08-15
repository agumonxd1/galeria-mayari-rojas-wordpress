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
		add_action( 'pre_get_posts', array( self::class, 'filter_public_queries' ), 20 );
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
		if ( ! is_singular( array( 'product', 'gmr_event', 'gmr_media_gallery' ) ) ) {
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

	public static function filter_public_queries( WP_Query $query ): void {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		$post_type = $query->get( 'post_type' );
		$is_protected_archive = $query->is_search() || is_post_type_archive( array( 'product', 'gmr_event', 'gmr_media_gallery' ) ) || is_tax( array( 'product_cat', 'gmr_artist', 'gmr_collection' ) );
		if ( ! $is_protected_archive && ! in_array( $post_type, array( 'product', 'gmr_event', 'gmr_media_gallery' ), true ) ) {
			return;
		}

		$allowed = current_user_can( 'gmr_view_collector_catalog' ) ? array( 'public', 'collectors' ) : array( 'public' );
		$meta_query = (array) $query->get( 'meta_query', array() );
		$meta_query[] = array(
			'relation' => 'OR',
			array(
				'key'     => 'gmr_visibility',
				'value'   => $allowed,
				'compare' => 'IN',
			),
			array(
				'key'     => 'gmr_visibility',
				'compare' => 'NOT EXISTS',
			),
		);

		$query->set( 'meta_query', $meta_query );
	}
}

