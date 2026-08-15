<?php
/** Executable visibility matrix. Creates and removes isolated fixtures. */
defined( 'ABSPATH' ) || exit( 1 );

$created_posts = array(); $created_terms = array(); $created_users = array(); $checks = array();
try {
	$collector_id = wp_insert_user( array( 'user_login'=>'gmr-visibility-' . wp_generate_password( 8, false, false ), 'user_email'=>wp_generate_uuid4().'@example.invalid', 'user_pass'=>wp_generate_password(24), 'role'=>'gmr_collector' ) );
	$created_users[] = $collector_id;
	$manager = get_users( array( 'role'=>'administrator', 'number'=>1 ) )[0] ?? null;
	foreach ( array( 'public', 'collectors', 'hidden' ) as $visibility ) {
		$id = wp_insert_post( array( 'post_type'=>'product', 'post_status'=>'publish', 'post_title'=>'GMR visibility '.$visibility.' '.wp_generate_uuid4() ) );
		$created_posts[ $visibility ] = $id; update_post_meta( $id, 'gmr_visibility', $visibility ); update_post_meta( $id, 'gmr_price_visibility', 'collectors' ); update_post_meta( $id, '_price', '1234' ); update_post_meta( $id, '_regular_price', '1234' );
	}
	$term = wp_insert_term( 'GMR private collection ' . wp_generate_password( 6, false, false ), 'gmr_collection', array( 'slug'=>'gmr-private-'.wp_generate_password(6,false,false) ) );
	$term_id = (int) $term['term_id']; $created_terms[] = $term_id; update_term_meta( $term_id, 'gmr_visibility', 'collectors' ); wp_set_object_terms( $created_posts['collectors'], array($term_id), 'gmr_collection' );

	wp_set_current_user( 0 );
	$checks['visitor_public'] = GMR_Core_Access::can_view( $created_posts['public'] );
	$checks['visitor_collectors_denied'] = ! GMR_Core_Access::can_view( $created_posts['collectors'] );
	$checks['visitor_hidden_denied'] = ! GMR_Core_Access::can_view( $created_posts['hidden'] );
	$checks['visitor_collection_denied'] = ! GMR_Core_Access::can_view_term( $term_id );
	$checks['visitor_price_denied'] = ! GMR_Core_Access::can_view_price( $created_posts['public'] );
	$markup = GMR_Core_Catalog::filter_structured_data( array( 'offers'=>array('price'=>'1234'), 'price'=>'1234' ), wc_get_product( $created_posts['public'] ) );
	$checks['structured_price_removed'] = ! isset( $markup['offers'], $markup['price'] );
	$rest = GMR_Core_Access::filter_rest_query( array() ); $checks['rest_filtered'] = ! empty( $rest['meta_query'] );
	$sitemap = GMR_Core_Access::filter_sitemap_posts( array(), 'product' ); $checks['sitemap_filtered'] = ! empty( $sitemap['meta_query'] );
	$checks['users_sitemap_removed'] = false === GMR_Core_Access::remove_user_sitemap( new stdClass(), 'users' );

	wp_set_current_user( $collector_id );
	$checks['collector_public'] = GMR_Core_Access::can_view( $created_posts['public'] );
	$checks['collector_private'] = GMR_Core_Access::can_view( $created_posts['collectors'] );
	$checks['collector_hidden_denied'] = ! GMR_Core_Access::can_view( $created_posts['hidden'] );
	$checks['collector_collection'] = GMR_Core_Access::can_view_term( $term_id );
	$checks['collector_price'] = GMR_Core_Access::can_view_price( $created_posts['public'] );

	if ( $manager ) { wp_set_current_user( $manager->ID ); $checks['admin_hidden'] = GMR_Core_Access::can_view( $created_posts['hidden'] ); }
} finally {
	wp_set_current_user( 0 );
	foreach ( $created_posts as $id ) wp_delete_post( $id, true );
	foreach ( $created_terms as $id ) wp_delete_term( $id, 'gmr_collection' );
	require_once ABSPATH . 'wp-admin/includes/user.php'; foreach ( $created_users as $id ) if ( ! is_wp_error($id) ) wp_delete_user( $id );
}
$checks['fixtures_removed'] = ! array_filter( $created_posts, 'get_post' );
$ok = ! in_array( false, $checks, true ); echo wp_json_encode( array('ok'=>$ok,'checks'=>$checks), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE ) . PHP_EOL; if(!$ok) exit(1);
