<?php
get_header();
$active_term = is_tax( 'gmr_media_topic' ) ? get_queried_object() : null;
$active_slug = $active_term instanceof WP_Term ? $active_term->slug : 'all';
$active_label = $active_term instanceof WP_Term ? $active_term->name : 'Archivo multimedia';
$galleries_query = new WP_Query( array( 'post_type' => 'gmr_media_gallery', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'menu_order date', 'order' => 'DESC', 'meta_query' => array( gmr_theme_visibility_meta_query() ) ) );
$galleries = $galleries_query->posts;
$topics = get_terms( array( 'taxonomy' => 'gmr_media_topic', 'hide_empty' => true ) );
$topics = is_wp_error( $topics ) ? array() : $topics;
?>
<header class="gmr-media-head"><div class="gmr-wrap">
	<div><span class="gmr-kicker">Fotografía, video y memoria documental</span><h1 data-media-title><?php echo esc_html( $active_label ); ?></h1></div>
	<div class="gmr-media-head__intro"><p>Relatos visuales sobre exposiciones, artistas, procesos y momentos que forman la memoria de la galería.</p><span><b data-media-count><?php echo esc_html( count( $galleries ) ); ?></b> galerías visibles</span></div>
	<?php if ( $topics ) : ?><nav class="gmr-media-filters" aria-label="Filtrar archivo multimedia" data-dynamic data-active="<?php echo esc_attr( $active_slug ); ?>"><a class="<?php echo 'all' === $active_slug ? 'is-current' : ''; ?>" data-filter="all" data-label="Archivo multimedia" href="<?php echo esc_url( get_post_type_archive_link( 'gmr_media_gallery' ) ); ?>">Todo</a><?php foreach ( $topics as $topic ) : ?><a class="<?php echo $active_slug === $topic->slug ? 'is-current' : ''; ?>" data-filter="<?php echo esc_attr( $topic->slug ); ?>" data-label="<?php echo esc_attr( $topic->name ); ?>" href="<?php echo esc_url( get_term_link( $topic ) ); ?>"><?php echo esc_html( $topic->name ); ?></a><?php endforeach; ?></nav><?php endif; ?>
</div></header>
<?php if ( $galleries ) : ?><section class="gmr-media-index"><div class="gmr-wrap gmr-media-bento" data-media-grid><?php foreach ( $galleries as $index => $gallery ) : $GLOBALS['post'] = $gallery; setup_postdata( $gallery ); get_template_part( 'template-parts/media', 'card', array( 'index' => $index ) ); endforeach; wp_reset_postdata(); ?></div></section><div class="gmr-media-no-results" hidden><div class="gmr-wrap"><p>No hay galerías visibles en este tema.</p></div></div>
<?php else : ?><section class="gmr-media-empty"><div class="gmr-wrap"><span class="gmr-kicker">Archivo en construcción</span><h2>La memoria también se reúne poco a poco.</h2><p>Estamos preparando recorridos fotográficos y documentales para compartirlos en este espacio.</p><?php if ( current_user_can( 'gmr_manage_agenda' ) || current_user_can( 'edit_posts' ) ) : ?><a class="gmr-button" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=gmr_media_gallery' ) ); ?>">Crear primera galería</a><?php endif; ?></div></section><?php endif; ?>
<?php get_footer(); ?>
