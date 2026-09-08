<?php
defined( 'ABSPATH' ) || exit;
$icons = array(
 'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r=".75" fill="currentColor" stroke="none"/>',
 'facebook' => '<path d="M14 21v-8h3l.5-4H14V7c0-1 .5-2 2-2h2V2h-3c-3 0-5 2-5 5v2H7v4h3v8"/>',
 'youtube' => '<rect x="2" y="5" width="20" height="14" rx="4"/><path d="m10 9 5 3-5 3Z" fill="currentColor" stroke="none"/>',
 'tiktok' => '<path d="M14 3v12a4 4 0 1 1-4-4M14 3c0 4 3 6 6 6V6c-2 0-3-1-3-3Z"/>',
 'linkedin' => '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M7 10v7m4 0v-7m0 3a3 3 0 0 1 6 0v4"/><circle cx="7" cy="7" r=".75" fill="currentColor" stroke="none"/>',
 'x' => '<path d="m4 3 12 18h4L8 3Zm0 18 7-8m2-2 7-8"/>',
);
$links = array_filter( array_map( static fn( $network ) => esc_url( gmr_theme_mod( 'gmr_social_' . $network ), array( 'http', 'https' ) ), array_combine( array_keys( $icons ), array_keys( $icons ) ) ) );
if ( ! $links ) return;
?>
<nav class="gmr-social-links" aria-label="Redes sociales">
<?php foreach ( $links as $network => $url ): ?>
 <a href="<?php echo esc_url( $url ); ?>" aria-label="<?php echo esc_attr( gmr_theme_social_networks()[$network] ); ?>" target="_blank" rel="noopener noreferrer"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?php echo $icons[$network]; ?></svg></a>
<?php endforeach; ?>
</nav>
