<?php
get_header();
$active_term = is_tax( 'gmr_event_type' ) ? get_queried_object() : null;
$active_slug = $active_term instanceof WP_Term ? $active_term->slug : 'all';
$all_events = new WP_Query( array(
	'post_type' => 'gmr_event', 'post_status' => 'publish', 'posts_per_page' => -1,
	'meta_key' => 'gmr_event_start', 'orderby' => 'meta_value', 'order' => 'ASC',
	'meta_query' => array( gmr_theme_visibility_meta_query() ),
) );
$events = $all_events->posts;
$upcoming = array_values( array_filter( $events, static fn( $event ) => gmr_theme_event_is_upcoming( $event->ID ) ) );
$past = array_values( array_filter( $events, static fn( $event ) => ! gmr_theme_event_is_upcoming( $event->ID ) ) );
$archive_mode = ! $upcoming;
$terms = get_terms( array( 'taxonomy' => 'gmr_event_type', 'hide_empty' => true ) );
$terms = is_wp_error( $terms ) ? array() : $terms;
$active_label = $active_term instanceof WP_Term ? $active_term->name : 'Agenda';
?>
<header class="gmr-agenda-head"><div class="gmr-wrap">
	<div><span class="gmr-kicker"><?php echo $archive_mode ? 'Memoria cultural' : 'Exposiciones, encuentros y talleres'; ?></span><h1 data-agenda-title><?php echo esc_html( $active_label ); ?></h1></div>
	<div class="gmr-agenda-head__intro"><p><?php echo $archive_mode ? 'Un archivo vivo de exposiciones, encuentros y experiencias que han formado parte de la historia de la galería.' : 'Encuentros y experiencias para acercarse al arte, sus procesos y sus creadores.'; ?></p><span><b data-agenda-count><?php echo esc_html( count( $events ) ); ?></b> actividades visibles</span></div>
	<nav class="gmr-agenda-filters" aria-label="Filtrar agenda" data-dynamic data-active="<?php echo esc_attr( $active_slug ); ?>"><a class="<?php echo 'all' === $active_slug ? 'is-current' : ''; ?>" data-filter="all" data-label="Agenda" href="<?php echo esc_url( get_post_type_archive_link( 'gmr_event' ) ); ?>">Todo</a><?php foreach ( $terms as $term ) : ?><a class="<?php echo $active_slug === $term->slug ? 'is-current' : ''; ?>" data-filter="<?php echo esc_attr( $term->slug ); ?>" data-label="<?php echo esc_attr( $term->name ); ?>" href="<?php echo esc_url( get_term_link( $term ) ); ?>"><?php echo esc_html( $term->name ); ?></a><?php endforeach; ?></nav>
</div></header>
<?php if ( $upcoming ) : ?><section class="gmr-agenda-section gmr-agenda-section--upcoming" data-event-section><div class="gmr-wrap"><div class="gmr-agenda-section__head"><span class="gmr-kicker">Próximamente</span><h2>Reserve la fecha</h2></div><div class="gmr-agenda-grid"><?php foreach ( $upcoming as $index => $event ) : $GLOBALS['post'] = $event; setup_postdata( $event ); get_template_part( 'template-parts/event', 'card', array( 'featured' => 0 === $index ) ); endforeach; wp_reset_postdata(); ?></div></div></section><?php endif; ?>
<?php if ( $past ) : ?><section class="gmr-agenda-section gmr-agenda-section--archive" data-event-section><div class="gmr-wrap"><div class="gmr-agenda-section__head"><span class="gmr-kicker">Archivo</span><h2><?php echo $archive_mode ? 'Historias que permanecen' : 'Eventos anteriores'; ?></h2></div><div class="gmr-agenda-grid"><?php foreach ( array_reverse( $past ) as $index => $event ) : $GLOBALS['post'] = $event; setup_postdata( $event ); get_template_part( 'template-parts/event', 'card', array( 'featured' => $archive_mode && 0 === $index ) ); endforeach; wp_reset_postdata(); ?></div></div></section><?php elseif ( ! $upcoming ) : ?><section class="gmr-agenda-empty"><div class="gmr-wrap"><p>Pronto anunciaremos nuevas actividades.</p></div></section><?php endif; ?>
<div class="gmr-agenda-no-results" hidden><div class="gmr-wrap"><p>No hay actividades visibles en esta categoría.</p></div></div>
<?php get_footer(); ?>
