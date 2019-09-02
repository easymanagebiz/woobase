<?php

if ( ! class_exists( 'Easymanage_Unsubscribers_Init' ) ) {

 class Easymanage_Unsubscribers_Init {

   const PARENT_UI_COMPONENET = 'mailsidebar_menu';

   const EMAIL_SHORTCODE = '[unsubscribe_link title="text title"]';
   const SHORT_CODE_NAME = 'unsubscribe_link';
   const FETCH_UNSUBSCRIBERS_KEY = 'endpointExportUnsubscribers';
   const SAVE_UNSUBSCRIBERS_KEY  = 'endpointSaveUnsubscribers';

    const FETCH_UNSUBSCRIBERS_URL = 'wp-json/easymanage/v1/exportunsubscribers';
    const SAVE_UNSUBSCRIBERS_URL = 'wp-json/easymanage/v1/saveunsubscribers';

    const ICON_NAME    = 'user_remove';
    const TABLE_INDEX  = 'unsubscribers_mails';

   public function __construct() {
     add_action( 'init', array( $this, 'includes' ), 15 );
   }

   public function includes(){
      if ( ! class_exists( 'WP_REST_Server' ) ) {
      	return;
      }

      if ( ! class_exists( 'WooCommerce' ) ) {
      	return;
      }
      add_action('rest_api_init', array( $this, 'register_easymanage_routes' ), 10 );
      add_action('easymanage_addons', array( $this, 'register_addon' ), 15);
      add_action('easymanage_addons_email_shortcodes', array( $this, 'process_mail_content' ), 15);
      add_action('template_redirect', array( $this, 'unsubscribe_template'));
      add_filter('query_vars', array( $this, 'add_query_vars'));
   }

   public function add_query_vars($vars) {
     $vars[] = 'easymanageunsubscribe';
     return $vars;
   }

   public function unsubscribe_template($template) {
     global $wp_query;

     if(!isset( $wp_query->query['easymanageunsubscribe'] )){
        return $template;
     }

     $unsubscribe_id = $wp_query->query['easymanageunsubscribe'];

     $email_row = $this->get_email($unsubscribe_id);
     if(!$email_row) {
       return $template;
     }

     $this->create_unsubscribe_record($email_row);

     $text = '<html><body><div style="text-align:center">';
     $text .= '<h4>' .  __('You are unsubscribed from email list', 'easymanage') . '</h4><br>';
     $text .= __('You will be redirected to home page in <span style="font-weight:bold;" id="sec">5</span> sec. Or click <a href="/">here</a>', 'easymanage');
     $text .= '<script>var interval = 5; setInterval(function() {
       interval--;
       if(interval <= 0) {
         document.location.href = "' . get_site_url() . '";
       }else{
         var el = document.getElementById("sec");
         el.innerHTML = interval;
       }
     }, 1000);</script>';

     $text .= '</div></body></html>';

     echo $text;

     exit();
   }

   protected function create_unsubscribe_record($email_row) {
      global $wpdb;
      $email_row_check = $wpdb->get_row(
 			$wpdb->prepare(
 				"SELECT * FROM
 					{$wpdb->prefix}easymanage_unsubscribers
   					WHERE email_address = %s", $email_row->email_address
   		));
      if($email_row_check) {
        return;
      }

  		$wpdb->insert("{$wpdb->prefix}easymanage_unsubscribers", array(
  			'email_address' => $email_row->email_address
  		));
   }

   protected function get_email($unique_id) {
   		global $wpdb;
   		$email_row = $wpdb->get_row(
   			$wpdb->prepare(
   				"SELECT * FROM
   					{$wpdb->prefix}easymanage_emails
   					WHERE unique_id = %s", $unique_id
   		));

   		if(!empty($email_row) && !empty($email_row->email_id)) {
   			return $email_row;
   		}
   }

   public function process_mail_content($_addonClass) {
     $shortcodes = $_addonClass->getEmailShortCodes();
     foreach($shortcodes as $_shortcode) {
       if($_shortcode['name'] == self::SHORT_CODE_NAME) {

         $title   = !empty($_shortcode['attrs'][0]['title']) ? $_shortcode['attrs'][0]['title'] : __('Unsubscribe', 'easymanage');
         $content = '<a href="' . $this->get_unsubscribe_link() . '" class="easymanage-unsubscribe">' . $title . '</a>';

         $emailContent = $_addonClass->getEmailContent();
         $newContent   = str_replace($_shortcode['shortcode'], $content, $emailContent);
         $_addonClass->setEmailContent( $newContent );
       }
     }
   }

   protected function get_unsubscribe_link() {
     return get_site_url(null, 'index.php?easymanageunsubscribe') . '=[$subscriberId]' ;
   }

   public function register_addon($_addonClass) {
     $config = $_addonClass->getAddons();
     $config[Easymanage_Addon::EMAIL_SHORTCODES_VAR][] = self::EMAIL_SHORTCODE;
     $config[Easymanage_Addon::ENDPOINTS_VAR][self::FETCH_UNSUBSCRIBERS_KEY] = self::FETCH_UNSUBSCRIBERS_URL;
     $config[Easymanage_Addon::ENDPOINTS_VAR][self::SAVE_UNSUBSCRIBERS_KEY] = self::SAVE_UNSUBSCRIBERS_URL;


     $config[Easymanage_Addon::ICONS_VAR] [self::ICON_NAME]   = $this->getIconCode();

     if(empty($config[Easymanage_Addon::PARENT_VAR][self::PARENT_UI_COMPONENET])) {
       $config[Easymanage_Addon::PARENT_VAR][self::PARENT_UI_COMPONENET] = [];
     }
     $config[Easymanage_Addon::PARENT_VAR][self::PARENT_UI_COMPONENET][] = $this->get_panel_upgrade();
     $config[Easymanage_Addon::TABLES_VAR][self::TABLE_INDEX] = $this->getTables();
     $_addonClass->setAddons($config);
   }

   public function register_easymanage_routes() {
     include_once( dirname(__FILE__) . '/class-easymanage-unsubscribers-controller.php');
     $controller = new Easymanage_Unsubscribers_Controller();
     $controller->register_routes();
   }

   public function get_panel_upgrade() {
     return [
       'class' => 'uicomponent-secondary',
       'icon' => self::ICON_NAME,
       'label' => __('Unsubscribed emails', 'easymanage'),
       'active_table' => self::TABLE_INDEX,
       'childs' => [
         $this->getTitleSidebar(),
         $this->getFetchButtonConfig(),
         $this->getSaveButtonConfig()
       ]
     ];
   }

   public function getTables() {
     return [
         'index' => self::TABLE_INDEX,
         'header' => [
           [
             'name' => 'email',
             'label' => __('Email', 'easymanage'),
             'width' => 200,
             'validation' => [
               [
                 'type' => 'required'
               ],
               [
                 'type' => 'email'
               ]
             ]
           ]
         ],
         'extra' => [
           'not_highlight' => true
         ],
         'title' => __('Unsubscribed emails', 'easymanage')
     ];
   }

   protected function getFetchButtonConfig() {
     return [
       'name' => Easymanage_Addon::UI_TYPE_BUTTON_FETCH,
       'params' => [
         'name' => 'export_unsubscribers',
         'type' => self::TABLE_INDEX,
         'label' => __('Export unsubscribed from store', 'easymanage'),
         'endpoint' => 'endpointExportUnsubscribers'
       ]
     ];
   }

   protected function getSaveButtonConfig() {
     return [
       'name' => Easymanage_Addon::UI_TYPE_BUTTON_SAVE,
       'params' => [
         'name' => 'save_unsubscribers',
         'type' => self::TABLE_INDEX,
         'label' => __('Update unsubscribed users', 'easymanage'),
         'endpoint' => 'endpointSaveUnsubscribers'
       ]
     ];
   }

   protected function getTitleSidebar() {
     return [
       'name'   => Easymanage_Addon::UI_TYPE_TITLE,
       'params' => [
         'label' => __('Manage unsubscribed users', 'easymanage'),
         'icon'  => self::ICON_NAME
       ]
     ];
   }

   protected function getIconCode() {
     return '<svg enable-background="new 0 0 48 48" height="48px" id="Layer_3" version="1.1" viewBox="0 0 48 48" width="48px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g><circle cx="20.897" cy="10.092" fill="#241F20" r="10.092"/><path d="M25,38c0-6.415,4.651-11.732,10.763-12.794c-1.653-2.127-3.714-3.894-6.06-5.164   c-2.349,2.08-5.425,3.352-8.806,3.352c-3.366,0-6.431-1.261-8.774-3.321C6.01,23.409,1.834,30.102,1.834,37.818   c0,1.215,0.109,2.401,0.307,3.557h23.317C25.169,40.297,25,39.17,25,38z" fill="#241F20"/><path d="M38,28c-5.522,0-10,4.478-10,10s4.478,10,10,10s10-4.478,10-10S43.522,28,38,28z M43.679,41.558   l-2.121,2.121L38,40.121l-3.558,3.559l-2.121-2.122L35.879,38l-3.558-3.558l2.121-2.121L38,35.879l3.558-3.558l2.121,2.122   L40.121,38L43.679,41.558z" fill="#241F20"/></g></svg>';
   }

 }


}

return new Easymanage_Unsubscribers_Init();
