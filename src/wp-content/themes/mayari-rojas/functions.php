<?php
defined( 'ABSPATH' ) || exit;
require_once get_theme_file_path( 'inc/customizer.php' );

add_action( 'after_setup_theme', function() {
	load_theme_textdomain( 'mayari-rojas', get_template_directory() . '/languages' );
	add_theme_support( 'title-tag' ); add_theme_support( 'post-thumbnails' ); add_theme_support( 'automatic-feed-links' ); add_theme_support( 'custom-logo', array( 'height'=>180, 'width'=>600, 'flex-height'=>true, 'flex-width'=>true ) );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'woocommerce', array( 'thumbnail_image_width' => 720, 'single_image_width' => 1400 ) );
	register_nav_menus( array( 'primary' => 'Navegacion principal', 'footer' => 'Navegacion del pie' ) );
} );

add_action( 'wp_enqueue_scripts', function() {
	$heading=rawurlencode(gmr_theme_mod('gmr_font_headings'));$body=rawurlencode(gmr_theme_mod('gmr_font_body'));
	wp_enqueue_style( 'gmr-fonts', 'https://fonts.googleapis.com/css2?family='.$heading.':wght@400;500;600&family='.$body.':wght@400;500;600&display=swap', array(), null );
	wp_enqueue_style( 'gmr-theme', get_stylesheet_uri(), array( 'gmr-fonts' ), wp_get_theme()->get( 'Version' ) );
	wp_enqueue_style( 'gmr-editorial', get_template_directory_uri() . '/assets/editorial.css', array( 'gmr-theme' ), wp_get_theme()->get( 'Version' ) );
	wp_enqueue_style( 'gmr-voices', get_template_directory_uri() . '/assets/voices.css', array( 'gmr-editorial' ), wp_get_theme()->get( 'Version' ) );
	wp_enqueue_style( 'gmr-elmar', get_template_directory_uri() . '/assets/elmar.css', array( 'gmr-voices' ), wp_get_theme()->get( 'Version' ) );
	wp_enqueue_style( 'gmr-inquiry', get_template_directory_uri() . '/assets/inquiry.css', array( 'gmr-elmar' ), wp_get_theme()->get( 'Version' ) );
	wp_enqueue_style( 'gmr-institution', get_template_directory_uri() . '/assets/institution.css', array( 'gmr-inquiry' ), wp_get_theme()->get( 'Version' ) );
	wp_enqueue_style( 'gmr-design-system', get_template_directory_uri() . '/assets/design-system.css', array( 'gmr-institution' ), wp_get_theme()->get( 'Version' ) );
	if ( is_front_page() ) wp_enqueue_style( 'gmr-home', get_template_directory_uri() . '/assets/home.css', array( 'gmr-design-system' ), wp_get_theme()->get( 'Version' ) );
	if ( is_post_type_archive('product') || is_tax(array('product_cat','gmr_artist','gmr_collection')) || is_singular('product') ) wp_enqueue_style( 'gmr-catalog', get_template_directory_uri() . '/assets/catalog.css', array( 'gmr-design-system' ), wp_get_theme()->get( 'Version' ) );
	wp_enqueue_script( 'gmr-theme', get_template_directory_uri() . '/assets/theme.js', array(), wp_get_theme()->get( 'Version' ), true );
} );

