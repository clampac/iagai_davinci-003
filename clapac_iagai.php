<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.meuscaminhos.com.br
 * @since             0.7.0
 * @package           Clapac_iagai
 *
 * @wordpress-plugin
 * Plugin Name:       Clapac IAGAI (Image Attributes Generator with Artificial Intelligence)
 * Plugin URI:        https://www.meuscaminhos.com.br
 * Description:       Este plugin gera automaticamente os atributos altText, Description, Title e um arquivo CSV com o nome atual do arquivo e uma sugestão de nome.
 * Version:           0.9.9
 * Author:            Claudio M. Bittencourt Pacheco
 * Author URI:        https://www.meuscaminhos.com.br
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       clapac_iagai
 * Domain Path:       /languages
 */
add_theme_support( 'post-thumbnails',array('page')); 
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 0.9.9 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'CLAPAC_IAGAI_VERSION', '0.9.9' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-clapac_iagai-activator.php
 */
function activate_clapac_iagai() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-clapac_iagai-activator.php';
	Clapac_iagai_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-clapac_iagai-deactivator.php
 */
function deactivate_clapac_iagai() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-clapac_iagai-deactivator.php';
	Clapac_iagai_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_clapac_iagai' );
register_deactivation_hook( __FILE__, 'deactivate_clapac_iagai' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-clapac_iagai.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.7.0
 */
function run_clapac_iagai() {

	$plugin = new Clapac_iagai();
	$plugin->run();

}
run_clapac_iagai();
