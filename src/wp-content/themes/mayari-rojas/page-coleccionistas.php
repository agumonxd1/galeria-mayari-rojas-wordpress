<?php
defined( 'ABSPATH' ) || exit;
get_header();
$can_access = current_user_can( 'gmr_view_collector_area' ) || current_user_can( 'gmr_manage_artworks' );
$action = sanitize_key( wp_unslash( $_GET['action'] ?? 'login' ) );
$status = sanitize_key( wp_unslash( $_GET['reset'] ?? $_GET['login_error'] ?? $_GET['access'] ?? '' ) );
$access_url = get_permalink();
?>
<?php if ( ! is_user_logged_in() ) : ?>
<main class="gmr-access">
	<section class="gmr-access__intro">
		<div class="gmr-access__brand"><img src="<?php echo esc_url( gmr_theme_logo_url( 'light' ) ); ?>" alt="Galería Mayarí Rojas"></div>
		<div><span class="gmr-kicker">Archivo privado · Coleccionistas</span><h1>Una mirada<br>más cercana.</h1><p>Acceda al catálogo reservado, precios autorizados, documentación y obras seleccionadas por la galería.</p></div>
		<small>El acceso es personal y concedido exclusivamente por Galería Mayarí Rojas.</small>
	</section>
	<section class="gmr-access__form-panel">
		<div class="gmr-access__form">
			<?php if ( 'lostpassword' === $action ) : ?>
				<span class="gmr-kicker">Recuperar acceso</span><h2>Restablecer contraseña</h2><p>Ingrese su correo electrónico o nombre de usuario. Si existe una cuenta autorizada, recibirá un enlace seguro.</p>
				<?php if ( 'sent' === $status ) : ?><div class="gmr-access__notice gmr-access__notice--success" role="status">Si los datos corresponden a una cuenta, enviaremos las instrucciones por correo.</div><?php elseif ( 'expired' === $status ) : ?><div class="gmr-access__notice" role="alert">El enlace ya no es válido. Solicite uno nuevo.</div><?php elseif ( 'security' === $status ) : ?><div class="gmr-access__notice" role="alert">La solicitud expiró. Inténtelo nuevamente.</div><?php endif; ?>
				<form method="post" action="<?php echo esc_url( $access_url ); ?>">
					<input type="hidden" name="gmr_access_action" value="lostpassword"><?php wp_nonce_field( 'gmr_collector_lostpassword', 'gmr_lost_nonce' ); ?>
					<label><span>Correo o usuario</span><input name="user_login" type="text" autocomplete="username" required></label>
					<button class="gmr-access__submit" type="submit">Enviar enlace <i aria-hidden="true">↗</i></button>
				</form>
				<a class="gmr-access__back" href="<?php echo esc_url( $access_url ); ?>">← Volver al inicio de sesión</a>
			<?php elseif ( 'reset' === $action ) : $key = sanitize_text_field( wp_unslash( $_GET['key'] ?? '' ) ); $login = sanitize_user( wp_unslash( $_GET['login'] ?? '' ) ); $reset_user = check_password_reset_key( $key, $login ); ?>
				<span class="gmr-kicker">Nuevo acceso</span><h2>Crear contraseña</h2>
				<?php if ( is_wp_error( $reset_user ) ) : ?><div class="gmr-access__notice" role="alert">Este enlace no es válido o ya expiró.</div><a class="gmr-access__submit" href="<?php echo esc_url( add_query_arg( 'action', 'lostpassword', $access_url ) ); ?>">Solicitar otro enlace <i aria-hidden="true">↗</i></a>
				<?php else : ?><p>Utilice al menos 10 caracteres. Una frase larga y única es más segura y fácil de recordar.</p><?php if ( 'mismatch' === $status ) : ?><div class="gmr-access__notice" role="alert">Las contraseñas deben coincidir y contener al menos 10 caracteres.</div><?php elseif ( 'security' === $status ) : ?><div class="gmr-access__notice" role="alert">La solicitud expiró. Abra nuevamente el enlace recibido.</div><?php endif; ?><form method="post" action="<?php echo esc_url( $access_url ); ?>"><input type="hidden" name="gmr_access_action" value="resetpassword"><input type="hidden" name="key" value="<?php echo esc_attr( $key ); ?>"><input type="hidden" name="login" value="<?php echo esc_attr( $login ); ?>"><?php wp_nonce_field( 'gmr_collector_reset_' . $login, 'gmr_reset_nonce' ); ?><label><span>Nueva contraseña</span><span class="gmr-access__password"><input name="pass1" type="password" autocomplete="new-password" minlength="10" required><button type="button" data-password-toggle aria-label="Mostrar contraseña">Ver</button></span></label><label><span>Confirmar contraseña</span><input name="pass2" type="password" autocomplete="new-password" minlength="10" required></label><button class="gmr-access__submit" type="submit">Guardar contraseña <i aria-hidden="true">↗</i></button></form><?php endif; ?>
			<?php else : ?>
				<span class="gmr-kicker">Bienvenido</span><h2>Acceso de Coleccionistas</h2><p>Ingrese con las credenciales proporcionadas por la galería.</p>
				<?php if ( 'invalid' === $status ) : ?><div class="gmr-access__notice" role="alert">No pudimos iniciar la sesión. Revise sus datos e inténtelo nuevamente.</div><?php elseif ( 'inactive' === $status ) : ?><div class="gmr-access__notice" role="alert">Esta cuenta no está activa. Contacte directamente a la galería.</div><?php elseif ( 'security' === $status ) : ?><div class="gmr-access__notice" role="alert">La sesión del formulario expiró. Inténtelo nuevamente.</div><?php elseif ( 'complete' === $status ) : ?><div class="gmr-access__notice gmr-access__notice--success" role="status">La contraseña fue actualizada. Ya puede ingresar.</div><?php endif; ?>
				<form method="post" action="<?php echo esc_url( $access_url ); ?>">
					<input type="hidden" name="gmr_access_action" value="login"><input type="hidden" name="redirect_to" value="<?php echo esc_attr( $access_url ); ?>"><?php wp_nonce_field( 'gmr_collector_login', 'gmr_login_nonce' ); ?>
					<label><span>Correo o usuario</span><input name="log" type="text" autocomplete="username" required></label>
					<label><span>Contraseña</span><span class="gmr-access__password"><input name="pwd" type="password" autocomplete="current-password" required><button type="button" data-password-toggle aria-label="Mostrar contraseña">Ver</button></span></label>
					<div class="gmr-access__options"><label><input name="rememberme" type="checkbox" value="forever"><span>Recordarme</span></label><a href="<?php echo esc_url( add_query_arg( 'action', 'lostpassword', $access_url ) ); ?>">¿Olvidó su contraseña?</a></div>
					<button class="gmr-access__submit" type="submit">Ingresar a la colección <i aria-hidden="true">↗</i></button>
				</form>
				<div class="gmr-access__help"><span>¿Necesita acceso?</span><p>Las cuentas son creadas personalmente por el equipo de la galería. Contáctenos para solicitar información.</p></div>
			<?php endif; ?>
		</div>
	</section>
