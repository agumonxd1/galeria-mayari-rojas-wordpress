<?php
/** Public in-gallery experience and QR tools for artworks. */
defined( 'ABSPATH' ) || exit;

final class GMR_Core_Artwork_Room {
	private const NONCE_ACTION = 'gmr_save_artwork_room';
	private const NONCE_NAME   = 'gmr_artwork_room_nonce';

	public static function register_hooks(): void {
		add_action( 'init', array( self::class, 'rewrite' ) );
		add_filter( 'query_vars', array( self::class, 'query_vars' ) );
		add_action( 'template_redirect', array( self::class, 'render' ), -20 );
		add_action( 'add_meta_boxes', array( self::class, 'add_box' ), 30 );
		add_action( 'save_post_product', array( self::class, 'save' ), 30 );
		add_action( 'admin_enqueue_scripts', array( self::class, 'admin_assets' ) );
		add_action( 'wp_enqueue_scripts', array( self::class, 'frontend_assets' ) );
	}

	public static function rewrite(): void {
		add_rewrite_rule( '^sala/([^/]+)/?$', 'index.php?gmr_artwork_room=$matches[1]', 'top' );
	}

	public static function query_vars( array $vars ): array {
		$vars[] = 'gmr_artwork_room';
		return $vars;
	}

	public static function url( int $post_id ): string {
		$post = get_post( $post_id );
		if ( ! $post instanceof WP_Post ) return '';
		$home = home_url( '/' );
		// Duplicator staging lives below a backup path where pretty subroutes are intercepted by the production server.
		return str_contains( $home, '/duplicator-backups/dup_staging/' )
			? add_query_arg( 'gmr_artwork_room', $post->post_name, $home )
			: home_url( '/sala/' . $post->post_name . '/' );
	}

	public static function add_box(): void {
		add_meta_box( 'gmr-artwork-room', __( 'Experiencia pública en sala y código QR', 'mayari-core' ), array( self::class, 'box' ), 'product', 'normal', 'default' );
	}

	public static function box( WP_Post $post ): void {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );
		$url   = self::url( $post->ID );
		$video = (string) get_post_meta( $post->ID, 'gmr_room_video_url', true );
		$text  = (string) get_post_meta( $post->ID, 'gmr_room_text', true );
		?>
		<div class="gmr-room-admin">
			<section class="gmr-room-admin__intro">
				<div><span class="gmr-room-admin__eyebrow"><?php esc_html_e( 'Recorrido de galería', 'mayari-core' ); ?></span><h3><?php esc_html_e( 'Contenido que descubrirá el visitante', 'mayari-core' ); ?></h3><p><?php esc_html_e( 'Esta página es siempre pública y está pensada para abrirse al escanear el código colocado junto a la obra. Admite video, texto o ambos.', 'mayari-core' ); ?></p></div>
				<div class="gmr-room-admin__qr"><div data-gmr-qr="<?php echo esc_attr( $url ); ?>"></div><strong><?php esc_html_e( 'QR de la obra', 'mayari-core' ); ?></strong><div class="gmr-room-admin__actions"><button type="button" class="button" data-gmr-qr-download><?php esc_html_e( 'Descargar PNG', 'mayari-core' ); ?></button><button type="button" class="button" data-gmr-copy="<?php echo esc_attr( $url ); ?>"><?php esc_html_e( 'Copiar enlace', 'mayari-core' ); ?></button><a class="button" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Abrir página', 'mayari-core' ); ?></a></div></div>
			</section>
			<section class="gmr-room-admin__fields"><label class="gmr-field"><span><?php esc_html_e( 'Video de YouTube', 'mayari-core' ); ?></span><input type="url" name="gmr_room_video_url" value="<?php echo esc_attr( $video ); ?>" placeholder="https://www.youtube.com/watch?v=..."><small><?php esc_html_e( 'Puede dejarse vacío. Se mostrará en formato adaptable para computadoras y teléfonos.', 'mayari-core' ); ?></small></label><div class="gmr-room-admin__editor"><strong><?php esc_html_e( 'Texto curatorial o testimonio', 'mayari-core' ); ?></strong><?php wp_editor( $text, 'gmr_room_text', array( 'textarea_name'=>'gmr_room_text', 'textarea_rows'=>9, 'media_buttons'=>false, 'teeny'=>false ) ); ?></div></section>
		</div>
		<?php
	}

	public static function save( int $post_id ): void {
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) || ! current_user_can( 'edit_post', $post_id ) || ! isset( $_POST[ self::NONCE_NAME ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION ) ) return;
		$video = isset( $_POST['gmr_room_video_url'] ) ? esc_url_raw( wp_unslash( $_POST['gmr_room_video_url'] ) ) : '';
		$host  = strtolower( (string) wp_parse_url( $video, PHP_URL_HOST ) );
		if ( $video && ! in_array( preg_replace( '/^www\./', '', $host ), array( 'youtube.com', 'youtu.be', 'youtube-nocookie.com' ), true ) ) $video = '';
		self::update_or_delete( $post_id, 'gmr_room_video_url', $video );
		self::update_or_delete( $post_id, 'gmr_room_text', isset( $_POST['gmr_room_text'] ) ? wp_kses_post( wp_unslash( $_POST['gmr_room_text'] ) ) : '' );
	}

	private static function update_or_delete( int $post_id, string $key, string $value ): void {
		'' === trim( $value ) ? delete_post_meta( $post_id, $key ) : update_post_meta( $post_id, $key, $value );
	}

	public static function admin_assets( string $hook ): void {
		$screen = get_current_screen();
		if ( ! $screen || 'product' !== $screen->post_type || ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) return;
		wp_enqueue_script( 'gmr-qrcode', plugins_url( 'assets/qrcode.min.js', GMR_CORE_FILE ), array(), GMR_CORE_VERSION, true );
		wp_enqueue_script( 'gmr-artwork-room', plugins_url( 'assets/artwork-room.js', GMR_CORE_FILE ), array( 'gmr-qrcode' ), GMR_CORE_VERSION, true );
	}

	public static function frontend_assets(): void {
		if ( ! is_singular( 'product' ) && ! get_query_var( 'gmr_artwork_room' ) ) return;
		wp_enqueue_script( 'gmr-qrcode', plugins_url( 'assets/qrcode.min.js', GMR_CORE_FILE ), array(), GMR_CORE_VERSION, true );
		wp_enqueue_script( 'gmr-artwork-room', plugins_url( 'assets/artwork-room.js', GMR_CORE_FILE ), array( 'gmr-qrcode' ), GMR_CORE_VERSION, true );
	}

	public static function render(): void {
		$slug = sanitize_title( (string) get_query_var( 'gmr_artwork_room' ) );
		if ( ! $slug ) return;
		$post = get_page_by_path( $slug, OBJECT, 'product' );
		if ( ! $post instanceof WP_Post || 'publish' !== $post->post_status ) {
			global $wp_query; $wp_query->set_404(); status_header( 404 ); return;
		}
		$template = locate_template( 'artwork-room.php' );
		if ( ! $template ) { status_header( 404 ); return; }
		global $wp_query;
		$wp_query->is_404 = false; $wp_query->is_singular = true; $wp_query->queried_object = $post; $wp_query->queried_object_id = $post->ID;
		$GLOBALS['post'] = $post; setup_postdata( $post ); status_header( 200 ); nocache_headers();
		include $template;
		wp_reset_postdata();
		exit;
	}
}
