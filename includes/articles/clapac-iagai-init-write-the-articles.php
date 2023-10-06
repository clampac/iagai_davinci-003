<?php
//Plugin Name: Clapac-Iagai Post Table

function show_posts_table() {
	if (!current_user_can('manage_options')) {
		wp_die( 'You do not have sufficient permissions to access this page.' );
	}
	global $wpdb;

    // FIRST it's needed to verify if outlines are created
    verify_created_outlines();

	$posts = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}clapac_iagai_posts ");//" WHERE created IS NOT TRUE");

    if (!$posts or !is_array($posts) or count($posts) < 1 ){

	    echo "<h1 class='iagai_h1'>You need to inform the subjects first.</h1>";
        echo "<h2 class='iagai_h2'>Go to subjects menu.</h2>";
        die();
    }
    ?>
        <div class="iagai_wrap iagai_wrap_full_size">
            <h1 class='iagai_h1'>Articles to be created</h1>

            <table class='table table-hover iagai_table iagai_table_article_definitions'>
                <thead>
                    <tr>
                        <th class='iagai_checkbox'><input class='iagai_checkbox' type='checkbox' id='select_all_checkbox'></th>
                        <th class='iagai_checkbox'>Done</th>
                        <th class='iagai_checkbox'>Outline created</th>
                        <th class='iagai_title'>Title</th>
                        <th>Keywords (comma-separated values)</th>
                    </tr>
                </thead>
            <tbody>

<?
	foreach ($posts as $post) {
		echo "<tr>";
		echo "<td><input type='checkbox' class='checkbox iagai_checkbox' name='checkbox[]' value='{$post->id}' ".(($post->created)?"disabled":"")."></td>";
		echo "<td class='iagai_checked'><input type='checkbox' class='checkbox iagai_checkbox' name='done[]' value='{$post->id}' disabled ".(($post->created)?"checked":"")."></td>";
		echo "<td class='iagai_checked'><input type='checkbox' class='checkbox iagai_checkbox' name='outline_created[]' value='{$post->id}' disabled ".(($post->outline_created)?"checked":"")."></td>";
		echo "<td>{$post->title}</td>";
		echo "<td><input type='text' class='iagai_keywords_field ".($post->created?"no_edit":"")."' ".($post->created?"disabled":"")." value='".$post->keywords."'></td>";
		echo "</tr>";
	}
    ?>
            </tbody>
        </table>
        <h3 class='iagai_h3'>Choose the titles you want to create articles for, then click the generate articles button.</h3>
            <div class = 'iagai_buttons'>
                <input type='button' id='save_post_definitions' class='btn btn-primary iagai_big_button' value='Save changes'>
                <input type='button' id='write_outlines_button' class='btn btn-primary iagai_big_button' value='Write outlines'>
                <input type='button' id="write_articles_button" class='btn btn-primary iagai_big_button' value='Write the Articles'>
            </div>
    </div>
	<script type="text/javascript">
        jQuery(document).ready(function($) {
            //Select All Checkbox
            jQuery('#select_all_checkbox').click(function() {
                jQuery('input[name="checkbox[]"]:not(:disabled)').prop('checked', this.checked);
            });

            //Generate Articles Button
            jQuery('#write_articles_button').click(function() {
                var ids = [];
                jQuery("input[name='checkbox[]']:checked").each(function() {
                    var id = jQuery(this).val();
                    ids.push(id);
                });
                //Ajax Call
                jQuery.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: "write_articles",
                        ids: ids },
                    success: function(response) {
                        if (response.redirect) {
                            window.location.href = response.url;
                        }
                    },
                    error: function (response){
                        alert('There no selected titles to write articles.')
                        console.log (response);
                    }
                });
            });
            //Generate Articles Button
            jQuery('#write_outlines_button').click(function() {
                var ids = [];
                jQuery("input[name='checkbox[]']:checked").each(function() {
                    var id = jQuery(this).val();
                    ids.push(id);
                });
                //Ajax Call
                jQuery.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: "write_outlines",
                        ids: ids },
                    success: function(response) {
                        if (response.redirect) {
                            window.location.href = response.url;
                        }
                    },
                    error: function (response){
                        alert('There no selected titles to write articles.')
                        console.log (response);
                    }
                });
            });
            $('#save_post_definitions').on('click', function(e) {
            	e.preventDefault();
                var ids = {};
                jQuery("input[name='checkbox[]']:enabled").each(function() {
                    if (!$(this).closest('input[name*="outline_created"]').checked)
                    {
                        var id = jQuery(this).val();
                        var keywords = jQuery(this).closest('tr').find(".iagai_keywords_field").val();
                        ids[id] = keywords;
                    }
                });
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: "save_post_definitions",
                        ids: ids
                    },
                    success: function (response) {
                        location.reload();
                    }
                });
            });
        });
	</script>
	<?php
}

