<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Include dependencies.
 */
if ( ! class_exists( 'WC_Product_CSV_Importer', false ) ) {
  include_once WC_ABSPATH . 'includes/import/class-wc-product-csv-importer.php';
}

if(! class_exists( 'Easymanage_Importer_Base', false )) {
  include_once dirname( __FILE__ ) . '/class-easymanage-importer-base.php';
}

class Easymanage_Importer extends WC_Product_CSV_Importer{

    protected $_importerBase = null;


    public function __construct() {
      $this->_importerBase = new Easymanage_Importer_Base();
    }

    public function prepareSaveData($data, $validateNotFound = false){
      return $this->_importerBase->prepareData( $data, $validateNotFound );
    }

    public function processSaveData($data) {
      $result = $this->_importerBase->processData( $data );

      $filename = !empty($result['filename']) ? $result['filename'] : null;
      if($filename) {
        $this->params = array(
    			'start_pos'        => 0, // File pointer start.
    			'end_pos'          => -1, // File pointer end.
    			'lines'            => -1, // Max lines to read.
    			'mapping'          => array(), // Column mapping. csv_heading => schema_heading.
    			'parse'            => true, // Whether to sanitize and format data.
    			'update_existing'  => false, // Whether to update existing items.
    			'delimiter'        => ',', // CSV delimiter.
    			'prevent_timeouts' => true, // Check memory and time usage and abort if reaching limit.
    			'enclosure'        => '"', // The character used to wrap text in the CSV.
    			'escape'           => "\0", // PHP uses '\' as the default escape character. This is not RFC-4180 compliant. This disables the escape character.
    		);
				if(!empty($result[ 'update_existing' ])) {
        	$this->params['update_existing'] = true;
				}
				$this->setMappedData($data);

        $this->file = $filename;
        $this->read_file();

        $data = $this->import();
      }
      return $result;
    }

    protected function  setMappedData($data) {
      $headers = $data['headers'];
      foreach($headers as $_headers) {
        $this->params['mapping'][$_headers['name']] = $_headers['name'];
      }
    }
}
