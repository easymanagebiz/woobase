<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Easymanage_API_Controller extends WC_REST_Controller{

	const VERSION = '1.0.1';

  protected $namespace = 'easymanage';

  protected $_version = 'v1';

  protected $rest_base = '';

	protected $_helper;

	protected $_response;

  public function __construct() {
    $this->namespace .= '/' . $this->_version;
		$this->_helper    = new Easymanage_Helper();
		$this->_response  = new Easymanage_Response();
  }

  public function register_routes() {
    register_rest_route( $this->namespace, $this->rest_base . '/test', array(
      'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'test_api' ),
			'args'     => array(

      )
    ));
    register_rest_route( $this->namespace, $this->rest_base . '/ping', array(
      'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'ping_api' ),
			'args'     => array(

      )
    ));

		/* products  */

		register_rest_route( $this->namespace, $this->rest_base . '/categories', array(
      'methods'  => WP_REST_Server::READABLE,
			'callback' => array( $this, 'categories' ),
			'args'     => array(

      )
    ));


		register_rest_route( $this->namespace, $this->rest_base . '/search', array(
      'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'search' ),
			'args'     => array(

      )
    ));


		register_rest_route( $this->namespace, $this->rest_base . '/exportproducts', array(
      'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'exportproducts' ),
			'args'     => array(

      )
    ));

		register_rest_route( $this->namespace, $this->rest_base . '/save', array(
      'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'save' ),
			'args'     => array(

      )
    ));


		register_rest_route( $this->namespace, $this->rest_base . '/import', array(
      'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'import' ),
			'args'     => array(

      )
    ));

		register_rest_route( $this->namespace, $this->rest_base . '/process', array(
      'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'process' ),
			'args'     => array(

      )
    ));


		/* customers */

		register_rest_route( $this->namespace, $this->rest_base . '/searchcustomers', array(
      'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'search_customers' ),
			'args'     => array(

      )
    ));

		register_rest_route( $this->namespace, $this->rest_base . '/exportcustomers', array(
      'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'export_customers' ),
			'args'     => array(

      )
    ));

		/* mail merge */
		register_rest_route( $this->namespace, $this->rest_base . '/mailtemplateall', array(
      'methods'  => WP_REST_Server::READABLE,
			'callback' => array( $this, 'mailtemplateall' ),
			'args'     => array(

      )
    ));

		register_rest_route( $this->namespace, $this->rest_base . '/mailtemplatesave', array(
      'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'mailtemplatesave' ),
			'args'     => array(

      )
    ));

		register_rest_route( $this->namespace, $this->rest_base . '/mailtemplateget', array(
      'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'mailtemplateget' ),
			'args'     => array(

      )
    ));

		register_rest_route( $this->namespace, $this->rest_base . '/mailtemplatedelete', array(
      'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'mailtemplatedelete' ),
			'args'     => array(

      )
    ));

		/* mail process */

		register_rest_route( $this->namespace, $this->rest_base . '/subscriberids', array(
      'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'subscriberids' ),
			'args'     => array(

      )
    ));

		register_rest_route( $this->namespace, $this->rest_base . '/mailprocess', array(
      'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'mailprocess' ),
			'args'     => array(

      )
    ));
  }

	public function import() {
		if(!$this->_helper->checkIsAdmin()) {
			return $this->_response->error($this->getErrorNotAuthMessage());
		}
		try {
			$data   = $this->_helper->getRequestJsonData();
			include_once EASYMANAGE_FILE_PATH . '/includes/import/class-easymanage-importer.php';
			$importer = new Easymanage_Importer();
			$result = $importer->prepareSaveData($data);

			return $this->_response->response([
        'status' => 'ok',
        'type'   => $data['extra']['type'],
        'sheet_id' => $data['sheet_id'],
        'not_found_sku' => !empty($result['not_found_sku']) ? $result['not_found_sku'] : false,
        'revisionId' => !empty($result['revision_id']) ? $result['revision_id'] : '0',
        'total_saved' => !empty($result['saved']) ? $result['saved'] : '0',
        'start_process' => !empty($result['start_process']) ? $result['start_process'] : false,
        'total' 				=> !empty($result['total']) ? $result['total'] : '0',
        'log_errors' => !empty($result['errors']) ? $result['errors'] : []
      ]);

		}catch(Exception $e) {
			return $this->_response->error($e->getMessage());
		}
	}

	public function save() {
		if(!$this->_helper->checkIsAdmin()) {
			return $this->_response->error($this->getErrorNotAuthMessage());
		}
		try {
			$data   = $this->_helper->getRequestJsonData();
			include_once EASYMANAGE_FILE_PATH . '/includes/import/class-easymanage-importer.php';
			$importer = new Easymanage_Importer();
			$result = $importer->prepareSaveData($data, true);

			return $this->_response->response([
        'status' => 'ok',
        'type'   => $data['extra']['type'],
        'sheet_id' => $data['sheet_id'],
        'not_found_sku' => !empty($result['not_found_sku']) ? $result['not_found_sku'] : false,
        'revisionId' => !empty($result['revision_id']) ? $result['revision_id'] : '0',
        'total_saved' => !empty($result['saved']) ? $result['saved'] : '0',
        'start_process' => !empty($result['start_process']) ? $result['start_process'] : false,
        'total' 				=> !empty($result['total']) ? $result['total'] : '0',
        'log_errors' => !empty($result['errors']) ? $result['errors'] : []
      ]);

		}catch(Exception $e) {
			return $this->_response->error($e->getMessage());
		}
	}

	public function process() {
		if(!$this->_helper->checkIsAdmin()) {
			return $this->_response->error($this->getErrorNotAuthMessage());
		}
		try {
			$data   = $this->_helper->getRequestJsonData();
			include_once EASYMANAGE_FILE_PATH . '/includes/import/class-easymanage-importer.php';
			$importer = new Easymanage_Importer();

			$result = $importer->processSaveData($data);

			return $this->_response->response([
        'status' => 'ok',
        'type'   => $data['extra']['type'],
        'sheet_id' => $data['sheet_id'],
        'not_found_sku' => !empty($result['not_found_sku']) ? $result['not_found_sku'] : false,
        'revisionId' => !empty($result['revision_id']) ? $result['revision_id'] : '0',
        'total_saved' => !empty($result['saved']) ? $result['saved'] : '0',
        'start_process' => !empty($result['start_process']) ? $result['start_process'] : false,
        'total' 				=> !empty($result['total']) ? $result['total'] : '0',
        'log_errors' => !empty($result['errors']) ? $result['errors'] : [],
				'log_message' => !empty($result['log_message']) ? $result['log_message'] : null,
				'reindex' => !empty($result['reindex']) ? $result['reindex'] : null
      ]);

		}catch(Exception $e) {
			return $this->_response->error($e->getMessage());
		}
	}

	public function exportproducts() {
		if(!$this->_helper->checkIsAdmin()) {
			return $this->_response->error($this->getErrorNotAuthMessage());
		}
		try {
			$data   = $this->_helper->getRequestJsonData();

			include_once EASYMANAGE_FILE_PATH . '/includes/export/class-easymanage-export-products.php';

			$exporter = new Easymanage_Export_Products();
			$exporter->init($data);
			$productRows = $exporter->fetch_products($data);

			return $this->_response->response([
				'postValues' => $data,
				'totalCount' => $productRows ? count($productRows) : 0,
				'dataProducts' => $productRows
			], true);

		}catch(Exception $e) {
			return $this->_response->error($e->getMessage());
		}
	}

	public function search() {
		if(!$this->_helper->checkIsAdmin()) {
			return $this->_response->error($this->getErrorNotAuthMessage());
		}
		try {
			$data   = $this->_helper->getRequestJsonData();
			$search = !empty( $data['search'] ) ? $data['search'] : null ;

			if(!$search) {
				return $this->_response->response([]);
			}

			include_once EASYMANAGE_FILE_PATH . '/includes/export/class-easymanage-export-products.php';

			$exporter = new Easymanage_Export_Products();
			$exporter->init($data);
			$productRows = $exporter->search_products( $search );

			return $this->_response->response([
				'postValues' => $data,
				'totalCount' => $productRows ? count($productRows) : 0,
				'dataProducts' => $productRows
			], true);

		}catch(Exception $e) {
			return $this->_response->error($e->getMessage());
		}
	}

	public function categories() {
		if(!$this->_helper->checkIsAdmin()) {
			return $this->_response->error($this->getErrorNotAuthMessage());
		}
		$categories = $this->get_categories();
		return $this->_response->response(
			$categories
		, true, true);
	}

	public function mailprocess() {
		if(!$this->_helper->checkIsAdmin()) {
			return $this->_response->error($this->getErrorNotAuthMessage());
		}
		$data = $this->_helper->getRequestJsonData();
		$template_id = !empty( $data['email_id'] ) ? $data['email_id'] : null ;
		if(!$template_id) {
			return;
		}
		$template = $this->get_one_email_template($template_id);
		if(empty($template)) {
			return;
		}
		$mailTemplateClass = new Easymanage_Mailtemplate();
		$mailTemplateClass->processTemplate($template);
		return $this->_response->response([
      'short_codes'    => $mailTemplateClass->getShortCodes(),
      'content_email'  => $mailTemplateClass->getContentFinal(),
      'subject' =>  $mailTemplateClass->getSubject(),
      'base_template'  => $mailTemplateClass->getBaseTemplate(),
      'unsubscribers' => $this->get_unsubscribers()
    ]);
	}

	public function subscriberids() {
		if(!$this->_helper->checkIsAdmin()) {
			return $this->_response->error($this->getErrorNotAuthMessage());
		}
		try{
			$data = $this->_helper->getRequestJsonData();
	    $output = [];
	    foreach($data as $row => $email) {
	      $email_object  = $this->get_email($email);
				if($email_object) {
	      	$output[$row] = $email_object->unique_id;
				}else{
					$output[$row] = $this->create_email($email);
				}
	    }

	    return $this->_response->response(
	      $output
	    , true);

		}catch(Exception $e) {
			return $this->_response->error($e->getMessage());
		}

	}

	public function mailtemplatedelete() {
		if(!$this->_helper->checkIsAdmin()) {
			return $this->_response->error($this->getErrorNotAuthMessage());
		}
		try{
			$data = $this->_helper->getRequestJsonData();
			$template_id = !empty($data['template_id']) ? $data['template_id'] : null;
			if(empty($template_id)) {
	      return $this->mailtemplateall();
	    }
			global $wpdb;
			$wpdb->query( $wpdb->prepare(
					"
					DELETE FROM {$wpdb->prefix}easymanage_email_template
					WHERE template_email_id = %d
					",$template_id
				) );

			return $this->_response->response([
				'all' => $this->get_email_templates_all()
			]);

		}catch(Exception $e) {
			return $this->_response->error($e->getMessage());
		}
	}

	public function mailtemplateget() {
		if(!$this->_helper->checkIsAdmin()) {
			return $this->_response->error($this->getErrorNotAuthMessage());
		}
		try{
			$data = $this->_helper->getRequestJsonData();

	    $template_id = !empty($data['template_id']) ? $data['template_id'] : null;
			if(empty($template_id)) {
	      return $this->mailtemplateall();
	    }
			$template = $this->get_one_email_template($template_id);
			if(empty($template)) {
	      return $this->mailtemplateall();
	    }

			return $this->_response->response([
				'all' => $this->get_email_templates_all(),
	      'selected' => $template->template_email_id,
	      'template_code' => $template->email_content,
	      'subject' => $template->email_subject
			]);

		}catch(Exception $e) {

			return $this->_response->error($e->getMessage());
		}
	}

	public function mailtemplatesave() {

		if(!$this->_helper->checkIsAdmin()) {
			return $this->_response->error($this->getErrorNotAuthMessage());
		}
		try{
			global $wpdb;

			$data = $this->_helper->getRequestJsonData();

			$subject = !empty($data['subject']) ? $data['subject'] : null;
	    $content = !empty($data['content']) ? $data['content'] : null;

	    $template_id = !empty($data['template_id']) ? $data['template_id'] : null;

	    if(empty($content) || empty($subject)) {
	      return $this->mailtemplateall();
	    }
			$template = false;
			if($template_id) {
				$template = $this->get_one_email_template($template_id);
			}

			if($template) { //update

				$wpdb->update("{$wpdb->prefix}easymanage_email_template", array(
					'email_subject' => $subject,
					'email_content' => $content
				), array(
					'template_email_id' => $template_id
				));

			}else{ //insert
				$wpdb->insert("{$wpdb->prefix}easymanage_email_template", array(
					'email_subject' => $subject,
					'email_content' => $content
				));

				$template_id = $wpdb->insert_id;
			}

			return $this->_response->response([
	      'all' => $this->get_email_templates_all(),
	      'selected' => $template_id,
				'content' => $content
	    ]);

		}catch(Exception $e) {
			//var_dump( $e->getMessage() ); die();
			return $this->_response->error($e->getMessage());
		}
	}


	public function mailtemplateall() {
		if(!$this->_helper->checkIsAdmin()) {
			return $this->_response->error($this->getErrorNotAuthMessage());
		}
		try{
			$templates = $this->get_email_templates_all();
			return $this->_response->response(
				($templates ? $templates : []), true
			);

		}catch(Exception $e) {
			return $this->_response->error($e->getMessage());
		}
	}

	public function export_customers() {
		if(!$this->_helper->checkIsAdmin()) {
			return $this->_response->error($this->getErrorNotAuthMessage());
		}

		try{
			$_request = $this->_helper->getRequestJsonData();
			$headers  = !empty($_request['headers']) ? $_request['headers'] : [];
			$_params  = !empty($_request['params']) ? $_request['params'] : '';

			$customers = new Easymanage_Customer();

			$customerData = $customers->exportCustomer($_params);

			return $this->_response->response([
        'postValues' => $_request,
        'totalCount' => $customerData ? count($customerData) : 0,
        'dataCustomers' => $this->_helper->prepareOutData($customerData, $headers)
			]);

		}catch(Exception $e) {
			return $this->_response->error($e->getMessage());
		}
	}

	public function search_customers() {
		if(!$this->_helper->checkIsAdmin()) {
			return $this->_response->error($this->getErrorNotAuthMessage());
		}

		try{
			$_request = $this->_helper->getRequestJsonData();
			$headers  = !empty($_request['headers']) ? $_request['headers'] : [];
			$search   = !empty($_request['search'])  ? $_request['search'] : '';

			$customers = new Easymanage_Customer();

			$customerData = $customers->searchCustomer($search);

			return $this->_response->response([
        'postValues' => $_request,
        'totalCount' => $customerData ? count($customerData) : 0,
        'dataCustomers' => $this->_helper->prepareOutData($customerData, $headers)
			]);

		}catch(Exception $e) {
			return $this->_response->error($e->getMessage());
		}
	}

  public function test_api() {
		if(!$this->_helper->checkIsAdmin()) {
			return $this->_response->error($this->getErrorNotAuthMessage());
		}
		return $this->_response->response([
			'authorized' => true
		]);
  }

	public function ping_api() {
		if(!$this->_helper->checkIsAdmin()) {
			return $this->_response->error($this->getErrorNotAuthMessage());
		}

		do_action( 'easymanage_addons', Easymanage_Addon::getInstance() );

		return $this->_response->response([
			'version' => self::VERSION,
			'addons' => Easymanage_Addon::getInstance()->getAddons()
		]);
	}

	protected function get_categories() {
		$orderby = 'term_id';
		$order = 'asc';
		$hide_empty = false ;
		$cat_args = array(
		    'orderby'    => $orderby,
		    'order'      => $order,
		    'hide_empty' => $hide_empty,
		);

		$product_categories = get_terms( 'product_cat', $cat_args );

		if(!$product_categories) {
			return [];
		}
		return [
			'name' => 'WOO Commerce Site',
			'id' => '',
			'children_data' => $this->sort_and_prepare_categories($product_categories, 0)
		];
	}

	protected function sort_and_prepare_categories($product_categories, $parent_id = 0) {
		$output = [];

		foreach($product_categories as $category_data) {

			if($category_data->parent == $parent_id) {
				$childs = $this->sort_and_prepare_categories($product_categories, $category_data->term_id);
				$output[] = [
					'name' => $category_data->name,
					'id'   => $category_data->slug,
					'children_data' => $childs ? $childs : [],
					'product_count' => $category_data->count
				];
			}

		}

		return $output;
	}

	protected function create_email($email) {
		global $wpdb;

		$unique_id = uniqid();
		$wpdb->insert("{$wpdb->prefix}easymanage_emails", array(
			'email_address' => $email,
			'unique_id' => $unique_id
		));

		return $unique_id;
	}

	protected function get_email($email) {
		global $wpdb;
		$email_row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM
					{$wpdb->prefix}easymanage_emails
					WHERE email_address = %s", $email
		));

		if(!empty($email_row) && !empty($email_row->email_id)) {
			return $email_row;
		}
	}

	protected function get_one_email_template($template_id) {
		global $wpdb;
		$template = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM
					{$wpdb->prefix}easymanage_email_template
					WHERE template_email_id = %d", $template_id
		));

		if(!empty($template) && !empty($template->template_email_id)) {
			return $template;
		}
	}

	protected function get_email_templates_all() {
		global $wpdb;

		$email_templates = $wpdb->get_results(
			"
			SELECT template_email_id, email_subject
			FROM {$wpdb->prefix}easymanage_email_template
			WHERE 1
			"
		);

		$out = [];
		foreach($email_templates as $template) {
			$out[] = [
				'label' => $template->email_subject,
				'value' => $template->template_email_id
			];
		}

		return $out;
	}

	protected function get_unsubscribers() {
		global $wpdb;

		$data = $this->_helper->getRequestJsonData();

		$emails = $wpdb->get_results(
			"
			SELECT email_address
			FROM {$wpdb->prefix}easymanage_unsubscribers
			WHERE 1
			"
		);

		$out = [];
		foreach($emails as $email) {
			$out[] = $email->email_address;
		}

		return $out;
	}


	protected function getErrorNotAuthMessage() {
		return __( 'User not found, or user not administrator, or API keys have no READ/WRITE permissions', 'easymanage' );
	}
}
