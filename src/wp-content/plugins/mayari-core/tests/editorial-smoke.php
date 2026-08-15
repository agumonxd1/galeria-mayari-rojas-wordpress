<?php
defined( 'ABSPATH' ) || exit;
echo wp_json_encode( array(
	'core_version'       => GMR_CORE_VERSION,
	'event_type'         => post_type_exists( 'gmr_event' ),
	'media_type'         => post_type_exists( 'gmr_media_gallery' ),
	'event_taxonomy'     => taxonomy_exists( 'gmr_event_type' ),
	'media_taxonomy'     => taxonomy_exists( 'gmr_media_topic' ),
	'event_start_meta'   => registered_meta_key_exists( 'post', 'gmr_event_start', 'gmr_event' ),
	'media_ids_meta'     => registered_meta_key_exists( 'post', 'gmr_media_ids', 'gmr_media_gallery' ),
	'event_save_hook'    => false !== has_action( 'save_post_gmr_event', array( GMR_Core_Admin_Editorial::class, 'save_event' ) ),
	'media_save_hook'    => false !== has_action( 'save_post_gmr_media_gallery', array( GMR_Core_Admin_Editorial::class, 'save_media' ) ),
) ) . "\n";
