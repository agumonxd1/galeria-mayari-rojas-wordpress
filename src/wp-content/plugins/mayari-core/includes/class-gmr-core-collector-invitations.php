<?php
/** Secure, single-use registration invitations for collector accounts. */
defined( 'ABSPATH' ) || exit;

final class GMR_Core_Collector_Invitations {
	private const VERSION = '1.0.0';
	private const QUERY_VAR = 'gmr_collector_invitation';
	private const CAPABILITY = 'gmr_manage_collectors';

	public static function register_hooks(): void {
		add_action( 'init', array( self::class, 'maybe_upgrade' ), 2 );
		add_action( 'init', array( self::class, 'rewrite' ), 20 );
		add_filter( 'query_vars', array( self::class, 'query_vars' ) );
		add_action( 'template_redirect', array( self::class, 'handle_registration' ), -30 );
		add_action( 'template_redirect', array( self::class, 'render' ), -25 );
		add_action( 'admin_menu', array( self::class, 'admin_menu' ) );
		add_action( 'admin_post_gmr_create_collector_invitation', array( self::class, 'create_invitation' ) );
		add_action( 'admin_post_gmr_revoke_collector_invitation', array( self::class, 'revoke_invitation' ) );
		add_action( 'admin_enqueue_scripts', array( self::class, 'admin_assets' ) );
	}

