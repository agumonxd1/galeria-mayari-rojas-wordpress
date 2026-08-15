<?php defined( 'ABSPATH' ) || exit; $product = wc_get_product( get_the_ID() ); ?>
<article <?php post_class( 'gmr-card' ); ?>><a href="<?php the_permalink(); ?>">
<div class="gmr-card__media"><?php if ( has_post_thumbnail() ) the_post_thumbnail( 'large' ); else echo wc_placeholder_img( 'large' ); ?></div>
<div class="gmr-card__meta"><div><h2><?php the_title(); ?></h2><p><?php echo esc_html( trim( gmr_theme_term_names( get_the_ID(), 'gmr_artist' ) . ( gmr_theme_year( get_the_ID() ) ? ' · ' . gmr_theme_year( get_the_ID() ) : '' ), ' ·' ) ); ?></p></div><span class="gmr-card__status"><?php echo esc_html( str_replace( '_', ' ', get_post_meta( get_the_ID(), 'gmr_commercial_status', true ) ?: 'disponible' ) ); ?></span></div>
</a></article>
