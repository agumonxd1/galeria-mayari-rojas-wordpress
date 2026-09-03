<?php
/** Public presentation and authenticated artwork originals. */
defined( 'ABSPATH' ) || exit;

final class GMR_Core_Artwork_Images {
	public static function register_hooks(): void {
		add_action('add_meta_boxes_product', static function() { add_meta_box('gmr-images','Imágenes pública y privada',array(self::class,'box'),'product','normal','high'); });
		add_action('post_edit_form_tag', static function() { echo ' enctype="multipart/form-data"'; });
		add_action('save_post_product',array(self::class,'save'));
		add_action('template_redirect',array(self::class,'serve'),-10);
		add_filter('post_thumbnail_id',array(self::class,'thumbnail'),20,2);
		add_filter('woocommerce_product_get_image_id',static function($value,$product) { return self::thumbnail($value,get_post($product->get_id())); },20,2);
	}
	public static function thumbnail($value,$post) {
		if (is_admin() || !$post || 'product' !== $post->post_type) return $value;
		$id=absint(get_post_meta($post->ID,'_gmr_public_image',true));
		return $id && wp_attachment_is_image($id) ? $id : $value;
	}
	public static function authorized(int $id): bool {
		return 'product'===get_post_type($id) && GMR_Core_Access::can_view($id)
			&& (current_user_can('gmr_manage_artworks') || current_user_can('manage_woocommerce') || ('publish'===get_post_status($id) && current_user_can('gmr_view_collector_catalog')));
	}
	private static function directory(): string {
		$vault=realpath(GMR_Core_Documents::vault_path());
		$root=realpath($_SERVER['DOCUMENT_ROOT'] ?? ABSPATH);
		if (!$vault || !$root || $vault===$root || str_starts_with($vault,$root.DIRECTORY_SEPARATOR)) return '';
		$dir=$vault.'/artwork-images';
		return (is_dir($dir) || wp_mkdir_p($dir)) ? $dir : '';
	}
	private static function path(int $id): string {
		$file=(string)get_post_meta($id,'_gmr_private_image',true);
		$dir=self::directory();
		return $dir && preg_match('/^[a-f0-9-]+\.(jpg|jpeg|png|webp)$/D',$file) ? $dir.'/'.$file : '';
	}
	public static function url(int $id): string {
		if (!self::authorized($id) || !is_file(self::path($id))) return '';
		return wp_nonce_url(add_query_arg('gmr-artwork-image',$id,home_url('/')),'gmr_image_'.$id,'token');
	}
	public static function box(WP_Post $post): void {
		wp_nonce_field('gmr_images','gmr_images_nonce');
		$id=absint(get_post_meta($post->ID,'_gmr_public_image',true));
		echo '<div class="gmr-image-access-grid"><section class="gmr-image-access-card gmr-image-access-card--public"><span class="gmr-image-access-card__eyebrow">Vitrina pública</span><h3>Imagen pública</h3><p>Versión con marca de agua o resolución reducida. Si queda vacía, se utilizará la imagen destacada.</p>';
		if ($id) echo '<div class="gmr-image-access-card__preview">'.wp_get_attachment_image($id,'thumbnail').'</div>';
		echo '<label class="gmr-upload-zone"><strong>Seleccionar imagen pública</strong><span>JPEG, PNG o WebP</span><input type="file" name="gmr_public_upload" accept="image/jpeg,image/png,image/webp"></label><label class="gmr-image-remove"><input type="checkbox" name="gmr_remove_public" value="1"> Quitar imagen pública alternativa</label></section>';
		$has_private=(bool)get_post_meta($post->ID,'_gmr_private_image',true);
		echo '<section class="gmr-image-access-card gmr-image-access-card--private"><span class="gmr-image-access-card__eyebrow">Coleccionistas</span><h3>Imagen privada</h3><p>Original protegido para coleccionistas autorizados y administradores.</p><p class="gmr-private-state '.($has_private?'is-ready':'is-empty').'">'.($has_private?'✓ Original privado guardado':'Sin original privado').'</p><label class="gmr-upload-zone"><strong>Seleccionar original privado</strong><span>JPEG, PNG o WebP · fuera de la biblioteca</span><input type="file" name="gmr_private_upload" accept="image/jpeg,image/png,image/webp"></label><label class="gmr-image-remove"><input type="checkbox" name="gmr_remove_private" value="1"> Quitar imagen privada</label></section></div><p class="gmr-image-security-note"><span class="dashicons dashicons-shield-alt" aria-hidden="true"></span> Las imágenes de la biblioteca son públicas. Los originales confidenciales deben cargarse únicamente como imagen privada.</p>';
		$error=get_transient('gmr_image_error_'.get_current_user_id());
		if ($error) { echo '<p role="alert">'.esc_html($error).'</p>'; delete_transient('gmr_image_error_'.get_current_user_id()); }
	}
	public static function save(int $id): void {
		if (wp_is_post_revision($id) || wp_is_post_autosave($id) || !current_user_can('edit_post',$id) || !isset($_POST['gmr_images_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['gmr_images_nonce'])),'gmr_images')) return;
		foreach (array('public','private') as $kind) {
			$key='_gmr_'.$kind.'_image';
			$file=$_FILES['gmr_'.$kind.'_upload'] ?? null;
			if (!$file || UPLOAD_ERR_NO_FILE===($file['error'] ?? UPLOAD_ERR_NO_FILE)) {
				// Detach only: retain old files for recovery, never delete a shared media item.
				if (!empty($_POST['gmr_remove_'.$kind])) delete_post_meta($id,$key);
				continue;
			}
			$mimes=array('jpg|jpeg'=>'image/jpeg','png'=>'image/png','webp'=>'image/webp');
			$check=wp_check_filetype_and_ext($file['tmp_name'],$file['name'],$mimes);
			if ($file['error'] || !is_uploaded_file($file['tmp_name']) || $file['size']>wp_max_upload_size() || empty($check['ext']) || !in_array(wp_get_image_mime($file['tmp_name']),$mimes,true)) {
				set_transient('gmr_image_error_'.get_current_user_id(),'No se guardó la imagen: use un JPEG, PNG o WebP válido dentro del límite de carga.',120); continue;
			}
			if ('public'===$kind) {
				require_once ABSPATH.'wp-admin/includes/file.php'; require_once ABSPATH.'wp-admin/includes/media.php'; require_once ABSPATH.'wp-admin/includes/image.php';
				$result=media_handle_upload('gmr_public_upload',$id,array(),array('test_form'=>false,'mimes'=>$mimes));
				if (!is_wp_error($result)) update_post_meta($id,$key,$result);
				else set_transient('gmr_image_error_'.get_current_user_id(),$result->get_error_message(),120);
			} else {
				$dir=self::directory(); $name=wp_generate_uuid4().'.'.$check['ext'];
				if ($dir && move_uploaded_file($file['tmp_name'],$dir.'/'.$name)) { chmod($dir.'/'.$name,0640); update_post_meta($id,$key,$name); }
				else set_transient('gmr_image_error_'.get_current_user_id(),'No se pudo guardar el archivo en el almacén privado.',120);
			}
		}
	}
	public static function serve(): void {
		if (!isset($_GET['gmr-artwork-image'])) return;
		$id=absint($_GET['gmr-artwork-image']);
		nocache_headers(); header('Cache-Control: private, no-store, max-age=0'); header('X-Robots-Tag: noindex, nofollow, noarchive'); header('X-Content-Type-Options: nosniff');
		if (!self::authorized($id) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['token'] ?? '')),'gmr_image_'.$id)) { status_header(404); exit; }
		$path=self::path($id);
		if (!$path || !is_file($path)) { status_header(404); exit; }
		$mime=wp_get_image_mime($path);
		if (!in_array($mime,array('image/jpeg','image/png','image/webp'),true)) { status_header(404); exit; }
		header('Content-Type: '.$mime); header('Content-Disposition: inline'); header('Content-Length: '.filesize($path)); readfile($path); exit;
	}
}