	public static function activate(): void {
		global $wpdb;
		$table = self::table();
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			token_hash char(64) NOT NULL,
			invitee_email varchar(190) NOT NULL DEFAULT '',
			invitee_name varchar(190) NOT NULL DEFAULT '',
			created_by bigint(20) unsigned NOT NULL DEFAULT 0,
			created_at datetime NOT NULL,
			expires_at datetime NOT NULL,
			revoked_at datetime NULL DEFAULT NULL,
			used_at datetime NULL DEFAULT NULL,
			used_by bigint(20) unsigned NOT NULL DEFAULT 0,
			PRIMARY KEY  (id),
			UNIQUE KEY token_hash (token_hash),
			KEY invitation_status (expires_at, revoked_at, used_at),
			KEY created_by (created_by)
		) {$wpdb->get_charset_collate()};" );
		update_option( 'gmr_collector_invitation_version', self::VERSION );
	}

	public static function maybe_upgrade(): void {
		if ( self::VERSION !== get_option( 'gmr_collector_invitation_version' ) ) self::activate();
	}

	public static function rewrite(): void {
		add_rewrite_rule( '^registro/invitacion/([A-Za-z0-9_-]+)/?$', 'index.php?' . self::QUERY_VAR . '=$matches[1]', 'top' );
	}

	public static function query_vars( array $vars ): array { $vars[] = self::QUERY_VAR; return $vars; }
	private static function table(): string { global $wpdb; return $wpdb->prefix . 'gmr_collector_invitations'; }
	private static function token_hash( string $token ): string { return hash( 'sha256', $token ); }
	private static function token(): string { return rtrim( strtr( base64_encode( random_bytes( 24 ) ), '+/', '-_' ), '=' ); }

	public static function url( string $token ): string {
		$home = home_url( '/' );
		return str_contains( $home, '/duplicator-backups/dup_staging/' )
			? add_query_arg( self::QUERY_VAR, rawurlencode( $token ), $home )
			: home_url( '/registro/invitacion/' . rawurlencode( $token ) . '/' );
	}

	public static function current(): ?object {
		$token = (string) get_query_var( self::QUERY_VAR );
		if ( ! preg_match( '/^[A-Za-z0-9_-]{24,80}$/', $token ) ) return null;
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE token_hash = %s LIMIT 1', self::token_hash( $token ) ) );
	}

	private static function state( ?object $invite ): string {
		if ( ! $invite ) return 'invalid';
		if ( $invite->revoked_at ) return 'revoked';
		if ( $invite->used_at ) return 'used';
		return strtotime( $invite->expires_at . ' UTC' ) <= time() ? 'expired' : 'active';
	}

	public static function handle_registration(): void {
		if ( ! get_query_var( self::QUERY_VAR ) || 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ?? '' ) ) return;
		$token = (string) get_query_var( self::QUERY_VAR );
		$target = self::url( $token );
		if ( ! isset( $_POST['gmr_invitation_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gmr_invitation_nonce'] ) ), 'gmr_use_collector_invitation_' . $token ) ) {
			wp_safe_redirect( add_query_arg( 'invite_error', 'security', $target ) ); exit;
		}
		$invite = self::current();
		if ( 'active' !== self::state( $invite ) ) { wp_safe_redirect( add_query_arg( 'invite_error', self::state( $invite ), $target ) ); exit; }
		$name = sanitize_text_field( wp_unslash( $_POST['gmr_invitee_name'] ?? '' ) );
		$email = sanitize_email( wp_unslash( $_POST['gmr_invitee_email'] ?? '' ) );
		$password = (string) wp_unslash( $_POST['gmr_invitee_password'] ?? '' );
		$confirm = (string) wp_unslash( $_POST['gmr_invitee_password_confirm'] ?? '' );
		if ( '' === $name || ! is_email( $email ) || strlen( $password ) < 10 || $password !== $confirm || ( $invite->invitee_email && strtolower( $email ) !== strtolower( $invite->invitee_email ) ) ) {
			wp_safe_redirect( add_query_arg( 'invite_error', 'details', $target ) ); exit;
		}
		if ( email_exists( $email ) ) { wp_safe_redirect( add_query_arg( 'invite_error', 'email_exists', $target ) ); exit; }
		global $wpdb;
		$claimed = $wpdb->query( $wpdb->prepare( "UPDATE " . self::table() . " SET used_at = UTC_TIMESTAMP() WHERE id = %d AND token_hash = %s AND used_at IS NULL AND revoked_at IS NULL AND expires_at > UTC_TIMESTAMP()", (int) $invite->id, self::token_hash( $token ) ) );
		if ( 1 !== $claimed ) { wp_safe_redirect( add_query_arg( 'invite_error', 'used', $target ) ); exit; }
		$login = self::unique_login( $email );
		$user_id = wp_insert_user( array( 'user_login' => $login, 'user_pass' => $password, 'user_email' => $email, 'display_name' => $name, 'nickname' => $name, 'role' => 'gmr_collector' ) );
		if ( is_wp_error( $user_id ) ) {
			$wpdb->update( self::table(), array( 'used_at' => null ), array( 'id' => (int) $invite->id, 'token_hash' => self::token_hash( $token ) ), array( '%s' ), array( '%d', '%s' ) );
			wp_safe_redirect( add_query_arg( 'invite_error', 'create', $target ) ); exit;
		}
		update_user_meta( $user_id, 'gmr_collector_status', 'active' );
		update_user_meta( $user_id, 'gmr_collector_invitation_id', (int) $invite->id );
		$wpdb->update( self::table(), array( 'used_by' => $user_id ), array( 'id' => (int) $invite->id ), array( '%d' ), array( '%d' ) );
		wp_set_current_user( $user_id ); wp_set_auth_cookie( $user_id, true );
		wp_safe_redirect( add_query_arg( 'welcome', 'invitation', self::collector_url() ) ); exit;
	}

	private static function unique_login( string $email ): string {
		$base = sanitize_user( strtok( $email, '@' ), true ); if ( '' === $base ) $base = 'coleccionista';
		$login = $base; $suffix = 2;
		while ( username_exists( $login ) ) { $login = substr( $base, 0, 52 ) . '-' . $suffix++; }
		return $login;
	}
	private static function collector_url(): string { $page = get_page_by_path( 'coleccionistas' ); return $page ? get_permalink( $page ) : home_url( '/coleccionistas/' ); }
	private static function can_manage(): bool { return current_user_can( self::CAPABILITY ) || current_user_can( 'gmr_manage_artworks' ) || current_user_can( 'create_users' ); }

	public static function render(): void {
		if ( ! get_query_var( self::QUERY_VAR ) ) return;
		$template = locate_template( 'collector-invitation.php' );
		if ( ! $template ) { status_header( 404 ); exit; }
		global $wp_query; $wp_query->is_404 = false; $wp_query->is_singular = true; status_header( 200 ); nocache_headers();
		header( 'X-Robots-Tag: noindex, nofollow, noarchive', true );
		include $template; exit;
	}

	public static function admin_menu(): void {
		add_users_page( __( 'Invitaciones', 'mayari-core' ), __( 'Invitaciones', 'mayari-core' ), 'gmr_manage_artworks', 'gmr-collector-invitations', array( self::class, 'admin_page' ) );
	}
	public static function admin_assets( string $hook ): void {
		if ( 'users_page_gmr-collector-invitations' !== $hook ) return;
		wp_enqueue_style( 'gmr-collector-invitations', plugins_url( 'assets/admin-invitations.css', GMR_CORE_FILE ), array(), GMR_CORE_VERSION );
		wp_enqueue_script( 'gmr-collector-invitations', plugins_url( 'assets/admin-invitations.js', GMR_CORE_FILE ), array(), GMR_CORE_VERSION, true );
	}

	public static function create_invitation(): void {
		if ( ! self::can_manage() ) wp_die( esc_html__( 'No tiene permisos para crear invitaciones.', 'mayari-core' ) );
		check_admin_referer( 'gmr_create_collector_invitation' );
		$email = sanitize_email( wp_unslash( $_POST['invitee_email'] ?? '' ) );
		$name = sanitize_text_field( wp_unslash( $_POST['invitee_name'] ?? '' ) );
		$days = max( 1, min( 365, absint( $_POST['expires_in_days'] ?? 7 ) ) );
		$token = self::token(); global $wpdb;
		$wpdb->insert( self::table(), array( 'token_hash'=>self::token_hash( $token ), 'invitee_email'=>$email, 'invitee_name'=>$name, 'created_by'=>get_current_user_id(), 'created_at'=>current_time( 'mysql', true ), 'expires_at'=>gmdate( 'Y-m-d H:i:s', time() + DAY_IN_SECONDS * $days ) ), array( '%s','%s','%s','%d','%s','%s' ) );
		if ( ! $wpdb->insert_id ) wp_die( esc_html__( 'No se pudo crear la invitación. Inténtelo de nuevo.', 'mayari-core' ) );
		set_transient( 'gmr_collector_invitation_created_' . get_current_user_id(), array( 'url'=>self::url( $token ), 'id'=>(int) $wpdb->insert_id ), 15 * MINUTE_IN_SECONDS );
		wp_safe_redirect( add_query_arg( 'created', '1', admin_url( 'users.php?page=gmr-collector-invitations' ) ) ); exit;
	}

	public static function revoke_invitation(): void {
		if ( ! self::can_manage() ) wp_die( esc_html__( 'No tiene permisos para revocar invitaciones.', 'mayari-core' ) );
		$id = absint( $_POST['invitation_id'] ?? 0 ); check_admin_referer( 'gmr_revoke_collector_invitation_' . $id );
		global $wpdb; $wpdb->query( $wpdb->prepare( 'UPDATE ' . self::table() . ' SET revoked_at = UTC_TIMESTAMP() WHERE id = %d AND used_at IS NULL AND revoked_at IS NULL', $id ) );
		wp_safe_redirect( add_query_arg( 'revoked', '1', admin_url( 'users.php?page=gmr-collector-invitations' ) ) ); exit;
	}

	public static function admin_page(): void {
		if ( ! self::can_manage() ) return;
		global $wpdb; $invites = $wpdb->get_results( 'SELECT * FROM ' . self::table() . ' ORDER BY id DESC LIMIT 100' );
		$created = get_transient( 'gmr_collector_invitation_created_' . get_current_user_id() ); if ( $created ) delete_transient( 'gmr_collector_invitation_created_' . get_current_user_id() );
		?>
		<div class="wrap gmr-invitations">
			<header class="gmr-invitations__hero"><div><span>COLECCIONISTAS · ACCESO PRIVADO</span><h1><?php esc_html_e( 'Invitaciones de registro', 'mayari-core' ); ?></h1><p><?php esc_html_e( 'Cree accesos personales, con vencimiento y de un único uso. El registro público permanece cerrado.', 'mayari-core' ); ?></p></div><div class="gmr-invitations__hero-note"><strong><?php esc_html_e( 'Enlace seguro', 'mayari-core' ); ?></strong><p><?php esc_html_e( 'El enlace completo se muestra una vez al crearlo. Guárdelo o envíelo de inmediato; si se pierde, revóquelo y genere otro.', 'mayari-core' ); ?></p></div></header>
			<?php if ( $created ) : ?><section class="gmr-invitations__created"><span><?php esc_html_e( 'INVITACIÓN CREADA', 'mayari-core' ); ?></span><h2><?php esc_html_e( 'Lista para compartir', 'mayari-core' ); ?></h2><div><code><?php echo esc_html( $created['url'] ); ?></code><button class="button button-primary" type="button" data-gmr-copy="<?php echo esc_attr( $created['url'] ); ?>"><?php esc_html_e( 'Copiar enlace', 'mayari-core' ); ?></button></div></section><?php elseif ( isset( $_GET['revoked'] ) ) : ?><div class="notice notice-success"><p><?php esc_html_e( 'La invitación fue revocada.', 'mayari-core' ); ?></p></div><?php endif; ?>
			<div class="gmr-invitations__layout"><section class="gmr-invitations__create"><span>01 · NUEVA INVITACIÓN</span><h2><?php esc_html_e( 'Preparar un acceso', 'mayari-core' ); ?></h2><form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><input type="hidden" name="action" value="gmr_create_collector_invitation"><?php wp_nonce_field( 'gmr_create_collector_invitation' ); ?><label>Nombre <input name="invitee_name" type="text" placeholder="Opcional"></label><label>Correo electrónico <input name="invitee_email" type="email" placeholder="Opcional; se podrá escribir al registrarse"></label><label>Vence en <select name="expires_in_days"><option value="1">1 día</option><option value="3">3 días</option><option value="7" selected>7 días</option><option value="14">14 días</option><option value="30">30 días</option><option value="90">90 días</option><option value="365">1 año</option></select></label><button class="button button-primary" type="submit">Crear invitación <span>↗</span></button></form></section>
			<section class="gmr-invitations__history"><div class="gmr-invitations__section-head"><div><span>02 · SEGUIMIENTO</span><h2><?php esc_html_e( 'Invitaciones recientes', 'mayari-core' ); ?></h2></div><p><?php esc_html_e( 'Se conserva el registro de la invitación, incluso si vence o es revocada.', 'mayari-core' ); ?></p></div><div class="gmr-invitations__table-wrap"><table><thead><tr><th>Destinatario</th><th>Creada</th><th>Vence</th><th>Estado</th><th>Registro</th><th></th></tr></thead><tbody><?php if ( ! $invites ) : ?><tr><td colspan="6" class="gmr-invitations__empty">Aún no hay invitaciones.</td></tr><?php endif; foreach ( $invites as $invite ) : $state = self::state( $invite ); $user = $invite->used_by ? get_userdata( (int) $invite->used_by ) : null; ?><tr><td><strong><?php echo esc_html( $invite->invitee_name ?: 'Sin nombre asignado' ); ?></strong><small><?php echo esc_html( $invite->invitee_email ?: 'Correo libre' ); ?></small></td><td><?php echo esc_html( get_date_from_gmt( $invite->created_at, 'j M Y' ) ); ?></td><td><?php echo esc_html( get_date_from_gmt( $invite->expires_at, 'j M Y · H:i' ) ); ?></td><td><span class="gmr-invitation-status is-<?php echo esc_attr( $state ); ?>"><?php echo esc_html( array( 'active'=>'Pendiente', 'used'=>'Registrada', 'expired'=>'Vencida', 'revoked'=>'Revocada' )[ $state ] ); ?></span></td><td><?php if ( $user ) : ?><strong><?php echo esc_html( $user->display_name ); ?></strong><small><?php echo esc_html( $user->user_email ); ?></small><?php else : ?>—<?php endif; ?></td><td><?php if ( 'active' === $state ) : ?><form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><input type="hidden" name="action" value="gmr_revoke_collector_invitation"><input type="hidden" name="invitation_id" value="<?php echo esc_attr( $invite->id ); ?>"><?php wp_nonce_field( 'gmr_revoke_collector_invitation_' . $invite->id ); ?><button class="button-link-delete" type="submit">Revocar</button></form><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div></section></div>
		</div><?php
	}
}
