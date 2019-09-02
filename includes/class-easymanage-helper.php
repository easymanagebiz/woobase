<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Easymanage_Helper {

  protected $_auth;


  public function __construct() {
    $this->_auth = new Easymanage_Auth();
  }

  public function getUser() {
    return $this->_auth->getUser();
  }

  public function checkIsAdmin() {
    return $this->_auth->checkIsAdmin();
  }

	public function getRequestJsonData() {
		$jsonStr = file_get_contents('php://input');
		return json_decode($jsonStr, true);
	}

	public function prepareOutData($arrData, $headers) {
		$arrData = $arrData ? $arrData : [];
		$out = [];
		foreach($arrData as $row) {

			$rowOut = [];
			foreach($headers as $header) {

				if(is_object($row)) {
					$checkVal = $header['name'];
					if(!empty($row->$checkVal)) {
						$rowOut[] = $row->$checkVal;
					}else{
						$rowOut[] = '';
					}

				}else{

					if(!empty($row[ $header['name']] )) {
						$rowOut[] = $row[$header['name']];
					}else{
						$rowOut[] = '';
					}

				}

			}

			$out[] = $rowOut;
		}

		return $out;
	}

}
