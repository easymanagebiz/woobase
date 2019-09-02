<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Easymanage_Autoloader' ) ) {

	class Easymanage_Autoloader {

		private $include_path = '';

		public function __construct() {
			if ( function_exists( '__autoload' ) ) {
				spl_autoload_register( '__autoload' );
			}

			spl_autoload_register( array( $this, 'autoload' ) );

			$this->include_path = untrailingslashit( EASYMANAGE_FILE_PATH  ) . '/includes/';
		}


		private function get_file_name_from_class( $class ) {
			return 'class-' . str_replace( '_', '-', $class ) . '.php';
		}

		private function load_file( $path ) {
			if ( $path && is_readable( $path ) ) {
				include_once $path;
				return true;
			}
			return false;
		}

		public function autoload( $class ) {
			$class = strtolower( $class );

			if ( 0 !== strpos( $class, 'easymanage_' ) ) {
				return;
			}

			$file = $this->get_file_name_from_class( $class );

			if (!$this->load_file($file ) ) {
				$this->load_file( $this->include_path . $file );
			}
		}

	}

}

new Easymanage_Autoloader();
