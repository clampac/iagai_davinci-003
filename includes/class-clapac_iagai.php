<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      0.5.0
 * @package    Clapac_iagai
 * @subpackage Clapac_iagai/includes
 * @author     Claudio M. Bittencourt Pacheco <claudio@meuscaminhos.com.br>
 */

class Clapac_iagai {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    0.7.0
	 * @access   protected
	 * @var      Clapac_iagai_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    0.7.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    0.7.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    0.7.0
	 */
	public function __construct() {
		if ( defined( 'CLAPAC_IAGAI_VERSION' ) ) {
			$this->version = CLAPAC_IAGAI_VERSION;
		} else {
			$this->version = '0.9.9';
		}
		$this->plugin_name = 'Clapac IAGAI - Image Attributes Generator using Artificial Intelligence';


		$stats = get_option('clapac_iagai_post_stats');
		if (!$stats){
			$stats = array ('prompt_tokens'=>0, 'completion_tokens'=>0, 'total_tokens'=>0, 'created_titles'=>0, 'created_articles'=>0);
			update_option('clapac_iagai_post_stats', $stats);
		}


		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();


		add_action('admin_menu', array($this, 'clapac_iagai_menu'));
		add_action('admin_init', array($this, 'clapac_iagai_admin_init'));
		add_action( 'admin_enqueue_scripts', array( $this, 'iagai_admin_scripts' ));
		add_action( 'plugins_loaded', array($this, 'clapac_iagai_load_textdomain' ));
	}

	// because Nginx + php-tfp wasn't flushing it's buffers...
    function iagai_flush() {
	    while ( @ob_end_flush() ) {
	    }
	    flush();
    }

	function clapac_iagai_load_textdomain() {
		load_plugin_textdomain( 'clapac_iagai', false, basename( dirname( __FILE__ ) ) . '/languages' );
	}


    public function iagai_admin_scripts(){


	    //styles	    wp_enqueue_style( 'font-awesome', get_stylesheet_directory_uri() . '/fontawesome-free-5.15.1-web/css/all.css' );

	    wp_enqueue_style( 'iagai-fontawesome', plugin_dir_url( __FILE__ ) . 'fonts/all.css' );
        wp_enqueue_style( 'clapac_iagai-styles', plugin_dir_url( __FILE__ ) . 'css/clapac_iagai_styles.css', array(), $this->version );
	    wp_enqueue_style( 'bootstrap', plugin_dir_url( __FILE__ ) . 'css/bootstrap.min.css' );

        // scripts
	    wp_enqueue_script( 'jquery-blockui', 'http://malsup.github.io/jquery.blockUI.js', array('jquery'), '1.33', true );
        wp_enqueue_script( 'bootstrap', plugin_dir_url( __FILE__ ) . 'js/bootstrap.min.js', array('jquery'), '', true );
        wp_enqueue_script( 'popper', plugin_dir_url( __FILE__ ) . 'js/popper.min.js', array('jquery'), '', true );

    }
    
