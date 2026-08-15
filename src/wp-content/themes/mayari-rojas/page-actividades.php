<?php
get_header();
$activities = new WP_Query( array( 'post_type' => 'gmr_event', 'post_status' => 'publish', 'posts_per_page' => 12, 'tax_query' => array( array( 'taxonomy' => 'gmr_event_type', 'field' => 'slug', 'terms' => array( 'actividad', 'taller', 'conversatorio' ) ) ), 'meta_key' => 'gmr_event_start', 'orderby' => 'meta_value', 'order' => 'DESC', 'meta_query' => array( gmr_theme_visibility_meta_query() ) ) );
?>
<header class="gmr-agenda-head"><div class="gmr-wrap"><div><span class="gmr-kicker">Aprendizaje y encuentro</span><h1>Actividades</h1></div><div class="gmr-agenda-head__intro"><p>Talleres, conversaciones y experiencias para acercarse al arte y sus creadores.</p><a href="<?php echo esc_url( get_post_type_archive_link( 'gmr_event' ) ); ?>">Ver agenda completa ↗</a></div></div></header>
<section class="gmr-agenda-section gmr-agenda-section--archive"><div class="gmr-wrap"><div class="gmr-agenda-grid"><?php if ( $activities->have_posts() ) : $index = 0; while ( $activities->have_posts() ) : $activities->the_post(); get_template_part( 'template-parts/event', 'card', array( 'featured' => 0 === $index++ ) ); endwhile; else : ?><p class="gmr-empty">Pronto anunciaremos nuevas actividades.</p><?php endif; wp_reset_postdata(); ?></div></div></section>
<?php get_footer(); ?>
