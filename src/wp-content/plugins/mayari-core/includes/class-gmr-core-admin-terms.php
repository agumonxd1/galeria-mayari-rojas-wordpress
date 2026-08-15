<?php
/**
 * Enriched artist and collection administration.
 *
 * @package MayariCore
 */

defined( 'ABSPATH' ) || exit;

final class GMR_Core_Admin_Terms {

	private const TAXONOMIES = array( 'gmr_artist', 'gmr_collection' );

	public static function register_hooks(): void {
		foreach ( self::TAXONOMIES as $taxonomy ) {
			add_action( "{$taxonomy}_add_form_fields", array( self::class, 'render_add_fields' ) );
			add_action( "{$taxonomy}_edit_form_fields", array( self::class, 'render_edit_fields' ) );
			add_action( "created_{$taxonomy}", array( self::class, 'save_fields' ) );
			add_action( "edited_{$taxonomy}", array( self::class, 'save_fields' ) );
			add_filter( "manage_edit-{$taxonomy}_columns", array( self::class, 'columns' ) );
			add_filter( "manage_{$taxonomy}_custom_column", array( self::class, 'column_content' ), 10, 3 );
		}
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue_assets' ) );
	}

	public static function enqueue_assets( string $hook ): void {
		if ( ! in_array( $hook, array( 'edit-tags.php', 'term.php' ), true ) || ! isset( $_GET['taxonomy'] ) ) {
			return;
		}
		$taxonomy = sanitize_key( wp_unslash( $_GET['taxonomy'] ) );
		if ( ! in_array( $taxonomy, self::TAXONOMIES, true ) ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'gmr-admin-terms', plugins_url( 'assets/admin-terms.css', GMR_CORE_FILE ), array(), GMR_CORE_VERSION );
		wp_enqueue_script( 'gmr-admin-terms', plugins_url( 'assets/admin-terms.js', GMR_CORE_FILE ), array( 'jquery' ), GMR_CORE_VERSION, true );
	}

	public static function render_add_fields( string $taxonomy ): void {
		wp_nonce_field( "gmr_save_{$taxonomy}", 'gmr_term_nonce' );
		if ( 'gmr_artist' === $taxonomy ) {
			self::artist_fields( null, false );
		} else {
			self::collection_fields( null, false );
		}
	}

	public static function render_edit_fields( WP_Term $term ): void {
		wp_nonce_field( "gmr_save_{$term->taxonomy}", 'gmr_term_nonce' );
		if ( 'gmr_artist' === $term->taxonomy ) {
			self::artist_fields( $term, true );
		} else {
			self::collection_fields( $term, true );
		}
	}

	private static function artist_fields( ?WP_Term $term, bool $table ): void {
		self::media_field( 'gmr_artist_portrait_id', 'Retrato', $term, $table, 'Imagen vertical para ficha y listados.' );
		self::media_field( 'gmr_artist_cover_id', 'Portada', $term, $table, 'Imagen panoramica para la cabecera del perfil.' );
		self::editor_field( 'gmr_artist_biography', 'Biografia', $term, $table );
		self::editor_field( 'gmr_artist_history', 'Historia y trayectoria', $term, $table );
		self::select_field( 'gmr_artist_special_template', 'Presentacion especial', $term, $table, array( '' => 'Estandar', 'elmar' => 'Elmar Rojas' ) );
		self::checkbox_field( 'gmr_artist_featured', 'Artista destacado', $term, $table );
		self::number_field( 'gmr_artist_order', 'Orden editorial', $term, $table );
	}

