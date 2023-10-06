<?php

function write_articles($ids){
	global $wpdb;
	ob_start();

	if (!current_user_can('manage_options')) {
		wp_die( 'You do not have sufficient permissions to access this page.' );
	}
	if (!$ids){
		echo ("<h3>There's no title selected to create articles or an error occurred.");
		die();
	}
    $post_ids = array_values($ids);
	startWritingProccess($post_ids);
}

function createArticleOutline($title_ids){
	global $wpdb;

    $ids = array_values($title_ids);

	$titles = $wpdb->get_results( "SELECT title, keywords, id FROM ".$wpdb->prefix.
	                              "clapac_iagai_posts WHERE id IN (".implode(',', $ids).")" );
	?>
	<h1 class='iagai_h1'>Writing articles outline.</h1>
	<h4 class='iagai_h4'>It can take up to 1 minute per article, but typically takes about 20 seconds. Please wait patiently.</h4>
    <div class="iagai_writing_article_outline">
	<?php
		while ( @ob_end_flush() ) {
		}
		flush();
	// Create post topics
	foreach ($titles as $title) {
        $keywords = str_replace(',',', ',$title->keywords);
		echo ( "<h2 class='iagai_h2'>Writing article <span class='iagai_creating_article_title'>{$title->title}</span></h2>");
        echo ("<div class='iagai_writing_article_outline_keywords'><h4 class='iagai_h4'>Keywords = {$keywords}</h4></div>");
		// because Nginx + php-tfp wasn't flushing it's buffers...
		while ( @ob_end_flush() ) {
		}
		flush();
		create_article_topics($title->title,$title->id, $title->keywords);
		//write_blog_posts($title->title,$title->id, $title->keywords);
	}
	?>
	<br><h2 class="iagai_h2">The outline were created. Enjoy!</h2>
	<h4 class="iagai_h2">Go back to Write Articles option to start posts production.</h4>
	<br><br><br>
	<script>
        window.scrollTo(0,document.body.scrollHeight+300);
	</script>
	<?php
	// because Nginx + php-tfp wasn't flushing it's buffers...
	while ( @ob_end_flush() ) {
	}
	flush();
}

function startWritingProccess($ids){
	global $wpdb;

	$titles = $wpdb->get_results( "SELECT title, keywords, id FROM ".$wpdb->prefix.
	                              "clapac_iagai_posts WHERE id IN (".implode(',', $ids).")" );
	?>
	<h1 class='iagai_h1'>Writing blog posts.</h1>
	<h4 class='iagai_h4'>It can take up to 4 minutes per article, but typically takes up to 2 minutes. Please wait patiently.</h4>
    <div class="iagai_writing_article_outline">
	<?php
	// Create post topics
	foreach ($titles as $title) {
        $keywords = str_replace(',',', ',$title->keywords);
		echo ( "<h2 class='iagai_h2'>Writing article <span class='iagai_creating_article_title'>{$title->title}</span></h2>");
        echo ("<div class='iagai_writing_article_outline_keywords'><h4 class='iagai_h4'>Keywords = {$keywords}</h4></div><br>");
		// because Nginx + php-tfp wasn't flushing it's buffers...
		while ( @ob_end_flush() ) {
		}
		flush();
		write_blog_posts($title->title,$title->id, $title->keywords);

        echo ("<hr>");
	}
	?>
	<br><h2 class="iagai_h2">The posts were created. Enjoy!</h2>
	<br><br><br>
	<script>
        window.scrollTo(0,document.body.scrollHeight+300);
	</script>
	<?php
	// because Nginx + php-tfp wasn't flushing it's buffers...
	while ( @ob_end_flush() ) {
	}
	flush();
}


