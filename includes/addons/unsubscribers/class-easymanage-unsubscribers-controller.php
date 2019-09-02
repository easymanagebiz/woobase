<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Easymanage_Unsubscribers_Controller extends Easymanage_API_Controller{

  public function __construct() {
    parent::__construct();
  }

  public function register_routes() {

    register_rest_route( $this->namespace, $this->rest_base . '/exportunsubscribers', array(
      'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'exportunsubscribers' ),
			'args'     => array(

      )
    ));

    register_rest_route( $this->namespace, $this->rest_base . '/saveunsubscribers', array(
      'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'saveunsubscribers' ),
			'args'     => array(

      )
    ));
  }

  public function exportunsubscribers() {
		if(!$this->_helper->checkIsAdmin()) {
			return $this->_response->error($this->getErrorNotAuthMessage());
		}
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
		$total = 0;
		foreach($emails as $email) {
			$out[] = [$email->email_address];
			$total++;
		}

		return $this->_response->response([
			'data'       => $out,
			'postValues' => $data,
			'totalCount' => $total
		]);
  }

	public function saveunsubscribers() {
		if(!$this->_helper->checkIsAdmin()) {
			return $this->_response->error($this->getErrorNotAuthMessage());
		}
		$this->clear_all();
		global $wpdb;

		$post = $this->_helper->getRequestJsonData();
		if(empty($post) || empty($post['data'])) {
      return [[
        'status' => 'ok',
        'type'   => $post['extra']['type'],
        'sheet_id' => $post['sheet_id'],
        'total_saved' => '0',
        'total' => 0,
      ]];
    }

		$c = 0;
    foreach($post['data'] as $emailRow) {
      $email = !empty($emailRow[0]) ? $emailRow[0] : null;

      if(!$email) {
        continue;
      }
			$wpdb->insert("{$wpdb->prefix}easymanage_unsubscribers", array(
				'email_address' => $email
			));
      $c++;
    }

    return [[
      'status' => 'ok',
      'type'   => $post['extra']['type'],
      'sheet_id' => $post['sheet_id'],
      'total_saved' => $c ? $c : '0',
      'total' => $c,

    ]];
	}

	protected function clear_all() {
		global $wpdb;

		$sql = "DELETE FROM {$wpdb->prefix}easymanage_unsubscribers
							WHERE 1";

		$wpdb->query($sql);
	}
}
