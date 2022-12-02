<?php
/**
 * A PHP autoloader class that follows the WordPress coding standards applying PSR-4 specification.
 *
 * @see https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/
 *
 * @author  Pablo dos S G Pacheco
 * @package pablo-sg-pacheco/wp-namespace-autoloader
 *
 * phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_error_log
 * phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_print_r
 */

namespace Pablo_Pacheco\WP_Namespace_Autoloader;

if ( ! class_exists( '\Pablo_Pacheco\WP_Namespace_Autoloader\WP_Namespace_Autoloader' ) ) {
	/**
	 * Autoloader - Main class
	 *
	 * Class WP_Namespace_Autoloader
	 *
	 * @package Pablo_Pacheco\WP_Namespace_Autoloader
	 */
	class WP_Namespace_Autoloader {

		/**
		 * The autoloader settings.
		 *
		 * @var array Associative array of settings as detailed in the constructor PHPDoc.
		 */
		private $args = array();

		/**
		 * Autoloader constructor.
		 *
		 * Autoloads all your WordPress classes in an easy way
		 *
		 * @param array|string $args                 {
		 *                                           Array of arguments.
		 *
		 * @type string        $directory            Current directory. Use __DIR__.
		 * @type string        $namespace_prefix     Main namespace of your project . Probably use __NAMESPACE__.
		 * @type array         $lowercase            If you want to lowercase. It accepts an array with two possible values: 'file' | 'folders'
		 * @type array         $underscore_to_hyphen If you want to convert underscores to hyphens. It accepts an array with two possible values: 'file' | 'folders'
		 * @type boolean       $prepend_class        If you want to prepend 'class-' before files
		 * @type boolean       $prepend_interface    If you want to prepend 'interface-' before files
		 * @type boolean       $prepend_trait        If you want to prepend 'trait-' before files
		 * @type string|array  $classes_dir          Name of the directories containing all your classes (optional).
		 * }
		 */
		public function __construct( $args = array() ) {

			$defaults = array(
				'directory'            => $this->get_calling_directory(),
				'namespace_prefix'     => $this->get_calling_file_namespace(),
				'lowercase'            => array( 'file' ), // 'file' | folders
				'underscore_to_hyphen' => array( 'file' ), // 'file' | folders
				'prepend_class'        => true,
				'prepend_interface'    => true,
				'prepend_trait'        => true,
				'classes_dir'          => array( '.', 'vendor' ),
				'debug'                => false,
			);

			$parsed_args = array_merge( $defaults, $args );

			$this->set_args( $parsed_args );
		}

		/**
		 * Register autoloader
		 */
		public function init() {
			spl_autoload_register( array( $this, 'autoload' ) );
		}

		/**
		 * Determine if the class has already been loaded.
		 *
		 * @param string $class classFQN.
		 *
		 * @return bool
		 */
		public function need_to_autoload( $class ) {
			$args      = $this->get_args();
			$namespace = $args['namespace_prefix'];

			if ( ! class_exists( $class ) && ! interface_exists( $class ) && ! trait_exists( $class ) ) {

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
		 * @param string $class classFQN.
		 */
		public function autoload( $class ) {
			if ( $this->need_to_autoload( $class ) ) {

				$file_paths = $this->convert_class_to_file( $class );
				foreach ( $file_paths as $file ) {
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

			if ( is_array( $args['classes_dir'] ) ) {

				$dirs = array();

				foreach ( $args['classes_dir']  as $classes_dir ) {

					$dir = $this->sanitize_file_path( $classes_dir );

					$classes_dir = empty( $dir ) ? '' : rtrim( $dir, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;

					$dirs[] = rtrim( $args['directory'], '/\\' ) . DIRECTORY_SEPARATOR . $classes_dir;
				}

				return $dirs;

			} else {

				$dir = $this->sanitize_file_path( $args['classes_dir'] );

				// Directory containing all classes.
				$classes_dir = empty( $dir ) ? '' : rtrim( $dir, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;

				return rtrim( $args['directory'], '/\\' ) . DIRECTORY_SEPARATOR . $classes_dir;
			}
		}

		/**
		 * Gets only the path leading to final file based on namespace
		 *
		 * @param string $class classFQN.
		 *
		 * @return string
		 */
		private function get_namespace_file_path( $class ) {
			$args             = $this->get_args();
			$namespace_prefix = $args['namespace_prefix'];

			// Sanitized class and namespace prefix.
			$sanitized_class            = $this->sanitize_namespace( $class, false );
			$sanitized_namespace_prefix = $this->sanitize_namespace( $namespace_prefix, true );

			// Removes prefix from class namespace.
			$namespace_without_prefix = preg_replace( '/' . $sanitized_namespace_prefix . '\/', '', $sanitized_class, 1 );

			// Gets namespace file path.
			$namespaces_without_prefix_arr = explode( '\\', $namespace_without_prefix );

			array_pop( $namespaces_without_prefix_arr );
			$namespace_file_path = implode( DIRECTORY_SEPARATOR, $namespaces_without_prefix_arr ) . DIRECTORY_SEPARATOR;

			if ( in_array( 'folders', $args['lowercase'], true ) ) {
				$namespace_file_path = strtolower( $namespace_file_path );
			}

			if ( in_array( 'folders', $args['underscore_to_hyphen'], true ) ) {
				$namespace_file_path = str_replace( array( '_', "\0" ), array( '-', '' ), $namespace_file_path );
			}

			if ( '\\' === $namespace_file_path || '\/' === $namespace_file_path ) {
				$namespace_file_path = '';
			}

			return $namespace_file_path;
		}

		/**
		 * Gets filename to be loaded considering WordPress coding standards
		 *
		 * Takes className or end of classFQN and converts it to WPCS "class-" prefixed filename.
		 *
		 * @param string $class className or classFNQ.
		 * @param string $object_type the type of object interface, trait or class (default).
		 *
		 * @return string
		 */
		private function get_file_applying_wp_standards( $class, $object_type = 'class' ) {
			$args        = $this->get_args();
			$object_type = in_array( $object_type, array( 'class', 'interface', 'trait' ), true ) ? $object_type : 'class';

			// Sanitized class and namespace prefix.
			$sanitized_class = $this->sanitize_namespace( $class, false );

			// Gets namespace file path.
			$namespaces_arr = explode( '\\', $sanitized_class );

			$final_file = array_pop( $namespaces_arr );

			// Final file name.
			if ( in_array( 'file', $args['lowercase'], true ) ) {
				$final_file = strtolower( $final_file );
			}

			// Final file with underscores replaced.
			if ( in_array( 'file', $args['underscore_to_hyphen'], true ) ) {
				$final_file = str_replace( array( '_', "\0" ), array( '-', '' ), $final_file );
			}

			// Prepend class.
			if ( $args['prepend_class'] || $args['prepend_interface'] || $args['prepend_trait'] ) {
				$prepended = preg_replace( '/(.*)-abstract$/', 'abstract-$1', $final_file );

				// If no changes were made when looking for interfaces and abstract classes, prepend "class-".
				if ( $prepended === $final_file ) {
					$final_file = $object_type . '-' . $final_file;
				} else {
					$final_file = $prepended;
				}
			}

			$final_file .= '.php';

			return $final_file;
		}

		/**
		 * Sanitizes file path.
		 * Removes leading and trailing / (DIRECTORY_SEPARATOR).
		 *
		 * @param string $file_path File path.
		 *
		 * @return string
		 */
		private function sanitize_file_path( $file_path ) {
			return trim( $file_path, DIRECTORY_SEPARATOR );
		}


		/**
		 * Sanitizes namespace or classFQN
		 *
		 * @param string $namespace Namespace or classFQN to sanitize.
		 * @param bool   $add_backslash Add a trailing backslash.
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
		 * @param string $class classFQN.
		 * @param bool   $check_loading_need Should short circuit if already loaded.
		 *
		 * @return array();
		 */
		public function convert_class_to_file( $class, $check_loading_need = false ) {
			if ( $check_loading_need ) {
				if ( ! $this->need_to_autoload( $class ) ) {
					return array();
				}
			}
			$args                = $this->get_args();
			$namespace_file_path = $this->get_namespace_file_path( $class );
			$final_files         = array( $this->get_file_applying_wp_standards( $class ) );

			if ( $args['prepend_interface'] ) {
				$final_files[] = $this->get_file_applying_wp_standards( $class, 'interface' );
			}
			if ( $args['prepend_trait'] ) {
				$final_files[] = $this->get_file_applying_wp_standards( $class, 'trait' );
			}

			$class_files = array();

			$dir = $this->get_dir();

			foreach ( $final_files as $final_file ) {
				if ( is_array( $dir ) ) {
					foreach ( $dir as $class_dir ) {
						$class_files[] = $class_dir . $namespace_file_path . $final_file;
					}
				} else {
					$class_files[] = $dir . $namespace_file_path . $final_file;
				}
			}

			return $class_files;
		}

		/**
		 * Get the directory of the file that instantiated this class.
		 *
		 * phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace
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
		 *
		 * phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace
		 * phpcs:disable WordPress.WP.AlternativeFunctions.file_system_read_fopen
		 * phpcs:disable WordPress.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
		 * phpcs:disable WordPress.WP.AlternativeFunctions.file_system_read_fclose
		 */
		protected function get_calling_file_namespace() {

			$debug_backtrace = debug_backtrace();

			// [0] is the __construct function, [1] is who called it.
			$calling_file = $debug_backtrace[1]['file'];

			$calling_namespace = null;
			$handle            = fopen( $calling_file, 'r' );
			if ( $handle ) {
				while ( false !== ( $line = fgets( $handle ) ) ) {
					if ( 0 === strpos( $line, 'namespace' ) ) {
						$parts             = explode( ' ', $line );
						$calling_namespace = rtrim( trim( $parts[1] ), ';' );
						break;
					}
				}
				fclose( $handle );
			}
			return $calling_namespace;
		}

		/**
		 * Getter for autoloader settings.
		 *
		 * @return array Associative array of settings as detailed in the constructor PHPDoc.
		 */
		public function get_args() {
			return $this->args;
		}

		/**
		 * Setter for autoloader settings.
		 *
		 * @param array $args Associative array of settings as detailed in the constructor PHPDoc.
		 */
		public function set_args( $args ) {
			$this->args = $args;
		}
	}
}
