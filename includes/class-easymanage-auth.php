<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Easymanage_Auth extends WC_REST_Authentication{

  const USER_ID_FIELD = 'ID';

	const READ_WRITE_PERMISSIONS = 'read_write';

  const ADMIN_ROLE = 'administrator';

  protected $_wpUser = null;

  public function getUser() {
    if(!$this->user) {
      $this->authenticate(null);
    }
    if($this->user) {
      $this->_wpUser = get_user_by( self::USER_ID_FIELD, $this->user->user_id );
      return $this->_wpUser;
    }

  }

  public function checkIsAdmin() {
    $user = $this->getUser();
    if(!$user) {
      return false;
    }
		//if($this->user->permissions != self::READ_WRITE_PERMISSIONS) {
	    //return false;
		//}
    return in_array(self::ADMIN_ROLE, $user->roles);
  }

  public function authenticate( $user_id ) {

    $this->auth_method = 'basic_auth';
    $consumer_key      = '';
    $consumer_secret   = '';

    // If the $_GET parameters are present, use those first.
    if ( ! empty( $_GET['consumer_key'] ) && ! empty( $_GET['consumer_secret'] ) ) { // WPCS: CSRF ok.
      $consumer_key    = $_GET['consumer_key']; // WPCS: CSRF ok, sanitization ok.
      $consumer_secret = $_GET['consumer_secret']; // WPCS: CSRF ok, sanitization ok.
    }

    // If the above is not present, we will do full basic auth.
    if ( ! $consumer_key && ! empty( $_SERVER['PHP_AUTH_USER'] ) && ! empty( $_SERVER['PHP_AUTH_PW'] ) ) {
      $consumer_key    = $_SERVER['PHP_AUTH_USER']; // WPCS: CSRF ok, sanitization ok.
      $consumer_secret = $_SERVER['PHP_AUTH_PW']; // WPCS: CSRF ok, sanitization ok.
    }

    // Stop if don't have any key.
    if ( ! $consumer_key || ! $consumer_secret ) {
      return false;
    }

    // Get user data.
    $this->user = $this->get_user_data_by_consumer_key( $consumer_key );
    if ( empty( $this->user ) ) {
      return false;
    }

    // Validate user secret.
    if ( ! hash_equals( $this->user->consumer_secret, $consumer_secret ) ) { // @codingStandardsIgnoreLine
      $this->set_error( new WP_Error( 'woocommerce_rest_authentication_error', __( 'Consumer secret is invalid.', 'woocommerce' ), array( 'status' => 401 ) ) );

      return false;
    }

    return $this->user->user_id;
	}

  private function get_user_data_by_consumer_key( $consumer_key ) {
		global $wpdb;

		$consumer_key = wc_api_hash( sanitize_text_field( $consumer_key ) );
		$user         = $wpdb->get_row(
			$wpdb->prepare(
				"
			SELECT key_id, user_id, permissions, consumer_key, consumer_secret, nonces
			FROM {$wpdb->prefix}woocommerce_api_keys
			WHERE consumer_key = %s
		",
				$consumer_key
			)
		);

		return $user;
	}

}
