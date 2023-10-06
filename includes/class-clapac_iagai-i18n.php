<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.meuscaminhos.com.br
 * @since      0.7.0
 *
 * @package    Clapac_iagai
 * @subpackage Clapac_iagai/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      0.7.0
 * @package    Clapac_iagai
 * @subpackage Clapac_iagai/includes
 * @author     Claudio M. Bittencourt Pacheco <claudio@meuscaminhos.com.br>
 */
class Clapac_iagai_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    0.7.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'clapac_iagai',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
