<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Easymanage_Install' ) ) {

	class Easymanage_Install {

    const EASYMANAGE_INSTALL_RUN     = 'easymanage_install_run';

    const EASYMANAGE_UPDATED_ACTION  = 'easymanage_updated';

    const EASYMANAGE_OPTION_VERSION  = 'easymanage_version';

    const EASYMANAGE_OPTION_INSTALL_DATE  = 'easymanage_install_date';

		protected $_unsubscribers_table  = 'easymanage_unsubscribers';
		protected $_email_table          = 'easymanage_emails';
		protected $_email_template_table = 'easymanage_email_template';

    public function __construct() {
      add_action( 'init', array( $this, 'check_version' ), 5 );
    }

    public function check_version() {

      $_version = get_option( self::EASYMANAGE_OPTION_VERSION );

      if ( ! defined( 'IFRAME_REQUEST' ) && !$_version && current_user_can( 'install_plugins' ) ) {
				$this->install();
				do_action( self::EASYMANAGE_UPDATED_ACTION );
			}

    }

    public function install() {

      if ( ! is_blog_installed() ) {
				return;
			}

			if ( 'yes' === get_transient( self::EASYMANAGE_INSTALL_RUN ) ) {
				return;
			}

      set_transient( self::EASYMANAGE_INSTALL_RUN, 'yes', MINUTE_IN_SECONDS * 5 );

      $this->create_email_unsubscribers_table();

			self::set_install_date();
			self::update_version();

			delete_transient( self::EASYMANAGE_INSTALL_RUN );

    }

    public static function set_install_date() {
  		add_site_option( self::EASYMANAGE_OPTION_INSTALL_DATE, time() );
    }

    public static function update_version() {
      update_option( self::EASYMANAGE_OPTION_VERSION, EASYMANAGE_VERSION );
    }

    protected function create_email_unsubscribers_table() {

      //https://codex.wordpress.org/Creating_Tables_with_Plugins#Creating_or_Updating_the_Table

      require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

      global $wpdb;

      $charset_collate = $wpdb->get_charset_collate();

      $table_name = $wpdb->prefix . $this->_email_table;

      $sql = " DROP TABLE IF EXISTS " . $table_name . ";
        CREATE TABLE " . $table_name . " (
        `email_id` INT( 12 ) NOT NULL AUTO_INCREMENT ,
        `email_address` TEXT NOT NULL ,
        `unique_id` TEXT NOT NULL default '',
        PRIMARY KEY ( `email_id` )
        )ENGINE=InnoDB DEFAULT CHARSET={$charset_collate};
      ";

      dbDelta( $sql );

			$table_name = $wpdb->prefix . $this->_unsubscribers_table;

			$sql = " DROP TABLE IF EXISTS " . $table_name . ";
        CREATE TABLE " . $table_name . " (
        `email_usubscribe_id` INT( 12 ) NOT NULL AUTO_INCREMENT ,
        `email_address` TEXT NOT NULL ,
        PRIMARY KEY ( `email_usubscribe_id` )
        )ENGINE=InnoDB DEFAULT CHARSET={$charset_collate};
      ";

      dbDelta( $sql );

			$table_name = $wpdb->prefix . $this->_email_template_table;

      $sql = " DROP TABLE IF EXISTS " . $table_name . ";
        CREATE TABLE " . $table_name . " (
        `template_email_id` INT( 12 ) NOT NULL AUTO_INCREMENT ,
        `email_subject` TEXT NOT NULL ,
        `email_content` LONGTEXT NOT NULL default '',
        PRIMARY KEY ( `template_email_id` )
        )ENGINE=InnoDB DEFAULT CHARSET={$charset_collate};
      ";

      dbDelta( $sql );
    }

  }

}

return new Easymanage_Install();
