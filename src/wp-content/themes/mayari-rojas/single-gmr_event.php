<?php
get_header(); the_post(); $id = get_the_ID(); $date = gmr_theme_event_date_parts( $id );
$venue = get_post_meta( $id, 'gmr_event_venue', true ); $address = get_post_meta( $id, 'gmr_event_address', true ); $modality = get_post_meta( $id, 'gmr_event_modality', true ); $register = get_post_meta( $id, 'gmr_event_registration', true );
?>
<article class="gmr-event-single">
<header class="gmr-event-hero"><div class="gmr-event-hero__image"><?php the_post_thumbnail( 'full' ); ?><i aria-hidden="true"></i></div><div class="gmr-wrap gmr-event-hero__content">
	<a href="<?php echo esc_url( get_post_type_archive_link( 'gmr_event' ) ); ?>">← Volver a la agenda</a>
	<div class="gmr-event-hero__title"><span class="gmr-kicker"><?php echo esc_html( gmr_theme_event_types( $id ) ?: 'Agenda' ); ?> · <?php echo esc_html( gmr_theme_event_status_label( $id ) ); ?></span><h1><?php the_title(); ?></h1><?php if ( has_excerpt() ) : ?><p><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?></div>
	<div class="gmr-event-hero__date"><strong><?php echo esc_html( $date['day'] ); ?></strong><span><?php echo esc_html( $date['month'] ); ?><small><?php echo esc_html( $date['year'] ); ?></small></span></div>
</div></header>
<section class="gmr-event-detail"><div class="gmr-wrap">
	<aside class="gmr-event-facts"><?php if ( $date['time'] ) : ?><div><span>Hora</span><strong><?php echo esc_html( $date['time'] ); ?></strong></div><?php endif; ?><?php if ( $venue ) : ?><div><span>Lugar</span><strong><?php echo esc_html( $venue ); ?></strong><?php if ( $address ) : ?><small><?php echo esc_html( $address ); ?></small><?php endif; ?></div><?php endif; ?><?php if ( $modality ) : ?><div><span>Modalidad</span><strong><?php echo esc_html( ucfirst( $modality ) ); ?></strong></div><?php endif; ?><?php if ( $register && gmr_theme_event_is_upcoming( $id ) ) : ?><a class="gmr-button" href="<?php echo esc_url( $register ); ?>">Registrarse</a><?php endif; ?></aside>
	<div class="gmr-event-content"><?php the_content(); ?></div>
</div></section>
</article>
<?php get_footer(); ?>
