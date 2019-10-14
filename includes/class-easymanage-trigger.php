<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Easymanage_Trigger {

  const TRIGGER_TYPE_INSERT_ROW = 'tableInsertRow';

  public function createTriggerInsertRow($table, $rowData) {
    $this->createTrigger(self::TRIGGER_TYPE_INSERT_ROW, [
      'table'  => $table,
      'data'   => $rowData
    ]);
  }

  public function createTrigger($type, $data = []) {
    global $wpdb;

    $wpdb->insert("{$wpdb->prefix}easymanage_triggers", array(
      'type' => $type,
      'data' => json_encode($data),
      'created_date' => current_time( 'mysql' ),
      'unique_id'    => $this->getUniqueId()
    ));

    $template_id = $wpdb->insert_id;
  }

  public function updateTriggerRundate($triggerUniqueId) {
    global $wpdb;

    $wpdb->update("{$wpdb->prefix}easymanage_triggers", array(
      'run_date' => current_time( 'mysql' ),
      'status' => 1
    ), array(
      'unique_id' => $triggerUniqueId
    ));
  }

  public function updateTriggerStatus($triggerUniqueId, $error = '') {
    global $wpdb;

    $wpdb->update("{$wpdb->prefix}easymanage_triggers", array(
      'status' => ($error == '' ? 2 : 1),
      'error' => $error
    ), array(
      'unique_id' => $triggerUniqueId
    ));
  }

  public function getTriggersToRun() {
    global $wpdb;

    $triggers = $wpdb->get_results(
      	"
      	SELECT *
      	FROM {$wpdb->prefix}easymanage_triggers
      	WHERE status = 0
      	"
      );

    return $triggers;
  }

  protected function getUniqueId() {
    return uniqid();
  }
}
