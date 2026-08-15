<?php
/** Collector account lifecycle and private response hardening. */
defined( 'ABSPATH' ) || exit;

final class GMR_Core_Collectors {
	private const ROLE = 'gmr_collector';
	private const STATUS_KEY = 'gmr_collector_status';

	public static function register_hooks(): void {
		add_filter( 'authenticate', array( self::class, 'block_inactive_login' ), 35, 3 );
		add_action( 'wp_login', array( self::class, 'record_login' ), 10, 2 );
		add_action( 'template_redirect', array( self::class, 'private_headers' ), 0 );
		add_action( 'template_redirect', array( self::class, 'enforce_active_session' ), -1 );
		add_action( 'admin_init', array( self::class, 'keep_collectors_out_of_admin' ) );
		add_filter( 'editable_roles', array( self::class, 'restrict_editable_roles' ) );
		add_filter( 'map_meta_cap', array( self::class, 'restrict_user_management' ), 10, 4 );
		add_action( 'show_user_profile', array( self::class, 'profile_fields' ) );
		add_action( 'edit_user_profile', array( self::class, 'profile_fields' ) );
		add_action( 'personal_options_update', array( self::class, 'save_profile_fields' ) );
		add_action( 'edit_user_profile_update', array( self::class, 'save_profile_fields' ) );
		add_filter( 'manage_users_columns', array( self::class, 'user_columns' ) );
		add_filter( 'manage_users_custom_column', array( self::class, 'user_column_value' ), 10, 3 );
		add_filter( 'woocommerce_store_api_product_response', array( self::class, 'filter_store_api_product' ), 20, 2 );
		add_filter( 'rest_product_query', array( self::class, 'filter_product_api_query' ) );
		add_filter( 'woocommerce_rest_product_object_query', array( self::class, 'filter_product_api_query' ) );
		add_filter( 'woocommerce_store_api_product_query_args', array( self::class, 'filter_product_api_query' ) );
		add_filter( 'rest_pre_dispatch', array( self::class, 'block_public_store_products' ), 10, 3 );
	}

	private static function is_collector( WP_User $user ): bool {
		return in_array( self::ROLE, (array) $user->roles, true );
	}

	public static function status( WP_User $user ): string {
		$status = get_user_meta( $user->ID, self::STATUS_KEY, true );
		return in_array( $status, array( 'active', 'suspended', 'expired' ), true ) ? $status : 'active';
	}

	public static function block_inactive_login( mixed $user, string $username, string $password ): mixed {
		if ( $user instanceof WP_User && self::is_collector( $user ) && 'active' !== self::status( $user ) ) {
			return new WP_Error( 'gmr_collector_inactive', __( 'Esta cuenta de Coleccionistas no esta activa. Contacte a la galeria.', 'mayari-core' ) );
		}
		return $user;
	}

	public static function record_login( string $login, WP_User $user ): void {
		if ( self::is_collector( $user ) ) update_user_meta( $user->ID, 'gmr_collector_last_login', current_time( 'mysql', true ) );
	}

	public static function private_headers(): void {
		if ( is_page( 'coleccionistas' ) || current_user_can( 'gmr_view_collector_catalog' ) ) {
			nocache_headers();
			header( 'X-Robots-Tag: noindex, nofollow, noarchive', true );
			header( 'Vary: Cookie', false );
		}
	}

	public static function enforce_active_session(): void {
		$user = wp_get_current_user();
		if ( $user->exists() && self::is_collector( $user ) && 'active' !== self::status( $user ) ) {
			wp_logout();
			wp_safe_redirect( add_query_arg( 'access', 'inactive', home_url( '/coleccionistas/' ) ) ); exit;
		}
	}

	public static function keep_collectors_out_of_admin(): void {
		if ( ! wp_doing_ajax() && current_user_can( 'gmr_view_collector_area' ) && ! current_user_can( 'gmr_manage_artworks' ) ) {
			wp_safe_redirect( home_url( '/coleccionistas/' ) ); exit;
		}
	}

	public static function restrict_editable_roles( array $roles ): array {
		if ( current_user_can( 'gmr_manage_collectors' ) && ! current_user_can( 'manage_options' ) ) {
			return isset( $roles[ self::ROLE ] ) ? array( self::ROLE => $roles[ self::ROLE ] ) : array();
		}
		return $roles;
	}