add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );
add_filter( 'woocommerce_show_page_title', '__return_false' );
add_filter( 'template_include', function( string $template ): string {
	if ( is_front_page() ) {
		$front = get_theme_file_path( 'front-page.php' );
		if ( is_readable( $front ) ) return $front;
	}
	foreach ( array( 'artistas', 'elmar-rojas', 'colecciones', 'coleccionistas', 'actividades', 'noticias', 'la-galeria', 'contacto' ) as $slug ) {
		if ( is_page( $slug ) ) {
			$editorial = get_theme_file_path( 'page-' . $slug . '.php' );
			if ( is_readable( $editorial ) ) return $editorial;
		}
	}
	return $template;
}, 99 );
add_action( 'pre_get_posts', function( WP_Query $query ) {
	if ( is_admin() || ! $query->is_main_query() ) return;
	if ( $query->is_post_type_archive( 'product' ) || $query->is_tax( array( 'product_cat', 'gmr_artist', 'gmr_collection' ) ) ) {
		$query->set( 'posts_per_page', 18 );
		$query->set( 'post_type', 'product' );
		if ( ! empty( $_GET['artista'] ) ) {
			$query->set( 'tax_query', array( array( 'taxonomy' => 'gmr_artist', 'field' => 'slug', 'terms' => sanitize_title( wp_unslash( $_GET['artista'] ) ) ) ) );
		}
		if ( ! empty( $_GET['estado'] ) ) {
			$query->set( 'meta_query', array( gmr_theme_visibility_meta_query(), array( 'key' => 'gmr_commercial_status', 'value' => sanitize_key( wp_unslash( $_GET['estado'] ) ) ) ) );
		}
	}
	if ( $query->is_post_type_archive( 'gmr_event' ) || $query->is_tax( 'gmr_event_type' ) ) {
		$query->set( 'posts_per_page', 12 );
		$query->set( 'meta_key', 'gmr_event_start' );
		$query->set( 'orderby', 'meta_value' );
		$query->set( 'order', 'ASC' );
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
	return class_exists( 'GMR_Core_Access' ) ? GMR_Core_Access::visibility_meta_query() : array( 'relation'=>'OR', array( 'key'=>'gmr_visibility','value'=>'public' ), array( 'key'=>'gmr_visibility','compare'=>'NOT EXISTS' ) );
}
function gmr_theme_can_view_term( int $term_id ): bool { return ! class_exists( 'GMR_Core_Access' ) || GMR_Core_Access::can_view_term( $term_id ); }
function gmr_theme_visible_work_count( string $taxonomy, int $term_id ): int {
	$query = new WP_Query( array( 'post_type'=>'product', 'post_status'=>'publish', 'fields'=>'ids', 'posts_per_page'=>1, 'no_found_rows'=>false, 'tax_query'=>array( array( 'taxonomy'=>$taxonomy, 'field'=>'term_id', 'terms'=>$term_id ) ), 'meta_query'=>array( gmr_theme_visibility_meta_query() ) ) );
	return (int) $query->found_posts;
}
function gmr_theme_page_url( string $slug ): string {
	$page = get_page_by_path( $slug );
	return $page instanceof WP_Post ? get_permalink( $page ) : home_url( '/' . trim( $slug, '/' ) . '/' );
}
function gmr_theme_event_date( int $post_id ): string {
	$value = get_post_meta( $post_id, 'gmr_event_start', true );
	if ( ! $value ) return '';
	$timestamp = strtotime( $value );
	return $timestamp ? wp_date( 'j M Y', $timestamp ) : $value;
}
function gmr_theme_event_types( int $post_id ): string {
	return gmr_theme_term_names( $post_id, 'gmr_event_type' );
}
function gmr_theme_commercial_labels():array{return array('available'=>'Disponible','reserved'=>'Reservada','sold'=>'Vendida','not_available'=>'No disponible','on_exhibition'=>'En exposición','archive'=>'Archivo');}
function gmr_theme_commercial_label(string $status):string{$labels=gmr_theme_commercial_labels();return$labels[$status]??ucfirst(str_replace('_',' ',$status));}
function gmr_theme_edition_label(int $post_id):string{$number=trim((string)get_post_meta($post_id,'gmr_edition_number',true));$size=trim((string)get_post_meta($post_id,'gmr_edition_size',true));if($number&&$size)return$number.' de '.$size;if($number)return$number;if($size)return'Tiraje de '.$size;return'';}
function gmr_theme_signature_label(int $post_id):string{$status=get_post_meta($post_id,'gmr_signature_status',true);$labels=array('signed'=>'Firmada','unsigned'=>'Sin firma','attributed'=>'Atribuida','unknown'=>'');$label=$labels[$status]??'';$location=trim((string)get_post_meta($post_id,'gmr_signature_location',true));return trim($label.($label&&$location?' · ':'').$location);}
function gmr_theme_certificate_label(int $post_id):string{$labels=array('included'=>'Incluido','available'=>'Disponible','not_available'=>'No disponible','unknown'=>'');return$labels[get_post_meta($post_id,'gmr_certificate_status',true)]??'';}
function gmr_theme_order_terms( array $terms, string $meta_key ): array {
	usort( $terms, static function( WP_Term $a, WP_Term $b ) use ( $meta_key ): int {
		$a_order = (int) get_term_meta( $a->term_id, $meta_key, true );
		$b_order = (int) get_term_meta( $b->term_id, $meta_key, true );
		if ( $a_order === $b_order ) return strcasecmp( $a->name, $b->name );
		if ( 0 === $a_order ) return 1;
		if ( 0 === $b_order ) return -1;
		return $a_order <=> $b_order;
	} );
	return $terms;
}
function gmr_theme_menu_fallback(): void {
	$items = array( 'La galeria' => gmr_theme_page_url( 'la-galeria' ), 'Elmar Rojas' => gmr_theme_page_url( 'elmar-rojas' ), 'Artistas' => gmr_theme_page_url( 'artistas' ), 'Catalogo' => get_post_type_archive_link( 'product' ), 'Colecciones' => gmr_theme_page_url( 'colecciones' ), 'Agenda' => get_post_type_archive_link( 'gmr_event' ) );
	echo '<ul>'; foreach ( $items as $label => $url ) printf( '<li><a href="%s">%s</a></li>', esc_url( $url ), esc_html( $label ) ); echo '</ul>';
}
