<?php
/**
 * WooCommerce catalog mode.
 *
 * @package MayariCore
 */

defined( 'ABSPATH' ) || exit;

final class GMR_Core_Catalog {

	public static function register_hooks(): void {
		add_filter( 'woocommerce_is_purchasable', '__return_false', 99 );
		add_filter( 'woocommerce_variation_is_purchasable', '__return_false', 99 );
		add_filter( 'woocommerce_get_price_html', array( self::class, 'filter_price_html' ), 99, 2 );
		add_filter( 'woocommerce_loop_add_to_cart_link', '__return_empty_string', 99 );
		add_action( 'wp', array( self::class, 'remove_purchase_ui' ) );
		add_action( 'template_redirect', array( self::class, 'redirect_commerce_pages' ), 5 );
		add_filter( 'woocommerce_structured_data_product', array( self::class, 'filter_structured_data' ), 99, 2 );
	}

	public static function filter_price_html( string $html, WC_Product $product ): string {
		if ( GMR_Core_Access::can_view_price( $product->get_id() ) ) {
			return $html;
		}

		$label = get_post_meta( $product->get_id(), 'gmr_price_label', true );
		return $label ? esc_html( $label ) : esc_html__( 'Consultar', 'mayari-core' );
	}

	public static function remove_purchase_ui(): void {
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
	}

	public static function redirect_commerce_pages(): void {
		if ( is_admin() || wp_doing_ajax() ) {
			return;
		}

		if ( is_cart() || is_checkout() ) {
			wp_safe_redirect( home_url( '/catalogo/' ), 302 );
			exit;
		}
	}

	public static function filter_structured_data( array $markup, WC_Product $product ): array {
		if ( ! GMR_Core_Access::can_view_price( $product->get_id() ) ) {
			unset( $markup['offers'], $markup['price'], $markup['lowPrice'], $markup['highPrice'], $markup['priceCurrency'] );
		}
		return $markup;
	}
}
