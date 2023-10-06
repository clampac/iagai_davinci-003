<?php
require_once plugin_dir_path( dirname( __FILE__ ,2) ) . 'includes/clapac-iagai-functions.php';

function create_title_suggestions(){
	require_once plugin_dir_path( dirname( __FILE__ ) ) . 'articles/class-clapac_iagai_ai_generator.php';
	global $wpdb;
	$titlesAmount = 8;

	$query = ("SELECT id, post_subject FROM ".$wpdb->prefix."clapac_iagai_post_subjects "
	          ." WHERE created IS NOT true ORDER BY id");
	$post_subjects = $wpdb->get_results($query);

	if (!isset($post_subjects) or count($post_subjects) < 1){
		echo ("<h1 class='iagai_h1'>There is no subject available to create the titles.</h1>");
		echo ("<h2 class='iagai_h2'>Make sure you've already set them and if they weren't already created.</h2>");
		die();
	}

	ob_start();

	echo ("<h1 class='iagai_h1'>Creating titles for the selected subjects</h1>");
	// because Nginx + php-tfp wasn't flushing it's buffers...
	while ( @ob_end_flush() ) {
	}
	flush();
	?>
	<?php

	foreach ( $post_subjects as $post_subject ) {
		?>
		<br><br><h3 class="iagai_h3">For subject: '<?php echo ($post_subject->post_subject); ?>'</h3><br>
		<?php
		// because Nginx + php-tfp wasn't flushing it's buffers...
		while ( @ob_end_flush() ) {
		}
		flush();

		$aiGenerator = new AI_Generator();
		$results = $aiGenerator->write_suggested_titles($post_subject->post_subject, $titlesAmount);
        $saved = save_suggested_titles($post_subject, $results);
        sleep(2);
    }
}

function save_suggested_titles($post_subject, $titles){

	global $wpdb;
	?>
	<ul>
	<?php

	foreach (preg_split("/((\r?\n)|(\r\n?))/", $titles) as $title){
		$title = trim(preg_replace("/^\d{1,2}\s?.?\s*/",'',$title));
		$title = trim($title, '\'"');
		$title = preg_replace("/^\W/", "", $title);
		if ($title and $title!= '' and strlen($title) > 1 ) {
			$sql = "INSERT INTO " . $wpdb->prefix . "clapac_iagai_suggested_title (post_subject_id, post_title)" .
			       " VALUES (" . $post_subject->id . ",'" . addslashes($title) . "');";
			$dbg = $wpdb->query( $sql );
			if ( $dbg ) {
				$table_subjects = $wpdb->prefix.'clapac_iagai_post_subjects';
				$wpdb->query ( 'UPDATE '.$table_subjects.' SET created=true WHERE id = '.$post_subject->id );
				?>
				<li class = 'iagai_li'>
				 <?php echo $title; ?>
				</li>
				<?php
			}
			// because Nginx + php-tfp wasn't flushing it's buffers...
			while ( @ob_end_flush() ) {
			}
			flush();
		}
	}
	?></ul><?php
}