<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Easymanage_Helper {

  protected $_auth;

	//by default expect text

	protected $_prepare_fields = [

		'description' => 'rich_html',
		'short_description' => 'rich_html',
		'stock_quantity' => 'integer',
		'stock_status' => 'zero_or_one',
		'weight' => 'integer',
		'length' => 'integer',
		'width' => 'integer',
		'height' => 'integer',
		'regular_price' => 'float',
		'sale_price' => 'float',
		'attributes:taxonomy1' => 'zero_or_one',
		'attributes:visible1' => 'zero_or_one',
		'attributes:taxonomy2' => 'zero_or_one',
		'attributes:visible2' => 'zero_or_one',

	];

	protected $_reach_html_allowed = [
		'a'      => [
			'href'  => [],
			'title' => [],
			'style' => [],
			'class' => []
		],
		'br'     => [
			'style' => []
		],
		'em'     => [
			'style' => []
		],
		'strong' => [
			'style' => []
		],
		'u' => [
			'style' => []
		],
		'b' => [
			'style' => []
		],
		's' => [
			'style' => []
		],
		'img' => [
			'src' => [],
			'style' => [],
			'class' => []
		],
		'h1' => [
			'style' => [],
			'class' => []
		],
		'h2' => [
			'style' => [],
			'class' => []
		],
		'h3' => [
			'style' => [],
			'class' => []
		],
		'h4' => [
			'style' => [],
			'class' => []
		],
		'h5' => [
			'style' => [],
			'class' => []
		],
		'ul' => [
			'style' => [],
			'class' => []
		],
		'ol' => [
			'style' => [],
			'class' => []
		],
		'p' => [
			'style' => [],
			'class' => []
		],
		'pre' => [
			'style' => [],
			'class' => []
		],
		'div' => [
			'style' => [],
			'class' => []
		]
	];

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

	public function prepareHtmlOutput($html) {
		return $html;
		return wp_kses( $html, $this->_reach_html_allowed );
	}

	//used for user input and output
	public function validateAndPrepareData($name, $val) {
		$type = $this->getDataType($name);
		switch($type) {

			case 'rich_html':
				return $this->prepareHtmlOutput($val);
			break;

			case 'integer':
				return $this->prepareInteger($val);
			break;

			case 'float':
				return $this->prepareFloat($val);
			break;

			case 'zero_or_one':
				return $this->prepareZeroOrOne($val);
			break;

			default:  //all others
				return $this->prepreText($val);
			break;
		}
	}

	protected function prepreText($val) {
		return sanitize_text_field( $val );
	}

	protected function prepareZeroOrOne($val = null) {
		return $val ? 1 : 0;
	}

	protected function prepareInteger($val) {
		return intval($val);
	}

	protected function prepareFloat($val) {
		return floatval($val);
	}

	protected function getDataType($name) {
		foreach($this->_prepare_fields as $fieldName => $prepareType) {
			if($name == $fieldName) {
				return $prepareType;
			}
		}
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
						$value = $row->$checkVal;
						$rowOut[] = $this->validateAndPrepareData($checkVal, $row->$checkVal);
					}else{
						$rowOut[] = '';
					}

				}else{

					if(!empty($row[ $header['name']] )) {
						$value = $row[ $header['name'] ];
						$rowOut[] = $this->validateAndPrepareData($header['name'], $value);
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