	public function clapac_iagai_menu() {
		if ( current_user_can( 'manage_options' ) ) {
			add_menu_page( 'clapac_iagai', 'Article Writer', 'manage_options',
                'clapac_iagai', array(	$this, 'clapac_iagai_options_page' ) );
		}

        // Add menu options
		add_submenu_page( 'clapac_iagai', 'Dashboard', 'Dashboard', 'manage_options',
			'clapac_iagai_dashboard', array(	$this, 'clapac_iagai_dashboard_page') );

//
//		add_submenu_page('clapac_iagai','Image Name Update', 'Image Name Update',
//            'manage_options', 'image_name_update', 'image_name_update_page');
//
//		add_submenu_page('clapac_iagai','Insert or discard images found in PIXABAY', 'Add images found to posts',
//            'manage_options', 'iagai_add_images', array($this,'clapac_iagai_add_images'));
//
//		// Bulk generate images results page
//		add_submenu_page( null, 'Image file name update', '', 'manage_options',
//			'admin_action_generate_attributes', array($this,'admin_action_generate_attributes'	) );

		add_submenu_page( 'clapac_iagai', 'Clapac_iagai Subjects', '1. Set the subjects',
			'manage_options', 'clapac_iagai_subjects', array($this,'clapac_iagai_subjects_page'));

		// Suggested title generator
		add_submenu_page( 'clapac_iagai', 'Choose the best title for each subject', '2. Selected created titles', 'manage_options',
			'choose_titles_admin_menu', array($this,'suggested_titles_admin_menu_callback'	) );
		add_action( 'admin_menu', 'choose_titles_admin_menu' );

		// Suggested title generator
		add_submenu_page( 'clapac_iagai', 'Review subtitles for not written posts', '2. Review subtitles', 'manage_options',
			'review_subtitles', array($this,'review_subtitles_menu_callback'	) );
		add_action( 'admin_menu', 'choose_titles_admin_menu' );

		add_submenu_page( 'clapac_iagai', 'Select titles for post automatic generation', '4. Write Articles',
			'manage_options', 'clapac_iagai_generate_articles', array($this,'clapac_iagai_generate_articles_page'));

		// Suggested title generator
		add_submenu_page( null, 'Create title suggestions for subjects', '', 'manage_options',
			'admin_action_create_title_suggestions', array($this,'admin_action_create_title_suggestions_callback'	) );
		add_action( 'admin_menu', 'admin_action_generate_title' );

		// Article writer
		add_submenu_page( null, 'The great article writer', '', 'manage_options',
			'write_the_articles', array($this, 'write_the_articles_callback' ) );


		// Article writer
		add_submenu_page( null, 'Write the articles outline', '', 'manage_options',
			'article_outline_writer', array($this, 'article_outline_writer_callback' ) );


		// Write all articles
		add_submenu_page( null, 'Writing the articles for you!', null, 'manage_options',
			'write_articles', array($this,'write_articles_callback'	) );

		add_submenu_page( 'clapac_iagai', 'Settings', 'Settings', 'manage_options',
			'clapac_iagai', array(	$this, 'clapac_iagai_options_page') );


		add_submenu_page( 'clapac_iagai', 'Log', 'Log', 'manage_options',
			'clapac_iagai_log', array( $this, 'clapac_iagai_log_page') );

		add_submenu_page( 'clapac_iagai', 'About', 'About', 'manage_options',
			'clapac_iagai_about', array( $this, 'clapac_iagai_about_page') );

//
//		// Bulk search images results page
//		add_submenu_page( null, 'Image search', 'Image Search', 'manage_options',
//			'admin_action_search_images', array($this,'admin_action_search_images'	) );
//
//
//        //Create edit post bulk actions and filters
//        // Update Image Attributes
//		add_filter( 'bulk_actions-edit-post', function( $bulk_actions ) {
//			$bulk_actions['clapac_iagai_generate_images_attributes'] = __( 'Generate image attributes (IAGAI)', 'txtdomain' );
//			return $bulk_actions;
//		});
//
//		// Search Images for Posts
//		add_filter( 'bulk_actions-edit-post', function( $bulk_actions ) {
//			$bulk_actions['clapac_iagai_search_images'] = __( 'Search images (IAGAI)', 'txtdomain' );
//			return $bulk_actions;
//		});
//
//
//
//		add_filter( 'handle_bulk_actions-edit-post', function ( $redirect_to, $doaction, $post_ids ) {
//            if ( $doaction !== 'clapac_iagai_generate_images_attributes' ) {
//				return $redirect_to;
//			}
//
//			$redirect_to = add_query_arg(
//				'post_ids',$post_ids, admin_url( 'admin.php?page=admin_action_generate_attributes' ));
//			return $redirect_to;
//		}, 10, 3 );
//
//		add_filter( 'handle_bulk_actions-edit-post', function ( $redirect_to, $doaction, $post_ids ) {
//            if ( $doaction !== 'clapac_iagai_search_images' ) {
//				return $redirect_to;
//			}
//
//			$redirect_to = add_query_arg(
//				'post_ids',$post_ids, admin_url( 'admin.php?page=admin_action_search_images' ));
//			return $redirect_to;
//		}, 10, 3 );
	}


    function review_subtitles_menu_callback(){
	    $review_subtitles = new Clapac_review_subtitles();
        $review_subtitles->review_subtitles();

    }

    // Plugin page settings
    function clapac_iagai_options_page(){
        $optionsPage = new Clapac_iagai_settings();
        $optionsPage->clapac_iagai_options_page();
    }

    function clapac_iagai_generate_articles_page(){
	    show_posts_table();
    }

    function write_the_articles_callback(){
	    $postIds = $_REQUEST['ids'];
	    write_articles($postIds);
    }


    function article_outline_writer_callback(){
	    $postIds = $_REQUEST['ids'];
	    createArticleOutline($postIds);
    }

    function suggested_titles_admin_menu_callback(){
        suggested_titles_admin_page();
    }


