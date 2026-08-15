<?php
/** Private document vault and authenticated downloads. */
defined( 'ABSPATH' ) || exit;

final class GMR_Core_Documents {
	private const NONCE = 'gmr_private_documents';

	public static function register_hooks(): void {
		add_action( 'add_meta_boxes_product', array( self::class, 'add_product_box' ) );
		add_action( 'save_post_product', array( self::class, 'save_product_documents' ), 30 );
		add_action( 'gmr_artist_edit_form_fields', array( self::class, 'artist_field' ), 30, 2 );
		add_action( 'gmr_artist_add_form_fields', array( self::class, 'artist_add_field' ), 30 );
		add_action( 'edited_gmr_artist', array( self::class, 'save_artist_documents' ) );
		add_action( 'created_gmr_artist', array( self::class, 'save_artist_documents' ) );
		add_action( 'admin_footer-term.php', array( self::class, 'enable_term_uploads' ) );
		add_action( 'admin_footer-edit-tags.php', array( self::class, 'enable_term_uploads' ) );
		add_action( 'template_redirect', array( self::class, 'maybe_download' ), -5 );
		add_action( 'admin_notices', array( self::class, 'vault_notice' ) );
	}

	public static function vault_path(): string {
		return untrailingslashit( (string) get_option( 'gmr_private_documents_path', '' ) );
	}

	public static function vault_ready(): bool {
		$path = self::vault_path();
		if ( ! $path || str_starts_with( wp_normalize_path( $path ), wp_normalize_path( ABSPATH ) ) ) return false;
		return is_dir( $path ) ? is_writable( $path ) : wp_mkdir_p( $path );
	}

	public static function add_product_box(): void {
		add_meta_box( 'gmr-private-documents', 'Documentos privados', array( self::class, 'product_box' ), 'product', 'normal', 'default' );
	}

	public static function product_box( WP_Post $post ): void {
		wp_nonce_field( self::NONCE, 'gmr_documents_nonce' );
		self::render_list( 'product', $post->ID );
		echo '<p><label><strong>Agregar archivos</strong><br><input type="file" name="gmr_private_documents[]" multiple accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx"></label></p><p class="description">PDF, imagen o documento. Se almacena fuera del directorio publico.</p>';
	}

	public static function artist_field( WP_Term $term ): void {
		echo '<tr class="form-field"><th>Documentos privados</th><td>';
		wp_nonce_field( self::NONCE, 'gmr_documents_nonce' ); self::render_list( 'artist', $term->term_id );
		echo '<input type="file" name="gmr_private_documents[]" multiple accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx"><p class="description">Solo Coleccionistas y administracion.</p></td></tr>';
	}

	public static function artist_add_field(): void {
		echo '<div class="form-field"><label>Documentos privados</label>';
		wp_nonce_field( self::NONCE, 'gmr_documents_nonce' );
		echo '<input type="file" name="gmr_private_documents[]" multiple accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx"></div>';
	}

	private static function render_list( string $owner_type, int $owner_id ): void {
		$documents = self::get_documents( $owner_type, $owner_id );
		if ( ! $documents ) { echo '<p>No hay documentos privados.</p>'; return; }
		echo '<ul>';
		foreach ( $documents as $document ) printf( '<li><strong>%s</strong> <label><input type="checkbox" name="gmr_remove_documents[]" value="%d"> Eliminar</label></li>', esc_html( $document->post_title ), (int) $document->ID );
		echo '</ul>';
	}

	public static function save_product_documents( int $post_id ): void {
		if ( ! current_user_can( 'gmr_manage_artworks' ) && ! current_user_can( 'manage_woocommerce' ) ) return;
		self::handle_save( 'product', $post_id );
	}

	public static function save_artist_documents( int $term_id ): void {
		if ( ! current_user_can( 'gmr_manage_artists' ) ) return;
		self::handle_save( 'artist', $term_id );
	}

