<?php
/** Artwork inquiry workflow. @package MayariCore */
defined( 'ABSPATH' ) || exit;

final class GMR_Core_Inquiry {
	public static function register_hooks(): void {
		add_shortcode( 'gmr_artwork_inquiry', array( self::class, 'shortcode' ) );
		add_action( 'admin_post_gmr_submit_inquiry', array( self::class, 'submit' ) );
		add_action( 'admin_post_nopriv_gmr_submit_inquiry', array( self::class, 'submit' ) );
		add_action( 'admin_init', array( self::class, 'settings' ) );
		add_filter( 'manage_gmr_inquiry_posts_columns', array( self::class, 'columns' ) );
		add_action( 'manage_gmr_inquiry_posts_custom_column', array( self::class, 'column' ), 10, 2 );
	}

	public static function settings(): void {
		register_setting( 'general', 'gmr_inquiry_email', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_email', 'default' => get_option( 'admin_email' ) ) );
		add_settings_field( 'gmr_inquiry_email', 'Correo para consultas de obra', array( self::class, 'email_setting' ), 'general' );
	}
	public static function email_setting(): void { printf( '<input type="email" class="regular-text" name="gmr_inquiry_email" value="%s"><p class="description">Destino de las consultas enviadas desde las fichas de obra.</p>', esc_attr( get_option( 'gmr_inquiry_email', get_option( 'admin_email' ) ) ) ); }

	public static function shortcode( array $atts = array() ): string {
		$atts = shortcode_atts( array( 'product_id' => get_the_ID() ), $atts );
		$product_id = absint( $atts['product_id'] );
		if ( 'product' !== get_post_type( $product_id ) ) return '';
		$product = wc_get_product( $product_id );
		$status = isset( $_GET['consulta'] ) ? sanitize_key( wp_unslash( $_GET['consulta'] ) ) : '';
		$started = time();
		ob_start(); ?>
		<section class="gmr-inquiry" id="consultar"><div class="gmr-inquiry__intro"><span class="gmr-kicker">Consulta privada</span><h2>Conversemos<br> sobre esta obra.</h2><p>Solicite disponibilidad, precio o información adicional. La galería responderá personalmente a su consulta.</p></div><div class="gmr-inquiry__panel">
		<?php if ( 'enviada' === $status ) : ?><div class="gmr-form-notice gmr-form-notice--success" role="status">Gracias. Su consulta fue recibida correctamente.</div><?php elseif ( 'error' === $status ) : ?><div class="gmr-form-notice" role="alert">No pudimos enviar la consulta. Revise los campos e intente nuevamente.</div><?php endif; ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="gmr_submit_inquiry"><input type="hidden" name="product_id" value="<?php echo esc_attr( $product_id ); ?>"><input type="hidden" name="started" value="<?php echo esc_attr( $started ); ?>"><input type="hidden" name="started_token" value="<?php echo esc_attr( wp_hash( $product_id . '|' . $started ) ); ?>"><?php wp_nonce_field( 'gmr_inquiry_' . $product_id, 'gmr_inquiry_nonce' ); ?>
			<?php $artist_names=wp_get_post_terms($product_id,'gmr_artist',array('fields'=>'names'));$artist_label=is_wp_error($artist_names)?'':implode(', ',$artist_names);?><div class="gmr-inquiry__reference"><span>Obra consultada</span><strong><?php echo esc_html( get_the_title( $product_id ) ); ?></strong><small><?php echo esc_html( $artist_label ); ?><?php echo $product&&$product->get_sku()?' · Ref. '.esc_html($product->get_sku()):'';?></small></div>
			<div class="gmr-form-grid"><label><span>Nombre completo</span><input required type="text" name="name" autocomplete="name" placeholder="Su nombre"></label><label><span>Correo electrónico</span><input required type="email" name="email" autocomplete="email" placeholder="nombre@correo.com"></label><label><span>Teléfono <em>opcional</em></span><input type="tel" name="phone" autocomplete="tel" placeholder="+502 0000 0000"></label></div>
			<label class="gmr-message-field"><span>¿Cómo podemos ayudarle?</span><textarea required name="message" rows="5">Me interesa recibir información sobre esta obra.</textarea></label>
			<label class="gmr-honeypot" aria-hidden="true">Sitio web<input name="website" tabindex="-1" autocomplete="off"></label>
			<div class="gmr-inquiry__actions"><label class="gmr-consent"><input required type="checkbox" name="consent" value="1"><span>Acepto que la galería utilice estos datos únicamente para responder mi consulta.</span></label>
			<button class="gmr-button" type="submit">Enviar consulta</button></div>
		</form></div></section><?php return ob_get_clean();
	}

	public static function submit(): void {
		$product_id = absint( $_POST['product_id'] ?? 0 );
		$redirect = get_permalink( $product_id ) ?: home_url( '/' );
		$fail = static function() use ( $redirect ): void { wp_safe_redirect( add_query_arg( 'consulta', 'error', $redirect ) . '#consultar' ); exit; };
		if ( ! $product_id || ! isset( $_POST['gmr_inquiry_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gmr_inquiry_nonce'] ) ), 'gmr_inquiry_' . $product_id ) ) $fail();
		$started = absint( $_POST['started'] ?? 0 );
		if ( ! hash_equals( wp_hash( $product_id . '|' . $started ), sanitize_text_field( wp_unslash( $_POST['started_token'] ?? '' ) ) ) || time() - $started < 3 || ! empty( $_POST['website'] ) ) $fail();
		$name = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) ); $email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ); $phone = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) ); $message = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );
		if ( ! $name || ! is_email( $email ) || ! $message || empty( $_POST['consent'] ) ) $fail();
		$rate_key = 'gmr_inquiry_' . hash( 'sha256', $email . '|' . $product_id ); if ( get_transient( $rate_key ) ) $fail(); set_transient( $rate_key, 1, 5 * MINUTE_IN_SECONDS );
		$product = wc_get_product( $product_id ); $artist_terms = wp_get_post_terms( $product_id, 'gmr_artist', array( 'fields' => 'names' ) ); $artist = is_wp_error( $artist_terms ) ? '' : implode( ', ', $artist_terms ); $sku = $product ? $product->get_sku() : '';
		$inquiry_id = wp_insert_post( array( 'post_type' => 'gmr_inquiry', 'post_status' => 'private', 'post_title' => sprintf( '%s — %s', get_the_title( $product_id ), $name ) ) );
		if ( is_wp_error( $inquiry_id ) || ! $inquiry_id ) $fail();
		foreach ( array( 'product_id'=>$product_id, 'name'=>$name, 'email'=>$email, 'phone'=>$phone, 'message'=>$message, 'artist'=>$artist, 'sku'=>$sku, 'consent_at'=>current_time( 'mysql' ), 'user_id'=>get_current_user_id() ) as $key=>$value ) update_post_meta( $inquiry_id, 'gmr_inquiry_' . $key, $value );
		$subject = sprintf( '[Galeria Mayari Rojas] Consulta por %s', get_the_title( $product_id ) );
		$body = "Obra: " . get_the_title( $product_id ) . "\nArtista: {$artist}\nSKU: {$sku}\n\nNombre: {$name}\nCorreo: {$email}\nTelefono: {$phone}\n\n{$message}\n";
		wp_mail( get_option( 'gmr_inquiry_email', get_option( 'admin_email' ) ), $subject, $body, array( 'Reply-To: ' . $name . ' <' . $email . '>' ) );
		wp_safe_redirect( add_query_arg( 'consulta', 'enviada', $redirect ) . '#consultar' ); exit;
	}

	public static function columns( array $columns ): array { return array( 'cb'=>$columns['cb'], 'title'=>'Obra y contacto', 'gmr_email'=>'Correo', 'gmr_phone'=>'Telefono', 'date'=>'Fecha' ); }
	public static function column( string $column, int $post_id ): void { if ( 'gmr_email' === $column ) echo esc_html( get_post_meta( $post_id, 'gmr_inquiry_email', true ) ); if ( 'gmr_phone' === $column ) echo esc_html( get_post_meta( $post_id, 'gmr_inquiry_phone', true ) ); }
}
