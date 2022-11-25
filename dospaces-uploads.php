<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Digitalocean Spaces
 * Plugin URI:
 * Description:
 * Version:           1.0.0
 * Author:            Fredrik Nordström
 * Author URI:
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:
 * Domain Path:
 */

namespace PrBiggerUploads;

// If this file is called directly, abort.
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PR_BIGGER_UPLOADS_VER', '1.0.0' );

/**
 * Path to the plugin root directory.
 */
define( 'PR_BIGGER_UPLOADS_PATH', plugin_dir_path( __FILE__ ) );

require_once PR_BIGGER_UPLOADS_PATH . 'vendor/autoload.php';

function guidv4($data = null) {
    // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
    $data = $data ?? random_bytes(16);
    assert(strlen($data) == 16);

    // Set version to 0100
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    // Set bits 6-7 to 10
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

    // Output the 36 character UUID.
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}
/*
register_activation_hook( __FILE__, function(){
    $authorizationKey = get_option(\PrBiggerUploads\Acf\SettingsPage::PR_SITE_DATA_AUTHORIZATION_OPTION_NAME, null);
    if($authorizationKey == null){
        update_option(\PrBiggerUploads\Acf\SettingsPage::PR_SITE_DATA_AUTHORIZATION_OPTION_NAME, guidv4());
    }
} );
*/
add_action( 'plugins_loaded', function(){
    (new Api\Media())->register();
    (new Wp\Filters())->register();
    (new Wp\Uploader())->register();
    (new Acf\SettingsPage());
} );
