<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Easymanage_Response {

  const STATUS_RESPONSE_KEY = 'status';

  public function response($data, $no_status = false, $no_array = false) {
		if(!$no_status) {
    	$data[self::STATUS_RESPONSE_KEY] = 'ok';
		}
		if(!$no_array) {
	    return $this->_response( [$data] );
		}
	  return $this->_response($data);
  }

  public function error($errorMessage) {
    $data = [
      'message' => $errorMessage
    ];
    return $this->_response( $data );
  }

  protected function _response($data = array()) {
    return new WP_REST_Response( $data );
  }

}
