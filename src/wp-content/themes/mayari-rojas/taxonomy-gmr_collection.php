<?php
get_header();
$term = get_queried_object();
$cover = gmr_theme_collection_cover_id( $term );
$subtitle = get_term_meta( $term->term_id, 'gmr_collection_subtitle', true );
$period = gmr_theme_collection_period( $term->term_id );
$text = get_term_meta( $term->term_id, 'gmr_collection_text', true );
$artist_ids = array_filter( array_map( 'absint', (array) get_term_meta( $term->term_id, 'gmr_collection_artists', true ) ) );
$count = gmr_theme_visible_work_count( 'gmr_collection', $term->term_id );
?>
<article class="gmr-collection-single"><header class="gmr-collection-hero">
	<div class="gmr-collection-hero__image"><?php echo wp_get_attachment_image( $cover, 'full', false, array( 'alt' => $term->name ) ); ?><i aria-hidden="true"></i></div>
	<div class="gmr-wrap gmr-collection-hero__content">
		<a class="gmr-collection-back" href="<?php echo esc_url( gmr_theme_page_url( 'colecciones' ) ); ?>">← Todas las colecciones</a>
		<div><span class="gmr-kicker">Colección<?php echo $period ? ' · ' . esc_html( $period ) : ''; ?></span><h1><?php echo esc_html( $term->name ); ?></h1><?php if ( $subtitle ) : ?><p><?php echo esc_html( $subtitle ); ?></p><?php endif; ?></div>
		<span class="gmr-collection-hero__count"><?php echo esc_html( $count . ( 1 === $count ? ' obra' : ' obras' ) ); ?></span>
	</div>
</header>
<?php if ( $text || $artist_ids ) : ?><section class="gmr-collection-story"><div class="gmr-wrap">
	<div><span class="gmr-kicker">Lectura curatorial</span><h2>Sobre la colección</h2></div>
	<div class="gmr-editorial"><?php echo $text ? wp_kses_post( wpautop( $text ) ) : '<p>Una selección del archivo de Galería Mayarí Rojas reunida por sus afinidades plásticas, simbólicas y temporales.</p>'; ?>
	<?php if ( $artist_ids ) : ?><div class="gmr-collection-artists"><span>Artistas relacionados</span><?php foreach ( $artist_ids as $artist_id ) : $artist = get_term( $artist_id, 'gmr_artist' ); if ( ! $artist || is_wp_error( $artist ) ) continue; ?><a href="<?php echo esc_url( gmr_theme_artist_url( $artist ) ); ?>"><?php echo esc_html( $artist->name ); ?></a><?php endforeach; ?></div><?php endif; ?></div>
</div></section><?php endif; ?>
<section class="gmr-collection-works"><div class="gmr-wrap">
	<div class="gmr-section__head"><div><span class="gmr-kicker">Catálogo relacionado</span><h2>Obras de la colección</h2></div><span class="gmr-collection-total"><?php echo esc_html( $count ); ?> piezas</span></div>
	<?php if ( have_posts() ) : ?><div class="gmr-grid"><?php while ( have_posts() ) : the_post(); get_template_part( 'template-parts/artwork', 'card' ); endwhile; ?></div><?php the_posts_pagination(); else : ?><p class="gmr-empty">No hay obras visibles en esta colección.</p><?php endif; ?>
</div></section></article>
<?php get_footer(); ?>
