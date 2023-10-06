<?php

add_action('wp_ajax_subtitles_review_reload', array( 'Clapac_review_subtitles', 'subtitles_review_reload' ));
add_action('wp_ajax_subtitles_review_save', array( 'Clapac_review_subtitles','subtitles_review_save'));
add_action('wp_ajax_recreate_outline', array( 'Clapac_review_subtitles','recreate_outline'));

class Clapac_review_subtitles {

    public static function recreate_outline (){

        //TODO salvar array com ids de subtitles para remover somente depois de criada a nova estrutura
	    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'articles/class-clapac_iagai_ai_generator.php';
	    global $wpdb;
	    $title_id = $_POST['title_id'];
	    $wpdb->delete("{$wpdb->prefix}clapac_iagai_post_subtitles", array( 'title_id' => "{$title_id}"));
        $post_definition = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}clapac_iagai_posts WHERE id = {$title_id}");

        $ai_generator = new AI_Generator();
        $new_outline = $ai_generator->generateArticleSubtitles($post_definition->title, $post_definition->keywords);
	    $subtitles = $ai_generator->convertSubtitlesToArray($new_outline);

	    $output = '';

	    foreach($subtitles as $title){
            $level = $title['level'];
            $title = $title['title'];
		    $sql = "INSERT INTO " . $wpdb->prefix . "clapac_iagai_post_subtitles (title_id, level, subtitle) VALUES (" . $title_id . ",'" . $level . "','" . $title . "');";
		    if ( $level > 1 ) {
			    $saved = $wpdb->get_results( $sql );
		    }
	    }
        self::updateTableLines($title_id);
        die();
    }

	public static function subtitles_review_reload(){
		if (!isset($_POST['title_id']) and $_POST['title_id'] == 0){
			return null;
		}

		$title_id = $_POST['title_id'];
        self::updateTableLines($title_id);
	}


    public static function updateTableLines($title_id){
	    global $wpdb;

	    $subtitles = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}clapac_iagai_post_subtitles ".
	                                    " WHERE title_id = {$title_id};");

	    foreach ($subtitles as $subtitle){
		    ?>
            <tr class="level" data-subtitle-id="<?php echo $subtitle->id; ?>">
                <td><?php echo $subtitle->level; ?></td>
                <td class='iagai_subtitle_level_<?php echo ($subtitle->level)?>'>&#x2022;
                    <input type='text' name='subtitle_<?php echo $subtitle->id; ?>' value='<?php echo $subtitle->subtitle; ?>'>
                </td>
            </tr>
	    <?php }
    }

    public static function subtitles_review_save(){
        if (!isset($_POST['subtitles'])){
            return null;
        }
        global $wpdb;
	    $table_name = $wpdb->prefix . 'clapac_iagai_post_subtitles';

	    foreach ($_POST['subtitles'] as $id => $val) {
		    $wpdb->update(
			    $table_name,array( 'subtitle' => $val ),array( 'id' => $id ),array( '%s' ),array( '%d' ));
	    }
    }




	function review_subtitles() {
		echo( "<h1 class='iagai_h1'>Subtitles Review</h1>" );
		echo( "<h3 class='iagai_h3'>Review your posts subtitles before writing the articles.</h3>" );
		?>
		<form action='post.php' method='POST'>
		<?php

        //buscar os valores de clapac_iagai_posts e os clapac_iagai_post_subtitles associados (pode usar inner join).
        global $wpdb;
        $sql_titles =  "SELECT * FROM {$wpdb->prefix}clapac_iagai_posts ".
                " WHERE created IS NOT TRUE;";


        $titles = $wpdb->get_results($sql_titles);
        // Para cada título, uma nova tabela.
        if($titles){
            foreach ($titles as $title){
                $sql_subtitles = "SELECT * FROM {$wpdb->prefix}clapac_iagai_post_subtitles ".
                                " WHERE title_id = {$title->id};";
                $subtitles = $wpdb->get_results($sql_subtitles);
                if ($subtitles){
                ?>
                        <div class="iagai_wrap">
                        <table class="iagai_table iagai_table_subtitles_review" data-title-id="<?php echo $title->id; ?>">
                            <thead>
                            <tr><th class="iagai_table_title" colspan="2"><?php echo $title->title; ?></th></tr>
                            <tr><th>Level</th><th>Subtitle</th></tr>
                            </thead>
                            <tbody>
                <?php
                    foreach ($subtitles as $subtitle){
	                    ?>
                                <tr class="level" data-subtitle-id="<?php echo $subtitle->id; ?>">
                                    <td><?php echo $subtitle->level; ?></td>
                                    <td class='iagai_subtitle_level_<?php echo ($subtitle->level)?>'>&#x2022;
                                        <input type='text' name='subtitle_<?php echo $subtitle->id; ?>' value='<?php echo $subtitle->subtitle; ?>'>
                                    </td>
                                </tr>
            <?php } ?>
                            </tbody>
                        </table>
                <!-- Os botões devem referir-se a cada tabela. -->
                <div class="iagai_buttons">
                    <input class='button button-primary save_subtitles iagai_button_subtitles_review' type='button' name='submit_<?php echo $title->id; ?>' value='salvar'>
                    <input class='button button-primary reload_subtitles iagai_button_subtitles_review' type='button' name='reload_<?php echo $title->id; ?>' value='Reload'>
                    <input class='button button-primary rewrite_subtitles iagai_button_subtitles_review' type='button' name='rewrite_<?php echo $title->id; ?>' value='Write new outline'>
                </div>
            </div>
        <?php
                }
            }
        }
        ?>
            <script>
                jQuery(document).ready( function ($){

                    $("input").on("input", function() {
                        let input = $(this);
                        let text = input.val();
                        let inputLength = text.length;
                        if (inputLength > 60 && inputLength < 80) {
                            input.css("color", "orange");
                        } else if (inputLength >= 80) {
                            input.css("color", "red");
                        } else  {
                            input.css("color", "black");
                        }
                    });

                    $('.rewrite_subtitles').on('click', function(e) {
                    	e.preventDefault();
                        if (confirm ('This action will delete this outline and create a new one. Are you sure?')){
                            let table = $(this).closest('.iagai_wrap').find('.iagai_table_subtitles_review');
                            let title_id = table.data('title-id');
                            $.ajax({
                                type: "POST",
                                url: ajaxurl,
                                data: {
                                    action: 'recreate_outline',
                                    title_id: title_id
                                },
                                beforeSend: function() {
                                    // Mostrar animação de carregamento antes de fazer a chamada AJAX
                                    $.blockUI({ message: '<div style="width:100%;height:0;padding-bottom:96%;position:relative;">' +
                                            '<iframe src="https://giphy.com/embed/9udYDMLdVzBAFuWkmF" width="100%" ' +
                                            'height="100%" style="position:absolute" frameBorder="0" class="giphy-embed" ' +
                                            'allowFullScreen></iframe></div' });
                                },
                                success: function(response) {
                                    $.unblockUI();
                                    $(table).find('tbody').html(response);
                                }
                            });
                        }
                    });
                    $('.reload_subtitles').click( function(e){
                        e.preventDefault();
                        let table = $(this).closest('.iagai_wrap').find('.iagai_table_subtitles_review');
                        let title_id = table.data('title-id');
                        $.ajax({
                            type: "POST",
                            url: ajaxurl,
                            data: {
                                action: 'subtitles_review_reload',
                                title_id: title_id
                            },
                            success: function(response) {
                                $(table).find('tbody').html(response);
                            }
                        });
                    });

                    $('.save_subtitles').click( function(e){
                        e.preventDefault();
                        let table = $(this).closest('.iagai_wrap').find('.iagai_table_subtitles_review');
                        let inputs = table.find('input');
                        let title_id = table.data('title-id');
                        let data = {};
                        inputs.each(function(){
                            let input = $(this);
                            let id = $(this).closest('tr').data('subtitle-id');
                            let value = input.val();
                            data[id] = value;
                        });
                        $.ajax({
                            type: "POST",
                            url: ajaxurl,
                            data: {
                                action: 'subtitles_review_save',
                                title_id: title_id,
                                subtitles: data
                            },
                            success: function(response) {
                                location.reload();
                            }
                        });
                    });

                    $(document).on('keypress', 'input[type="text"]', function (event) {
                        if (event.keyCode == 13) {
                            $(this).blur();
                        }
                    });


                    $(document).on("click", ".iagai_table_subtitles_review tr td", function(e) {
                        if (!$(e.target).is("input")) {
                            $(this).parent().toggleClass("selected_row");
                        }
                    });

                    // para versão 2.0
                //     $(document).on("keydown", function(e) {
                //         if (e.keyCode === 46) { // 46 é o código da tecla "delete"
                //             $(".iagai_table_subtitles_review tr.selected_row").remove();
                //         }
                //     });
                //     $(document).on('keyup', 'input[type="text"]', function (event) {
                //         if (event.keyCode == 45) {
                //             let currentClass = $(this).closest('td').attr('class');
                //             let currentLevel = currentClass.split('_')[3];
                //             let newLine = `<tr>
                //         <td><select class="select_level">
                //             <option value="2" ${currentLevel == 2 ? 'selected' : ''}>2</option>
                //             <option value="3" ${currentLevel == 3 ? 'selected' : ''}>3</option>
                //             <option value="4" ${currentLevel == 4 ? 'selected' : ''}>4</option>
                //         </select></td>
                //         <td class="${currentClass}">&#x2022;<input type='text'></td>
                //        </tr>`;
                //             $(this).closest('tr').after(newLine);
                //         }
                //     });
                //
                //     $(document).on( 'change', '.select_level', function() {
                //         let selectedOption = $(this).find(':selected').val();
                //         $(this).closest('tr').find('td:nth-child(2)').removeClass().addClass('iagai_subtitle_level_' + selectedOption);
                //     });
                });
            </script>
        
        <?php
	}
}
