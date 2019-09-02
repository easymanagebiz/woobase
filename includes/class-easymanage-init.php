<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Easymanage_Rest_API {

  const QUERY_API_NAME = 'easymanage';

  public function __construct() {

    add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );
    add_action( 'init', array( $this, 'add_endpoint' ), 0 );
    add_action( 'parse_request', array( $this, 'handle_api_requests' ), 0 );
		/* register search by sku */
		add_filter( 'woocommerce_product_data_store_cpt_get_products_query', array( $this, 'handle_custom_query_var'), 10, 2 );
    $this->easymanage_rest_api_init();
  }

	public function handle_custom_query_var($query, $query_vars) {

		if ( ! empty( $query_vars['search_by_sku'] ) ) {
			$query['meta_query'][] = array(
				'key' => '_sku',
				'value' => esc_attr( $query_vars['search_by_sku'] ),
				'compare' => 'LIKE'
			);
		}

		return $query;
	}

  public function add_query_vars( $vars ) {
		$vars[] = self::QUERY_API_NAME;
		return $vars;
	}

  public static function add_endpoint() {
		add_rewrite_endpoint( self::QUERY_API_NAME, EP_ALL );
	}

  public function handle_api_requests() {
		global $wp;

		if ( ! empty( $_GET[self::QUERY_API_NAME] ) ) {
			$wp->query_vars[self::QUERY_API_NAME] = sanitize_key( wp_unslash( $_GET[self::QUERY_API_NAME] ) );
		}

		if ( ! empty( $wp->query_vars[self::QUERY_API_NAME] ) ) {
			ob_start();
			wc_nocache_headers();
			$api_request = strtolower( wc_clean( $wp->query_vars[self::QUERY_API_NAME] ) );
			do_action( self::QUERY_API_NAME . '_api_request', $api_request );
			status_header( has_action( self::QUERY_API_NAME . '_api_' . $api_request ) ? 200 : 400 );
			do_action( self::QUERY_API_NAME . '_api_' . $api_request );
			ob_end_clean();
			die( '-1' );
		}
	}

  public function easymanage_rest_api_init() {
    if ( ! class_exists( 'WP_REST_Server' ) ) {
			return;
		}

    if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
    add_action( 'rest_api_init', array( $this, 'register_easymanage_routes' ), 10 );
  }

  public function rest_api_includes() {
    include_once( dirname( __FILE__ ) . '/api/class-easymanage-controller.php' );
  }

  public function register_easymanage_routes() {
    $this->rest_api_includes();

    $controllers = array(
			'Easymanage_API_Controller'
		);

		foreach ( $controllers as $controller ) {
			$this->$controller = new $controller();
			$this->$controller->register_routes();
		}
  }
}

return new Easymanage_Rest_API();
