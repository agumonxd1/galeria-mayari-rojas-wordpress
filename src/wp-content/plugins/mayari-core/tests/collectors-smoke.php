<?php
/** WP-CLI smoke test for collector lifecycle. */
defined( 'ABSPATH' ) || exit( 1 );

$login = 'gmr-smoke-' . wp_generate_password( 8, false, false );
$user_id = wp_insert_user( array( 'user_login'=>$login, 'user_email'=>$login . '@example.invalid', 'user_pass'=>wp_generate_password( 24 ), 'role'=>'gmr_collector' ) );
if ( is_wp_error( $user_id ) ) { echo wp_json_encode( array( 'ok'=>false, 'error'=>$user_id->get_error_message() ) ) . PHP_EOL; exit( 1 ); }

$user = get_userdata( $user_id );
$checks = array(
	'core_version' => GMR_CORE_VERSION,
	'role' => $user instanceof WP_User && in_array( 'gmr_collector', $user->roles, true ),
	'default_active' => 'active' === GMR_Core_Collectors::status( $user ),
	'catalog_cap' => $user->has_cap( 'gmr_view_collector_catalog' ),
	'frontend_handler' => false !== has_action( 'template_redirect', array( GMR_Core_Collectors::class, 'handle_frontend_access' ) ),
);
$reset_message = GMR_Core_Collectors::collector_reset_message( 'native', 'sample-key', $login, $user );
$access_page = get_page_by_path( 'coleccionistas' );
$checks['branded_reset_url'] = $access_page && str_contains( $reset_message, get_permalink( $access_page ) ) && ! str_contains( $reset_message, 'wp-login.php' );
update_user_meta( $user_id, 'gmr_collector_status', 'suspended' );
$blocked = GMR_Core_Collectors::block_inactive_login( $user, $login, 'unused' );
$checks['suspended_blocked'] = is_wp_error( $blocked ) && 'gmr_collector_inactive' === $blocked->get_error_code();
$query = GMR_Core_Collectors::filter_product_api_query( array() );
$checks['api_visibility_filter'] = ! empty( $query['meta_query'] );

require_once ABSPATH . 'wp-admin/includes/user.php';
wp_delete_user( $user_id );
$checks['cleanup'] = false === get_userdata( $user_id );
$ok = ! in_array( false, $checks, true );
echo wp_json_encode( array( 'ok'=>$ok, 'checks'=>$checks ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) . PHP_EOL;
if ( ! $ok ) exit( 1 );