</main>
<?php elseif ( ! $can_access ) : ?>
<section class="gmr-private"><div class="gmr-private__panel"><h1>Acceso restringido</h1><p>Su cuenta no tiene acceso al área de Coleccionistas. Contacte a la galería.</p><a class="gmr-button" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>">Cerrar sesión</a></div></section>
<?php else : $private = new WP_Query( array( 'post_type'=>'product', 'post_status'=>'publish', 'posts_per_page'=>12, 'meta_query'=>array( array( 'key'=>'gmr_visibility', 'value'=>array( 'public','collectors' ), 'compare'=>'IN' ) ) ) ); $available_query = new WP_Query( array( 'post_type'=>'product', 'post_status'=>'publish', 'fields'=>'ids', 'posts_per_page'=>1, 'meta_query'=>array( array( 'key'=>'gmr_visibility', 'value'=>array( 'public','collectors' ), 'compare'=>'IN' ), array( 'key'=>'gmr_commercial_status', 'value'=>'available' ) ) ) ); $available = (int) $available_query->found_posts; ?>
<header class="gmr-private-head"><div class="gmr-wrap"><span class="gmr-kicker">Área privada</span><h1>Bienvenido, <?php echo esc_html( wp_get_current_user()->display_name ); ?></h1><p><?php echo esc_html( $private->found_posts ); ?> obras autorizadas · <?php echo esc_html( $available ); ?> disponibles</p><div class="gmr-private-nav"><a href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ); ?>">Catálogo completo</a><a href="<?php echo esc_url( add_query_arg( 'estado', 'available', get_post_type_archive_link( 'product' ) ) ); ?>">Obras disponibles</a><a href="<?php echo esc_url( gmr_theme_page_url( 'colecciones' ) ); ?>">Colecciones</a><a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>">Cerrar sesión</a></div></div></header><section class="gmr-section"><div class="gmr-wrap"><div class="gmr-section__head"><div><span class="gmr-kicker">Selección privada</span><h2>Catálogo para Coleccionistas</h2></div><a class="gmr-button" href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ); ?>">Ver catálogo completo</a></div><div class="gmr-grid"><?php while ( $private->have_posts() ) { $private->the_post(); get_template_part( 'template-parts/artwork', 'card' ); } wp_reset_postdata(); ?></div></div></section>
<?php endif; get_footer(); ?>