function create_article_topics($title, $id, $keywords){
	require_once plugin_dir_path( dirname( __FILE__ ) ) . 'articles/class-clapac_iagai_ai_generator.php';
	global $wpdb;

    $aiGenerator = new AI_Generator();
	$textWithSubtitles =   $aiGenerator->generateArticleSubtitles($title, $keywords);
    ?>
	        <h3 class='iagai_h3 iagai_underline'>Blog article outline:</h3><br>
    <?php
    $subtitles = $aiGenerator->convertSubtitlesToArray($textWithSubtitles);

	foreach($subtitles as $item){
		$level = $item['level'];
		$title = $item['title'];

        if ($level>1){
	        $sql = "INSERT INTO ".$wpdb->prefix."clapac_iagai_post_subtitles (title_id, level, subtitle) VALUES (".$id.",'".$level."','".$title."');";
        }
		$wpdb->query($sql);

		echo ("<h{$level} class='iagai_h{$level} iagai_h{$level}_subtitles_list '>{$title}</h{$level}>");
	}

	?>
	        <br><hr>
        </div>
	<script>
        window.scrollTo(0,document.body.scrollHeight+300);
	</script>
	<?php
	// because Nginx + php-tfp wasn't flushing it's buffers...
	while ( @ob_end_flush() ) {
	}
	flush();
}

function write_blog_posts($title, $id, $keywords){
	require_once plugin_dir_path( dirname( __FILE__ ) ) . 'articles/class-clapac_iagai_ai_generator.php';
	global $wpdb;

	$subtitles = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.
	                            "clapac_iagai_post_subtitles ".
	                            " WHERE title_id =".$id.";");

	if (!empty($subtitles)){
        show_subtitles($subtitles);

		$aiGenerator = new AI_Generator();

		$post_content = $aiGenerator->write_blog_topic_content($title, $subtitles, $keywords);
		$permalink = $aiGenerator->write_article_permalink($title, $subtitles, $keywords);

		$inserted_post = publish_the_post($title, $post_content, $permalink);

		if (!is_wp_error($inserted_post) and $inserted_post > 0 ){
			$wpdb->query("UPDATE {$wpdb->prefix}clapac_iagai_posts SET created = true, ".
			             "post_id = $inserted_post WHERE id = ".$id);

			//$wpdb->query("DELETE FROM {$wpdb->prefix}clapac_iagai_post_subtitles WHERE ".
			  //           " title_id = ".$id.";");
			?>
			<h4 class='iagai_h4_green'><strong>Post successfully created.</strong></h4><hr>
			<?php
			// because Nginx + php-tfp wasn't flushing it's buffers...
			while ( @ob_end_flush() ) {
			}
			flush();

			$stats = (array)get_option('clapac_iagai_post_stats');
			$stats['created_articles'] ++;
			update_option('clapac_iagai_post_stats', $stats);
		}
		// because Nginx + php-tfp wasn't flushing it's buffers...
		while ( @ob_end_flush() ) {
		}
		flush();
	}
}

function show_subtitles($subtitles){
    ?>
		<ul>
    <?php

    foreach ($subtitles as $subtitle){
        ?>
            <li class="iagai_li iagai_li_h<?php echo ($subtitle->level); ?>"><?php echo ($subtitle->subtitle); ?></li>
        <?php
    }
    ?>
    </ul>
	<?php
        // because Nginx + php-tfp wasn't flushing it's buffers...
		while ( @ob_end_flush() ) {
		}
		flush();
}


function publish_the_post($title, $content, $permalink){
	require_once plugin_dir_path( dirname( __FILE__,2 ) ) . 'includes/clapac-iagai-functions.php';

    $tidy = new Tidy();
    $tidy->parseString($content, array('indent'=>true, 'show-body-only'=>true, 'wrap'=>'none', 'output-xhtml'=>true));



	$content = convertToWPBlocks($content);
	$post = array(
		'post_title'    => $title,
		'post_content'  => $content,
		'post_status'   => 'publish',
		'post_date'     => get_next_date(),
		'post_permalink'=> $permalink,
		'post_author'   => 1,
		'post_type'     => 'post'
	);

// Try to publish the post created
	return wp_insert_post( $post );
}

