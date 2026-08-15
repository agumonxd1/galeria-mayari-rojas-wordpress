<?php
/**
 * Plugin Name: Mayari Core
 * Plugin URI:  https://galeriamayarirojas.com/
 * Description: Catalogo, artistas, colecciones, privacidad y herramientas editoriales de Galeria Mayari Rojas.
 * Version:     1.4.0
 * Requires at least: 6.7
 * Requires PHP: 8.1
 * Author:      Galeria Mayari Rojas
 * Text Domain: mayari-core
 *
 * @package MayariCore
 */

defined( 'ABSPATH' ) || exit;

define( 'GMR_CORE_VERSION', '1.4.0' );
define( 'GMR_CORE_FILE', __FILE__ );
define( 'GMR_CORE_PATH', plugin_dir_path( __FILE__ ) );

require_once GMR_CORE_PATH . 'includes/class-gmr-core-capabilities.php';
require_once GMR_CORE_PATH . 'includes/class-gmr-core-content.php';
require_once GMR_CORE_PATH . 'includes/class-gmr-core-meta.php';
require_once GMR_CORE_PATH . 'includes/class-gmr-core-access.php';
require_once GMR_CORE_PATH . 'includes/class-gmr-core-catalog.php';
require_once GMR_CORE_PATH . 'includes/class-gmr-core-admin-artwork.php';
require_once GMR_CORE_PATH . 'includes/class-gmr-core-admin-terms.php';
require_once GMR_CORE_PATH . 'includes/class-gmr-core-admin-editorial.php';
require_once GMR_CORE_PATH . 'includes/class-gmr-core-inquiry.php';
require_once GMR_CORE_PATH . 'includes/class-gmr-core-institution.php';
require_once GMR_CORE_PATH . 'includes/class-gmr-core-collectors.php';
require_once GMR_CORE_PATH . 'includes/class-gmr-core-documents.php';
require_once GMR_CORE_PATH . 'includes/class-gmr-core-migration-preview.php';
require_once GMR_CORE_PATH . 'includes/class-gmr-core-migration.php';
require_once GMR_CORE_PATH . 'includes/class-gmr-core-plugin.php';

register_activation_hook( __FILE__, array( 'GMR_Core_Capabilities', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'GMR_Core_Plugin', 'deactivate' ) );

GMR_Core_Plugin::instance();
