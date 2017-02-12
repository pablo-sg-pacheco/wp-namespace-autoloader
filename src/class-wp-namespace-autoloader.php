<?php
/**
 * WP Namespace Autoloader - Main class
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Pablo S G Pacheco
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
		 * Autoloads all your classes in a easy way
		 *
		 * @param array|string $args {
		 *     Array of arguments.
		 *
		 *     @type string       $directory       Current directory. Use __DIR__.
		 *     @type string       $namespace       Namespace you want to look for. Probably use __NAMESPACE__.
		 *     @type string       $classes_dir     Name of the directory containing all your classes (optional).
		 * }
		 */
		function __construct( $args = array() ) {
			$args = wp_parse_args( $args, array(
				'directory'   => null,
				'namespace'   => null,
				'classes_dir' => '',
			) );

			$this->set_args( $args );
			spl_autoload_register( array( $this, 'autoload' ) );
		}

		public function autoload( $class ) {
			$args = $this->get_args();
			$namespace = $args['namespace'];

			if ( false !== strpos( $class, $namespace ) ) {

				if ( ! class_exists( $class ) ) {

					// Name of directory containing all classes
					$classes_dir_name = empty( $args['classes_dir'] ) ? '' : $args['classes_dir'] . DIRECTORY_SEPARATOR;

					// Full path of directory containing all classes
					$classes_full_path = untrailingslashit( $args['directory'] ) . DIRECTORY_SEPARATOR . $classes_dir_name;

					// Removes namespace from class
					$class = str_replace( $namespace . DIRECTORY_SEPARATOR, '', $class );

					// Class formatted to WordPress standards (without class- yet)
					$class_formatted     = strtolower( str_replace( array( '_', "\0" ), array('-','',), $class ) . '.php' );
					$class_formatted_arr = explode( DIRECTORY_SEPARATOR, $class_formatted );

					// File name to load (including "class-" before it, like wordpress standards says to)
					$file_name = 'class-' . array_pop( $class_formatted_arr );

					// Path to final file
					$final_file_path = implode( DIRECTORY_SEPARATOR, $class_formatted_arr );
					$final_file_path .= count( $class_formatted_arr ) >= 1 ? DIRECTORY_SEPARATOR : '';

					// Final file to load
					$file = $classes_full_path . $final_file_path . $file_name;

					if ( file_exists( $file ) ) {
						require_once $file;
					} else {
						error_log( 'WP_Namespace_Autoloader could not load file: ' . print_r( $file, true ) );
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
