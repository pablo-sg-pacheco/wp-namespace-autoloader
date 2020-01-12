<?php
/**
 * Autoloader - Main class
 *
 * @author  Pablo dos S G Pacheco
 */

namespace Pablo_Pacheco\WP_Namespace_Autoloader;


if ( ! class_exists( '\Pablo_Pacheco\WP_Namespace_Autoloader\WP_Namespace_Autoloader' ) ) {
	class WP_Namespace_Autoloader {

		private $args;

		/**
		 * Autoloader constructor.
		 *
		 * Autoloads all your WordPress classes in a easy way
		 *
		 * @param array|string $args                 {
		 *                                           Array of arguments.
		 *
		 * @type string        $directory            Current directory. Use __DIR__.
		 * @type string        $namespace_prefix     Main namespace of your project . Probably use __NAMESPACE__.
		 * @type array         $lowercase            If you want to lowercase. It accepts an array with two possible values: 'file' | 'folders'
		 * @type array         $underscore_to_hyphen If you want to convert underscores to hyphens. It accepts an array with two possible values: 'file' | 'folders'
		 * @type boolean       $prepend_class        If you want to prepend 'class-' before files
		 * @type string|array  $classes_dir          Name of the directories containing all your classes (optional).
		 * }
		 */
		function __construct( $args = array() ) {
			$defaults = array(
				'directory'            => $this->get_calling_directory(),
				'namespace_prefix'     => $this->get_calling_file_namespace(),
				'lowercase'            => array( 'file' ), // 'file' | folders
				'underscore_to_hyphen' => array( 'file' ), // 'file' | folders
				'prepend_class'        => true,
				'classes_dir'          => array( '.', 'vendor' ),
				'debug'                => false,
			);

			$parsed_args = array_merge( $defaults, $args );

			$this->set_args( $parsed_args );
		}

		/**
		 * Register autoloader
		 *
		 * @return string
		 */
		public function init() {
			spl_autoload_register( array( $this, 'autoload' ) );
		}

		public function need_to_autoload( $class ) {
			$args      = $this->get_args();
			$namespace = $args['namespace_prefix'];

			if ( ! class_exists( $class ) && ! interface_exists( $class ) ) {

				if ( false !== strpos( $class, $namespace ) ) {
					if ( ! class_exists( $class ) ) {
						return true;
					}
				}

			}

			return false;
		}

		/**
		 * Autoloads classes
		 *
		 * @param string $class
		 */
		public function autoload( $class ) {
			if ( $this->need_to_autoload( $class ) ) {
				$file_paths = $this->convert_class_to_file( $class );
				foreach( $file_paths as $file ) {
					if ( file_exists( $file ) ) {
						require_once $file;
						return;
					}
				}

				$args = $this->get_args();
				if ( $args['debug'] ) {
					error_log( 'WP Namespace Autoloader could not load file: ' . print_r( $file_paths, true ) );
				}

			}
		}

		/**
		 * Gets full path of directories containing classes, using the $args['classes_dir'] input argument.
		 *
		 * @return string|array
		 */
		private function get_dir() {
			$args = $this->get_args();

			if( is_array( $args['classes_dir'] ) ) {

				$dirs = array();

				foreach( $args['classes_dir']  as $classes_dir ) {

					$dir = $this->sanitize_file_path( $classes_dir );

					$classes_dir = empty( $dir ) ? '' : rtrim( $dir, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;

					$dirs[] = untrailingslashit( $args['directory'] ) . DIRECTORY_SEPARATOR . $classes_dir;
				}

				return $dirs;

			} else {

				$dir  = $this->sanitize_file_path( $args['classes_dir'] );

				// Directory containing all classes
				$classes_dir = empty( $dir ) ? '' : rtrim( $dir, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;

			  return rtrim( $args['directory'], '/\\' ) . DIRECTORY_SEPARATOR . $classes_dir;

		}

		/**
		 * Gets only the path leading to final file based on namespace
		 *
		 * @param string $class
		 *
		 * @return string
		 */
		private function get_namespace_file_path( $class ) {
			$args             = $this->get_args();
			$namespace_prefix = $args['namespace_prefix'];

			// Sanitized class and namespace prefix
			$sanitized_class            = $this->sanitize_namespace( $class, false );
			$sanitized_namespace_prefix = $this->sanitize_namespace( $namespace_prefix, true );

			// Removes prefix from class namespace
			$namespace_without_prefix = str_replace( $sanitized_namespace_prefix, '', $sanitized_class );

			// Gets namespace file path
			$namespaces_without_prefix_arr = explode( '\\', $namespace_without_prefix );

			array_pop( $namespaces_without_prefix_arr );
			$namespace_file_path = implode( DIRECTORY_SEPARATOR, $namespaces_without_prefix_arr ) . DIRECTORY_SEPARATOR;

			if ( in_array( "folders", $args['lowercase'] ) ) {
				$namespace_file_path = strtolower( $namespace_file_path );
			}

			if ( in_array( "folders", $args['underscore_to_hyphen'] ) ) {
				$namespace_file_path = str_replace( array( '_', "\0" ), array( '-', '', ), $namespace_file_path );
			}

			if ( $namespace_file_path == '\\' || $namespace_file_path == '\/' ) {
				$namespace_file_path = '';
			}

			return $namespace_file_path;
		}

		/**
		 * Gets final file to be loaded considering WordPress coding standards
		 *
		 * @param string $class
		 *
		 * @return string
		 */
		private function get_file_applying_wp_standards( $class ) {
			$args = $this->get_args();

			// Sanitized class and namespace prefix
			$sanitized_class = $this->sanitize_namespace( $class, false );

			// Gets namespace file path
			$namespaces_arr = explode( '\\', $sanitized_class );

			$final_file = array_pop( $namespaces_arr );

			// Final file name
			if ( in_array( 'file', $args['lowercase'] ) ) {
				$final_file = strtolower( $final_file );
			}

			// Final file with underscores replaced
			if ( in_array( 'file', $args['underscore_to_hyphen'] ) ) {
				$final_file = str_replace( array( '_', "\0" ), array( '-', '', ), $final_file );
			}

			// Prepend class
			if ( $args['prepend_class'] ) {
				$prepended = preg_replace( '/(.*)-interface$/', 'interface-$1', $final_file );
				$prepended = preg_replace( '/(.*)-abstract$/', 'abstract-$1', $prepended );

				// If no changes were made when looking for interfaces and abstract classes, prepend "class-".
				if ( $prepended === $final_file ) {
					$final_file = 'class-' . $final_file;
				} else {
					$final_file = $prepended;
				}
			}

			$final_file .= '.php';

			return $final_file;
		}

		/**
		 * Sanitizes file path
		 *
		 * @param string $file_path
		 *
		 * @return string
		 */
		private function sanitize_file_path( $file_path ) {
			return trim( $file_path, DIRECTORY_SEPARATOR );
		}


		/**
		 * Sanitizes namespace
		 *
		 * @param string $namespace
		 * @param bool   $add_backslash
		 *
		 * @return string
		 */
		private function sanitize_namespace( $namespace, $add_backslash = false ) {
			if ( $add_backslash ) {
				return trim( $namespace, '\\' ) . '\\';
			} else {
				return trim( $namespace, '\\' );
			}
		}

		/**
		 * Converts a namespaced class in a file to be loaded
		 *
		 * @param string $class
		 * @param bool   $check_loading_need
		 *
		 * @return array();
		 */
		public function convert_class_to_file( $class, $check_loading_need = false ) {
			if ( $check_loading_need ) {
				if ( ! $this->need_to_autoload( $class ) ) {
					return array();
				}
			}

			$namespace_file_path = $this->get_namespace_file_path( $class );
			$final_file          = $this->get_file_applying_wp_standards( $class );

			$class_files = array();

			$dir                 = $this->get_dir();

			if( is_array( $dir ) ) {

				foreach( $dir as $class_dir ) {
					$class_files[] = $class_dir . $namespace_file_path . $final_file;
				}

			} else {
				$class_files[] = $dir . $namespace_file_path . $final_file;
			}

			return $class_files;
		}

		/**
		 * Get the directory of the file that instantiated this class.
		 */
		public function get_calling_directory() {

			$debug_backtrace = debug_backtrace();

			// [0] is the __construct function, [1] is who called it.
			$calling_file = $debug_backtrace[1]['file'];

			$calling_directory = dirname( $calling_file );

			return $calling_directory;
		}

		/**
		 * Get the namespace of the file that instantiated this class, presumably the root namespace.
		 */
		protected function get_calling_file_namespace() {

			$debug_backtrace = debug_backtrace();

			// [0] is the __construct function, [1] is who called it.
			$calling_file = $debug_backtrace[1]['file'];

			$calling_namespace = null;
			$handle = fopen($calling_file, "r");
			if ($handle) {
				while (false !== ($line = fgets($handle))) {
					if (0 === strpos($line, 'namespace')) {
						$parts = explode(' ', $line);
						$calling_namespace = rtrim(trim($parts[1]), ';');
						break;
					}
				}
				fclose($handle);
			}
			return $calling_namespace;
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