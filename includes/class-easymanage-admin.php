<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Easymanage_Admin {

	const PLUGIN_PAGE = 'easymanage';

	const YOUTUBE_VIDEO  = '<iframe width="560" height="315" src="https://www.youtube.com/embed/9Qqt4p5r_wc" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';

	const APP_GOOGLE_URL = 'https://workspace.google.com/marketplace/app/easymanage/755136019733';

  public function __construct() {
    return $this->_init_admin();
  }

  public function _init_admin()
  {
		add_filter(
        'plugin_action_links_' . EASYMANAGE_PLUGIN,
        array( $this, 'addPluginAction' ),
        10,
        4
    );
		add_action( 'admin_menu', array($this, 'register_about_page') );
		add_action( 'admin_enqueue_scripts', array($this, 'load_custom_wp_admin_style') );
	}

	public function load_custom_wp_admin_style( $hook )
	{
		if($hook != 'admin_page_' . self::PLUGIN_PAGE) {
			return;
		}

		wp_enqueue_style( 'custom_wp_admin_css', plugins_url('/../static/css/admin-style.css', __FILE__) );
	}

	public function register_about_page()
	{
  	add_submenu_page(
        'my_parent_slug'
        , 'Easymanage'
        , ''
        , 'manage_options'
        , self::PLUGIN_PAGE
        , array($this, 'render_about_page')
    );
	}

	public function render_about_page()
	{
		$this->_title();
		$this->_container();
	}

  public function addPluginAction( $actions )
  {
			if ( defined( 'EASYMANAGE_PLUGIN' ) ) {
      	$actions[] = '<a href="' . $this->getLink() . '">' . __( 'About Extension', 'easymanage' ) . '</a>';
			}
			return $actions;
  }

  public function getLink() {
		return admin_url( 'admin.php?page=' . self::PLUGIN_PAGE );
	}

	protected function _container()
	{
		$out = '<div class="easymanage-container">';

		$out .= $this->_video();
		$out .= $this->_install();

		$out .= '</div>';
		$out .= '<div class="easymanage-container">';
		$out .= $this->_plugins();
		$out .= '</div>';

		echo $out;
	}

	protected function _video() {
		$out  = '<div class="easymanage-video-container">';
		$out .= '<h3>' . __('Video', 'easymanage') . '</h3>';
		$out .= self::YOUTUBE_VIDEO;

		$out .= '</div>';

		return $out;
	}

	protected function _install() {
		$out  = '<div class="easymanage-install-container">';

		$out .= $this->_getInstallButton();

		$out .= '</div>';

		return $out;
	}

	protected function _getInstallButton() {
		$button = '<a href="' . self::APP_GOOGLE_URL . '" target="_blank" class="button">' . __('Install in spreadsheet', 'easymanage') . '</a>';
		return $button;
	}

	protected function _plugins()
	{
		$out  = '<div class="easymanage-plugins-container">';
		$out .= '<h3>' . __('Plugins', 'easymanage') . '</h3>';

		$out .= '<div class="easymanage-plugin">';

		$out .= '<a href="https://easymanage.biz/easymanage-order-sync/" target="_blank">
									<img src="https://easymanage.biz/wp-content/uploads/2021/07/order_sync_main-768x522.jpg" />
									<br>
									<h4>' . __('Woocommerce Google spreadsheet orders plugin') . '</h4>
						</a>';

		$out .= '</div>';

		$out .= '</div>';

		return $out;
	}

	protected function _title()
	{
		echo '<h1>Easymanage(' . EASYMANAGE_VERSION . ')</h1>';
	}
}

return new Easymanage_Admin();
