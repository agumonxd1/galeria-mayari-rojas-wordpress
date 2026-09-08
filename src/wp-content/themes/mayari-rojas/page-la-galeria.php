<?php
get_header();
the_post();
$portrait = 6434;
$space    = 6441;
$history  = get_post_meta( get_the_ID(), 'gmr_gallery_history', true );
?>
<header class="gmr-institution-hero"><div class="gmr-wrap gmr-institution-hero__inner"><div class="gmr-institution-hero__lead"><span class="gmr-kicker">Ciudad de Guatemala</span><h1>La <em>galería</em></h1></div><div class="gmr-institution-hero__statement"><span class="gmr-institution-hero__rule" aria-hidden="true"></span><p>Un proyecto dedicado a preservar legados, acompañar artistas y acercar el arte latinoamericano a nuevas generaciones.</p></div></div></header>
<section class="gmr-section gmr-institution-profile"><div class="gmr-wrap gmr-institution-split"><figure class="gmr-institution-portrait"><?php echo wp_get_attachment_image($portrait,'large',false,array('alt'=>'Mayarí Rojas, directora y curadora de la galería'));?><figcaption>Dirección y curaduría</figcaption></figure><div class="gmr-institution-profile__copy"><span class="gmr-kicker">Dirección y curaduría</span><h2>Mayarí Rojas</h2><div class="gmr-editorial"><?php the_content();?></div></div></div></section>
<section class="gmr-section gmr-institution-history-section"><div class="gmr-wrap"><header class="gmr-institution-history__head"><span class="gmr-kicker">Nuestra historia</span><h2>Del Centro Cultural a Galería Mayarí Rojas</h2><p>Una trayectoria construida a través de espacios, encuentros y una convicción persistente por el arte guatemalteco.</p></header><figure class="gmr-institution-space"><?php echo wp_get_attachment_image($space,'large',false,array('alt'=>'Interior de Galería Mayarí Rojas'));?></figure><div class="gmr-history"><?php echo wp_kses_post($history);?></div></div></section>
<?php get_footer();?>
