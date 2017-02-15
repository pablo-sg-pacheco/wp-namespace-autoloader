<?php
/**
 * WP Namespace Autoloader - Main class
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Pablo dos S G Pacheco
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


if ( ! class_exists( 'WP_Namespace_Autoloader' ) ) {
	class WP_Namespace_Autoloader {

		private $args;
		private $namespace;

		/**
		 * WP_Namespace_Autoloader constructor.
		 *
		 * Autoloads all your WordPress classes in a easy way
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 * @param array|string $args {
		 *     Array of arguments.
		 *
		 *     @type string       $directory                Current directory. Use __DIR__.
		 *     @type string       $namespace_prefix         Main namespace of your project . Probably use __NAMESPACE__.
		 *     @type string       $namespace_to_lowercase   If you want to keep all your folders lowercased
		 *     @type string       $classes_dir              Name of the directory containing all your classes (optional).
		 * }
		 */
		function __construct( $args = array() ) {
			$args = wp_parse_args( $args, array(
				'directory'              => null,
				'namespace_prefix'       => null,
				'namespace_to_lowercase' => false,
				'classes_dir'            => '',
			) );

			$this->set_args( $args );
			spl_autoload_register( array( $this, 'autoload' ) );
		}

		/**
		 * Gets the directory of where all classes will be located.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 * @return string
		 */
		private function get_classes_dir(){
			$args = $this->get_args();
			return empty( $args['classes_dir'] ) ? '' : $args['classes_dir'] . DIRECTORY_SEPARATOR;
		}

		/**
		 * Gets the full path directory of where all classes will be located.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 * @param $classes_dir
		 * @return string
		 */
		private function get_classes_full_path( ) {
			// Directory containing all classes
			$classes_dir = $this->get_classes_dir();

			$args = $this->get_args();
			return untrailingslashit( $args['directory'] ) . DIRECTORY_SEPARATOR . $classes_dir;
		}

		/**
		 * Removes namespace from class
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 * @param $class
		 * @return mixed
		 */
		private function remove_namespace_from_class( $class ) {
			$args = $this->get_args();
			$namespace = $args['namespace'];
			return str_replace( $namespace . DIRECTORY_SEPARATOR, '', $class );
		}

		/**
		 * Gets filename to be loaded applying wordpress coding standards.
		 *
		 * Includes 'class-' before it, lowercases it and replaces underscores by hyphens.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 * @param $class
		 * @return string
		 */
		private function get_filename_applying_wp_standards( $class ){
			$class_formatted_arr = explode( DIRECTORY_SEPARATOR, $class );
			$file_name = 'class-' . strtolower(array_pop( $class_formatted_arr ));
			$file_name_dash_replaced = str_replace( array( '_', "\0" ), array( '-', '', ), $file_name ) . '.php';
			return $file_name_dash_replaced;
		}

		/**
		 * Gets namespace path without main namespace that leads to final file to be loaded.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 * @param $class
		 * @return string
		 */
		private function get_namespace_path( $class ){
			$args = $this->get_args();
			$class_without_namespace = $this->remove_namespace_from_class( $class );
			$class_formatted_arr = explode( DIRECTORY_SEPARATOR, $class_without_namespace );
			array_pop( $class_formatted_arr );
			$final_file_path = implode( DIRECTORY_SEPARATOR, $class_formatted_arr );
			$final_file_path .= count( $class_formatted_arr ) >= 1 ? DIRECTORY_SEPARATOR : '';
			if($args['namespace_to_lowercase']){
				$final_file_path = strtolower($final_file_path);
			}
			return $final_file_path;
		}

		/**
		 * Autoloads classes
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 * @param $class
		 */
		public function autoload( $class ) {
			$args      = $this->get_args();
			$namespace = $args['namespace'];

			if ( false !== strpos( $class, $namespace ) ) {

				if ( ! class_exists( $class ) ) {
					// Full path of directory containing all classes
					$full_path_dir = $this->get_classes_full_path();

					// Gets final file name to be loaded using wp standards
					$file_name = $this->get_filename_applying_wp_standards( $class );

					// Namespace path without main namespace
					$namespace_path = $this->get_namespace_path( $class );

					// Final file to load
					$file = $full_path_dir . $namespace_path . $file_name;

					if ( file_exists( $file ) ) {
						require_once $file;
					} else {
						error_log( 'WP Namespace Autoloader could not load file: ' . print_r( $file, true ) );
					}
				}
			}
		}

		/**
		 * @return mixed
		 */
		public function get_args() {
			return $this->args;
		}

		/**
		 * @param mixed $args
		 */
		public function set_args( $args ) {
			$this->args = $args;
		}

	}
}
