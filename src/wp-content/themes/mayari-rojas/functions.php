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
	if ( is_post_type_archive('product') || is_tax(array('product_cat','gmr_artist','gmr_collection')) || is_singular('product') || is_page('elmar-rojas') ) wp_enqueue_style( 'gmr-catalog', get_template_directory_uri() . '/assets/catalog.css', array( 'gmr-design-system' ), wp_get_theme()->get( 'Version' ) );
	if ( is_page('colecciones') || is_tax('gmr_collection') ) {
		wp_enqueue_style( 'gmr-catalog', get_template_directory_uri() . '/assets/catalog.css', array( 'gmr-design-system' ), wp_get_theme()->get( 'Version' ) );
		wp_enqueue_style( 'gmr-collections', get_template_directory_uri() . '/assets/collections.css', array( 'gmr-catalog' ), wp_get_theme()->get( 'Version' ) );
	}
	if ( is_page('artistas') || is_tax('gmr_artist') ) {
		wp_enqueue_style( 'gmr-catalog', get_template_directory_uri() . '/assets/catalog.css', array( 'gmr-design-system' ), wp_get_theme()->get( 'Version' ) );
		wp_enqueue_style( 'gmr-artists', get_template_directory_uri() . '/assets/artists.css', array( 'gmr-catalog' ), wp_get_theme()->get( 'Version' ) );
	}
	if ( is_page('elmar-rojas') ) wp_enqueue_style( 'gmr-elmar-special', get_template_directory_uri() . '/assets/elmar-special.css', array( 'gmr-catalog' ), wp_get_theme()->get( 'Version' ) );
	if ( is_post_type_archive('gmr_event') || is_tax('gmr_event_type') || is_singular('gmr_event') || is_page('actividades') ) {
		wp_enqueue_style( 'gmr-agenda', get_template_directory_uri() . '/assets/agenda.css', array( 'gmr-design-system' ), wp_get_theme()->get( 'Version' ) );
		wp_enqueue_script( 'gmr-agenda', get_template_directory_uri() . '/assets/agenda.js', array(), wp_get_theme()->get( 'Version' ), true );
	}
	if ( is_post_type_archive('gmr_media_gallery') || is_tax('gmr_media_topic') || is_singular('gmr_media_gallery') ) {
		wp_enqueue_style( 'gmr-multimedia', get_template_directory_uri() . '/assets/multimedia.css', array( 'gmr-design-system' ), wp_get_theme()->get( 'Version' ) );
		wp_enqueue_script( 'gmr-multimedia', get_template_directory_uri() . '/assets/multimedia.js', array(), wp_get_theme()->get( 'Version' ), true );
	}
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
function gmr_theme_can_view_post( int $post_id ): bool { return ! class_exists( 'GMR_Core_Access' ) || GMR_Core_Access::can_view( $post_id ); }
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
function gmr_theme_event_is_upcoming(int $post_id):bool{$status=get_post_meta($post_id,'gmr_event_status',true);if(in_array($status,array('upcoming','ongoing'),true))return true;if(in_array($status,array('finished','cancelled'),true))return false;$end=get_post_meta($post_id,'gmr_event_end',true);$start=get_post_meta($post_id,'gmr_event_start',true);$timestamp=strtotime($end?:$start);return$timestamp&&$timestamp>=current_time('timestamp');}
function gmr_theme_event_date_parts(int $post_id):array{$value=get_post_meta($post_id,'gmr_event_start',true);$timestamp=$value?strtotime($value):false;if(!$timestamp)return array('day'=>'','month'=>'','year'=>'','time'=>'');$all_day=(bool)get_post_meta($post_id,'gmr_event_all_day',true);return array('day'=>wp_date('d',$timestamp),'month'=>wp_date('M',$timestamp),'year'=>wp_date('Y',$timestamp),'time'=>$all_day?'':wp_date('H:i',$timestamp));}
function gmr_theme_event_status_label(int $post_id):string{$status=get_post_meta($post_id,'gmr_event_status',true);$labels=array('upcoming'=>'Próximo','ongoing'=>'En curso','finished'=>'Finalizado','cancelled'=>'Cancelado');return$labels[$status]??(gmr_theme_event_is_upcoming($post_id)?'Próximo':'Archivo');}
function gmr_theme_media_ids(int $post_id):array{return array_values(array_unique(array_filter(array_map('absint',explode(',',(string)get_post_meta($post_id,'gmr_media_ids',true))))));}
function gmr_theme_media_topics(int $post_id):string{return gmr_theme_term_names($post_id,'gmr_media_topic');}
function gmr_theme_commercial_labels():array{return array('available'=>'Disponible','reserved'=>'Reservada','sold'=>'Vendida','not_available'=>'No disponible','on_exhibition'=>'En exposición','archive'=>'Archivo');}
function gmr_theme_commercial_label(string $status):string{$labels=gmr_theme_commercial_labels();return$labels[$status]??ucfirst(str_replace('_',' ',$status));}
function gmr_theme_edition_label(int $post_id):string{$number=trim((string)get_post_meta($post_id,'gmr_edition_number',true));$size=trim((string)get_post_meta($post_id,'gmr_edition_size',true));if($number&&$size)return$number.' de '.$size;if($number)return$number;if($size)return'Tiraje de '.$size;return'';}
function gmr_theme_signature_label(int $post_id):string{$status=get_post_meta($post_id,'gmr_signature_status',true);$labels=array('signed'=>'Firmada','unsigned'=>'Sin firma','attributed'=>'Atribuida','unknown'=>'');$label=$labels[$status]??'';$location=trim((string)get_post_meta($post_id,'gmr_signature_location',true));return trim($label.($label&&$location?' · ':'').$location);}
function gmr_theme_certificate_label(int $post_id):string{$labels=array('included'=>'Incluido','available'=>'Disponible','not_available'=>'No disponible','unknown'=>'');return$labels[get_post_meta($post_id,'gmr_certificate_status',true)]??'';}
function gmr_theme_artist_url(WP_Term $artist):string{if('elmar-rojas'===$artist->slug||'elmar'===get_term_meta($artist->term_id,'gmr_artist_special_template',true))return gmr_theme_page_url('elmar-rojas');$url=get_term_link($artist);return is_wp_error($url)?gmr_theme_page_url('artistas'):$url;}
function gmr_theme_artist_image_id(WP_Term $artist):int{$portrait=absint(get_term_meta($artist->term_id,'gmr_artist_portrait_id',true));$cover=absint(get_term_meta($artist->term_id,'gmr_artist_cover_id',true));if($portrait||$cover)return$portrait?:$cover;$works=new WP_Query(array('post_type'=>'product','post_status'=>'publish','posts_per_page'=>1,'fields'=>'ids','tax_query'=>array(array('taxonomy'=>'gmr_artist','field'=>'term_id','terms'=>$artist->term_id)),'meta_query'=>array(gmr_theme_visibility_meta_query())));return$works->posts?absint(get_post_thumbnail_id($works->posts[0])):0;}
function gmr_theme_elmar_discipline_image(string $slug):string{$fallbacks=array('pintura'=>'https://galeriamayarirojas.com/wp-content/uploads/elementor/thumbs/da883ca0-0bcc-47cc-acec-99bdfc6c6910-rq4gswgrr0p58qt4hip8csekfi2t9etopjrodzwe36.jpg','escultura'=>'https://galeriamayarirojas.com/wp-content/uploads/2025/06/WhatsApp-Image-2025-06-09-at-6.16.10-PM.jpeg','obra-grafica'=>'https://galeriamayarirojas.com/wp-content/uploads/2025/05/obra-grafica.jpg','joyeria'=>'https://galeriamayarirojas.com/wp-content/uploads/2025/05/JOYERIA.jpg');$q=new WP_Query(array('post_type'=>'product','post_status'=>'publish','posts_per_page'=>1,'fields'=>'ids','tax_query'=>array('relation'=>'AND',array('taxonomy'=>'gmr_artist','field'=>'slug','terms'=>'elmar-rojas'),array('taxonomy'=>'product_cat','field'=>'slug','terms'=>$slug)),'meta_query'=>array(gmr_theme_visibility_meta_query())));return$q->posts?(get_the_post_thumbnail_url($q->posts[0],'large')?:($fallbacks[$slug]??'')):($fallbacks[$slug]??'');}
function gmr_theme_collection_cover_id(WP_Term $collection):int{$cover=absint(get_term_meta($collection->term_id,'gmr_collection_cover_id',true));if($cover)return$cover;$sample=get_posts(array('post_type'=>'product','post_status'=>'publish','posts_per_page'=>1,'fields'=>'ids','tax_query'=>array(array('taxonomy'=>'gmr_collection','field'=>'term_id','terms'=>$collection->term_id)),'meta_query'=>array(gmr_theme_visibility_meta_query())));return$sample?(int)get_post_thumbnail_id($sample[0]):0;}
function gmr_theme_collection_period(int $term_id):string{$start=absint(get_term_meta($term_id,'gmr_collection_year_start',true));$end=absint(get_term_meta($term_id,'gmr_collection_year_end',true));if($start&&$end&&$start!==$end)return$start.'–'.$end;return(string)($start?:$end);}
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
