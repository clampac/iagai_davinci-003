<?php

/**
 * Plugin Name: Suggested Titles
 * Plugin URI: http://www.example.com
 * Description: A plugin to show system generated post titles
 * Version: 1.0
 * Author: IA
 * Author URI: http://www.IAdevs.com
 **/

//
//// Exit if accessed directly
//if ( !defined( 'ABSPATH' ) ) {
//    exit;
//}
//

/**
 * Suggested Titles class
 *
 * @since 1.0
 **/

/**
 * Constructor
 *
 * @since 1.0
 **/

add_action( 'wp_ajax_save_title', 'save_title'  );



/**
 * Admin page HTML
 *
 * @since 1.0
 **/
function suggested_titles_admin_page()
{
    global $wpdb;

    $post_subjects = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."clapac_iagai_post_subjects" );

    $suggested_titles = array();

    if ( ! empty( $post_subjects ) && is_array( $post_subjects ) ) {
        foreach ( $post_subjects as $post_subject ) {
            $title_query = $wpdb->get_results( $wpdb->prepare("SELECT * FROM ".$wpdb->prefix."clapac_iagai_suggested_title WHERE post_subject_id = %d", $post_subject->id) );
            if ( ! empty( $title_query ) && is_array( $title_query ) ) {
                foreach ( $title_query as $title ) {
                    $suggested_titles[ $post_subject->post_subject ][] = $title;
                }
            }
        }
    }

    ?>

    <div class="wrap">
        <h1 class="iagai_h1">Suggested Titles</h1>
        <form method="post" action="" id="suggested-titles-form">
            <?php wp_nonce_field( 'save_title_selection' ); ?>
            <?php

            if ( !empty( $suggested_titles ) && is_array( $suggested_titles ) ) {
                foreach ( $suggested_titles as $post_subject => $titles ) {
                    ?>
                    <div name="<?php echo $post_subject; ?>">
                    <div class="form-group" >
                        <div class="form-check">
                            <table class="iagai_table">
                                <thead>
                                    <tr>
                                        <th colspan="2" class='iagai_table_title'><?php echo $post_subject; ?></th>
                                    </tr>
                                    <tr>
                                        <th></th>
                                        <th>Titles</th>
                                    </tr>
                                </thead>
                                <tbody>
                        <?php
                        foreach ( $titles as $title ) {
                            ?>
                                    <tr>
                                        <td><input type="checkbox" class="form-check-input" name="title_selection[<?php echo $post_subject; ?>]" value="<?php echo $title->id; ?>" <?php if ( isset( $_POST['title_selection'][ $post_subject ] ) &&  $_POST['title_selection'][ $post_subject ] == $title->id ) echo 'checked'; ?>></td>
                                        <td><label class="form-check-label"><?php echo $title->post_title; ?></label></td>
                                    </tr>
                            <?php
                        }
                        ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="iagai_buttons">
                        <button type="button" class="button button-primary save-title iagai_button_subjects_definition" data-subject="<?php echo esc_attr( $post_subject ); ?>">Save</button>
                        <button type="button" class="button button-primary clear-title iagai_button_subjects_definition" data-subject="<?php echo esc_attr( $post_subject ); ?>">Clear Selection</button>
                    </div>
                    <hr>
                    </div>
                    <?php
                }
            }
            ?>
        </form>
    </div>

    <script type="text/javascript">
        jQuery(document).ready( function($){
            $('.save-title').on('click', function(e) {
                e.preventDefault();
                var titleSubject = $(this).data('subject');
                var selectedValues = [];
                $('input[name="title_selection['+titleSubject+']"]:checked').each(function() {
                    selectedValues.push($(this).val());
                });
                var idTitle = $('input[name="post_subject_id['+titleSubject+']"]').val();
                $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: {
                        action: "save_title",
                        selectedValues: selectedValues,
                        idTitle: idTitle
                    },
                    success: function(response) {
                        $('input[name="title_selection['+titleSubject+']"]:checked').each(function() {
                            selectedValues.push($(this).closest('tr').remove());
                        });
                    }
                });
            });


            $('.clear-title').on('click', function() {
                $('input[name="title_selection['+$(this).data('subject')+']"]').prop("checked",false);
            });
        });
    </script>

    <?php
}

/**
 * AJAX Save title
 *
 * @since 1.0
 **/
function save_title()
{
	global $wpdb;

    $selectedTitles = isset( $_POST['selectedValues'] ) ? $_POST['selectedValues'] : null;

    if (!$selectedTitles){
        die();
    }

    $titles = $wpdb->get_results('SELECT id, post_title FROM '.$wpdb->prefix.'clapac_iagai_suggested_title '.
                                   ' WHERE id IN ('.implode(',', $selectedTitles).');');




    foreach ($titles as $title) {
	    $sql = 'INSERT INTO ' . $wpdb->prefix . 'clapac_iagai_posts (title) values ("' . addslashes($title->post_title) . '");';

	    $saved = $wpdb->query( $sql );

	    if ( $saved ) {
		    $deleteSuggestedTitle = 'DELETE FROM ' . $wpdb->prefix . 'clapac_iagai_suggested_title where id=' . $title->id;
		    $wpdb->query( $deleteSuggestedTitle );

//		    $deleteSubject = 'DELETE FROM ' . $wpdb->prefix . 'clapac_iagai_post_subjects where id=' . $id_title;
//		    $wpdb->query( $deleteSubject );
	    }
    }
    wp_send_json_success( __( 'Title saved.', 'suggested-titles' ) );
}

/**
 * AJAX Clear title
 *
 * @since 1.0
 **/
function clear_title()
{
    $nonce = isset( $_POST['nonce'] ) ? sanitize_key( $_POST['nonce'] ) : '';
    $post_subject = isset( $_POST['post_subject'] ) ? sanitize_text_field( $_POST['post_subject'] ) : '';
    $title_selection = isset( $_POST['title_selection'] ) ? array_map( 'sanitize_text_field', $_POST['title_selection'] ) : array();

    if ( ! wp_verify_nonce( $nonce, 'save_title_selection' ) ) {
        wp_send_json_error( __( 'Authorization failed', 'suggested-titles' ) );
    }

    // do clear title logic
    wp_die();
}
