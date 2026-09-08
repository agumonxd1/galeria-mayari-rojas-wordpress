<?php
defined( 'ABSPATH' ) || exit;
$id         = get_the_ID();
$artist     = gmr_theme_term_names( $id, 'gmr_artist' ) ?: __( 'Galería Mayarí Rojas', 'mayari-rojas' );
$video_url  = (string) get_post_meta( $id, 'gmr_room_video_url', true );
$room_text  = (string) get_post_meta( $id, 'gmr_room_text', true );
$public_id  = absint( get_post_meta( $id, '_gmr_public_image', true ) );
$image_id   = $public_id && wp_attachment_is_image( $public_id ) ? $public_id : get_post_thumbnail_id( $id );
$visibility = get_post_meta( $id, 'gmr_visibility', true ) ?: 'public';
$embed      = $video_url ? wp_oembed_get( $video_url, array( 'width'=>1280 ) ) : '';
get_header();
?>
<main class="gmr-room-page" id="main">
	<header class="gmr-room-hero<?php echo $image_id ? '' : ' gmr-room-hero--plain'; ?>">
		<?php if ( $image_id ) echo wp_get_attachment_image( $image_id, 'full', false, array( 'class'=>'gmr-room-hero__image', 'loading'=>'eager' ) ); ?>
		<div class="gmr-room-hero__veil"></div><div class="gmr-wrap gmr-room-hero__content"><span class="gmr-kicker">Obra en sala</span><p><?php echo esc_html( $artist ); ?></p><h1><?php the_title(); ?></h1><span class="gmr-room-hero__cue">Descubrir <i aria-hidden="true">↓</i></span></div>
	</header>
	<?php if ( $embed || $room_text ) : ?>
	<section class="gmr-room-story"><div class="gmr-wrap">
		<div class="gmr-room-story__intro"><span class="gmr-kicker">La historia detrás de la obra</span><h2>Una mirada más cercana.</h2></div>
		<div class="gmr-room-story__content">
			<?php if ( $embed ) : ?><div class="gmr-room-video"><?php echo $embed; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endif; ?>
			<?php if ( $room_text ) : ?><div class="gmr-room-copy gmr-editorial"><?php echo wp_kses_post( wpautop( $room_text ) ); ?></div><?php endif; ?>
		</div>
	</div></section>
	<?php endif; ?>
	<section class="gmr-room-close"><div class="gmr-wrap"><span class="gmr-kicker">Galería Mayarí Rojas</span><h2>Continúe explorando la obra.</h2><?php if ( 'public' === $visibility ) : ?><a class="gmr-button" href="<?php echo esc_url( get_permalink( $id ) ); ?>">Ver ficha de la obra <span aria-hidden="true">↗</span></a><?php else : ?><p>Esta obra forma parte del recorrido actual de la galería.</p><?php endif; ?></div></section>
</main>
<?php get_footer(); ?>
