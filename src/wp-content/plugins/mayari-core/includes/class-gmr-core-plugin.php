<?php
/**
 * Main plugin coordinator.
 *
 * @package MayariCore
 */

defined( 'ABSPATH' ) || exit;

final class GMR_Core_Plugin {

	private static ?self $instance = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'boot' ) );
	}

	public function boot(): void {
		load_plugin_textdomain( 'mayari-core', false, dirname( plugin_basename( GMR_CORE_FILE ) ) . '/languages' );

		GMR_Core_Content::register_hooks();
		GMR_Core_Meta::register_hooks();
		GMR_Core_Access::register_hooks();

		if ( class_exists( 'WooCommerce' ) ) {
			GMR_Core_Catalog::register_hooks();
		} else {
			add_action( 'admin_notices', array( $this, 'woocommerce_notice' ) );
		}
	}

	public function woocommerce_notice(): void {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			esc_html__( 'Mayari Core requiere WooCommerce activo para gestionar las obras del catalogo.', 'mayari-core' )
		);
	}

	public static function deactivate(): void {
		flush_rewrite_rules();
	}
}

