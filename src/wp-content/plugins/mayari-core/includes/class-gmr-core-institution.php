<?php
/** Institutional contact settings. @package MayariCore */
defined( 'ABSPATH' ) || exit;
final class GMR_Core_Institution {
	private const FIELDS = array(
		'gmr_address' => 'Direccion de la galeria', 'gmr_hours' => 'Horario de visita', 'gmr_public_phone' => 'Telefono publico',
		'gmr_public_email' => 'Correo publico', 'gmr_map_url' => 'Enlace de Google Maps', 'gmr_instagram_url' => 'Instagram',
		'gmr_facebook_url' => 'Facebook', 'gmr_whatsapp_url' => 'WhatsApp',
	);
	public static function register_hooks(): void { add_action( 'admin_init', array( self::class, 'settings' ) ); }
	public static function settings(): void {
		foreach ( self::FIELDS as $key => $label ) {
			$sanitize = str_contains( $key, 'email' ) ? 'sanitize_email' : ( str_contains( $key, 'url' ) ? 'esc_url_raw' : 'sanitize_text_field' );
			register_setting( 'general', $key, array( 'type'=>'string', 'sanitize_callback'=>$sanitize, 'default'=>'' ) );
			add_settings_field( $key, $label, array( self::class, 'field' ), 'general', 'default', array( 'key'=>$key ) );
		}
	}
	public static function field( array $args ): void { $key=$args['key'];$type=str_contains($key,'email')?'email':(str_contains($key,'url')?'url':'text');printf('<input class="regular-text" type="%s" name="%s" value="%s">',esc_attr($type),esc_attr($key),esc_attr(get_option($key,''))); }
}
