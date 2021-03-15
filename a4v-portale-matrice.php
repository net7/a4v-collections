<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://netseven.it
 * @since             1.0.0
 * @package           A4v_Collection
 *
 * @wordpress-plugin
 * Plugin Name:       Arianna 4view Portale matrice
 * Plugin URI:        http://netseven.it
 * Description:       This plugin is an extesion of Muruca Core v2 plugin to enable collections
 * Author:            Netseven
 * Author URI:        http://netseven.it
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       a4v-collection
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'MURUCA_CORE_PREFIX',  "a4v" );
define( 'MURUCA_CORE_TEXTDOMAIN',  "a4v-collection" );
define( 'MURUCA_CORE_PLUGIN_NAME',  "a4v-collection" );
define( 'MURUCA_CORE_V2_REST_VERSION',  "v1" );

require plugin_dir_path( __FILE__ ) . 'includes/third-part/muruca-core-v2-collection/muruca-core-v2-collection.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-a4v-portale-matrice-collection.php';


function run_a4v_portale_matrice_collection() {
    $plugin = new A4v_Portale_Matrice_Collection();
    $plugin->run();
}

run_a4v_portale_matrice_collection();

