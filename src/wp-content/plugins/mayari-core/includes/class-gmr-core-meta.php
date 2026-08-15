<?php
/**
 * Registered metadata schemas.
 *
 * @package MayariCore
 */

defined( 'ABSPATH' ) || exit;

final class GMR_Core_Meta {

	public static function register_hooks(): void {
		add_action( 'init', array( self::class, 'register_meta' ), 20 );
	}

	public static function register_meta(): void {
		$text_fields = array(
			'gmr_history',
			'gmr_internal_notes',
			'gmr_technique_notes',
			'gmr_dimensions_notes',
			'gmr_edition_number',
			'gmr_signature_location',
			'gmr_provenance',
			'gmr_condition_notes',
			'gmr_physical_location',
			'gmr_consignor',
			'gmr_price_label',
			'gmr_legacy_id',
		);

		foreach ( $text_fields as $key ) {
			self::register_product_meta( $key, 'string', 'sanitize_textarea_field' );
		}

		$number_fields = array(
			'gmr_year_start'   => 'integer',
			'gmr_year_end'     => 'integer',
			'gmr_height'       => 'number',
			'gmr_width'        => 'number',
			'gmr_depth'        => 'number',
			'gmr_diameter'     => 'number',
			'gmr_weight'       => 'number',
			'gmr_edition_size' => 'integer',
		);

		foreach ( $number_fields as $key => $type ) {
			self::register_product_meta( $key, $type, array( self::class, 'sanitize_number' ) );
		}

		$boolean_fields = array(
			'gmr_undated',
			'gmr_dimensions_variable',
			'gmr_unique_piece',
			'gmr_price_negotiable',
			'gmr_featured',
		);

		foreach ( $boolean_fields as $key ) {
			self::register_product_meta( $key, 'boolean', 'rest_sanitize_boolean' );
		}

		$enum_fields = array(
			'gmr_dimension_unit'     => array( 'cm' ),
			'gmr_weight_unit'        => array( 'kg', 'g' ),
			'gmr_signature_status'   => array( 'signed', 'unsigned', 'attributed', 'unknown' ),
			'gmr_certificate_status' => array( 'included', 'available', 'not_available', 'unknown' ),
			'gmr_commercial_status'  => array( 'available', 'reserved', 'sold', 'not_available', 'on_exhibition', 'archive' ),
			'gmr_condition'          => array( 'excellent', 'good', 'restored', 'review', 'unknown' ),
			'gmr_price_visibility'   => array( 'admins', 'collectors', 'public' ),
			'gmr_visibility'         => array( 'public', 'collectors', 'hidden' ),
		);

		foreach ( $enum_fields as $key => $allowed ) {
			register_post_meta(
				'product',
				$key,
				array(
					'type'              => 'string',
					'single'            => true,
					'default'           => self::default_for( $key ),
					'show_in_rest'      => false,
					'sanitize_callback' => static fn( $value ) => in_array( $value, $allowed, true ) ? $value : self::default_for( $key ),
					'auth_callback'     => array( self::class, 'can_edit_product_meta' ),
				)
			);
		}

		self::register_shared_visibility( 'gmr_event' );
		self::register_shared_visibility( 'gmr_media_gallery' );
		self::register_shared_visibility( 'gmr_tribute' );
		self::register_editorial_meta();
		self::register_term_meta();
	}

	private static function register_editorial_meta(): void {
		$event_fields = array(
			'gmr_event_start'        => 'sanitize_text_field',
			'gmr_event_end'          => 'sanitize_text_field',
			'gmr_event_venue'        => 'sanitize_text_field',
			'gmr_event_address'      => 'sanitize_textarea_field',
			'gmr_event_modality'     => 'sanitize_key',
			'gmr_event_status'       => 'sanitize_key',
			'gmr_event_registration' => 'esc_url_raw',
		);
		$media_fields = array(
			'gmr_media_date_label' => 'sanitize_text_field',
			'gmr_media_credits'    => 'sanitize_textarea_field',
			'gmr_media_ids'        => array( self::class, 'sanitize_attachment_ids' ),
		);
		$tribute_fields = array(
			'gmr_tribute_author' => 'sanitize_text_field',
			'gmr_tribute_role'   => 'sanitize_text_field',
			'gmr_tribute_source' => 'sanitize_text_field',
			'gmr_tribute_date'   => 'sanitize_text_field',
		);
		foreach ( $event_fields as $key => $sanitize ) self::register_editorial_field( 'gmr_event', $key, $sanitize );
		foreach ( $media_fields as $key => $sanitize ) self::register_editorial_field( 'gmr_media_gallery', $key, $sanitize );
		foreach ( $tribute_fields as $key => $sanitize ) self::register_editorial_field( 'gmr_tribute', $key, $sanitize );
		register_post_meta( 'gmr_tribute', 'gmr_tribute_featured', array( 'type' => 'boolean', 'single' => true, 'show_in_rest' => false, 'sanitize_callback' => 'rest_sanitize_boolean', 'auth_callback' => static fn() => current_user_can( 'edit_posts' ) ) );
		register_post_meta( 'gmr_event', 'gmr_event_all_day', array( 'type' => 'boolean', 'single' => true, 'show_in_rest' => false, 'sanitize_callback' => 'rest_sanitize_boolean', 'auth_callback' => static fn() => current_user_can( 'edit_posts' ) ) );
	}

