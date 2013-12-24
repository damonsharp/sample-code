<?php if ( !class_exists('SWS_WPSL_Options') ) {

	class SWS_WPSL_Options {

		/**
		 * Hold plugin options from config array
		 */
		private $_opts = array();


		/**
		 * Constructor
		 *
		 * Set $_opts array and actions for creating options pages
		 *
		 * @since 1.0
		 * @param void
		 * @return void
		 */
		public function __construct()
		{
			$this->_opts = require_once(SWS_WPSL_PATH . 'sws_wpsl_options.php');

			// Create plugin options pages based on $this->_opts
			add_action( 'admin_menu', array($this, 'initialize_option_pages') );

			// Add options to option pages above
			add_action( 'admin_init', array($this, 'register_settings') );

		}

		/**
		 * Using the config array, setup the main options page and any subpages
		 *
		 * Additionally this will call the method to update the main plugin page sidebar label
		 * to "Dashboard"
		 *
		 * @since 1.0
		 * @param void
		 * @return html page content
		 */
		public function initialize_option_pages()
		{
			foreach ( $this->_opts['option_pages'] as $opts )
			{
				add_menu_page($opts['title'], $opts['menu_title'], $opts['capability'], $opts['menu_slug'], $opts['function'], $opts['icon_url'], $opts['position']);
				foreach( $opts['submenu_pages'] as $submenu_pages )
				{
					add_submenu_page($opts['menu_slug'], $submenu_pages['title'], $submenu_pages['menu_title'], $submenu_pages['capability'], $submenu_pages['menu_slug'], $submenu_pages['function']);
				}
			}
			$this->change_main_submenu_name();
		}


		/**
		 * Callback from initialize_option_pages() above to register option settings
		 *
		 * @since 1.0
		 * @param void
		 * @return html page content
		 */
		public function register_settings()
		{
			// Register theme options page, create sections and settings fields
			register_setting('sws_wpsl_settings', 'sws_wpsl_settings', array('SWS_Validation', 'process_settings') );
			
			// Plugin options from config file
			foreach ( $this->_opts['option_settings'] as $page => $page_options )
			{
				if ( sws_wpsl_page_is($page) )
				{
					foreach ( $page_options['page_sections'] as $section => $field_options )
					{
						add_settings_section( $section, $section, function(){}, $page );
						foreach ( $field_options as $field )
						{
							add_settings_field($field['name'], $field['title'], array('SWS_WPSL_Form', $field['type']), $page, $section, $field);
						}
					}
				}
			}
		}


		/**
		 * Callback from register_settings() above to add in main options page shell
		 *
		 * This utilizes a custom helper function from helpers/sws_wpsl_helpers.php
		 *
		 * @since 1.0
		 * @param void
		 * @return html page content
		 */
		public function get_page_content()
		{
			sws_get_plugin_part( SWS_WPSL_PAGES, 'sws_settings_page_template' );
		}


		/**
		 * Modification of the main plugin pages label/title in the admin nav menu
		 *
		 * @since 1.0
		 * @param void
		 * @return string admin menu label
		 */
		public function change_main_submenu_name()
		{
			global $submenu;
			
			if ( isset( $submenu['sws_wpsl_dashboard'] ) )
			{
				$submenu['sws_wpsl_dashboard'][0][0] = __( 'Dashboard', 'swswpsl' );
			}
		}

	}
}