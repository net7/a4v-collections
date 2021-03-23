<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://netseven.it
 * @since      1.0.0
 *
 * @package    Muruca_Core_V2
 * @subpackage Muruca_Core_V2/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Muruca_Core_V2
 * @subpackage Muruca_Core_V2/admin
 * @author     Netseven <info@netseven.it>
 */
class Muruca_Core_V2_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
    private $version;

    private $submenu_pages;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name = "" ) {
        $this->plugin_name = $plugin_name;
		$this->load_dependencies();
        $this->settings_pages = array();
        $this->initSettigsPage();
	}
    
    public function run( ){
        add_action( 'admin_menu', array($this, 'muruca_core_v2_admin_page' ));
    }
    
	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/muruca-core-v2-admin.js', array( 'jquery' ), $this->version, false );
    }

    private function load_dependencies() {
        if(!class_exists('Net7_AdminOptionsPage')){
            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-admin-option-page.php';
           // require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-admin-option-page-sub.php';
        }
    }

    public function initSettigsPage() {
        $this->settings_pages = array(
            [
                "options" => $this->generalOptionsPage(),
                "tabs" => array(
                    'main' => _x( "Muruca Settings", "Tab label in settings page", MURUCA_CORE_TEXTDOMAIN )
                ),
                "title" => "Muruca settings",
                "menutitle" => "Muruca settings",
                "menuslug" => MURUCA_CORE_PREFIX . "_general_settings"
            ]
        );
    }

    public function muruca_core_v2_admin_page() {

        $settings_pages = $this->get_settings_pages();
        if( !empty($settings_pages) ){
            foreach( $settings_pages as $settings ){
                $admin_page_class = new Net7_AdminOptionsPage( $this->plugin_name, $this->version, MURUCA_CORE_PREFIX);
                $admin_page_class->init(
                    $settings['options'],
                    $settings['tabs'],
                    $settings['title'],
                    $settings['menutitle'],
                    $settings['menuslug']
                );
                $admin_page_class->add_submenu_page( $this->plugin_name );
                add_action( 'admin_init', array($admin_page_class, 'register_page_settings') );
            }
        }

        /*$tabs = array(
            'main' => _x( "Settings", "Tab label in settings page", MURUCA_CORE_TEXTDOMAIN )
        );

        $this->admin_page_class->init(
            $this->generalOptionsPage(),
            $tabs,
            "Settings",
            "Settings",
            MURUCA_CORE_PREFIX . '_settings'
        );

        $this->admin_page_class->add_submenu_page( $this->plugin_name );*/
    }

    private function generalOptionsPage() {
        return array(
			/**
			 * MAIN TAB
			 * Settings
			 */
			MURUCA_CORE_PREFIX . "_graphql_url" => array(
                'label' => _x( "Url of graphql installation", "Option label in settings page" , MURUCA_CORE_TEXTDOMAIN ),
				'attrs' => array( 'size' => 50 ),
				'tab'   => 'main'
            ),
			MURUCA_CORE_PREFIX . "_graphql_token" => array(
                'label' => _x( "authorization token", "Option label in settings page" , MURUCA_CORE_TEXTDOMAIN ),
				'attrs' => array( 'size' => 50 ),
				'tab'   => 'main'
            )
		); //general_options
    }

    private function get_submenu_pages(){
        return apply_filters( 'mrc/add_submenu_pages', $this->submenu_pages );
    }

    private function get_settings_pages() {

        /**
         * Filters settings page. It alows to add settigs page to main plugin menu
         * @param array $settings an array containing all settings pages' options.
         * @param type  $var Description.
        */
        return apply_filters( 'mrc/add_setting_pages', $this->settings_pages  );
    }

}
