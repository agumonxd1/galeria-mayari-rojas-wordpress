<!doctype html><html <?php language_attributes(); ?>><head><meta charset="<?php bloginfo( 'charset' ); ?>"><meta name="viewport" content="width=device-width,initial-scale=1"><?php wp_head(); ?></head>
<body <?php body_class(); ?>><?php wp_body_open(); ?><a class="gmr-skip-link" href="#main">Saltar al contenido</a><div class="gmr-site">
<header class="gmr-header"><div class="gmr-wrap gmr-header__inner">
<button class="gmr-menu-toggle" type="button" aria-expanded="false" aria-controls="gmr-nav"><span>Menu</span><i aria-hidden="true"></i></button>
<a class="gmr-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>"><b aria-hidden="true">MR</b><span>Mayari Rojas<small>Galeria de arte</small></span></a>
<nav class="gmr-nav" id="gmr-nav" aria-label="Principal"><?php gmr_theme_menu_fallback(); ?></nav>
<a class="gmr-account" href="<?php echo esc_url( is_user_logged_in() ? gmr_theme_page_url( 'coleccionistas' ) : wp_login_url( gmr_theme_page_url( 'coleccionistas' ) ) ); ?>"><span><?php echo is_user_logged_in() ? 'Mi coleccion' : 'Coleccionistas'; ?></span><i aria-hidden="true">↗</i></a>
</div></header><main id="main">
