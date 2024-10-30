<?php
/**
 * @link              http://www.pcis.com/laiser-tag
 * @since             1.0.0
 * @package           LaiserTag
 *
 * @wordpress-plugin
 * Plugin Name:       Laiser Tag
 * Plugin URI:        https://developer.wordpress.org/plugins/laiser-tag/
 * Description:       Uses the OpenCalais API to automatically generate tags for existing posts.
 * Version:           1.2.5
 * Author:            PCIS
 * Author URI:        http://www.pcis.com/laiser-tag
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define('LTOC_PLUGIN_VERSION', '1.2.4');
define('LTOC_PLUGIN_PATH', dirname(__FILE__));
define('LTOC_TEMPLATES', dirname(__FILE__) . '/templates/');
define('LTOC_PLUGIN_NAME', 'laiser-tag');
define('LTOC_ASSETS_URL', plugins_url(LTOC_PLUGIN_NAME . '/assets/'));
define('LTOC_HISTORICAL_LOG', LTOC_PLUGIN_PATH."/ltoc_historical_log.txt");
define('LTOC_BATCH_LOG', LTOC_PLUGIN_PATH."/ltoc_batch_log.txt");
define('LTOC_ERROR_LOG', LTOC_PLUGIN_PATH."/ltoc_error_log.txt");
define('LTOC_PROCESS_FILE', LTOC_PLUGIN_PATH."/ltoc_tagging.pid");

require_once __DIR__ . '/include/OpenCalais/OpenCalais.php';
require_once __DIR__ . '/include/OpenCalais/Exception/OpenCalaisException.php';
require_once __DIR__ . '/include/Tagging.php';
require_once __DIR__ . '/include/Templates.php';
$ltoc_tagging = \LTOC\Tagging::getInstance();

register_activation_hook( __FILE__, array($ltoc_tagging, 'activatePlugin') );
register_deactivation_hook( __FILE__, array($ltoc_tagging, 'deactivatePlugin') );