	public static function restrict_user_management( array $caps, string $cap, int $user_id, array $args ): array {
		if ( ! in_array( $cap, array( 'edit_user', 'remove_user', 'delete_user', 'promote_user' ), true ) || current_user_can( 'manage_options' ) ) return $caps;
		if ( current_user_can( 'gmr_manage_collectors' ) && ! empty( $args[0] ) ) {
			$target = get_userdata( (int) $args[0] );
			if ( ! $target || ! self::is_collector( $target ) ) return array( 'do_not_allow' );
		}
		return $caps;
	}

	public static function profile_fields( WP_User $user ): void {
		if ( ! self::is_collector( $user ) || ! current_user_can( 'gmr_manage_collectors' ) ) return;
		?><h2><?php esc_html_e( 'Acceso de Coleccionistas', 'mayari-core' ); ?></h2><table class="form-table"><tr><th><label for="gmr_collector_status">Estado</label></th><td><select name="gmr_collector_status" id="gmr_collector_status"><?php foreach ( array( 'active'=>'Activa', 'suspended'=>'Suspendida', 'expired'=>'Expirada' ) as $value=>$label ) printf( '<option value="%s" %s>%s</option>', esc_attr( $value ), selected( self::status( $user ), $value, false ), esc_html( $label ) ); ?></select><p class="description">Las cuentas suspendidas o expiradas no pueden iniciar sesion.</p></td></tr><tr><th>Ultimo acceso</th><td><?php echo esc_html( get_user_meta( $user->ID, 'gmr_collector_last_login', true ) ?: 'Sin accesos registrados' ); ?></td></tr></table><?php
	}

	public static function save_profile_fields( int $user_id ): void {
		if ( ! current_user_can( 'gmr_manage_collectors' ) || ! current_user_can( 'edit_user', $user_id ) ) return;
		$user = get_userdata( $user_id );
		if ( ! $user || ! self::is_collector( $user ) ) return;
		$status = isset( $_POST['gmr_collector_status'] ) ? sanitize_key( wp_unslash( $_POST['gmr_collector_status'] ) ) : 'active';
		if ( in_array( $status, array( 'active', 'suspended', 'expired' ), true ) ) update_user_meta( $user_id, self::STATUS_KEY, $status );
		if ( 'active' !== $status ) WP_Session_Tokens::get_instance( $user_id )->destroy_all();
	}

	public static function user_columns( array $columns ): array { $columns['gmr_collector_status'] = 'Acceso Mayari'; return $columns; }
	public static function user_column_value( string $value, string $column, int $user_id ): string {
		if ( 'gmr_collector_status' !== $column ) return $value;
		$user = get_userdata( $user_id );
		return $user && self::is_collector( $user ) ? esc_html( ucfirst( self::status( $user ) ) ) : '&mdash;';
	}

	public static function filter_store_api_product( mixed $response, mixed $product ): mixed {
		if ( ! $product instanceof WC_Product || GMR_Core_Access::can_view_price( $product->get_id() ) ) return $response;
		if ( $response instanceof WP_REST_Response ) {
			$data = $response->get_data();
			foreach ( array( 'prices', 'price_html' ) as $key ) unset( $data[ $key ] );
			$response->set_data( $data );
		} elseif ( is_array( $response ) ) {
			unset( $response['prices'], $response['price_html'] );
		}
		return $response;
	}

	public static function filter_product_api_query( array $args ): array {
		$allowed = current_user_can( 'gmr_view_collector_catalog' ) ? array( 'public', 'collectors' ) : array( 'public' );
		$args['meta_query'] = isset( $args['meta_query'] ) && is_array( $args['meta_query'] ) ? $args['meta_query'] : array();
		$args['meta_query'][] = array( 'relation'=>'OR', array( 'key'=>'gmr_visibility', 'value'=>$allowed, 'compare'=>'IN' ), array( 'key'=>'gmr_visibility', 'compare'=>'NOT EXISTS' ) );
		return $args;
	}

	public static function block_public_store_products( mixed $result, WP_REST_Server $server, WP_REST_Request $request ): mixed {
		if ( str_starts_with( $request->get_route(), '/wc/store/' ) && str_contains( $request->get_route(), '/products' ) && ! current_user_can( 'gmr_view_collector_catalog' ) ) {
			return new WP_Error( 'gmr_private_catalog', __( 'Catalogo API no disponible.', 'mayari-core' ), array( 'status'=>404 ) );
		}
		return $result;
	}
}
