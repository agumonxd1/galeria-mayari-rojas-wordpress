<?php
defined( 'ABSPATH' ) || exit;
$product_ids = get_posts( array( 'post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids' ) );
$html = $product_ids ? do_shortcode( '[gmr_artwork_inquiry product_id="' . $product_ids[0] . '"]' ) : '';
echo wp_json_encode( array(
	'post_type' => post_type_exists( 'gmr_inquiry' ),
	'shortcode' => shortcode_exists( 'gmr_artwork_inquiry' ),
	'public_hook' => false !== has_action( 'admin_post_nopriv_gmr_submit_inquiry', array( GMR_Core_Inquiry::class, 'submit' ) ),
	'product_id' => $product_ids[0] ?? 0,
	'has_nonce' => str_contains( $html, 'gmr_inquiry_nonce' ),
	'has_reference' => str_contains( $html, 'gmr-inquiry__reference' ),
	'has_honeypot' => str_contains( $html, 'gmr-honeypot' ),
) ) . "\n";
