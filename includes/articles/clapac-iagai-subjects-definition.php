<?php

// Início da página
function clapac_iagai_subjects_definition_page(){
// Criação da tabela
    if (isset($_POST['exists']) && $_POST['exists']){

    }
    else {
	    global $wpdb;
	    $subjects = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."clapac_iagai_post_subjects order by id");
    }
	?>
    <style type="text/css">
        .row-selected {
            background-color: #e2e2e2;
        }
    </style>
    <div class="wrap">
        <h1 class="iagai_h1">Post Subjects Definition</h1>
        <!-- Criação da tabela -->
        <form action="" method="post">
            <div class="table-responsive">
                <table id="post_subjects" class="table table-striped iagai_table">
                    <thead>
                    <tr>
                        <th class="iagai_checkbox"><input type="checkbox" class="select-all"/></th>
                        <th class="iagai_checkbox">Created</th>
                        <th>Post Subject</th>
                    </tr>
                    </thead>
                    <tbody>
					<?php
					// Imprime cada linha da tabela
                    $line = 0;
					foreach($subjects as $subject){

						?>
                        <tr>
                            <td><input type="checkbox" name="selected[]" class="select-row" value="<?php echo $line; ?>"/></td>
                            <td><?php echo($subject->created?'&#10004;':'') ?></td>
                            <td><input type="hidden" name="bd_id[]" value="<?php echo $subject->id?>"/> <input type="text" name="post_subject[]" value="<?php echo $subject->post_subject; ?>"/></td>
                        </tr>
						<?php
                        $line ++;
					}
					?>
                    </tbody>
                </table>
            </div>
            <!-- Botões flutuantes -->
            <div class="float-buttons iagai_buttons">
                <input type="button" class="button button-primary iagai_button_subjects_definition" id="add-new-line" value="Add new line" />
                <input type="submit" class="button button-primary iagai_button_subjects_definition" name="save-all" value="Save all" />
                <input type="submit" class="button button-primary iagai_button_subjects_definition" name="delete_selected" value="Delete selected" />
                <input type="submit" class="button button-primary iagai_button_subjects_definition" name="delete_all" value="Delete all" />
            </div>
            <div class="float-buttons iagai_buttons">
                <input type="button" class="button button-primary iagai_post_generation_start_button" name="start-generator-proccess" value="Start post generator proccess" />
            </div>
        </form>
    </div>
    <script type="text/javascript">
        // Javascript/jQuery/AJAX
        jQuery(document).ready(function($) {

            function createNewLine(doc) {
                var lastLine = parseInt($("#post_subjects tr:last input[name='selected[]']").val()) + 1;

                $("#post_subjects > tbody").append('<tr><td><input type="checkbox" name="selected[]" class="select-row" value="' + lastLine + '"/></td>' +
                    '<td></td><td><input type="hidden" name="bd_id[]" value=""  />' +
                    '<input type="text" name="post_subject[]" value=""/></td></tr>');
            }

            // Função para selecionar/desselecionar os checkbox da tabela
            $('.select-all').click(function () {
                // Seleciona todos os checkbox
                if ($(this).prop('checked') == true) {
                    $('.select-row').prop('checked', true);
                    $('tbody tr').addClass('row-selected');
                }
                // Desseleciona todos os checkbox
                else {
                    $('.select-row').prop('checked', false);
                    $('tbody tr').removeClass('row-selected');
                }
            });

            // Função para destacar as linhas selecionadas
            $('.select-row').click(function () {
                if ($(this).prop('checked') == true) {
                    $(this).parent().parent().addClass('row-selected');
                } else {
                    $(this).parent().parent().removeClass('row-selected');
                }
            });

            //Create a button that creates a blank line at the end of the table
            $('#add-new-line').click(function () {
                // Cria uma nova linha em branco
                createNewLine($(this));

            });


            $(document).on('keypress', 'input[name="post_subject[]"]', function (event) {
                if (event.keyCode == 13) {
                    var index = $(this).closest('td').index();
                    var row = $(this).closest('tr').index();
                    var totalRows = $(this).closest('tbody').find('tr').length;

                    event.preventDefault($(this));
                    if (row == totalRows - 1) {
                        createNewLine($(this));
                        $(this).closest('tr').next().find('td').eq(index).find('input').focus();
                    } else {
                        $(this).closest('tr').next().find('td').eq(index).find('input').focus();
                    }
                }
            });


            $('input[name="start-generator-proccess"]').click(function (e) {
                e.preventDefault();
                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'start_generator_proccess'
                    },
                    success: function(response) {
                        if (response.redirect) {
                            window.location.href = response.url;
                        }
                    }
                });
            });



            // Função para salvar os dados da tabela
            $('input[name="save-all"]').click(function () {
                var subjects = [];
                var ids = [];
                jQuery("input[name='post_subject[]']").each(function () {
                    subjects.push(jQuery(this).val());
                });
                jQuery("input[name='bd_id[]']").each(function () {
                    ids.push(jQuery(this).val());
                });
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'save_data',
                        post_subjects: subjects,
                        ids: ids
                    },
                    success: function (data) {
                        console.log(data);
                    }
                });
            });

            // Função para excluir linhas selecionadas
            $('input[name="delete_selected"]').click(function (e) {
                e.preventDefault();
                $('.select-row:checked').each(function (i, elem) {
                    if (confirm("Warning: deleting the subject will also delete titles that " +
                        "have not yet been selected. Do you want to delete anyway?")) {
                        var bd_id = $(elem).closest('tr').find('[name="bd_id[]"]').val();
                        if (bd_id) {
                            $.ajax({
                                url: ajaxurl,
                                type: 'POST',
                                data: {
                                    action: 'delete_selected',
                                    bd_id: bd_id
                                },
                                success: function (data) {
                                    console.log(data);
                                }
                            })
                                .done(function (data) {
                                    $(elem).closest('tr').remove();
                                });
                        } else {
                            // Remove row from table ( no need to remove from database )
                            $(elem).closest('tr').remove();
                        }
                    }
                });
            });

            // Função para limpar todos os dados da tabela
            $('input[name="delete_all"]').click(function () {
                if (confirm("Warning: deleting the subject will also delete titles " +
                    "that have not yet been selected. Do you want to delete anyway?")) {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'delete_all'
                        },
                        success: function (data) {
                            location.reload();
                        }
                    });
                }
            });

        });


    </script>
	<?php
}



