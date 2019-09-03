<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Product_CSV_Exporter', false ) ) {
  include_once WC_ABSPATH . 'includes/export/class-wc-product-csv-exporter.php';
}

class Easymanage_Export_Products extends WC_Product_CSV_Exporter {

	const PRODUCTS_LIMIT = 1000;//to do make pagination on fetch G App Script

  protected $_special_procces_fields = [
    'price',
    'regular_price',
    'sale_price'
  ];

  protected $_price_fields = [
    'price',
    'regular_price',
    'sale_price'
  ];

	protected $_exportHeaders = [];

  protected $_search = null;

  protected $_categories = null;

  public function init($data) {
		add_filter( "woocommerce_product_export_{$this->export_type}_query_args", array( $this, 'add_params'), 15, 2 );
    $headers = !empty($data['headers']) ? $data['headers'] : null;

    if($headers) {
      $this->set_headers( $headers );
    }
  }

  public function set_headers($headers) {
    $columns = [];
    $columns_to_export = [];
    foreach($headers as $header) {
      $columns[$header['name']] = $header['label'];
      $columns_to_export[] = $header['name'];
    }
		$this->_exportHeaders = $columns;
		if($this->downloadsEnabled()) {
			$columns_to_export[] = 'downloads';
		}
		if($this->attributesEnabled()) {
			$columns_to_export[] = 'attributes';
		}
		if($this->metaEnabled()) {
			$columns_to_export[] = 'attributes';
		}
		$this->set_columns_to_export($columns_to_export);
    $this->set_column_names($columns);

  }

	protected function downloadsEnabled() {
		foreach($this->_exportHeaders as $key=>$label) {
			if(strstr($key, 'downloads:')) {
				return true;
			}
		}
	}

	protected function attributesEnabled() {
		foreach($this->_exportHeaders as $key=>$label) {
			if(strstr($key, 'attributes:')) {
				return true;
			}
		}
	}

	protected function metaEnabled() {
		foreach($this->_exportHeaders as $key=>$label) {
			if(strstr($key, 'meta:')) {
				return true;
			}
		}
	}

  public function add_params($args) {
    $search = $this->getSearch();
    if($search) {
      $args['search_by_sku'] = $search;
    }

    $categories = $this->getCategories();
    if($categories) {
      $args['category'] = $categories;
    }
    return $args;
  }

  public function fetch_products($data) {
    $filterParams = ['default filter'];

    if(!empty($data['params'])) {
      $str = parse_str($data['params'], $filterParams);
    }
    $categories = !empty($filterParams['from_categories']) ? $filterParams['from_categories'] : null;
    if($categories && !(count($categories) == 1 && $categories[0] == '')) {
      $this->setCategories( $categories );
    }
    $this->prepare_data_to_export();
    return $this->getRowsData();
  }

	public function get_limit() {
		return self::PRODUCTS_LIMIT;
	}

  public function search_products($search) {
    $this->setSearch($search);
    $this->prepare_data_to_export();
    return $this->getRowsData();
  }

  public function setSearch($search) {
    $this->_search = $search;
  }

  public function setCategories($categories) {
    $this->_categories = $categories;
  }

  public function getCategories() {
    return $this->_categories;
  }

  public function getSearch() {
    return $this->_search;
  }

  public function getRowsData() {
    $defaultRows = $this->row_data ? $this->row_data : [];
    $outputRows  = [];
    foreach($defaultRows as $row) {
      $rowData = [];
      foreach($this->_exportHeaders as $key=>$label) {
				$val = !empty($row[$key]) ? $row[$key] : '';
        if(in_array($key, $this->_special_procces_fields)) {
          $val = $this->prepreValues($key, $val);
        }
        $rowData[] = $val;
      }
      $outputRows[] = $rowData;
    }
    return $outputRows;
  }

  protected function prepreValues($nameField, $val) {
    if(in_array($nameField, $this->_price_fields )) {
      return $this->processPriceField($val);
    }
  }

  protected function processPriceField($val) {
    return number_format($val, 2, '.', '');
  }

}
