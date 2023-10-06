<?php
/** @package    Clapac_iagai
 * @since 0.8.0
 * @subpackage Clapac_iagai/includes
 * @author     Claudio M. Bittencourt Pacheco <claudio@meuscaminhos.com.br>
 */

function image_name_update_page() {
    global $wpdb;
	$table_name = $wpdb->prefix . 'clapac_iagai_img_file_name';
    $results = $wpdb->get_results(
      "SELECT id, originalName, suggestedName, post_id ".
            "FROM ".$table_name." WHERE 1=1;"
   );
   ?>
   <div class="wrap">
      <h2>Image Name Update</h2>
      <form action="" method="post">
         <table class="widefat" style="width:100%">
            <thead>
               <tr>
                   <th><input type="checkbox" name="select_all" /></th>
                   <th>Post Title</th>
                   <th>Original Name</th>
                   <th>Suggested Name</th>
               </tr>
            </thead>
            <tbody>
               <?php foreach ( $results as $result ) { ?>
                  <tr>
                      <td><input type="checkbox" name="ids[]" value="<?php echo $result->id; ?>" /></td>
                      <td><?php echo get_the_title($result->post_id)?></td>
                      <td><?php echo $result->originalName; ?></td>
                      <td><?php echo $result->suggestedName; ?></td>
                  </tr>
               <?php } ?>
            </tbody>
         </table>
         <input type="submit" name="update" value="Update" />
         <input type="submit" name="delete" value="Delete" />
      </form>
   </div>
   <?php
}

function clapac_iagai_process_form() {
	if ( isset( $_POST['update'] ) ) {
		updateImages( $_POST['ids'] );
	}
	elseif ( isset( $_POST['delete'] ) ) {
		deleteImages( $_POST['ids'] );
	}
}
add_action( 'admin_init', 'clapac_iagai_process_form' );


function deleteImages( $ids ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'clapac_iagai_img_file_name';
	if ( ! empty( $ids ) ) {
		foreach ( $ids as $id ) {
			$sql = $wpdb->prepare(
				"DELETE FROM ". $table_name ." WHERE id = %d",
				$id
			);
			$wpdb->query( $sql );
		}
	}
}


function updateImages( $ids ) {
	if ( empty( $ids ) ) {
		return;
	}
	global $wpdb;
	$table_name = $wpdb->prefix . 'clapac_iagai_img_file_name';
	$results = $wpdb->get_results(
		"SELECT id, originalName, suggestedName, post_id ".
		"FROM ".$table_name." WHERE id in (".implode(',',$ids).");");


    foreach ( $results as $result ) {
	    $uploads = wp_upload_dir();
        $originalImageURL = $result->originalName;
	    $suggestedName = $result->suggestedName;
        $post_id = $result->post_id;

	    $file_path = str_replace( $uploads['baseurl'], $uploads['basedir'], $originalImageURL );

        if ($originalImageURL != $file_path){
            $dir = dirname($file_path);
	        $newFileName = $dir.'/'.$suggestedName;
            rename($file_path, $newFileName);
            updateImageNameInPost($post_id, $originalImageURL, $suggestedName);
	        updateImageNameInAttachedFile($post_id, $originalImageURL, $suggestedName);
        }

        // Faça a lógica para atualizar o banco de dados aqui
    }

}

function updateImageNameInAttachedFile($post_id, $originalImageURL, $suggestedName) {
	$newURL  = dirname( $originalImageURL ) . '/' . $suggestedName;
	$oldName = basename( $originalImageURL );

	global $wpdb;
	$table_name = $wpdb->prefix . 'posts';

	//updating posts table
	$query = ( "UPDATE " . $table_name .
	           " SET post_title = '" . $suggestedName . "', post_name = '" . $suggestedName . "', guid = '" . $newURL . "'" .
	           " WHERE guid = '" . $originalImageURL . "' AND post_parent = " . $post_id . ";" );

	$results = $wpdb->get_results( $query );

	//updating postmeta
	$query   = ( "SELECT id FROM " . $table_name . " WHERE post_parent = " . $post_id . " AND guid = '" . $newURL . "';" );
	$results = $wpdb->get_results( $query );

	foreach ( $results as $result ) {
        updatePostMeta($result->id, $oldName, $suggestedName);
	}


}
function updatePostMeta($postId, $oldName, $newName){

	$postmetas = get_post_meta( $postId );


	echo ("<div style='margin-left:300px'>");

	foreach ( $postmetas as $meta_key => $meta_value ) {

        $oldMetaValue = $meta_value;
        $isArray = is_array($oldMetaValue);


        if ($isArray){
            if (count($oldMetaValue)==1){
	            $newMetaValue = str_replace($oldName, $newName, $oldMetaValue[0]);
            }
            else{
	            $newMetaValue = array();
	            foreach ($oldMetaValue as $oldMV){
		            $newMV = str_replace($oldName, $newName, $oldMV);
		            array_push($newMetaValue, $newMV);
	            }
            }
        }
        else{
	        $newMetaValue = str_replace($oldName, $newName, $oldMetaValue);
        }

		$ret = update_post_meta($postId, $meta_key, $newMetaValue);
	}
}


function updateImageNameInPost($post_id, $originalImageURL, $suggestedName){
	$post = get_post($post_id);
	// get the post content with images
	$postContent = $post->post_content;
	$result = '';

	// get a DOMDocument object
	$domDoc = new DOMDocument();
	libxml_use_internal_errors(true);
	// load a HTML string into the DOMDocument
	$domDoc->loadHTML('<html><body>'.$postContent.'</body></html>');
	libxml_clear_errors();

	// Get the images in the DOMDocument
	$images = $domDoc->getElementsByTagName('img');

	// Loop through images adding / editing alt attribute
	foreach ($images as $image) {
		$imageURL = $image->getAttribute('src');
        if ($imageURL == $originalImageURL){
            $newFileName = str_replace(basename($originalImageURL), $suggestedName, $imageURL);
            $image->setAttribute('src',$newFileName);

	        $postContent = trim(preg_replace('~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', $domDoc->saveHTML()));
	        $post->post_content = $postContent;

	        remove_action( 'post_updated', 'wp_save_post_revision' );
	        wp_update_post($post);
	        add_action( 'post_updated', 'wp_save_post_revision' );
	        return $post;
        }
	}
}
