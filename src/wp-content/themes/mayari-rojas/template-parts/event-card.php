<?php
$event_id = get_the_ID();
$date = gmr_theme_event_date_parts( $event_id );
$featured = ! empty( $args['featured'] );
$type_slugs = wp_get_post_terms( $event_id, 'gmr_event_type', array( 'fields' => 'slugs' ) );
$type_slugs = is_wp_error( $type_slugs ) ? array() : $type_slugs;
?>
<article <?php post_class( 'gmr-event-card' . ( $featured ? ' gmr-event-card--featured' : '' ) ); ?> data-event-types="<?php echo esc_attr( implode( ' ', $type_slugs ) ); ?>"><a href="<?php the_permalink(); ?>">
	<figure><?php if ( has_post_thumbnail() ) the_post_thumbnail( 'large' ); ?><i aria-hidden="true"></i><span><?php echo esc_html( gmr_theme_event_status_label( $event_id ) ); ?></span></figure>
	<div class="gmr-event-card__body">
		<div class="gmr-event-date"><strong><?php echo esc_html( $date['day'] ); ?></strong><span><?php echo esc_html( $date['month'] ); ?><small><?php echo esc_html( $date['year'] ); ?></small></span></div>
		<div class="gmr-event-card__copy"><span><?php echo esc_html( gmr_theme_event_types( $event_id ) ?: 'Agenda' ); ?></span><h2><?php the_title(); ?></h2><?php if ( has_excerpt() ) : ?><p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p><?php endif; ?><em>Conocer más ↗</em></div>
	</div>
</a></article>
