<?php
/**
 * Roles and capabilities.
 *
 * @package MayariCore
 */

defined( 'ABSPATH' ) || exit;

final class GMR_Core_Capabilities {

	private const READ_CAPS = array(
		'gmr_view_collector_area',
		'gmr_view_collector_catalog',
		'gmr_view_private_prices',
		'gmr_download_collector_documents',
	);

	private const MANAGE_CAPS = array(
		'gmr_manage_artworks',
		'gmr_manage_artists',
		'gmr_manage_collections',
		'gmr_manage_agenda',
		'gmr_manage_media_galleries',
		'gmr_manage_collectors',
		'gmr_export_catalog',
	);

	public static function activate(): void {
		add_role(
			'gmr_collector',
			__( 'Coleccionista', 'mayari-core' ),
			array_merge(
				array( 'read' => true ),
				array_fill_keys( self::READ_CAPS, true )
			)
		);

		$manager_caps = array_merge(
			array(
				'read'                   => true,
				'upload_files'           => true,
				'edit_posts'             => true,
				'edit_others_posts'      => true,
				'edit_published_posts'   => true,
				'publish_posts'          => true,
				'delete_posts'           => true,
				'delete_published_posts' => true,
				'edit_products'          => true,
				'edit_others_products'   => true,
				'publish_products'       => true,
				'read_private_products'  => true,
			),
			array_fill_keys( array_merge( self::READ_CAPS, self::MANAGE_CAPS ), true )
		);

		add_role( 'gmr_gallery_manager', __( 'Gestor de galeria', 'mayari-core' ), $manager_caps );
		$manager = get_role( 'gmr_gallery_manager' );
		if ( $manager ) {
			foreach ( $manager_caps as $capability => $grant ) {
				$manager->add_cap( $capability, $grant );
			}
		}

		$administrator = get_role( 'administrator' );
		if ( $administrator ) {
			foreach ( array_merge( self::READ_CAPS, self::MANAGE_CAPS ) as $capability ) {
				$administrator->add_cap( $capability );
			}
		}

		GMR_Core_Content::register_content();
		update_option( 'gmr_core_version', GMR_CORE_VERSION );
		flush_rewrite_rules();
	}

	public static function maybe_upgrade(): void {
		if ( GMR_CORE_VERSION !== get_option( 'gmr_core_version' ) ) {
			self::activate();
		}
	}
}
