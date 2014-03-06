<?php namespace SWS_WPSL;

	defined( 'ABSPATH' ) OR exit;

	if ( ! class_exists('WPSL') ) {


		/**
		* Plugin Name: SWS WP Sports Leagues
		* Plugin URI: http://wpsportsleagues.com
		* Description:  An all-in-one management solution for recreational sports leagues. Currently available for baseball only.  More sports planned.
		* Version: 1.0.0
		* Author: Sharp Web Solutions
		* Author URI: http://sharpwebsolutions.com
		* License: ??
		*
		**/
		class WPSL {

			public $opts;

			private $_addons;


			/**
			 * Constructor
			 *
			 * Initialize the plugin
			 * 
			 * @since 1.0
			 * @param void
			 * @return void
			 */
			public function __construct()
			{
				// Setup base plugin constants
				$this->setup_constants();

				// Load any helper files
				$this->load_helpers();

				// Set or update database options
				$this->setup_default_options();

				// Autoload any needed classes
				spl_autoload_register( array($this, 'autoload_classes') );

				// Setup action and filter hooks
				$this->setup_hooks();
			}


			/**
			 * Setup Plugin Constants
			 *
			 * @since 1.0
			 * @param void
			 * @return void
			 */
			public function setup_constants()
			{
				// Plugin name
				define('SWS_WPSL_PLUGIN_NAME', 'SWS WP Sports Leagues');

				// Plugin file name
				define('SWS_WPSL_PLUGIN_FILE', plugin_basename(__FILE__));

				// Plugin version
				define('SWS_WPSL_VERSION', '1.0.0');
				
				// WPSM current database version
				define('SWS_WPSL_DB_VERSION', 1);

				// WordPress compatibility version number
				define('SWS_WPSL_REQUIRED_WP_VERSION', '3.5.2');
				
				// WPSM required PHP version
				define('SWS_WPSL_REQUIRED_PHP_VERSION', '5.3.0');

				// Plugin Website URL
				define('SWS_WPSL_PLUGIN_URL', 'http://wpsportsleagues.com');

				// Plugin Support URL
				define('SWS_WPSL_SUPPORT_URL', SWS_WPSL_PLUGIN_URL . '/support');

				// WordPress Version
				define('WP_VERSION', get_bloginfo('version'));
				
				// Skip choosing a sport to install
				define('SWS_WPSL_SKIP_INSTALL', FALSE);

				// Plugin path
				define('SWS_WPSL_PATH', plugin_dir_path(__FILE__));
				
				// Controller directory
				define('SWS_WPSL_OPTIONS', SWS_WPSL_PATH . 'options/');
				
				// Controller directory
				define('SWS_WPSL_CLASSES', SWS_WPSL_PATH . 'classes/');
				
				// Views directory
				define('SWS_WPSL_VIEW', SWS_WPSL_PATH . 'views/');
				
				// Pages view directory
				define('SWS_WPSL_PAGES', SWS_WPSL_VIEW . 'pages/');
				
				// Settings pages view directory
				define('SWS_WPSL_OPTIONS_PAGES', SWS_WPSL_VIEW . 'pages/options/');
				
				// Dashboard widgets pages view directory
				define('SWS_WPSL_DASHBOARD_WIDGET_PAGES', SWS_WPSL_VIEW . 'pages/dashboard_widgets/');
				
				// Partials view directory
				define('SWS_WPSL_PARTIALS', SWS_WPSL_VIEW . 'partials/');
				
				// Helper directory
				define('SWS_WPSL_HELPERS', SWS_WPSL_PATH . 'helpers/');
				
				// Includes directory
				define('SWS_WPSL_INC', SWS_WPSL_PATH . 'inc/');
				
				// JavaScript directory
				define('SWS_WPSL_JS', SWS_WPSL_VIEW . 'js/');	
				
				// CSS directory
				define('SWS_WPSL_CSS', SWS_WPSL_VIEW . 'css/');	
				
				// Image directory
				define('SWS_WPSL_IMG', SWS_WPSL_VIEW . 'img/');
				
				// Image URL
				define('SWS_WPSL_IMG_URL', plugins_url('wp-sports-leagues/view/img/'));
				
				// Style URL
				define('SWS_WPSL_CSS_URL', plugins_url('wp-sports-leagues/view/css/'));
				
				// JavaScript URL
				define('SWS_WPSL_JS_URL', plugins_url('wp-sports-leagues/view/js/'));
			}


			public function setup_hooks()
			{
				register_activation_hook( __FILE__, array($this, 'on_activation') );
				register_deactivation_hook( __FILE__, array($this, 'on_deactivation') );

				add_action( 'init', array($this, 'get_all_options') );
				add_action( 'init', array($this, 'instantiate_classes') );
				add_action( 'init', array($this, 'setup_post_types') );
				add_action( 'init', array($this, 'setup_taxonomies') );
				add_filter( 'plugin_action_links_' . SWS_WPSL_PLUGIN_FILE, array($this, 'filter_plugin_action_links') );
				add_filter( 'manage_edit-teams_columns', array($this, 'custom_teams_table_columns') );
			}


			/**
			 * Get the core plugin's option array
			 *
			 * @since 1.0
			 * @param void
			 * @return array the plugin options array
			 */
			public function get_options_array()
			{
				return require(SWS_WPSL_OPTIONS . 'sws_wpsl_options.php');
			}


			public function load_helpers()
			{
				// Load any helper functions
				require_once(SWS_WPSL_HELPERS . 'sws_wpsl_helpers.php');
			}


			/**
			 * Check database for core plugin options and update as necessary
			 *
			 * @since 1.0
			 * @param void
			 * @return void
			 */
			public function setup_default_options()
			{
				$options = sws_get_plugin_options();
				$options = $options ? $options : new \stdClass;
				$options->plugin_version = SWS_WPSL_VERSION;
				update_option('wpsl_core', $options);
			}


			/**
			 * Registered autoloading method
			 *
			 * Load any classes passed in from the classes directory
			 * 
			 * @since 1.0
			 * @param string $class class name
			 * @return void
			 */
			public function autoload_classes( $class, $dir = SWS_WPSL_CLASSES )
			{
				$class = str_replace('sws_wpsl\\', '', strtolower($class));
				$file = $dir . "$class.class.php";

				if ( file_exists($file) )
				{
					require_once($file);
				}
			}


			/**
			 * Instantiate necessary plugin classes
			 *
			 * @since 1.0
			 * @param void
			 * @return void
			 */
			public function instantiate_classes()
			{
				new Options($this->opts['option_pages']);
				new Dashboard_Widgets($this->opts);
			}


			/**
			 * Do activation stuff here
			 *
			 * @since 1.0
			 * @param void
			 * @return void
			 */
			public function on_activation()
			{
				
			}


			/**
			 * Do deactivation stuff here
			 *
			 * @since 1.0
			 * @param void
			 * @return void
			 */
			public function on_deactivation()
			{

			}


			/**
			 * Setup Post Types
			 *
			 * Callback to setup any post types for the plugin
			 * using the option array values
			 *
			 * @since 1.0.0
			 * @param void
			 * @return void
			 */
			public function setup_post_types()
			{
				foreach ( $this->opts['post_types'] as $post_type => $args )
				{
					register_post_type( $post_type, $args );
					add_filter( 'enter_title_here', array($this, 'change_title_placeholder'), 10, 2 );
				}
			}


			/**
			 * Setup Custom Taxonomies Types
			 *
			 * Callback to setup any custom taxonomies for the plugin
			 * using the option array values
			 *
			 * @since 1.0.0
			 * @param void
			 * @return void
			 */
			public function setup_taxonomies()
			{
				foreach ( $this->opts['taxonomies'] as $taxonomy => $args )
				{
					register_taxonomy( $taxonomy, $taxonomy, $args );
				}
			}


			/**
			 * Plugin Action Links
			 * 
			 * Add additional important action links related to the plugins
			 * links on the plugin page using the option array values
			 *
			 * @since 1.0.0
			 * @param $links the WordPress action links array
			 * @return $links modified links array
			 */
			public function filter_plugin_action_links( array $links )
			{
				return wp_parse_args(
					$this->opts['action_links'],
					$links
				);
			}


			/**
			 * Team table columns
			 *
			 * Customize Teams custom post type table column names
			 *
			 * @since 1.0
			 * @param columns array of current tagle columns
			 * @return array the plugin options array
			 */
			public function custom_teams_table_columns( array $columns )
			{
				$columns = wp_parse_args( $this->opts['table_columns']['teams'], $columns );
				unset($columns['date']);
				return $columns;
			}


			/**
			 * Get all plugin options (including registered addons)
			 *
			 * @since 1.0
			 * @param void
			 * @return array the plugin options array
			 */
			public function get_all_options()
			{
				$this->opts = $this->get_options_array();
				if ( ! empty($this->_addons) )
				{
					foreach ( $this->_addons as $addon_key => $addon )
					{
						foreach ( $addon['options'] as $opt_key => $options )
						{
							$this->opts[$opt_key][] = $options[$addon_key];
						}
					}
				}
				return $this->opts;
			}


			/**
			 * Register addon
			 *
			 * If there are addons installed and activated they will call
			 * this function to register themselves with the core plugin
			 *
			 * @since 1.0
			 * @param void
			 * @return void set $opts property
			 */
			public function register_addons( array $addon = array() )
			{
				$this->_addons = array();
				array_push( $this->_addons, $addon );
			}


			public function change_title_placeholder( $title )
			{
				$screen = get_current_screen();
				switch ( $screen->post_type )
				{
					case 'teams':
						$str = 'team name';
						break;
					
					case 'leagues':
						$str = 'league name (ex. Midget League, Pony League, etc.)';
						break;
					
					case 'divisions':
						$str = 'division name (ex. Baseball, etc.)';
						break;
					
					case 'events':
						$str = 'event name';
						break;

					case 'sponsor_regs':
						$str = 'sponsor\'s name/business';
						break;

					default:
						$str = 'title';

				}
				return sprintf('Enter %s here', $str);
			}

		}

		// Get the party started...
		$wpsl = new WPSL;
	}