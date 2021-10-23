<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Easymanage_Importer_Base{

    const STEP_START = 1;

    const STEP_PROCESS_CREATE = 2;

    const STEP_PROCESS_UPDATE = 3;

    const STEP_COMPLETE = 4;

    const FOLDER_REVISION = 'easymanage_revision';

    const FOLDER_PROCESS  = 'easymanage_process';

    const FOLDER_IMPORT   = 'easymanage_import';

    const FILE_CREATE_INDEX   = 'create_';

    const FILE_UPDATE_INDEX   = 'update_';

    const FILE_PROCESS_INDEX = 'process_';

		const DEFAULT_REINDEX = 2;

		const LANGUAGE_COLUMN_NAME = 'language';

		const SKU_COLUMN_NAME      = 'sku';

    protected $_processProductsPerCall = 20;

    protected $_errors;

		protected $languageCodeIndex;

		protected $skuIndex;

    protected $_logs;

    protected $_revisionId;

    protected $_updateRows = [];

    protected $_createRows = [];

		protected $_createSkus = [];

    protected $_baseData = null;

    protected $_csvHeaderArr = [];

    protected $_notFoundSkus = [];

    protected $_flagStartProcess = false;

    protected $_nextStepFlag = false;

		protected $lockData;

    public function processData( $data ) {
      $this->_revisionId = $data['revison_id'];
      $this->lockData = $this->readProcessFile();
      if(!$this->lockData) {
        $this->addError(__('Process file not found!'), 'easymanage');
        return $this->_result();
      }
      $step = $this->getCurrentStep();

      $rowIndex = $this->lockData->row_index;
      $saved    = $this->lockData->saved;

			if($this->lockData->saved == $this->lockData->total) {

				$this->removeFileProcess();
				$this->removeImportFile();
				return [
					'total' => $this->lockData->total,
					'saved' => $this->lockData->saved,
					'reindex' => self::DEFAULT_REINDEX
				];
			}

      switch($step) {

        case self::STEP_PROCESS_CREATE:
          $process = $this->createImportFile(self::FILE_CREATE_INDEX, $rowIndex);
        break;

        case self::STEP_PROCESS_UPDATE:
          $process = $this->createImportFile(self::FILE_UPDATE_INDEX, $rowIndex);
        break;
      }

			if($this->_nextStepFlag && $step == self::STEP_PROCESS_CREATE && $this->checkUpdateFile()) {//move from create to update
				$this->updateFileProcess([
					'step'      => self::STEP_PROCESS_UPDATE,
					'row_index' => 0,
					'total' => $this->lockData->total,
					'saved' => $process['saved']
				]);
			}else{
				$this->updateFileProcess([
					'step'      => $this->lockData->step,
					'row_index' => $process['row_index'],
					'total' => $this->lockData->total,
					'saved' => $process['saved']
				]);
			}

			if($step == self::STEP_PROCESS_UPDATE) {
				$process['update_existing'] = true;
			}
			$process['log_errors']  = $this->getErrors();
			$process['log_message'] = $this->getLogs();
      return $process;
    }

		public function getLockData() {
			return $this->lockData;
		}

    public function prepareData($data, $validateNotFound = false) {

      $this->_baseData = $data;
      $this->generateRevisionId();
      $this->prepareHeaders();
      $this->createRowsData();
      if($validateNotFound && count($this->_createSkus)) {
        $this->_notFoundSkus = $this->_createSkus;
        return $this->_result();
      }
      $this->createFileRevision();
      $this->updateFileRevision();

      $this->updateFileProcess([
        'step'      => self::STEP_START,
        'row_index' => 0,
        'total' => count($this->_createRows) + count($this->_updateRows),
        'saved' => 0
      ]);
      return $this->_result();
    }

    protected function createImportFile($fileNameStart, $rowIndex) {
      $out = [];
      $filePath = $this->getFolderRevision() . '/' . $fileNameStart . $this->_revisionId . '.csv';

      $csvData  = $this->readCsv($filePath);
      $csvDataLength = count( $csvData );

			$totalAdded = 0;
			$headerCSV  = null;
      $importData = [];
      $filename   = null;
			$c = 0;

      foreach($csvData as $csvRow) {
        if(!$headerCSV) {
          $headerCSV = $csvRow;
					continue;
        }
        if($rowIndex <= ($c) && $totalAdded <= $this->_processProductsPerCall) {
          $importData[] = $csvRow;
					$totalAdded++;
        }

				if($this->_processProductsPerCall == $totalAdded){
					break;
				}

				$c++;
      }

			if(($csvDataLength-1) == ($totalAdded + $rowIndex)) {
				$this->_nextStepFlag = true;
			}
      if(count($importData) > 0) {
        $filename = $this->_createImportFile($headerCSV, $importData);
      }
      return [
        'filename'  => $filename,
				'row_index' => ($totalAdded + $rowIndex),
				'added' => $totalAdded,
				'saved' => $this->lockData->saved + $totalAdded,
				'total' => $this->lockData->total
      ];
    }

		protected function removeImportFile() {
			$folder = $this->getImportFolder();
      $filePath = $folder . '/' . $this->_revisionId . '.csv';

			if(is_file($filePath)) {
				unlink($filePath);
			}
		}

    protected function _createImportFile($header, $importDataArray) {
      $folder = $this->getImportFolder();
      $filePath = $folder . '/' . $this->_revisionId . '.csv';
      $this->saveCsv($filePath, $importDataArray, $header);

      return $filePath;
    }

    protected function getImportFolder() {
      $upload_dir = wp_upload_dir();
      $process_dirname = $upload_dir['basedir'] . '/' . self::FOLDER_IMPORT;
      if(!file_exists($process_dirname)) {
        wp_mkdir_p($process_dirname);
      }

      return $process_dirname;
    }

    protected function _result($total = 0, $saved = 0) {
      return [
        'revision_id' => $this->_revisionId,
        'not_found_sku' => $this->_notFoundSkus,
        'saved' => $saved,
        'start_process' => $this->_flagStartProcess,
        'errors' => $this->getErrors(),
        'total'  => $total ? $total : count($this->_createRows) + count($this->_updateRows),
        'log_message' => $this->getLogs()
      ];
    }

    protected function getCurrentStep() {
      $step = $this->lockData->step;
      $step = ($step == self::STEP_START ? self::STEP_PROCESS_CREATE : $step);
      if($step == self::STEP_PROCESS_CREATE && !$this->checkCreateFile()) {
        $step = self::STEP_PROCESS_UPDATE;
      }

      return $step;
    }

    protected function createFileRevision() {
      $data = $this->_createRows;
      if(!count($data)) {
        return;
      }

      $this->_flagStartProcess = true;
      $filePath = $this->getFolderRevision() . '/' . self::FILE_CREATE_INDEX . $this->_revisionId . '.csv';
      $this->saveCsv($filePath, $data);
    }

    protected function checkCreateFile() {
      $filePath = $this->getFolderRevision() . '/' . self::FILE_CREATE_INDEX . $this->_revisionId . '.csv';
      if(!is_file($filePath)) {
        return false;
      }
      return true;
    }

    protected function updateFileRevision() {
      $data = $this->_updateRows;
      if(!count($data)) {
        return;
      }

      $this->_flagStartProcess = true;
      $filePath = $this->getFolderRevision() . '/' . self::FILE_UPDATE_INDEX . $this->_revisionId . '.csv';
      $this->saveCsv($filePath, $data);
    }

    protected function checkUpdateFile() {
      $filePath = $this->getFolderRevision() . '/' . self::FILE_UPDATE_INDEX . $this->_revisionId . '.csv';
      if(!is_file($filePath)) {
        return false;
      }
      return true;
    }

    protected function saveCsv($filePath, $data, $headersDef = null) {
      $file = fopen($filePath, "w");
      $headers = $headersDef ? $headersDef : $this->_csvHeaderArr;
      fputcsv($file, $headers);
      foreach ($data as $line){
          fputcsv($file, $line);
      }
      fclose($file);
    }

    protected function readCsv($filePath) {
      return array_map('str_getcsv', file($filePath));
    }

    protected function readProcessFile() {
      $folder = $this->getFolderProcess();
      $filePath   = $this->getLockingFileName();

      if(!is_file($folder . '/' . $filePath)) {
        $this->addError(__('Process file not found'), 'easymanage');
        return;
      }
      $dataStr = file_get_contents( $folder . '/' . $filePath );
      return json_decode($dataStr);
    }

    protected function updateFileProcess($processData) {
      $folder = $this->getFolderProcess();
      $filePath   = $this->getLockingFileName();

      $dataStr = json_encode($processData);
      $file = fopen($folder . '/' . $filePath, "w");
      fwrite($file, $dataStr);
      fclose($file);
    }

    protected function removeFileProcess() {
      $folder = $this->getFolderProcess();
      $file   = $this->getLockingFileName();

      unlink($folder . '/' . $file);
    }

    protected function getLockingFileName() {
      return self::FILE_PROCESS_INDEX . $this->_revisionId;
    }

    protected function prepareHeaders() {
			$this->languageCodeIndex = null;
			$this->skuIndex = null;
			$_headersData = $this->_baseData['headers'];
      foreach($_headersData as $index => $_header) {
				$this->_csvHeaderArr[] = $_header['name'];
				if($_header['name'] == self::LANGUAGE_COLUMN_NAME) {
						$this->languageCodeIndex = $index;
				}
				if($_header['name'] == self::SKU_COLUMN_NAME) {
						$this->skuIndex = $index;
				}
      }
    }

    protected function createRowsData() {
      $_productsData = $this->_baseData['products'];
      foreach($_productsData as $index => $_productRow) {
				$sku  = $this->getSkuFromRow($_productRow);
				$lang = $this->getLanguageFromRow($_productRow);
        if($this->productExists($sku, $lang)) {
          $this->_updateRows[] = $_productRow;
        }else{
          $this->_createRows[] = $_productRow;
					$this->_createSkus[] = $sku;
        }
      }
    }

		protected function getSkuFromRow($_productRow)
		{
			if($this->skuIndex == null){
				return $_productRow[0];
			}
			return !empty( $_productRow[$this->skuIndex] ) ? $_productRow[$this->skuIndex] : $_productRow[0];
		}

		protected function getLanguageFromRow($_productRow){
			if($this->languageCodeIndex == null){
				return;
			}

			return !empty($_productRow[$this->languageCodeIndex]) ? $_productRow[$this->languageCodeIndex] : null;
		}

    protected function productExists($sku, $lang = null) {

			if($lang == null) {
				$productId = wc_get_product_id_by_sku( $sku );
			}else{
				$productId = $this->getProductByLangAndSku($sku, $lang);
			}

			return $productId;

			/*
				if($lang == null || !$productId) {
					return ($productId ? true : false);
				}
				$currentProductLang = $this->getLangTaxonomyValue($productId);

				return ($currentProductLang == $lang);
			*/
		}

		protected function getProductByLangAndSku($sku, $lang)
		{
			$cc_args = array(
			    'posts_per_page'   => -1,
			    'post_type'        => 'product',
			    'meta_key'         => '_sku',
			    'meta_value'       => $sku
			);
			$cc_query = new WP_Query( $cc_args );
			$products = $cc_query->posts;
			$filledProductIds = [];

			foreach($products as $product) {

				if($product->post_status != 'publish') {
					continue;
				}

				if(in_array($product->ID, $filledProductIds)) {
					continue;
				}

				$currentLang = $this->getLangTaxonomyValue($product->ID);
				if($currentLang == $lang) {
					return $product->ID;
				}
				$filledProductIds[] = $product->ID;
			}
		}

		protected function getLangTaxonomyValue($productId)
		{
				$terms = get_the_terms( $productId, 'language' );
				if(empty($terms) || empty($terms[0])) {
					return '';
				}

				$objTaxonomy = $terms[0];
				if(empty($objTaxonomy->slug)) {
					return '';
				}

				return $objTaxonomy->slug;
		}

    protected function generateRevisionId() {
      $this->_revisionId = uniqid();
    }

    protected function addError($errorText = '') {
      $this->_errors[] = $errorText;
    }

    protected function getErrors() {
      return $this->_errors;
    }

    protected function addLog($logText = '') {
      $this->_logs[] = $logText;
    }

    protected function getLogs() {
      return $this->_logs;
    }

    public function getFolderRevision($date = null) {
      $upload_dir = wp_upload_dir();
      $date = $date ? $date : date('Y-m-d');
      $revision_dirname = $upload_dir['basedir'] . '/' . self::FOLDER_REVISION . '/' . $date;
      if(!file_exists($revision_dirname)) {
        wp_mkdir_p($revision_dirname);
      }

      return $revision_dirname;
    }

    public function getFolderProcess() {
      $upload_dir = wp_upload_dir();
      $process_dirname = $upload_dir['basedir'] . '/' . self::FOLDER_PROCESS;
      if(!file_exists($process_dirname)) {
        wp_mkdir_p($process_dirname);
      }

      return $process_dirname;
    }
}
