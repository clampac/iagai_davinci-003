<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://www.meuscaminhos.com.br
 * @since      0.7.0
 *
 * @package    Clapac_iagai
 * @subpackage Clapac_iagai/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      0.7.0
 * @package    Clapac_iagai
 * @subpackage Clapac_iagai/includes
 * @author     Claudio M. Bittencourt Pacheco <claudio@meuscaminhos.com.br>
 */
class Clapac_iagai_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    0.7.0
	 */
	public static function deactivate() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'clapac_iagai_images_found';
		$sql = "DROP TABLE IF EXISTS $table_name";
		$wpdb->query($sql);

		$table_name = $wpdb->prefix . 'clapac_iagai_img_file_name';
		$sql = "DROP TABLE IF EXISTS $table_name";
		$wpdb->query($sql);

		$table_name = $wpdb->prefix . "clapac_iagai_suggested_title";
		$sql = "DROP TABLE IF EXISTS $table_name";
		$wpdb->query($sql);

		$table_name = $wpdb->prefix . 'clapac_iagai_post_subjects';
		$sql = "DROP TABLE IF EXISTS $table_name";
		$wpdb->query($sql);

		$table_name = $wpdb->prefix . 'clapac_iagai_post_subtitles';
		$sql = "DROP TABLE IF EXISTS $table_name";
		$wpdb->query($sql);

		$table_name = $wpdb->prefix . 'clapac_iagai_posts';
		$sql = "DROP TABLE IF EXISTS $table_name";
		$wpdb->query($sql);


		//delete_option("clapac_iagai_log");
		//delete_option("clapac_iagai_openAI_key");
	}
}