	private static function collection_fields( ?WP_Term $term, bool $table ): void {
		self::text_field( 'gmr_collection_subtitle', 'Subtitulo', $term, $table );
		self::number_field( 'gmr_collection_year_start', 'Ano inicial', $term, $table, 1000, 2200 );
		self::number_field( 'gmr_collection_year_end', 'Ano final', $term, $table, 1000, 2200 );
		self::media_field( 'gmr_collection_cover_id', 'Portada', $term, $table, 'Imagen principal de la coleccion.' );
		self::editor_field( 'gmr_collection_text', 'Texto curatorial', $term, $table );
		self::artist_selector( $term, $table );
		self::select_field( 'gmr_visibility', 'Visibilidad', $term, $table, array( 'public' => 'Publica', 'collectors' => 'Solo coleccionistas', 'hidden' => 'Oculta' ), 'public' );
		self::number_field( 'gmr_collection_order', 'Orden editorial', $term, $table );
	}

	private static function field_value( ?WP_Term $term, string $key, mixed $default = '' ): mixed {
		return $term ? get_term_meta( $term->term_id, $key, true ) : $default;
	}

	private static function wrap( string $key, string $label, string $control, bool $table, string $description = '' ): void {
		$description_html = $description ? '<p class="description">' . esc_html( $description ) . '</p>' : '';
		if ( $table ) {
			printf( '<tr class="form-field"><th scope="row"><label for="%1$s">%2$s</label></th><td>%3$s%4$s</td></tr>', esc_attr( $key ), esc_html( $label ), $control, $description_html ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			printf( '<div class="form-field"><label for="%1$s">%2$s</label>%3$s%4$s</div>', esc_attr( $key ), esc_html( $label ), $control, $description_html ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	private static function text_field( string $key, string $label, ?WP_Term $term, bool $table ): void {
		self::wrap( $key, $label, sprintf( '<input type="text" id="%1$s" name="%1$s" value="%2$s">', esc_attr( $key ), esc_attr( self::field_value( $term, $key ) ) ), $table );
	}

	private static function number_field( string $key, string $label, ?WP_Term $term, bool $table, int $min = 0, int $max = 9999 ): void {
		self::wrap( $key, $label, sprintf( '<input type="number" id="%1$s" name="%1$s" value="%2$s" min="%3$d" max="%4$d" step="1">', esc_attr( $key ), esc_attr( self::field_value( $term, $key ) ), $min, $max ), $table );
	}

	private static function editor_field( string $key, string $label, ?WP_Term $term, bool $table ): void {
		$control = sprintf( '<textarea id="%1$s" name="%1$s" rows="8">%2$s</textarea>', esc_attr( $key ), esc_textarea( self::field_value( $term, $key ) ) );
		self::wrap( $key, $label, $control, $table, 'Admite texto enriquecido basico.' );
	}

	private static function checkbox_field( string $key, string $label, ?WP_Term $term, bool $table ): void {
		$control = sprintf( '<label><input type="checkbox" id="%1$s" name="%1$s" value="1" %2$s> Mostrar con prioridad editorial</label>', esc_attr( $key ), checked( (bool) self::field_value( $term, $key ), true, false ) );
		self::wrap( $key, $label, $control, $table );
	}

	private static function select_field( string $key, string $label, ?WP_Term $term, bool $table, array $options, string $default = '' ): void {
		$value = self::field_value( $term, $key, $default );
		$html  = '<select id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '">';
		foreach ( $options as $option => $caption ) {
			$html .= sprintf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $option ), selected( $value, $option, false ), esc_html( $caption ) );
		}
		self::wrap( $key, $label, $html . '</select>', $table );
	}

	private static function media_field( string $key, string $label, ?WP_Term $term, bool $table, string $description ): void {
		$id      = absint( self::field_value( $term, $key ) );
		$preview = $id ? wp_get_attachment_image( $id, 'thumbnail' ) : '';
		$control = sprintf( '<div class="gmr-media-field"><input type="hidden" id="%1$s" name="%1$s" value="%2$d"><div class="gmr-media-preview">%3$s</div><button type="button" class="button gmr-select-media">Seleccionar imagen</button> <button type="button" class="button-link-delete gmr-remove-media"%4$s>Quitar</button></div>', esc_attr( $key ), $id, $preview, $id ? '' : ' hidden' );
		self::wrap( $key, $label, $control, $table, $description );
	}

	private static function artist_selector( ?WP_Term $term, bool $table ): void {
		$selected = array_map( 'absint', (array) self::field_value( $term, 'gmr_collection_artists', array() ) );
		$artists  = get_terms( array( 'taxonomy' => 'gmr_artist', 'hide_empty' => false ) );
		$html     = '<select id="gmr_collection_artists" name="gmr_collection_artists[]" multiple size="6">';
		if ( ! is_wp_error( $artists ) ) {
			foreach ( $artists as $artist ) {
				$html .= sprintf( '<option value="%1$d" %2$s>%3$s</option>', $artist->term_id, selected( in_array( $artist->term_id, $selected, true ), true, false ), esc_html( $artist->name ) );
			}
		}
		self::wrap( 'gmr_collection_artists', 'Artistas relacionados', $html . '</select>', $table, 'Use Ctrl o Cmd para seleccionar varios.' );
	}

	public static function save_fields( int $term_id ): void {
		$term = get_term( $term_id );
		if ( ! $term instanceof WP_Term || ! in_array( $term->taxonomy, self::TAXONOMIES, true ) ) {
			return;
		}
		$capability = 'gmr_artist' === $term->taxonomy ? 'gmr_manage_artists' : 'gmr_manage_collections';
		if ( ! current_user_can( $capability ) || ! isset( $_POST['gmr_term_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gmr_term_nonce'] ) ), "gmr_save_{$term->taxonomy}" ) ) {
			return;
		}
		$fields = 'gmr_artist' === $term->taxonomy
			? array( 'gmr_artist_biography', 'gmr_artist_history', 'gmr_artist_portrait_id', 'gmr_artist_cover_id', 'gmr_artist_featured', 'gmr_artist_special_template', 'gmr_artist_order' )
			: array( 'gmr_collection_subtitle', 'gmr_collection_year_start', 'gmr_collection_year_end', 'gmr_collection_text', 'gmr_collection_cover_id', 'gmr_collection_artists', 'gmr_visibility', 'gmr_collection_order' );
		foreach ( $fields as $key ) {
			$value = $_POST[ $key ] ?? ( 'gmr_artist_featured' === $key ? false : ( 'gmr_collection_artists' === $key ? array() : '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			update_term_meta( $term_id, $key, wp_unslash( $value ) );
		}
	}

	public static function columns( array $columns ): array {
		$columns['gmr_image'] = 'Imagen';
		$columns['gmr_editorial'] = 'Datos editoriales';
		return $columns;
	}

	public static function column_content( string $content, string $column, int $term_id ): string {
		if ( 'gmr_image' === $column ) {
			$term = get_term( $term_id );
			$key  = $term instanceof WP_Term && 'gmr_artist' === $term->taxonomy ? 'gmr_artist_portrait_id' : 'gmr_collection_cover_id';
			return wp_get_attachment_image( absint( get_term_meta( $term_id, $key, true ) ), array( 48, 48 ) ) ?: '&mdash;';
		}
		if ( 'gmr_editorial' === $column ) {
			$term = get_term( $term_id );
			if ( $term instanceof WP_Term && 'gmr_artist' === $term->taxonomy ) {
				return 'elmar' === get_term_meta( $term_id, 'gmr_artist_special_template', true ) ? 'Perfil especial: Elmar Rojas' : ( get_term_meta( $term_id, 'gmr_artist_featured', true ) ? 'Destacado' : 'Estandar' );
			}
			$start = get_term_meta( $term_id, 'gmr_collection_year_start', true );
			$end   = get_term_meta( $term_id, 'gmr_collection_year_end', true );
			return esc_html( trim( $start . ( $end ? "–{$end}" : '' ) ) . ' / ' . GMR_Core_Meta::sanitize_visibility( get_term_meta( $term_id, 'gmr_visibility', true ) ) );
		}
		return $content;
	}
}