function verify_created_outlines(){
    global $wpdb;

    //TODO verificar se há necessidade de manter essa consulta
    $post_ids = $wpdb->get_col("SELECT DISTINCT posts.id FROM wp_clapac_iagai_posts posts ".
                               "INNER JOIN wp_clapac_iagai_post_subtitles subtitles ON ".
                               "posts.id = subtitles.title_id WHERE created IS NOT TRUE;");

    if (isset($post_ids) and count($post_ids)>0){
        $wpdb->query("UPDATE {$wpdb->prefix}clapac_iagai_posts SET outline_created = false WHERE ".
                " created IS NOT TRUE AND id NOT IN (".implode(",", $post_ids).");");
        $wpdb->query("UPDATE {$wpdb->prefix}clapac_iagai_posts SET outline_created = true WHERE ".
                     " id IN (".implode(",", $post_ids).");");
    }
}

add_action('wp_ajax_write_articles', 'write_all_articles' );
function write_all_articles(){

    if (!isset($_POST['ids']) or !is_array($_POST['ids'])){
	    json_encode(array(
		    'error' => 'No selected title.'
	    ));
        wp_die();
    }

    $ids = $_POST['ids'];

    $url = add_query_arg("page", "write_the_articles", admin_url());
    $url = add_query_arg("ids", $ids, $url);

    echo json_encode(array(
        'redirect' => true,
        'url' => $url
    ));
    wp_die();
}

add_action('wp_ajax_write_outlines', 'write_outlines');
function write_outlines (){
    global $wpdb;

	if (!isset($_POST['ids']) or !is_array($_POST['ids'])){
		json_encode(array(
			'error' => 'No selected title.'
		));
		wp_die();
	}
	$ids = $_POST['ids'];

    $hava_ouline_ids = $wpdb->get_col("SELECT DISTINCT posts.id FROM {$wpdb->prefix}clapac_iagai_posts posts ".
                               "INNER JOIN wp_clapac_iagai_post_subtitles subtitles ON ".
                               "posts.id = subtitles.title_id WHERE created IS NOT TRUE;");

    $only_no_outline = array();
    foreach ($ids as $id){
        if (!in_array($id, $hava_ouline_ids)){
            array_push($only_no_outline, $id);
        }
    }

    if (count($only_no_outline) > 0) {
	    $url = add_query_arg( "page", "article_outline_writer", admin_url() );
	    $url = add_query_arg( "ids", $ids, $url );

	    echo json_encode( array(
		    'redirect' => true,
		    'url'      => $url
	    ) );
    }
	wp_die();
}



add_action('wp_ajax_save_post_definitions', 'save_post_definitions');
function save_post_definitions (){
    global $wpdb;
	if (!isset($_POST['ids']) or !is_array($_POST['ids'])){
		json_encode(array(
			'error' => 'No selected title.'
		));
		wp_die();
	}

	$ids = $_POST['ids'];
    foreach ($ids as $id => $keywords){
        $wpdb->query("UPDATE {$wpdb->prefix}clapac_iagai_posts SET keywords = '{$keywords}' WHERE id = {$id};");

    }
	foreach ($ids as $id => $keywords){
		$keywords_no_spaces = preg_replace('/\s*,\s*/',',',$keywords);
		$keywords_no_spaces = preg_replace('/\s{2,}/',' ',$keywords_no_spaces);
		$wpdb->query("UPDATE ".$wpdb->prefix."clapac_iagai_posts SET keywords = '".trim($keywords_no_spaces).
		             "' WHERE id = ".$id);
		$post_ids[] = $id;
	}
}

?>