	private static function register_editorial_field( string $post_type, string $key, callable|string $sanitize ): void {
		register_post_meta( $post_type, $key, array( 'type' => 'string', 'single' => true, 'show_in_rest' => false, 'sanitize_callback' => $sanitize, 'auth_callback' => static fn() => current_user_can( 'edit_posts' ) ) );
	}

	public static function sanitize_attachment_ids( mixed $value ): string {
		return implode( ',', array_values( array_unique( array_filter( array_map( 'absint', explode( ',', (string) $value ) ) ) ) ) );
	}

	private static function register_term_meta(): void {
		$artist = array(
			'gmr_artist_biography'        => array( 'string', 'wp_kses_post' ),
			'gmr_artist_history'          => array( 'string', 'wp_kses_post' ),
			'gmr_artist_chronology'       => array( 'string', 'wp_kses_post' ),
			'gmr_artist_awards'           => array( 'string', 'wp_kses_post' ),
			'gmr_artist_document_ids'     => array( 'string', array( self::class, 'sanitize_attachment_ids' ) ),
			'gmr_artist_media_ids'        => array( 'string', array( self::class, 'sanitize_attachment_ids' ) ),
			'gmr_artist_portrait_id'      => array( 'integer', 'absint' ),
			'gmr_artist_cover_id'         => array( 'integer', 'absint' ),
			'gmr_artist_featured'         => array( 'boolean', 'rest_sanitize_boolean' ),
			'gmr_artist_special_template'=> array( 'string', array( self::class, 'sanitize_artist_template' ) ),
			'gmr_artist_order'            => array( 'integer', 'intval' ),
		);

		$collection = array(
			'gmr_collection_subtitle'   => array( 'string', 'sanitize_text_field' ),
			'gmr_collection_year_start' => array( 'integer', 'absint' ),
			'gmr_collection_year_end'   => array( 'integer', 'absint' ),
			'gmr_collection_text'       => array( 'string', 'wp_kses_post' ),
			'gmr_collection_cover_id'   => array( 'integer', 'absint' ),
			'gmr_collection_artists'    => array( 'array', array( self::class, 'sanitize_term_ids' ) ),
			'gmr_visibility'            => array( 'string', array( self::class, 'sanitize_visibility' ) ),
			'gmr_collection_order'      => array( 'integer', 'intval' ),
		);

		self::register_taxonomy_meta_group( 'gmr_artist', $artist, 'gmr_manage_artists' );
		self::register_taxonomy_meta_group( 'gmr_collection', $collection, 'gmr_manage_collections' );
	}

	private static function register_taxonomy_meta_group( string $taxonomy, array $fields, string $capability ): void {
		foreach ( $fields as $key => $definition ) {
			$sanitize = $definition[1];
			register_term_meta( $taxonomy, $key, array(
				'type'              => $definition[0],
				'single'            => true,
				'show_in_rest'      => false,
				'sanitize_callback' => static fn( $value ) => call_user_func( $sanitize, $value ),
				'auth_callback'     => static fn() => current_user_can( $capability ),
			) );
		}
	}

	public static function sanitize_artist_template( mixed $value ): string {
		return 'elmar' === $value ? 'elmar' : '';
	}

	public static function sanitize_visibility( mixed $value ): string {
		return in_array( $value, array( 'public', 'collectors', 'hidden' ), true ) ? $value : 'public';
	}

	public static function sanitize_term_ids( mixed $value ): array {
		return array_values( array_unique( array_filter( array_map( 'absint', (array) $value ) ) ) );
	}

	private static function register_product_meta( string $key, string $type, callable|string $sanitize ): void {
		register_post_meta(
			'product',
			$key,
			array(
				'type'              => $type,
				'single'            => true,
				'show_in_rest'      => false,
				'sanitize_callback' => $sanitize,
				'auth_callback'     => array( self::class, 'can_edit_product_meta' ),
			)
		);
	}

	private static function register_shared_visibility( string $post_type ): void {
		register_post_meta(
			$post_type,
			'gmr_visibility',
			array(
				'type'              => 'string',
				'single'            => true,
				'default'           => 'public',
				'show_in_rest'      => false,
				'sanitize_callback' => static fn( $value ) => in_array( $value, array( 'public', 'collectors', 'hidden' ), true ) ? $value : 'public',
				'auth_callback'     => static fn() => current_user_can( 'edit_posts' ),
			)
		);
	}

	private static function default_for( string $key ): string {
		$defaults = array(
			'gmr_visibility'       => 'public',
			'gmr_price_visibility' => 'collectors',
			'gmr_commercial_status'=> 'available',
		);

		return $defaults[ $key ] ?? '';
	}

	public static function sanitize_number( mixed $value ): int|float|string {
		if ( '' === $value || null === $value ) {
			return '';
		}

		return is_numeric( $value ) ? 0 + $value : '';
	}

	public static function can_edit_product_meta(): bool {
		return current_user_can( 'gmr_manage_artworks' ) || current_user_can( 'manage_woocommerce' );
	}
}
