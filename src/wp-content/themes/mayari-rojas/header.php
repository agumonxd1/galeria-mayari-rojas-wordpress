<!doctype html><html <?php language_attributes(); ?>><head><meta charset="<?php bloginfo( 'charset' ); ?>"><meta name="viewport" content="width=device-width,initial-scale=1"><?php wp_head(); ?></head>
<body <?php body_class(); ?>><?php wp_body_open(); ?><div class="gmr-site">
<header class="gmr-header"><div class="gmr-wrap gmr-header__inner">
<button class="gmr-menu-toggle" type="button" aria-expanded="false" aria-controls="gmr-nav">Menu</button>
<a class="gmr-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">Mayari Rojas<small>Galeria de arte</small></a>
<nav class="gmr-nav" id="gmr-nav" aria-label="Principal"><?php gmr_theme_menu_fallback(); ?></nav>
<a class="gmr-account" href="<?php echo esc_url( is_user_logged_in() ? home_url( '/coleccionistas/' ) : wp_login_url( home_url( '/coleccionistas/' ) ) ); ?>">Coleccionistas</a>
</div></header><main id="main">
