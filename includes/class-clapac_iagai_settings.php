<?php
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/AI_Config.php';
add_action( 'wp_ajax_save-writing-mode-options', array( 'Clapac_iagai_settings', 'save_writing_mode_options' ) );
add_action( 'wp_ajax_save-datetime-limits-settings', array( 'Clapac_iagai_settings', 'save_date_time_limits' ) );
add_action( 'wp_ajax_save_api_settings', array( 'Clapac_iagai_settings', 'save_api_settings' ) );


class Clapac_iagai_settings {

    // Show the tabs content
	public function clapac_iagai_options_page() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'api_settings';
		?>
        <div class="iagai_wrap">
            <div class="nav-tab-wrapper">
                <a href="?page=clapac_iagai&tab=api_settings" class="nav-tab <?php if($tab == 'api_settings') echo 'nav-tab-active'; ?>"><?_e('API Settings', 'clapac_iagai');?></a>
                <a href="?page=clapac_iagai&tab=post_data_time" class="nav-tab <?php if($tab == 'post_data_time') echo 'nav-tab-active'; ?>"><?_e('Posts date and time', 'clapac_iagai');?></a>
                <a href="?page=clapac_iagai&tab=writing" class="nav-tab <?php if($tab == 'writing') echo 'nav-tab-active'; ?>"><?_e('Writing', 'clapac_iagai');?></a>
                <a href="?page=clapac_iagai&tab=limits" class="nav-tab <?php if($tab == 'limits') echo 'nav-tab-active'; ?>"><?_e('Text Limits', 'clapac_iagai');?></a>
            </div>
            <div id="tab_container">
				<?php
				if ($tab == 'api_settings') {
					$this->clapac_iagai_api_settings_section();
				} else if ($tab == 'post_data_time') {
					$this->clapac_iagai_post_data_time_section();
				} else if ($tab == 'writing') {
					$this->clapac_iagai_writing_section();
				} else if ($tab == 'limits') {
					$this->clapac_iagai_limits_section();
				}
				?>
            </div><!-- #tab_container-->
        </div><!-- .wrap -->
		<?php
	}


// Show API settings content
public function clapac_iagai_api_settings_section() {

	$pixabay_key = get_option('clapac_iagai_pixabay_key');
	$openAI_key = get_option('clapac_iagai_openAI_key');
	?>
    <div id="tab-api_settings">
        <h1 class="iagai_h1"><?php _e("API Keys", "clapac_iagai") ?></h1>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="clapac_iagai_openAI_key"><?php _e("OpenAI API Key", "clapac_iagai") ?>:</label></th>
                <td>
                    <input type="text" id="clapac_iagai_openAI_key" name="clapac_iagai_openAI_key" value="<?php echo $openAI_key; ?>" size="50" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="clapac_iagai_pixabay_key"><?php _e("Pixabay Keys", "clapac_iagai") ?>:</label></th>
                <td>
                    <input type="text" id="clapac_iagai_pixabay_key" name="clapac_iagai_pixabay_key" value="<?php echo $pixabay_key; ?>" size="50" />
                </td>
            </tr>
        </table>
        <input type="submit" value="<?php _e("Save", "clapac_iagai") ?>" class="button save_api_settings button-primary button-large" />
    </div>
    <script>
        jQuery(document).ready( function($) {
            $('.save_api_settings').click( function(e) {
            	e.preventDefault();
                var api_settings = {};
                api_settings.openAI_key = $('#clapac_iagai_openAI_key').val();
                api_settings.pixabay_key = $('#clapac_iagai_pixabay_key').val();

                $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: {
                        action: "save_api_settings",
                        clapac_iagai_api_settings: api_settings
                    },
                    success: function(response) {
                        location.reload();
                    }
                });
            });
        });
    </script>
	<?php
}