	function clapac_iagai_add_images(){
		$add_post_images = new clapac_iagai_add_post_images();
        $add_post_images->clapac_iagai_add_images_found();
    }


	function admin_action_create_title_suggestions_callback(){
		create_title_suggestions();
	}


	public function image_name_update(){

	}

	function clapac_iagai_subjects_page(){
		clapac_iagai_subjects_definition_page();
    }

	function admin_action_search_images(){
        $postIds = $_REQUEST['post_ids'];
	    search_images($postIds);
    }

	function admin_action_generate_attributes(){
        $postIds = $_REQUEST['post_ids'];
	    generate_attributes($postIds);
    }

	public function clapac_iagai_admin_init() {
		register_setting(  'clapac_iagai_options', 'clapac_iagai_openAI_key', 'clapac_iagai_openAI_key_validation', 'clapac_iagai_date_limits_settings');
	}

	public function clapac_iagai_log_page() {
		?>
        <div class="wrap">
            <h2>clapac_iagai Log</h2>
			<?php
			$log_arr = get_option('clapac_iagai_log');
			if (!empty($log_arr)) {
				?>
                <ul>
					<?php foreach ($log_arr as $log_entry) { ?>
                        <li><?php echo $log_entry; ?></li>
					<?php } ?>
                </ul>
				<?php
			} else {
				echo '<p>No log entries found.</p>';
			}
			?>
        </div>
		<?php
	}

	public function clapac_iagai_about_page() {
		?>
        <div class="wrap">
            <h2>About <?php echo ($this->get_plugin_name()); ?></h2>
            <p>Plugin Name: <?php echo ($this->get_plugin_name()); ?></p>
            <p>Version: <?php echo ($this->get_version()); ?></p>
            <p>Author: Claudio M. Bittencourt Pacheco</p>
        </div>
		<?php
	}

	function clapac_iagai_dashboard_page(){

		$stats = get_option('clapac_iagai_post_stats');

?>
            <h1 class="iagai_h1">Statistics</h1>
        <table class="iagai_table">
            <thead>
            <tr>
                <th colspan="2">Created</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>Titles</td>
                <td><?php echo $stats['created_titles']; ?></td>
            </tr>
            <tr>
                <td>Articles</td>
                <td><?php echo $stats['created_articles']; ?></td>
            </tr>
            </tbody>
        </table>
        <table class="iagai_table">
            <thead>
                <tr>
                    <th colspan="2">OpenAI Usage</th>
                </tr>
            </thead>
            <tbody>
            <tr>
                <td>Prompt tokens</td>
                <td><?php echo $stats['prompt_tokens']; ?></td>
            </tr>
            <tr>
                <td>Completion tokens</td>
                <td><?php echo $stats['completion_tokens']; ?></td>
            </tr>
            <tr>
                <td>Total tokens</td>
                <td><?php echo $stats['total_tokens']; ?></td>
            </tr>
                <td>Gasto em US$</td>
                <td><?php echo ((($stats['total_tokens'])/1000)*.02); ?></td>
            </tbody>
        </table>
		<?php
	}


	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Clapac_iagai_Loader. Orchestrates the hooks of the plugin.
	 * - Clapac_iagai_i18n. Defines internationalization functionality.
	 * - Clapac_iagai_Admin. Defines all hooks for the admin area.
	 * - Clapac_iagai_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    0.7.0
	 * @access   private
	 */
	private function load_dependencies() {



		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-clapac_iagai_settings.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-clapac_iagai-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-clapac_iagai-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-clapac_iagai-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-clapac_iagai-public.php';

		/**
		 * The page responsible for generate image attributes for selected posts using openAI
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/images/clapac-iagai-generate-images-attributes.php';

		/**
		 * The page responsible for search images for selected posts using openAI
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/images/clapac-iagai-image-search.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/images/clapac-iagai-image-name-update.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/images/clapac_iagai_add_images_found.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/articles/clapac-iagai-subjects-definition.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/articles/clapac-iagai-create-title-suggestion.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/articles/clapac-iagai_select_titles.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/articles/clapac-iagai-init-write-the-articles.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/articles/clapac-iagai-create-articles.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/articles/class-clapac_review_subtitles.php';


        $this->loader = new Clapac_iagai_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Clapac_iagai_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    0.7.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Clapac_iagai_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

 	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    0.7.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Clapac_iagai_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    0.7.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Clapac_iagai_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    0.7.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     0.7.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     0.7.0
	 * @return    Clapac_iagai_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     0.7.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
