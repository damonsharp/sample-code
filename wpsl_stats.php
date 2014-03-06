<?php namespace SWS_WPSL;

	defined( 'ABSPATH' ) OR exit;

	if ( ! class_exists('Stats') ) {

		/**
		* Plugin Name: SWS WP Sports Leagues Statistics Addon
		* Plugin URI: http://wpsportsleagues.com/addons
		* Description:  Adds statistics management capabilities to the SWS WP Sports Leagues plugin. SWS WP Sports Leagues plugin is required.
		* Version: 1.0.0
		* Author: Sharp Web Solutions
		* Author URI: http://sharpwebsolutions.com
		* License: ??
		*
		**/
		class Stats {

			/**
			 * Contructor
			 * 
			 * @since 1.0
			 * @param void
			 * @return void
			 */
			public function __construct()
			{
				// Must be called at 'init'
				add_action('init', array($this, 'initialize'));
			}


			/**
			 * Initialize
			 *
			 * If the core WPSL class exists, initialize the addon plugin.
			 * 
			 * @since 1.0
			 * @param void
			 * @return void
			 */
			public function initialize()
			{
				// Setup base plugin constants
				$this->setup_stats_constants();

				// Check that 
				global $wpsl;
				if ( is_null($wpsl) )
				{
					add_action( 'admin_notices', array($this, 'missing_base_plugin') );
					add_action( 'network_admin_notices', array($this, 'missing_base_plugin') );
				}
				else
				{
					$this->register_addon();
					add_action( 'init', array($this, 'setup_autoload') );				
					add_action( 'init', array($this, 'instantiate_classes') );
				}
			}


			/**
			 * Autoload any necessary Stats classes
			 * 
			 * @since 1.0
			 * @param void
			 * @return void
			 */
			public function setup_autoload()
			{
				// Load any helper functions
				// require_once(SWS_WPSL_STATS_HELPERS . 'sws_wpsl_stats_helpers.php');

				// Autoload any needed classes
				spl_autoload_register( array($this, 'autoload_stats_classes') );
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
			public function autoload_stats_classes( $class )
			{
				global $wpsl;
				$wpsl->autoload_classes( $class, SWS_WPSL_STATS_CLASSES);
			}


			/**
			 * Setup Plugin Constants
			 *
			 * @since 1.0
			 * @param void
			 * @return void
			 */
			public function setup_stats_constants()
			{
				// Plugin name
				define('SWS_WPSL_STATS_PLUGIN_NAME', 'SWS WP Sports Leagues Statistics Addon');

				// Plugin file name
				define('SWS_WPSL_STATS_PLUGIN_FILE', plugin_basename(__FILE__));

				// Plugin version
				define('SWS_WPSL_STATS_VERSION', '1.0.0');
				
				// WPSM current database version
				define('SWS_WPSL_STATS_DB_VERSION', 1);

				// WordPress compatibility version number
				define('SWS_WPSL_STATS_REQUIRED_WP_VERSION', '3.5.2');
				
				// WPSM required PHP version
				define('SWS_WPSL_STATS_REQUIRED_PHP_VERSION', '5.3.0');

				// Plugin Website URL
				define('SWS_WPSL_STATS_PLUGIN_URL', 'http://wpsportsleagues.com/addons');

				// Plugin Support URL
				define('SWS_WPSL_STATS_SUPPORT_URL', 'http://wpsportsleagues.com/support');

				// WordPress Version
				define('WP_VERSION', get_bloginfo('version'));
				
				// Skip choosing a sport to install
				define('SWS_WPSL_STATS_SKIP_INSTALL', FALSE);

				// Plugin path
				define('SWS_WPSL_STATS_PATH', plugin_dir_path(__FILE__));
				
				// Controller directory
				define('SWS_WPSL_STATS_OPTIONS', SWS_WPSL_STATS_PATH . 'options/');
				
				// Controller directory
				define('SWS_WPSL_STATS_CLASSES', SWS_WPSL_STATS_PATH . 'classes/');
				
				// Views directory
				define('SWS_WPSL_STATS_VIEW', SWS_WPSL_STATS_PATH . 'views/');
				
				// Pages view directory
				define('SWS_WPSL_STATS_PAGES', SWS_WPSL_STATS_VIEW . 'pages/');
				
				// Pages view directory
				define('SWS_WPSL_STATS_OPTIONS_PAGES', SWS_WPSL_STATS_VIEW . 'pages/options/');
				
				// Dashboard widgets pages view directory
				define('SWS_WPSL_STATS_DASHBOARD_WIDGET_PAGES', SWS_WPSL_STATS_VIEW . 'pages/dashboard_widgets/');

				// Partials view directory
				define('SWS_WPSL_STATS_PARTIALS', SWS_WPSL_STATS_VIEW . 'partials/');
				
				// Helper directory
				define('SWS_WPSL_STATS_HELPERS', SWS_WPSL_STATS_PATH . 'helpers/');
				
				// Includes directory
				define('SWS_WPSL_STATS_INC', SWS_WPSL_STATS_PATH . 'inc/');
				
				// JavaScript directory
				define('SWS_WPSL_STATS_JS', SWS_WPSL_STATS_VIEW . 'js/');	
				
				// CSS directory
				define('SWS_WPSL_STATS_CSS', SWS_WPSL_STATS_VIEW . 'css/');	
				
				// Image directory
				define('SWS_WPSL_STATS_IMG', SWS_WPSL_STATS_VIEW . 'img/');
				
				// Image URL
				define('SWS_WPSL_STATS_IMG_URL', plugins_url('sws_wp_sports_leagues/view/img/'));
				
				// Style URL
				define('SWS_WPSL_STATS_CSS_URL', plugins_url('sws_wp_sports_leagues/view/css/'));
				
				// JavaScript URL
				define('SWS_WPSL_STATS_JS_URL', plugins_url('sws_wp_sports_leagues/view/js/'));
			}


			/**
			 * Instantiate additional Stats classes
			 *
			 * @since 1.0
			 * @param void
			 * @return void
			 */
			public function instantiate_classes()
			{

			}


			/**
			 * Display message to user if the main SWS WPSL plugin is not available
			 *
			 * @since 1.0
			 * @param void
			 * @return html the message for the user
			 */
			public function missing_base_plugin()
			{
				$html  = '<div class="error">';
        		$html .= '<p>' . __( 'The SWS WP Sports Leagues plugin must be installed and activated to use the ' . SWS_WPSL_STATS_PLUGIN_NAME . '.', 'sws_wpsl_stats' ) . '</p>';
    			$html .= '</div>';
    			echo $html;
			}


			/**
			 * Get the addon's option array file
			 *
			 * @since 1.0
			 * @param void
			 * @return array of plugin options
			 */
			public function get_stats_options()
			{
				$stats_opts = require(SWS_WPSL_STATS_OPTIONS . 'sws_wpsl_stats_options.php');
				return $stats_opts;
			}


			/**
			 * Register addon with core WPSL plugin via the main plugin's register_addons method
			 *
			 * @since 1.0
			 * @param void
			 * @return void
			 */
			public function register_addon()
			{
				global $wpsl;
				$addon = array(
					'name' => SWS_WPSL_STATS_PLUGIN_NAME,
					'options' => $this->get_stats_options()					
				);
				$wpsl->register_addons($addon);
			}

		}

		// Get the party started...
		$wpsl_stats = new Stats;
	}