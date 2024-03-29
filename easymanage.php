<?php
/*
 * Plugin Name: Easymanage
 * Plugin URI:  https://easymanage.biz
 * Description: Integration module for Woocommerce and Easymanage app(Google drive spreadsheet)
 * Author:      Easymanage Team
 * Version:     1.0.5
 * Text Domain: easymanage
 * Domain Path: /languages/
 *
 * WC requires at least: 	3.6.0
 * WC tested up to: 3.7.1
 *
 * Copyright: © 2019 easymanage, (easymanage.biz@gmail.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

 if ( ! class_exists( 'Easymanage' ) ) {

  class Easymanage {

    protected $_addons_to_register = [
      '/unsubscribers/class-easymanage-unsubscribers-init.php'
    ];

    protected static $_instance = null;

    public static $version = '1.0.5';

    public static $required_woo = '3.0.0';

    public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

    public function __construct() {
      $this->setup_constants();
      add_action( 'init', array( $this, 'includes' ), 12 );
			$this->admin_includes();
		}

    public function setup_constants() {

      $this->define('EASYMANAGE_PLUGIN', plugin_basename(__FILE__));
      $this->define('EASYMANAGE_VERSION', self::$version);
			$this->define('EASYMANAGE_FILE_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
      $this->define('EASYMANAGE_TEMPLATE_PATH', EASYMANAGE_FILE_PATH . '/templates//');

    }

    private function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

    public function includes() {
			include_once( EASYMANAGE_FILE_PATH . '/includes/class-easymanage-autoloader.php' );
			include_once( EASYMANAGE_FILE_PATH . '/includes/class-easymanage-init.php' );
			include_once( EASYMANAGE_FILE_PATH . '/includes/class-easymanage-addon.php' );

      $this->_register_addons();
		}

    public function admin_includes() {
			if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
				require_once( EASYMANAGE_FILE_PATH . '/includes/class-easymanage-install.php' );
  			require_once( EASYMANAGE_FILE_PATH . '/includes/class-easymanage-admin.php' );
			}
		}

    protected function _register_addons() {
      foreach($this->_addons_to_register as $file_path) {
        include_once( EASYMANAGE_FILE_PATH . '/includes/addons' . $file_path);
      }
    }
  }

}


return Easymanage::instance();
