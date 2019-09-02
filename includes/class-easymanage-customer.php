<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Easymanage_Customer {

  protected $_role = 'customer';

  public function searchCustomer($search) {

    $users_args = array(
      'role' => $this->_role,
      'search' => '*' . $search . '*'
    );

    return get_users($users_args);
  }

  public function exportCustomer($filters) {
    $filterParams = ['default filter'];

    if($filters) {
      $str = parse_str($filters, $filterParams);
    }

    $args = [
    	'role' => $this->_role,
    ];

    if($filterParams['registrated_from-data']) {

      $args['date_query'] = [
        [
           'after' => date('Y-m-d', intval($filterParams['registrated_from-data'])),
           'inclusive' => true
        ]
      ];
    }

    return get_users($args);
  }

}
