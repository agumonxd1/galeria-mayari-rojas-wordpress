<?php
get_header();
$events = array();
if ( have_posts() ) while ( have_posts() ) { the_post(); $events[] = get_post(); }
wp_reset_postdata();
$upcoming = array_values( array_filter( $events, static fn( $event ) => gmr_theme_event_is_upcoming( $event->ID ) ) );
$past = array_values( array_filter( $events, static fn( $event ) => ! gmr_theme_event_is_upcoming( $event->ID ) ) );
$archive_mode = ! $upcoming;
$terms = get_terms( array( 'taxonomy' => 'gmr_event_type', 'hide_empty' => true ) );
$terms = is_wp_error( $terms ) ? array() : $terms;
?>
<header class="gmr-agenda-head"><div class="gmr-wrap">
	<div><span class="gmr-kicker"><?php echo $archive_mode ? 'Memoria cultural' : 'Exposiciones, encuentros y talleres'; ?></span><h1><?php echo is_tax( 'gmr_event_type' ) ? esc_html( single_term_title( '', false ) ) : 'Agenda'; ?></h1></div>
	<div class="gmr-agenda-head__intro"><p><?php echo $archive_mode ? 'Un archivo vivo de exposiciones, encuentros y experiencias que han formado parte de la historia de la galería.' : 'Encuentros y experiencias para acercarse al arte, sus procesos y sus creadores.'; ?></p><span><?php echo esc_html( count( $events ) ); ?> actividades visibles</span></div>
	<nav class="gmr-agenda-filters" aria-label="Filtrar agenda"><a class="<?php echo is_post_type_archive( 'gmr_event' ) ? 'is-current' : ''; ?>" href="<?php echo esc_url( get_post_type_archive_link( 'gmr_event' ) ); ?>">Todo</a><?php foreach ( $terms as $term ) : ?><a class="<?php echo is_tax( 'gmr_event_type', $term->slug ) ? 'is-current' : ''; ?>" href="<?php echo esc_url( get_term_link( $term ) ); ?>"><?php echo esc_html( $term->name ); ?></a><?php endforeach; ?></nav>
</div></header>
<?php if ( $upcoming ) : ?><section class="gmr-agenda-section gmr-agenda-section--upcoming"><div class="gmr-wrap"><div class="gmr-agenda-section__head"><span class="gmr-kicker">Próximamente</span><h2>Reserve la fecha</h2></div><div class="gmr-agenda-grid"><?php foreach ( $upcoming as $index => $event ) : setup_postdata( $event ); get_template_part( 'template-parts/event', 'card', array( 'featured' => 0 === $index ) ); endforeach; wp_reset_postdata(); ?></div></div></section><?php endif; ?>
<?php if ( $past ) : ?><section class="gmr-agenda-section gmr-agenda-section--archive"><div class="gmr-wrap"><div class="gmr-agenda-section__head"><span class="gmr-kicker">Archivo</span><h2><?php echo $archive_mode ? 'Historias que permanecen' : 'Eventos anteriores'; ?></h2></div><div class="gmr-agenda-grid"><?php foreach ( array_reverse( $past ) as $index => $event ) : setup_postdata( $event ); get_template_part( 'template-parts/event', 'card', array( 'featured' => $archive_mode && 0 === $index ) ); endforeach; wp_reset_postdata(); ?></div></div></section><?php elseif ( ! $upcoming ) : ?><section class="gmr-agenda-empty"><div class="gmr-wrap"><p>Pronto anunciaremos nuevas actividades.</p></div></section><?php endif; ?>
<?php get_footer(); ?>