// Função para salvar os dados da tabela
add_action('wp_ajax_save_data', 'save_data');
function save_data(){

	if(!isset($_POST['post_subjects']) && !is_array($_POST['post_subjects']))
        die();

    // For the empty table
	if(!isset($_POST['ids'])){
        $ids=array();
    }
    else{
	    $ids = $_POST['ids'];
    }

    $post_subjects = $_POST['post_subjects'];

    // Get the values saved on DB to know if we update or insert database
	global $wpdb;
	$table = $wpdb->prefix.'clapac_iagai_post_subjects';
	$query = "SELECT id, post_subject FROM ".$wpdb->prefix."clapac_iagai_post_subjects order by id";
	$existingTable = $wpdb->get_results($query);


    //Check if the post_subjects array was sent
    if(!empty($post_subjects)){
    //Loop through the array and update the database
	    foreach($post_subjects as $key => $value){

		    // if post_subject from html table is empty, ignore and go to the next line
		    if (!$value OR trim($value) == ''){
			    continue;
		    }

            // to prevent from error caused by empty table
            $id = $key < count ($ids)?$ids[$key]:-1;
            // get the id from database if the post_subject already exists
		    $db_id = array_search($value,  array_column($existingTable, 'post_subject','id'));

            // get the post_subject from database if the id was found, or search the value in another line
            if ($db_id){
	            $db_value = array_search($value,  array_column($existingTable, 'post_subject','post_subject'));
            }
            else{
	            $db_value = array_search($id,  array_column($existingTable, 'id','post_subject'));
            }

            // if the post_subject and ID are the same that exists on the database
            if (in_array($value, array_column($existingTable, 'post_subject'))){
                if ($db_value == $value AND
                    $db_id == $ids[$key]){
                    continue;
                }
            }

            // Save or update. If the html table has an ID, update, otherwise, insert into DB
            if (!in_array($id, array_column($existingTable,'id'))){
                $sql = 'INSERT IGNORE INTO '.$table.' (post_subject) VALUES ("'.$value.'");';
	            $wpdb->query($sql);
            }
            else {
                $wpdb->update(
                    $table,
                    array(
                        'post_subject' => $value
                    ),
                    array( 'id' => $id )
                );
            }
		}
        //Return the response
		$response = json_encode(array(
			'status' => 200
		));
		echo $response;
		exit;
	}
	else{
        //Return the response
		$response = json_encode(array(
			'status' => 400
		));
		echo $response;
		exit;
	}

	wp_die();
}


// Função para excluir linhas selecionadas
add_action('wp_ajax_delete_selected', 'delete_selected' );
function delete_selected(){

	global $wpdb;
	$table_subjects = $wpdb->prefix . 'clapac_iagai_post_subjects';
	$table_suggested_title = $wpdb->prefix . 'clapac_iagai_suggested_title';

	$bd_id = $_POST['bd_id'];



	$wpdb->delete(
		$table_suggested_title,
		array( 'post_subject_id' => $bd_id ),
		array( '%d' )
	);
	$wpdb->delete(
		$table_subjects,
		array( 'id' => $bd_id ),
		array( '%d' )
	);

	echo json_encode(array( 'success' => true ));
	wp_die();
}

// Função para limpar a tabela
add_action('wp_ajax_delete_all', 'delete_all' );
function delete_all(){
	global $wpdb;
	$table_subjects = $wpdb->prefix . 'clapac_iagai_post_subjects';
	$table_suggested_title = $wpdb->prefix . 'clapac_iagai_suggested_title';

	$wpdb->query('DELETE FROM '.$table_suggested_title);

	$wpdb->query('DELETE FROM '.$table_subjects);
	echo 'All data was removed!';
	wp_die();
}


// Função para iniciar o processo de geração de posts
add_action('wp_ajax_start_generator_proccess', 'start_generator_proccess');
function start_generator_proccess(){

    $url = add_query_arg("page", "admin_action_create_title_suggestions", admin_url());

    echo json_encode(array(
		'redirect' => true,
		'url' => $url
	));

	wp_die();
}
?>