// Show Posts data and time content
public function clapac_iagai_post_data_time_section() {

	$options = get_option('clapac_iagai_date_limits_settings');
    if (!isset($options) or !is_array($options)){
	    $options = AI_Config::$PUBLICATION_DATE_CONFIG;
        update_option('clapac_iagai_date_limits_settings', $options);
    }
    $start_date = $options['start_date'];
    $start_time = $options['start_time'];
    $end_date = $options['end_date'];
    $end_time = $options['end_time'];
    $articles_day = $options['articles_day'];
    ?>
        <div id="tab-post_data_time">
            <h1 class="iagai_h1"><?php _e('Publish Date and Time', 'clapac_iagai') ?></h1>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><label for="clapac_iagai_start_date"><?php _e('Start date', 'clapac_iagai') ?></label></th>
                    <td>
                        <input type="date" id="clapac_iagai_start_date" name="clapac_iagai_start_date" value="<?php echo $start_date; ?>:" size="50" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="clapac_iagai_start_time"><?php _e('Start time', 'clapac_iagai') ?>:</label></th>
                    <td>
                        <input type="time" id="clapac_iagai_start_time" name="clapac_iagai_start_time" value="<?php echo $start_time; ?>" size="50" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="clapac_iagai_end_date"><?php _e('End date', 'clapac_iagai') ?>:</label></th>
                    <td>
                        <input type="date" id="clapac_iagai_end_date" name="clapac_iagai_end_date" value="<?php echo $end_date; ?>" size="50" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="clapac_iagai_end_time"><?php _e('End Time', 'clapac_iagai') ?>:</label></th>
                    <td>
                        <input type="time" id="clapac_iagai_end_time" name="clapac_iagai_end_time" value="<?php echo $end_time; ?>" size="50" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="clapac_iagai_articles_day"><?php _e('Articles per Day', 'clapac_iagai') ?>:</label></th>
                    <td>
                        <input type="number" id="clapac_iagai_articles_day" name="clapac_iagai_articles_day" value="<?php echo $articles_day; ?>" min="1" max="6" size="50" />
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="Submit" class="button-primary save-change-datetime-limits" value="<?php _e('Save changes', 'clapac_iagai') ?>" />
                <input type="submit" name="clapac_iagai_restore_defaults" value="<?php _e('Restore defaults', 'clapac_iagai') ?>" class="button-primary" />
            </p>
            </form>
        </div>
        <script>
            jQuery(document).ready( function($) {
                $('.save-change-datetime-limits').click(function (e) {
                    e.preventDefault();

                    var openAI_key = jQuery('#clapac_iagai_openAI_key').val();
                    var pixabay_key = jQuery('#clapac_iagai_pixabay_key').val();
                    var start_date = jQuery('#clapac_iagai_start_date').val();
                    var start_time = jQuery('#clapac_iagai_start_time').val();
                    var end_date = jQuery('#clapac_iagai_end_date').val();
                    var end_time = jQuery('#clapac_iagai_end_time').val();
                    var articles_day = jQuery('#clapac_iagai_articles_day').val();

                    var options = {
                        openAI_key: openAI_key,
                        pixabay_key: pixabay_key,
                        start_date: start_date,
                        start_time: start_time,
                        end_date: end_date,
                        end_time: end_time,
                        articles_day: articles_day
                    };
                    $.ajax({
                        url: ajaxurl,
                        data: {
                            action: 'save-datetime-limits-settings',
                            options: options
                        },
                        method: 'POST',
                        dataType: 'json',
                        success: function (response) {

                            if (response.success == true) {
                                window.location.reload();
                            }
                        }
                    });
                });
            });
        </script>

        <?php
    }


    private function generate_options($options_array, $selected_options, $selected) {

        $options = array();
        foreach ($options_array as $val => $translation) {
            $option = new stdClass();
            $option->value = $val;
            $option->text = esc_html__($translation['label'],'clapac_iagai');
            $option->description = $translation['description'];
            $option->selected = $selected && in_array($val, $selected_options);
            $options[] = $option;
        }

        usort($options, function ($a, $b) {
            return strcmp($a->text, $b->text);
        });
        $options_str = '';
	    foreach ($options as $option) {
		    if ($selected && in_array($option->value, $selected_options)) {
			    $options_str .= '<option value="' . $option->value .
			                    '" data-value-translate="' . esc_html__($option->text, 'clapac_iagai') .
			                    '" selected>' . esc_html__($option->text, 'clapac_iagai') .'</option>';

		    }
            if (!$selected && !in_array($option->value, $selected_options)){
			    $options_str .= '<option  value="' . $option->value .
			                    '" data-value-translate="' . esc_html__($option->text, 'clapac_iagai') .
			                    '">' . esc_html__($option->text, 'clapac_iagai') .'</option>';
		    }
	    }
        return $options_str;
    }

    function clapac_iagai_writing_section(){
        ?>
        <div id="tab-writing">
        <?php

        // Get user selected values from database and then fill selected and available options
	    $options = get_option('clapac_iagai_writing_settings');
	    $selected_styles = isset($options['selected_styles']) ? $options['selected_styles'] : array();
	    $selected_tones = isset($options['selected_tones']) ? $options['selected_tones'] : array();
	    $selected_moods = isset($options['selected_moods']) ? $options['selected_moods'] : array();

	    //Style
        $style_right_column_options = $this->generate_options(AI_Config::$STYLE, $selected_styles, true);
        $style_left_column_options = $this->generate_options(AI_Config::$STYLE, $selected_styles, false);

        //Tone
        $tone_right_column_options = $this->generate_options(AI_Config::$TONE, $selected_tones, true);
        $tone_left_column_options  = $this->generate_options(AI_Config::$TONE, $selected_tones, false);;

        //Mood / Emotion
        $mood_right_column_options = $this->generate_options(AI_Config::$MOOD_EMOTION, $selected_moods, true);
        $mood_left_column_options  = $this->generate_options(AI_Config::$MOOD_EMOTION, $selected_moods, false);;


        $this->generate_help_modal('style', AI_Config::$STYLE);
        $this->generate_help_modal('mood', AI_Config::$MOOD_EMOTION);
        $this->generate_help_modal('tone', AI_Config::$TONE);

	    //UI
	    ?>
        <div class="iagai_writing_settings_wrap">
            <form method="post">
                <div class="iagai_writing_settings_attribute">
                    <label for="writing_setting_form">
                        <?php _e('Style', 'clapac_iagai');?>
                        <button type="button" id="style-help-button" class="btn btn-light iagai_help_button iagai_style_help_button">
                            <i class="fas fa-question-circle"></i>
                        </button>
                    </label>
                    <div id="writing_setting_form" class="iagai_writing_setting_form">
                        <div class="iagai_left_column">
                            <label for="iagai_left_column"><?php _e('Available', 'clapac_iagai');?></label>
                            <select multiple name="iagai_left_column" id="style-left-column">
                                <?php echo $style_left_column_options; ?>
                            </select>
                        </div>
                        <div class="iagai_writing_settings_buttom_column">
                            <input type="button" onclick="moveOptions(document.getElementById('style-left-column'), document.getElementById('style-right-column'))" value=">" />
                            <input type="button" onclick="moveOptions(document.getElementById('style-right-column'), document.getElementById('style-left-column'))" value="<" />
                        </div>
                        <div class="iagai_right_column">
                            <label for="iagai_right_column"><?php _e('Selected', 'clapac_iagai');?></label>
                            <select multiple name="selected_style" id="style-right-column">
                                <?php echo $style_right_column_options; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="iagai_writing_settings_attribute">
                    <label for="writing_setting_form">
                        <?php _e('Tone', 'clapac_iagai');?>
                        <button type="button" id="tone-help-button" class="btn btn-light iagai_help_button iagai_tone_help_button">
                            <i class="fas fa-question-circle"></i>
                        </button>
                    </label>
                    <div class="iagai_writing_setting_form">
                        <div class="iagai_left_column">
                            <label for="iagai_left_column"><?php _e('Available', 'clapac_iagai');?></label>
                            <select multiple name="iagai_left_column" id="tone-left-column">
                                <?php echo $tone_left_column_options; ?>
                            </select>
                        </div>
                        <div class="iagai_writing_settings_buttom_column">
                            <input type="button" onclick="moveOptions(document.getElementById('tone-left-column'), document.getElementById('tone-right-column'))" value=">" />
                            <input type="button" onclick="moveOptions(document.getElementById('tone-right-column'), document.getElementById('tone-left-column'))" value="<" />
                        </div>
                        <div class="iagai_right_column">
                            <label for="tone-right-iagai_right_column"><?php _e('Selected', 'clapac_iagai');?></label>
                            <select multiple name="selected_tone" id="tone-right-column">
                                <?php echo $tone_right_column_options; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="iagai_writing_settings_attribute">
                    <label for="writing_setting_form">
                        <?php _e('Mood / Emotion', 'clapac_iagai');?>
                        <button type="button" id="mood-help-button" class="btn btn-light iagai_help_button iagai_mood_help_button">
                            <i class="fas fa-question-circle"></i>
                        </button>
                    </label>
                    <div class="iagai_writing_setting_form">
                        <div class="iagai_left_column">
                            <label for="iagai_left_column"><?php _e('Available', 'clapac_iagai');?></label>
                            <select multiple  name="iagai_left_column" id="mood-left-column">
                                <?php echo $mood_left_column_options; ?>
                            </select>
                        </div>
                        <div class="iagai_writing_settings_buttom_column">
                            <input type="button" onclick="moveOptions(document.getElementById('mood-left-column'), document.getElementById('mood-right-column'))" value=">" />
                            <input type="button" onclick="moveOptions(document.getElementById('mood-right-column'), document.getElementById('mood-left-column'))" value="<" />
                        </div>
                        <div class="iagai_right_column">
                            <label for="iagai_right_column"><?php _e('Selected', 'clapac_iagai');?></label>
                            <select multiple name="selected_mood" id="mood-right-column">
                                <?php echo $mood_right_column_options; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <input type="submit" value="<?php _e('Save', 'clapac_iagai'); ?>" class="button save_writing_mode_options button-primary button-large" />
            </form>
        </div>

            <script type="text/javascript">
                function moveOptions(from, to) {
                        var selectedOptions = [];
                        var options = from.options;
                        for (var i = 0; i < options.length; i++) {
                            var opt = options[i];
                            if (opt.selected) {
                                selectedOptions.push(opt);
                            }

                        }
                        //verificação adicional para evitar adição vazia
                        if(selectedOptions.length === 0){
                            return;

                        }
                        // ordena as opções alfabeticamente
                        selectedOptions.sort(function(a, b) {
                            var textA = a.text.toUpperCase();
                            var textB = b.text.toUpperCase();
                            return (textA < textB) ? -1 : (textA > textB) ? 1 : 0;

                        });
                        for (var i = 0; i < selectedOptions.length; i++) {
                            var option = selectedOptions[i];
                            to.appendChild(option);

                        }
                        // Ordena todas as opções
                        options = to.options;
                        for (var i = 0; i < options.length; i++) {
                            options[i].selected = false;
                        }
                        options = from.options;
                        for (var i = 0; i < options.length; i++) {
                            options[i].selected = false;
                        }
                        sortOptions(to);
                        sortOptions(from);

                    }
                    function sortOptions(select) {
                        var options = select.options;
                        var optArray = [];
                        for (var i = 0; i < options.length; i++) {
                            optArray.push(options[i]);
                        }
                        optArray.sort(function(a, b) {
                            var textA = a.text.toUpperCase();
                            var textB = b.text.toUpperCase();
                            return (textA < textB) ? -1 : (textA > textB) ? 1 : 0;
                        });
                        select.innerHTML = '';
                        for (var i = 0; i < optArray.length; i++) {
                            select.appendChild(optArray[i]);
                        }
                    }
                </script>

                <script>
                    jQuery(document).ready(function ($){

                        jQuery(document).on('click', '.iagai_style_help_button', function() {
                            // Mostrando o modal de ajuda de estilo
                            $('#iagai_style_help_modal').modal('show');
                        });

                        jQuery(document).on('click', '.iagai_mood_help_button', function() {
                            // Mostrando o modal de ajuda de humor
                            $('#iagai_mood_help_modal').modal('show');
                        });

                        jQuery(document).on('click', '.iagai_tone_help_button', function() {
                            // Mostrando o modal de ajuda de tom
                            $('#iagai_tone_help_modal').modal('show');
                        });


                        $('.save_writing_mode_options').click(function (e) {
                            e.preventDefault();
                            var selected_styles = [];
                            var selected_tones = [];
                            var selected_moods = [];

                            $('select[name="selected_style"] option').each(function () {
                                selected_styles.push(this.value);
                            });
                            $('select[name="selected_tone"] option').each(function () {
                                selected_tones.push(this.value);
                            });
                            $('select[name="selected_mood"] option').each(function () {
                                selected_moods.push(this.value);
                            });
                            $.ajax({
                                type: "POST",
                                url: ajaxurl,
                                data: {
                                    action: 'save-writing-mode-options',
                                    selected_styles: selected_styles,
                                    selected_tones: selected_tones,
                                    selected_moods: selected_moods
                                },
                                success: function (response) {
                                    console.log(response);
                                }
                            });
                        });
                    });
            </script>
        </div id="tab-writing">
	    <?php
    }


    public function generate_help_modal($option, $options_help){
        ?>

        <div id="iagai_<?php echo ($option) ?>_help_modal" class="modal fade">
            <div class="modal-dialog iagai_modal_dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title iagai_modal_title" id="helpModalLabel">
                            <?php _e(ucfirst($option),'clapac_iagai'); ?>
                        </h5>
                        <button type="button" class="close iagai_close_button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body iagai_modal_body">
                        <ul>
                            <?php
                            usort($options_help, function ($a, $b) {
	                            return strcmp(esc_html__($a['label'], 'clapac_iagai'), esc_html__($b['label'], 'clapac_iagai'));
                            });

                            foreach ($options_help as $val => $translation){
                                echo ("<li><strong>".esc_html__($translation['label'], 'clapac_iagai')."</strong>: ".
                                      esc_html__($translation['description'], 'clapac_iagai')."</li>");
                            }
                            ?>
                        </ul>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public static function save_writing_mode_options() {

        $selected_styles = (isset($_POST['selected_styles'])?$_POST['selected_styles']:null);
	    if ( is_array($selected_styles)
                 and count($selected_styles)>0) {
		    $options['selected_styles'] = $selected_styles;
	    }
	    else {
		    $options['selected_moods'] = array();
	    }

	    $selected_tones = (isset($_POST['selected_tones'])?$_POST['selected_tones']:null);
	    if ( is_array($selected_tones)
	             and count($selected_tones)>0) {
		    $options['selected_tones'] = $selected_tones;
	    }
	    else {
		    $options['selected_moods'] = array();
	    }

	    $selected_moods = (isset($_POST['selected_moods'])?$_POST['selected_moods']:null);
	    if ( is_array($selected_moods)
	             and count($selected_moods)>0) {
		    $options['selected_moods'] = $selected_moods;
	    }
        else {
	        $options['selected_moods'] = array();
        }

        update_option('clapac_iagai_writing_settings', $options);
        die();
    }

	// This function is to save the options
	public static function save_date_time_limits(){
		if (!isset($_POST['options'])){
			die();
		}
		$new_options = $_POST['options'];

		$options = get_option('clapac_iagai_date_limits_settings');
		if ($new_options['start_date'])
			$options['start_date'] = $new_options['start_date'];

		if ($new_options['start_time'])
			$options['start_time'] = $new_options['start_time'];

		if ($new_options['end_date'])
			$options['end_date'] = $new_options['end_date'];

		if ($new_options['end_time'])
			$options['end_time'] = $new_options['end_time'];

		if ($new_options['articles_day'])
			$options['articles_day'] = $new_options['articles_day'];

		update_option('clapac_iagai_date_limits_settings', $options);

		exit;
	}

	public static function save_api_settings(){

		if (isset ($_POST['clapac_iagai_api_settings'])){
			$settings = $_POST['clapac_iagai_api_settings'];

			update_option('clapac_iagai_pixabay_key', $settings['pixabay_key']);
			update_option('clapac_iagai_openAI_key', $settings['openAI_key']);
		}
		die();
	}
}