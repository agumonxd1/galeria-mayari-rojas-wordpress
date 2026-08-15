<?php
defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', function() {
	load_theme_textdomain( 'mayari-rojas', get_template_directory() . '/languages' );
	add_theme_support( 'title-tag' ); add_theme_support( 'post-thumbnails' ); add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'woocommerce', array( 'thumbnail_image_width' => 720, 'single_image_width' => 1400 ) );
	register_nav_menus( array( 'primary' => 'Navegacion principal', 'footer' => 'Navegacion del pie' ) );
} );

add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_style( 'gmr-fonts', 'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Inter:wght@400;500&display=swap', array(), null );
	wp_enqueue_style( 'gmr-theme', get_stylesheet_uri(), array( 'gmr-fonts' ), wp_get_theme()->get( 'Version' ) );
	wp_enqueue_script( 'gmr-theme', get_template_directory_uri() . '/assets/theme.js', array(), wp_get_theme()->get( 'Version' ), true );
} );

add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );
add_filter( 'woocommerce_show_page_title', '__return_false' );
add_filter( 'template_include', function( string $template ): string {
	if ( is_front_page() ) {
		$front = get_theme_file_path( 'front-page.php' );
		if ( is_readable( $front ) ) return $front;
	}
	return $template;
}, 99 );
add_action( 'pre_get_posts', function( WP_Query $query ) {
	if ( is_admin() || ! $query->is_main_query() ) return;
	if ( $query->is_post_type_archive( 'product' ) || $query->is_tax( array( 'product_cat', 'gmr_artist', 'gmr_collection' ) ) ) {
		$query->set( 'posts_per_page', 18 );
		$query->set( 'post_type', 'product' );
	}
} );

function gmr_theme_term_names( int $post_id, string $taxonomy ): string {
	$terms = wp_get_post_terms( $post_id, $taxonomy, array( 'fields' => 'names' ) );
	return is_wp_error( $terms ) ? '' : implode( ', ', $terms );
}
function gmr_theme_year( int $post_id ): string {
	if ( get_post_meta( $post_id, 'gmr_undated', true ) ) return 'Sin fecha';
	$start = get_post_meta( $post_id, 'gmr_year_start', true ); $end = get_post_meta( $post_id, 'gmr_year_end', true );
	return $start ? $start . ( $end ? '–' . $end : '' ) : '';
}
function gmr_theme_dimensions( int $post_id ): string {
	$diameter = get_post_meta( $post_id, 'gmr_diameter', true ); if ( '' !== (string) $diameter ) return 'Ø ' . $diameter . ' cm';
	$values = array_filter( array( get_post_meta( $post_id, 'gmr_height', true ), get_post_meta( $post_id, 'gmr_width', true ), get_post_meta( $post_id, 'gmr_depth', true ) ), static fn( $v ) => '' !== (string) $v );
	return $values ? implode( ' × ', $values ) . ' cm' : '';
}
function gmr_theme_can_view_price( int $post_id ): bool {
	$visibility = get_post_meta( $post_id, 'gmr_price_visibility', true ) ?: 'collectors';
	if ( current_user_can( 'gmr_manage_artworks' ) || current_user_can( 'manage_woocommerce' ) ) return true;
	if ( 'public' === $visibility ) return true;
	return 'collectors' === $visibility && current_user_can( 'gmr_view_private_prices' );
}
function gmr_theme_visibility_meta_query(): array {
	$allowed = current_user_can( 'gmr_view_collector_catalog' ) ? array( 'public', 'collectors' ) : array( 'public' );
	return array( 'relation' => 'OR', array( 'key' => 'gmr_visibility', 'value' => $allowed, 'compare' => 'IN' ), array( 'key' => 'gmr_visibility', 'compare' => 'NOT EXISTS' ) );
}
function gmr_theme_menu_fallback(): void {
	$items = array( 'La galeria' => home_url( '/la-galeria/' ), 'Elmar Rojas' => home_url( '/elmar-rojas/' ), 'Artistas' => home_url( '/artistas/' ), 'Catalogo' => get_post_type_archive_link( 'product' ), 'Colecciones' => home_url( '/colecciones/' ), 'Agenda' => get_post_type_archive_link( 'gmr_event' ) );
	echo '<ul>'; foreach ( $items as $label => $url ) printf( '<li><a href="%s">%s</a></li>', esc_url( $url ), esc_html( $label ) ); echo '</ul>';
}
