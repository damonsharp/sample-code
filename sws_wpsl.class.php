<?php if ( !class_exists('SWS_WPSL') ) {

	/**
	 * Main WP Sports Leagues Class
	 *
	 * This class follows the singleton pattern, registers an autoload method
	 * to autoload any required classes, and instantiates any classes that are
	 * needed via the __constructor()
	 */
	class SWS_WPSL {

		/**
		 * Hold an instance of the main SWS_WPSL class singleton
		 */
		private static $_instance = null;
 

 		/**
 		 * Get a single instance of the SWS_WPSL class
 		 * 
 		 * @since 1.0
 		 * @param void
 		 * @return object instance of SWS_WPSL class
 		 */
		public static function getInstance()
		{
			if ( is_null(static::$_instance) )
			{
				static::$_instance = new SWS_WPSL();
			}
		}

		/**
		 * Contructor
		 *
		 * Setup an autoloading method for loading necessary classes
		 * 
		 * @since 1.0
		 * @param void
		 * @return void
		 */
		private function __construct()
		{
			spl_autoload_register( array($this, 'autoloadClasses') );
			new SWS_WPSL_Options();
		}


		/**
		 * Since this is a Singleton, prevent cloning
		 *
		 * 
		 * @since 1.0
		 * @param void
		 * @return null
		 */
		private function __clone()
		{
			return null;
		}


		/**
		 * Since this is a Singleton, prevent wakeup
		 *
		 * 
		 * @since 1.0
		 * @param void
		 * @return null
		 */
		private function __wakeup()
		{
			return null;
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
		private function autoloadClasses( $class )
		{
			$class = strtolower($class);
			$items = scandir(SWS_WPSL_CLASSES);
			$file = "$class.class.php";
			if ( in_array($file, $items) && $file !== 'sws_wpsl.class.php' )
			{
				require_once($file);
			}
		}

	}
}