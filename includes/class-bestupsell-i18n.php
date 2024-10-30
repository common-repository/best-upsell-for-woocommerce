<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.identixweb.com/
 * @since      1.2.0
 *
 * @package    Bestupsell
 * @subpackage Bestupsell/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.2.0
 * @package    Bestupsell
 * @subpackage Bestupsell/includes
 * @author     identixweb <https://www.identixweb.com/>
 */
class Bestupsell_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.2.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'bestupsell',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
