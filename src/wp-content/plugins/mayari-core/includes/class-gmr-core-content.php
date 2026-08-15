<?php
/**
 * Content types and taxonomies.
 *
 * @package MayariCore
 */

defined( 'ABSPATH' ) || exit;

final class GMR_Core_Content {

	public static function register_hooks(): void {
		add_action( 'init', array( self::class, 'register_content' ), 5 );
	}

	public static function register_content(): void {
		self::register_artist_taxonomy();
		self::register_collection_taxonomy();
		self::register_artwork_taxonomies();
		self::register_event_type();
		self::register_media_gallery_type();
	}

	private static function register_artist_taxonomy(): void {
		register_taxonomy(
			'gmr_artist',
			array( 'product', 'post', 'gmr_event', 'gmr_media_gallery' ),
			array(
				'labels'            => self::taxonomy_labels( 'Artista', 'Artistas' ),
				'public'            => true,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'hierarchical'      => false,
				'capabilities'      => self::taxonomy_capabilities( 'gmr_manage_artists', 'gmr_manage_artworks' ),
				'rewrite'           => array( 'slug' => 'artistas', 'with_front' => false ),
			),
		);
	}

	private static function register_collection_taxonomy(): void {
		register_taxonomy(
			'gmr_collection',
			array( 'product', 'post', 'gmr_event', 'gmr_media_gallery' ),
			array(
				'labels'            => self::taxonomy_labels( 'Coleccion', 'Colecciones' ),
				'public'            => true,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'hierarchical'      => false,
				'capabilities'      => self::taxonomy_capabilities( 'gmr_manage_collections', 'gmr_manage_artworks' ),
				'rewrite'           => array( 'slug' => 'colecciones', 'with_front' => false ),
			),
		);
	}

	private static function register_artwork_taxonomies(): void {
		$taxonomies = array(
			'gmr_technique' => array( 'Tecnica', 'Tecnicas' ),
			'gmr_support'   => array( 'Soporte', 'Soportes' ),
			'gmr_material'  => array( 'Material', 'Materiales' ),
		);

		foreach ( $taxonomies as $taxonomy => $names ) {
			register_taxonomy(
				$taxonomy,
				array( 'product' ),
				array(
					'labels'            => self::taxonomy_labels( $names[0], $names[1] ),
					'public'            => false,
					'publicly_queryable'=> false,
					'show_ui'           => true,
					'show_admin_column' => true,
					'show_in_rest'      => true,
					'hierarchical'      => false,
					'capabilities'      => self::taxonomy_capabilities( 'gmr_manage_artworks', 'gmr_manage_artworks' ),
				),
			);
		}
	}

	private static function register_event_type(): void {
		register_post_type(
			'gmr_event',
			array(
				'labels'       => self::post_type_labels( 'Evento', 'Agenda' ),
				'public'       => true,
				'show_in_rest' => true,
				'has_archive'  => 'agenda',
				'rewrite'      => array( 'slug' => 'agenda', 'with_front' => false ),
				'menu_icon'    => 'dashicons-calendar-alt',
				'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields' ),
			),
		);

		register_taxonomy(
			'gmr_event_type',
			array( 'gmr_event' ),
			array(
				'labels'       => self::taxonomy_labels( 'Tipo de agenda', 'Tipos de agenda' ),
				'public'       => true,
				'show_in_rest' => true,
				'hierarchical' => false,
			),
		);
	}

	private static function register_media_gallery_type(): void {
		register_post_type(
			'gmr_media_gallery',
			array(
				'labels'       => self::post_type_labels( 'Galeria multimedia', 'Archivo multimedia' ),
				'public'       => true,
				'show_in_rest' => true,
				'has_archive'  => 'archivo-multimedia',
				'rewrite'      => array( 'slug' => 'archivo-multimedia', 'with_front' => false ),
				'menu_icon'    => 'dashicons-format-gallery',
				'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields' ),
			),
		);

		register_taxonomy(
			'gmr_media_topic',
			array( 'gmr_media_gallery' ),
			array(
				'labels'       => self::taxonomy_labels( 'Tema multimedia', 'Temas multimedia' ),
				'public'       => true,
				'show_in_rest' => true,
				'hierarchical' => true,
				'rewrite'      => array( 'slug' => 'archivo-multimedia/tema', 'with_front' => false ),
			)
		);
	}

	private static function taxonomy_labels( string $singular, string $plural ): array {
		return array(
			'name'          => $plural,
			'singular_name' => $singular,
			'search_items'  => sprintf( 'Buscar %s', strtolower( $plural ) ),
			'all_items'     => sprintf( 'Todos los %s', strtolower( $plural ) ),
			'edit_item'     => sprintf( 'Editar %s', strtolower( $singular ) ),
			'update_item'   => sprintf( 'Actualizar %s', strtolower( $singular ) ),
			'add_new_item'  => sprintf( 'Añadir %s', strtolower( $singular ) ),
			'menu_name'     => $plural,
		);
	}

	private static function taxonomy_capabilities( string $manage, string $assign ): array {
		return array(
			'manage_terms' => $manage,
			'edit_terms'   => $manage,
			'delete_terms' => $manage,
			'assign_terms' => $assign,
		);
	}

	private static function post_type_labels( string $singular, string $plural ): array {
		return array(
			'name'          => $plural,
			'singular_name' => $singular,
			'add_new_item'  => sprintf( 'Añadir %s', strtolower( $singular ) ),
			'edit_item'     => sprintf( 'Editar %s', strtolower( $singular ) ),
			'new_item'      => sprintf( 'Nuevo %s', strtolower( $singular ) ),
			'view_item'     => sprintf( 'Ver %s', strtolower( $singular ) ),
			'search_items'  => sprintf( 'Buscar en %s', strtolower( $plural ) ),
			'menu_name'     => $plural,
		);
	}
}
