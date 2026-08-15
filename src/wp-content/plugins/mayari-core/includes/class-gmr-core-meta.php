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

