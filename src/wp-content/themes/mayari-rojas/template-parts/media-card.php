<?php
$gallery_id = get_the_ID();
$topics = wp_get_post_terms( $gallery_id, 'gmr_media_topic' );
$topics = is_wp_error( $topics ) ? array() : $topics;
$topic_slugs = wp_list_pluck( $topics, 'slug' );
$image_count = count( gmr_theme_media_ids( $gallery_id ) );
$index = absint( $args['index'] ?? 0 );
?>
<article <?php post_class( 'gmr-media-card gmr-media-card--' . ( ( $index % 6 ) + 1 ) ); ?> data-media-topics="<?php echo esc_attr( implode( ' ', $topic_slugs ) ); ?>"><a href="<?php the_permalink(); ?>">
	<figure><?php if ( has_post_thumbnail() ) the_post_thumbnail( 'large' ); ?><i aria-hidden="true"></i></figure>
	<div class="gmr-media-card__top"><span><?php echo esc_html( $topics ? $topics[0]->name : 'Archivo' ); ?></span><span><?php echo esc_html( $image_count . ( 1 === $image_count ? ' imagen' : ' imágenes' ) ); ?></span></div>
	<div class="gmr-media-card__copy"><small><?php echo esc_html( get_post_meta( $gallery_id, 'gmr_media_date_label', true ) ); ?></small><h2><?php the_title(); ?></h2><?php if ( has_excerpt() ) : ?><p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p><?php endif; ?><em>Explorar archivo ↗</em></div>
</a></article>
