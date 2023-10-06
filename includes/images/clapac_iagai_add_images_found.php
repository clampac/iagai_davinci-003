<?php
/**
 * Plugin Name: Clapac-iagai
 * Plugin URI: http://www.example.com/
 * Description: Plugin to search, select and import images from Clapac-iagai into Wordpress posts.
 * @since: 0.8.0
 * Author: Your Name
 * Author URI: http://www.example.com/
 * License: GPL2
 */
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}




if ( !class_exists( 'clapac_iagai_add_post_images' ) ) {


//create clapac_iagai_add_post_images class
	class clapac_iagai_add_post_images {

//		public function __construct() {
//			add_action( 'wp_ajax_delete_from_images_table_action', array( $this, 'clapac_iagai_delete_images' ) );
//		}


		//initial function
		function clapac_iagai_add_images_found(){

			//require and include
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );


			//read data from existent database clapac_iagai_images_found
			global $wpdb;
			$table_name = $wpdb->prefix . 'clapac_iagai_images_found';
			$data = $wpdb->get_results("SELECT * FROM $table_name");

			//nest data by post_id
			$post_ids = array();
			foreach ($data as $row) {
				$post_ids[$row->post_id][] = array(
					'id' => $row->id,
					'large_image_url' => $row->large_image_url,
					'preview_url' => $row->preview_url,
					'tags' => $row->tags,
					'author' => $row->author,
					'keyword_searched' => $row->keyword_searched
				);
			}


			//add_action('wp_ajax_delete_from_add_images_table', array ($this, 'clapac_iagai_delete_images'));

			// This function deletes the selected images from the table
//			function deleteSelectedImagesFromTable($idList){
//				global $wpdb;
//				$table_name = $wpdb->prefix . 'clapac_iagai_images_found';
//
//				if(!empty($idList)){
//					$ids = implode(',', $idList);
//                    $sql = ("DELETE FROM " . $table_name . " WHERE id IN ($ids)");
//                    clapac_iagai_log($sql);
//					$wpdb->query($sql);
//					wp_send_json_success();
//				}else{
//					wp_send_json_error();
//				}
//			}
			?>

            <script type="text/javascript">
                //select all checkbox
                jQuery(document).ready(function($) {
                    jQuery('#selectAll').click(function(){
                        var checked = jQuery(this).prop('checked');
                        jQuery('.checkbox').prop('checked',checked);
                    });
                });

                //delete from table
                function deleteFromTable(){
                    var ids = [];
                    jQuery("input[name='checkbox[]']:checked").each(function () {
                        ids.push(jQuery(this).val());
                    });
                    if(ids.length > 0) {
                        jQuery.ajax({
                            url: '<?php echo admin_url('admin-ajax.php'); ?>',
                            type: 'post',
                            data: {
                                action: 'delete_from_images_table_action',
                                ids: ids
                            },
                            success: function(){
                                    location.reload();
                            },
                            error: function(error){ console.log(error); }
                        });
                    } else {
                        alert('Error: Please select at least one image to delete.');
                    }
                }

                //import images to posts
                function importImagesToPost(){
                    var ids = [];
                    jQuery("input[name='checkbox[]']:checked").each(function () {
                        ids.push(jQuery(this).val());
                    });
                    if(ids.length > 0) {
                        jQuery.ajax({
                            url: '<?php echo admin_url('admin-ajax.php'); ?>',
                            type: 'post',
                            data: {
                                action: 'import_images_from_table_action',
                                ids: ids
                            },
                            success: function(){
                                location.reload();
                            }
                        });
                    } else {
                        alert('Error: Please select at least one image to import.');
                    }
                }
            </script>

            <style>
                .separator {
                    background-color: #ffc107;
                    padding: 5px 10px;
                    margin-bottom: 10px;
                }

                .tr-hoverable:hover {
                    background-color: #f1f1f1;
                }

                .tr-selected {
                    background-color: #e0e0e0;
                }

                .table td {
                    vertical-align: middle;
                }

                .checkbox {
                    margin-right: 5px;
                }
            </style>

            <!-- Floating Buttons -->
            <div class="float-buttons">
                <button type="button" class="btn btn-primary" onclick="deleteFromTable()">Delete</button>
                <button type="button" class="btn btn-primary" onclick="importImagesToPost()">Import images</button>
            </div>

            <!-- Content -->
			<?php
			if(!empty($post_ids)) {
				foreach($post_ids as $post_id => $images) {
					$post_title = get_the_title($post_id);
					$keyword_searched = $images[0]['keyword_searched'];
					?>
                    <div class="separator">Post: <?php echo $post_title; ?></div>
                    <div class="separator">Keyword: <?php echo $keyword_searched; ?></div>
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll" /></th>
                            <th>Image</th>
                            <th>Tags</th>
                            <th>Author</th>
                        </tr>
                        </thead>
                        <tbody>
						<?php
						foreach($images as $image) {
							?>
                            <tr class="tr-hoverable">
                                <td><input type="checkbox" name="checkbox[]" class="checkbox" value="<?php echo $image['id']; ?>" /></td>
                                <td><img src="<?php echo $image['preview_url']; ?>" width="150" /></td>
                                <td><?php echo $image['tags']; ?></td>
                                <td><?php echo $image['author']; ?></td>
                            </tr>
							<?php
						}
						?>
                        </tbody>
                    </table>
					<?php
				}
			} else {
				echo 'No images found.';
			}
			?>

            <script type="text/javascript">
                //table row hoverable
                jQuery(document).ready(function($) {
                    jQuery('.tr-hoverable').click(function(){
                        if(jQuery(this).hasClass('tr-selected')) {
                            jQuery(this).removeClass('tr-selected');
                            jQuery(this).find('.checkbox').prop('checked',false);
                        } else {
                            jQuery(this).addClass('tr-selected');
                            jQuery(this).find('.checkbox').prop('checked',true);
                        }
                    });
                });
            </script>

			<?php
		}

		//delete images from table
		function deleteFromImagesTable() {
			if(isset($_POST['ids']) && is_array($_POST['ids'])) {
				global $wpdb;
				$table_name = $wpdb->prefix . 'clapac_iagai_images_found';
				$ids = implode(',', $_POST['ids']);
				$wpdb->query("DELETE FROM $table_name WHERE id IN($ids)");
				echo 'success';
			}
			die();
		}


		//import images to posts
		function clapac_iagai_import_images() {
			if(isset($_POST['ids']) && is_array($_POST['ids'])) {
				global $wpdb;
				$table_name = $wpdb->prefix . 'clapac_iagai_images_found';
				$ids = implode(',', $_POST['ids']);
				$images = $wpdb->get_results("SELECT * FROM $table_name WHERE id IN($ids)");
				if($images) {
					foreach($images as $image) {
						$this->clapac_iagai_import_image_to_post($image);
						$wpdb->delete(
							$wpdb->prefix . "clapac_iagai_images_found",
							array( 'id' => $image->id )
						);
					}
					echo 'success';
				}
			}
			die();
		}

		//import image to post
		function clapac_iagai_import_image_to_post($image) {
			$post_id = $image->post_id;
			$image_url = $image->large_image_url;
			$upload_dir = wp_upload_dir();
			$image_data = file_get_contents($image_url);
			$filename = basename($image_url);
			if(wp_mkdir_p($upload_dir['path'])) {
				$file = $upload_dir['path'] . '/' . $filename;
			} else {
				$file = $upload_dir['basedir'] . '/' . $filename;
			}
			file_put_contents($file, $image_data);
			$wp_filetype = wp_check_filetype($filename, null);
			$attachment = array(
				'post_mime_type' => $wp_filetype['type'],
				'post_title' => sanitize_file_name($filename),
				'post_content' => '',
				'post_status' => 'inherit'
			);
			$attach_id = wp_insert_attachment($attachment, $file, $post_id);
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			$attach_data = wp_generate_attachment_metadata($attach_id, $file);
			wp_update_attachment_metadata($attach_id, $attach_data);
			set_post_thumbnail($post_id, $attach_id);
		}
	}
}

$clapac_iagai_add_post_images = new Clapac_iagai_Add_Post_Images();

// add ajax action
add_action('wp_ajax_delete_from_images_table_action', array($clapac_iagai_add_post_images, 'deleteFromImagesTable'));
add_action('wp_ajax_import_images_from_table_action', array($clapac_iagai_add_post_images, 'clapac_iagai_import_images'));
