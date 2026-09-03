<?php
/**
 * Shared visual language for the WordPress administration area.
 *
 * @package MayariCore
 */

defined( 'ABSPATH' ) || exit;

final class GMR_Core_Admin_Design {

	public static function register_hooks(): void {
		if ( ! is_admin() ) {
			return;
		}

		add_filter( 'admin_body_class', array( self::class, 'body_class' ) );
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue_assets' ), 1 );
		add_action( 'admin_bar_menu', array( self::class, 'rename_site_node' ), 80 );
		add_filter( 'admin_footer_text', array( self::class, 'footer_text' ) );
	}

	public static function body_class( string $classes ): string {
		return trim( $classes . ' gmr-admin' );
	}

	public static function enqueue_assets(): void {
		wp_enqueue_style(
			'gmr-admin-design',
			plugins_url( 'assets/admin-design.css', GMR_CORE_FILE ),
			array(),
			GMR_CORE_VERSION
		);

		$palette = array(
			'ink'      => self::theme_color( 'gmr_color_ink', '#191815' ),
			'charcoal' => self::theme_color( 'gmr_color_charcoal', '#1b1916' ),
			'paper'    => self::theme_color( 'gmr_color_paper', '#f3efe7' ),
			'canvas'   => self::theme_color( 'gmr_color_canvas', '#faf8f3' ),
			'accent'   => self::theme_color( 'gmr_color_accent', '#a67b4f' ),
		);

		$heading = self::theme_font( 'gmr_font_headings', 'Cormorant Garamond' );
		$body    = self::theme_font( 'gmr_font_body', 'Inter' );
		$css     = sprintf(
			'body.gmr-admin{--gmr-admin-ink:%1$s;--gmr-admin-charcoal:%2$s;--gmr-admin-paper:%3$s;--gmr-admin-canvas:%4$s;--gmr-admin-accent:%5$s;--gmr-admin-serif:"%6$s",Georgia,serif;--gmr-admin-sans:"%7$s","Segoe UI",Arial,sans-serif}',
			esc_attr( $palette['ink'] ),
			esc_attr( $palette['charcoal'] ),
			esc_attr( $palette['paper'] ),
			esc_attr( $palette['canvas'] ),
			esc_attr( $palette['accent'] ),
			esc_attr( $heading ),
			esc_attr( $body )
		);
		wp_add_inline_style( 'gmr-admin-design', $css );
	}

	public static function rename_site_node( WP_Admin_Bar $admin_bar ): void {
		$node = $admin_bar->get_node( 'site-name' );
		if ( ! $node ) {
			return;
		}

		$node->title = esc_html( get_bloginfo( 'name' ) );
		$admin_bar->add_node( (array) $node );
	}

	public static function footer_text(): string {
		return sprintf(
			/* translators: %s: site name. */
			esc_html__( 'Administración editorial de %s', 'mayari-core' ),
			esc_html( get_bloginfo( 'name' ) )
		);
	}

	private static function theme_color( string $key, string $fallback ): string {
		$value = get_theme_mod( $key, $fallback );
		return sanitize_hex_color( $value ) ?: $fallback;
	}

	private static function theme_font( string $key, string $fallback ): string {
		$allowed = array( 'Cormorant Garamond', 'Playfair Display', 'Libre Baskerville', 'Inter', 'Manrope', 'Source Sans 3' );
		$value   = sanitize_text_field( (string) get_theme_mod( $key, $fallback ) );
		return in_array( $value, $allowed, true ) ? $value : $fallback;
	}
}
