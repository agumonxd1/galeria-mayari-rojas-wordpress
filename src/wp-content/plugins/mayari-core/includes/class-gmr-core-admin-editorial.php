<?php
/** Simple editorial forms for agenda and multimedia. @package MayariCore */
defined( 'ABSPATH' ) || exit;

final class GMR_Core_Admin_Editorial {
	public static function register_hooks(): void {
		add_action( 'add_meta_boxes', array( self::class, 'add_boxes' ) );
		add_action( 'save_post_gmr_event', array( self::class, 'save_event' ) );
		add_action( 'save_post_gmr_media_gallery', array( self::class, 'save_media' ) );
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue_assets' ) );
	}
	public static function enqueue_assets( string $hook ): void {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || 'gmr_media_gallery' !== get_current_screen()->post_type ) return;
		wp_enqueue_media();
		wp_enqueue_script( 'gmr-admin-editorial', plugins_url( 'assets/admin-editorial.js', GMR_CORE_FILE ), array( 'jquery' ), GMR_CORE_VERSION, true );
	}

	public static function add_boxes(): void {
		add_meta_box( 'gmr-event-details', 'Datos de agenda', array( self::class, 'event_box' ), 'gmr_event', 'normal', 'high' );
		add_meta_box( 'gmr-media-details', 'Datos multimedia', array( self::class, 'media_box' ), 'gmr_media_gallery', 'normal', 'high' );
	}

	public static function event_box( WP_Post $post ): void {
		wp_nonce_field( 'gmr_editorial_save', 'gmr_editorial_nonce' );
		self::field( $post, 'gmr_event_start', 'Inicio', 'datetime-local' );
		self::field( $post, 'gmr_event_end', 'Final', 'datetime-local' );
		printf( '<p><label><input type="checkbox" name="gmr_event_all_day" value="1" %s> Evento de dia completo</label></p>', checked( get_post_meta( $post->ID, 'gmr_event_all_day', true ), true, false ) );
		self::field( $post, 'gmr_event_venue', 'Lugar' );
		self::field( $post, 'gmr_event_address', 'Direccion' );
		self::select( $post, 'gmr_event_modality', 'Modalidad', array( 'presencial' => 'Presencial', 'virtual' => 'Virtual', 'hibrida' => 'Hibrida' ) );
		self::select( $post, 'gmr_event_status', 'Estado', array( 'upcoming' => 'Proximo', 'ongoing' => 'En curso', 'finished' => 'Finalizado', 'cancelled' => 'Cancelado' ) );
		self::field( $post, 'gmr_event_registration', 'Enlace de registro', 'url' );
		self::select( $post, 'gmr_visibility', 'Visibilidad', array( 'public' => 'Publico', 'collectors' => 'Coleccionistas', 'hidden' => 'Oculto' ) );
	}

	public static function media_box( WP_Post $post ): void {
		wp_nonce_field( 'gmr_editorial_save', 'gmr_editorial_nonce' );
		self::field( $post, 'gmr_media_date_label', 'Fecha o periodo' );
		self::field( $post, 'gmr_media_credits', 'Creditos y derechos' );
		self::field( $post, 'gmr_media_ids', 'Imagenes seleccionadas' );
		echo '<p><button type="button" class="button" id="gmr-select-media">Seleccionar u ordenar imagenes</button></p><div id="gmr-media-preview"></div><p class="description">Use la imagen destacada como portada. El selector conserva el orden elegido.</p>';
		self::select( $post, 'gmr_visibility', 'Visibilidad', array( 'public' => 'Publico', 'collectors' => 'Coleccionistas', 'hidden' => 'Oculto' ) );
	}

	private static function field( WP_Post $post, string $key, string $label, string $type = 'text' ): void {
		printf( '<p><label for="%1$s"><strong>%2$s</strong></label><br><input class="widefat" type="%3$s" id="%1$s" name="%1$s" value="%4$s"></p>', esc_attr( $key ), esc_html( $label ), esc_attr( $type ), esc_attr( get_post_meta( $post->ID, $key, true ) ) );
	}

	private static function select( WP_Post $post, string $key, string $label, array $options ): void {
		$current = get_post_meta( $post->ID, $key, true );
		printf( '<p><label for="%s"><strong>%s</strong></label><br><select class="widefat" id="%s" name="%s">', esc_attr( $key ), esc_html( $label ), esc_attr( $key ), esc_attr( $key ) );
		foreach ( $options as $value => $text ) printf( '<option value="%s" %s>%s</option>', esc_attr( $value ), selected( $current, $value, false ), esc_html( $text ) );
		echo '</select></p>';
	}

	public static function save_event( int $post_id ): void { if ( self::save( $post_id, array( 'gmr_event_start', 'gmr_event_end', 'gmr_event_venue', 'gmr_event_address', 'gmr_event_modality', 'gmr_event_status', 'gmr_event_registration', 'gmr_visibility' ) ) ) update_post_meta( $post_id, 'gmr_event_all_day', isset( $_POST['gmr_event_all_day'] ) ); }
	public static function save_media( int $post_id ): void { self::save( $post_id, array( 'gmr_media_date_label', 'gmr_media_credits', 'gmr_media_ids', 'gmr_visibility' ) ); }
	private static function save( int $post_id, array $keys ): bool {
		if ( ! isset( $_POST['gmr_editorial_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gmr_editorial_nonce'] ) ), 'gmr_editorial_save' ) || ! current_user_can( 'edit_post', $post_id ) ) return false;
		foreach ( $keys as $key ) if ( isset( $_POST[ $key ] ) ) update_post_meta( $post_id, $key, wp_unslash( $_POST[ $key ] ) );
		return true;
	}
}