	private static function handle_save( string $owner_type, int $owner_id ): void {
		if ( ! isset( $_POST['gmr_documents_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gmr_documents_nonce'] ) ), self::NONCE ) ) return;
		foreach ( array_map( 'absint', (array) ( $_POST['gmr_remove_documents'] ?? array() ) ) as $document_id ) self::delete_document( $document_id, $owner_type, $owner_id );
		if ( empty( $_FILES['gmr_private_documents']['name'] ) || ! self::vault_ready() ) return;
		$files = $_FILES['gmr_private_documents'];
		foreach ( (array) $files['name'] as $index=>$name ) {
			if ( UPLOAD_ERR_OK !== (int) $files['error'][ $index ] ) continue;
			self::store_upload( array( 'name'=>$name, 'type'=>$files['type'][$index], 'tmp_name'=>$files['tmp_name'][$index], 'error'=>$files['error'][$index], 'size'=>$files['size'][$index] ), $owner_type, $owner_id );
		}
	}

	private static function store_upload( array $file, string $owner_type, int $owner_id ): int|WP_Error {
		$checked = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], array( 'pdf'=>'application/pdf','jpg|jpeg'=>'image/jpeg','png'=>'image/png','webp'=>'image/webp','doc'=>'application/msword','docx'=>'application/vnd.openxmlformats-officedocument.wordprocessingml.document' ) );
		if ( empty( $checked['ext'] ) ) return new WP_Error( 'gmr_document_type', 'Tipo de archivo no permitido.' );
		$stored = wp_generate_uuid4() . '.' . $checked['ext']; $target = self::vault_path() . DIRECTORY_SEPARATOR . $stored;
		if ( ! @move_uploaded_file( $file['tmp_name'], $target ) ) return new WP_Error( 'gmr_document_move', 'No fue posible guardar el archivo.' );
		@chmod( $target, 0640 );
		$document_id = wp_insert_post( array( 'post_type'=>'gmr_document', 'post_status'=>'private', 'post_title'=>sanitize_text_field( pathinfo( $file['name'], PATHINFO_FILENAME ) ) ), true );
		if ( is_wp_error( $document_id ) ) { @unlink( $target ); return $document_id; }
		update_post_meta( $document_id, 'gmr_document_file', $stored ); update_post_meta( $document_id, 'gmr_document_mime', $checked['type'] ); update_post_meta( $document_id, 'gmr_document_owner_type', $owner_type ); update_post_meta( $document_id, 'gmr_document_owner_id', $owner_id );
		return $document_id;
	}

	public static function import_attachment( int $attachment_id, string $owner_type, int $owner_id, bool $delete_public = false ): int|WP_Error {
		if ( ! self::vault_ready() ) return new WP_Error( 'gmr_vault', 'La boveda no esta configurada.' );
		$source = get_attached_file( $attachment_id ); $mime = get_post_mime_type( $attachment_id );
		if ( ! $source || ! is_file( $source ) || ! in_array( $mime, array( 'application/pdf','image/jpeg','image/png','image/webp','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document' ), true ) ) return new WP_Error( 'gmr_attachment', 'Adjunto no valido.' );
		$stored = wp_generate_uuid4() . '.' . strtolower( pathinfo( $source, PATHINFO_EXTENSION ) ); $target = self::vault_path() . DIRECTORY_SEPARATOR . $stored;
		if ( ! copy( $source, $target ) ) return new WP_Error( 'gmr_document_copy', 'No fue posible copiar el archivo.' );
		@chmod( $target, 0640 );
		$document_id = wp_insert_post( array( 'post_type'=>'gmr_document', 'post_status'=>'private', 'post_title'=>get_the_title( $attachment_id ) ), true );
		if ( is_wp_error( $document_id ) ) { @unlink( $target ); return $document_id; }
		update_post_meta( $document_id, 'gmr_document_file', $stored ); update_post_meta( $document_id, 'gmr_document_mime', $mime ); update_post_meta( $document_id, 'gmr_document_owner_type', $owner_type ); update_post_meta( $document_id, 'gmr_document_owner_id', $owner_id );
		if ( $delete_public ) wp_delete_attachment( $attachment_id, true );
		return $document_id;
	}

	private static function delete_document( int $document_id, string $owner_type, int $owner_id ): void {
		if ( $owner_type !== get_post_meta( $document_id, 'gmr_document_owner_type', true ) || $owner_id !== (int) get_post_meta( $document_id, 'gmr_document_owner_id', true ) ) return;
		$file = self::document_path( $document_id ); if ( $file && is_file( $file ) ) @unlink( $file ); wp_delete_post( $document_id, true );
	}

	public static function get_documents( string $owner_type, int $owner_id ): array {
		return get_posts( array( 'post_type'=>'gmr_document', 'post_status'=>'private', 'posts_per_page'=>-1, 'orderby'=>'menu_order title', 'order'=>'ASC', 'meta_query'=>array( array( 'key'=>'gmr_document_owner_type','value'=>$owner_type ), array( 'key'=>'gmr_document_owner_id','value'=>$owner_id ) ) ) );
	}

	public static function url( int $document_id ): string {
		return wp_nonce_url( add_query_arg( 'gmr-document', $document_id, home_url( '/' ) ), 'gmr_download_' . $document_id, 'token' );
	}

	private static function can_download( int $document_id ): bool {
		if ( 'gmr_document' !== get_post_type( $document_id ) ) return false;
		return current_user_can( 'gmr_download_collector_documents' ) || current_user_can( 'gmr_manage_artworks' ) || current_user_can( 'gmr_manage_artists' );
	}

	private static function document_path( int $document_id ): string {
		$file = basename( (string) get_post_meta( $document_id, 'gmr_document_file', true ) ); return $file ? self::vault_path() . DIRECTORY_SEPARATOR . $file : '';
	}

	public static function maybe_download(): void {
		if ( ! isset( $_GET['gmr-document'] ) ) return;
		$document_id = absint( $_GET['gmr-document'] );
		if ( ! $document_id || ! isset( $_GET['token'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['token'] ) ), 'gmr_download_' . $document_id ) || ! self::can_download( $document_id ) ) self::deny();
		$path = self::document_path( $document_id ); if ( ! $path || ! is_file( $path ) ) self::deny();
		nocache_headers(); header( 'X-Robots-Tag: noindex, nofollow, noarchive', true ); header( 'X-Content-Type-Options: nosniff', true );
		header( 'Content-Type: ' . ( get_post_meta( $document_id, 'gmr_document_mime', true ) ?: 'application/octet-stream' ) ); header( 'Content-Length: ' . filesize( $path ) ); header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( get_the_title( $document_id ) ) . '.' . pathinfo( $path, PATHINFO_EXTENSION ) . '"' );
		readfile( $path ); exit;
	}

	public static function deny(): never { status_header( 404 ); nocache_headers(); exit; }
	public static function enable_term_uploads(): void { $screen=get_current_screen(); if($screen&&'gmr_artist'===$screen->taxonomy) echo '<script>const gmrTermForm=document.querySelector("form#edittag,form#addtag");if(gmrTermForm)gmrTermForm.setAttribute("enctype","multipart/form-data");</script>'; }
	public static function vault_notice(): void { if(current_user_can('manage_options')&&!self::vault_ready()) echo '<div class="notice notice-error"><p><strong>Mayari:</strong> configure una ruta escribible fuera de ABSPATH en <code>gmr_private_documents_path</code>.</p></div>'; }
}
