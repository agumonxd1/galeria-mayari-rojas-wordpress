<?php
get_header();
$collections = get_terms( array( 'taxonomy' => 'gmr_collection', 'hide_empty' => false ) );
$collections = is_wp_error( $collections ) ? array() : gmr_theme_order_terms( $collections, 'gmr_collection_order' );
$visible = array();
foreach ( $collections as $collection ) {
	if ( ! gmr_theme_can_view_term( $collection->term_id ) ) continue;
	$count = gmr_theme_visible_work_count( 'gmr_collection', $collection->term_id );
	if ( ! $count && ! current_user_can( 'gmr_manage_artworks' ) ) continue;
	$visible[] = array( 'term' => $collection, 'count' => $count );
}
?>
<header class="gmr-collections-head"><div class="gmr-wrap">
	<div><span class="gmr-kicker">Archivo curatorial</span><h1>Colecciones</h1></div>
	<div class="gmr-collections-head__intro"><p>Series y conjuntos para recorrer los temas, periodos y lenguajes que habitan el acervo de la galería.</p><span><?php echo esc_html( count( $visible ) ); ?> recorridos</span></div>
</div></header>
<section class="gmr-collections-index"><div class="gmr-wrap gmr-collections-bento">
	<?php foreach ( $visible as $index => $item ) : $collection = $item['term']; $subtitle = get_term_meta( $collection->term_id, 'gmr_collection_subtitle', true ); $period = gmr_theme_collection_period( $collection->term_id ); ?>
	<article class="gmr-collection-tile gmr-collection-tile--<?php echo esc_attr( ( $index % 6 ) + 1 ); ?>"><a href="<?php echo esc_url( get_term_link( $collection ) ); ?>">
		<?php echo wp_get_attachment_image( gmr_theme_collection_cover_id( $collection ), 'large', false, array( 'alt' => $collection->name ) ); ?><i aria-hidden="true"></i>
		<div class="gmr-collection-tile__top"><span><?php echo esc_html( $period ?: 'Colección' ); ?></span><span><?php echo esc_html( $item['count'] . ( 1 === $item['count'] ? ' obra' : ' obras' ) ); ?></span></div>
		<div class="gmr-collection-tile__copy"><h2><?php echo esc_html( $collection->name ); ?></h2><?php if ( $subtitle ) : ?><p><?php echo esc_html( $subtitle ); ?></p><?php endif; ?><span>Explorar colección ↗</span></div>
	</a></article>
	<?php endforeach; ?>
</div></section>
<?php get_footer(); ?>
