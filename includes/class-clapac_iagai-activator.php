<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.meuscaminhos.com.br
 * @since      0.7.0
 *
 * @package    Clapac_iagai
 * @subpackage Clapac_iagai/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      0.7.0
 * @package    Clapac_iagai
 * @subpackage Clapac_iagai/includes
 * @author     Claudio M. Bittencourt Pacheco <claudio@meuscaminhos.com.br>
 */
class Clapac_iagai_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    0.7.0
	 */
	public static function activate() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		// create table
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$table_name = $wpdb->prefix . 'clapac_iagai_img_file_name';
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			// double check if table exists
			$sql = " CREATE TABLE  $table_name (";
			$sql .= " id int NOT NULL AUTO_INCREMENT, ";
			$sql .= " post_id int NOT NULL, ";
			$sql .= " originalName varchar(255) NOT NULL, ";
			$sql .= " suggestedName varchar(255) NOT NULL, ";
			$sql .= " PRIMARY KEY  (id) ) $charset_collate;";
			dbDelta( $sql );
		}

		$table_name = $wpdb->prefix . 'clapac_iagai_images_found';
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			$sql = "CREATE TABLE $table_name ( ";
			$sql .= " id int NOT NULL AUTO_INCREMENT, ";
			$sql .= " post_id int NOT NULL, ";
			$sql .= " large_image_url text NOT NULL, ";
			$sql .= " preview_url text NOT NULL, ";
			$sql .= " tags text NOT NULL, ";
			$sql .= " author text NOT NULL, ";
			$sql .= " keyword_searched text NOT NULL, ";
			$sql .= " PRIMARY KEY (id) ) $charset_collate;";
			dbDelta( $sql );
		}

		$table_name = $wpdb->prefix . 'clapac_iagai_post_subjects';
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			$sql = "CREATE TABLE $table_name (";
			$sql .= " id int NOT NULL AUTO_INCREMENT,";
			$sql .= " created BOOL NULL,";
			$sql .= " post_subject text NOT NULL,";
			$sql .= " PRIMARY KEY  (id) ) $charset_collate;";
			dbDelta( $sql );
		}

		$table_name = $wpdb->prefix . "clapac_iagai_suggested_title";
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {

			$sql = "CREATE TABLE $table_name ( ";
			$sql .= "id int NOT NULL AUTO_INCREMENT, ";
			$sql .= "post_subject_id int NOT NULL, ";
			$sql .= " created BOOL NULL,";
			$sql .= "post_title varchar(200) NOT NULL, ";
			$sql .= "PRIMARY KEY  (id), ";
			$sql .= "FOREIGN KEY (post_subject_id) REFERENCES ".$wpdb->prefix."clapac_iagai_post_subjects(ID)) $charset_collate; ";
			dbDelta( $sql );
		}

		$table_name = $wpdb->prefix . 'clapac_iagai_posts';
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {

			$sql = "CREATE TABLE $table_name ( ";
			$sql .= " id int NOT NULL AUTO_INCREMENT, ";
			$sql .= " post_id int, ";
			$sql .= " created boolean, ";
			$sql .= " outline_created boolean NULL, ";
			$sql .= " title varchar(120) NOT NULL, ";
			$sql .= " keywords varchar(150) NULL, ";
			$sql .= " snippet_title varchar(60) NULL, ";
			$sql .= " meta_description varchar(160) NULL, ";
			$sql .= " permalink varchar(60) NULL,";
			$sql .= " PRIMARY KEY  (id)) $charset_collate;";
			dbDelta( $sql );
		}

		$table_name = $wpdb->prefix . 'clapac_iagai_post_subtitles';
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {

			$sql = "CREATE TABLE $table_name ( ";
			$sql .= " id int NOT NULL AUTO_INCREMENT, ";
			$sql .= " title_id int NOT NULL, ";
			$sql .= " level INT NULL, ";
			$sql .= " subtitle varchar(80) NOT NULL, ";
			$sql .= " PRIMARY KEY  (id), ";
			$sql .= " FOREIGN KEY (title_id) REFERENCES ".$wpdb->prefix."clapac_iagai_posts(ID) ) $charset_collate;";
			dbDelta( $sql );
		}

	}
}
