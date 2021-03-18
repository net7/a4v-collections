<?php


/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://netseven.it
 * @since      1.0.0
 *
 * @package    Muruca_Core_V2_Collection
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Muruca_Core_V2_Collection
 * @author     Netseven <info@netseven.it>
 */
class Muruca_Core_V2_Collection
{

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

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */

    const ACF_PREFIX =  'field_mrc_';

    private $time_post_type;

    public function __construct() {
        $this->collection_post_type = MURUCA_CORE_PREFIX . "_collection";
    }

    public function run(){

        add_action("init", array($this, "register_collection"));
       // add_action('rest_api_init', array($this, 'register_rest_route'));
       add_filter('use_block_editor_for_post_type', array($this, 'disable_gutenberg'),  10, 2);
      // add_action( 'admin_menu', array($this, 'collection_menu'));
       // add_filter('tiny_mce_before_init', array($this, "tiny_editor_settings"), 10, 2);
       // add_filter('wp_editor_settings', array($this, "editor_settings"), 10, 2);
    }

    public function collection_menu() {
        add_menu_page(
            "Muruca Collection",
            'Muruca Collection',
            'edit_posts',
            MURUCA_CORE_COLLECTION_PLUGIN_NAME,
            '',
            'dashicons-book-alt',
            5
        );
    }

    public function register_collection() {        

        $args = array(
            'public' => true,
            'labels' =>  array(
                'name'              => _x('Collections', 'post type general name', MURUCA_CORE_TEXTDOMAIN),
                'singular_name'     => _x('Collection', 'post type singular name', MURUCA_CORE_TEXTDOMAIN),
                'menu_name'         => _x('Muruca collection', 'Admin Menu text', MURUCA_CORE_TEXTDOMAIN),
                'add_new_item'      => __('Add New Collection', MURUCA_CORE_TEXTDOMAIN),
                'new_item'          => __('New Collection', MURUCA_CORE_TEXTDOMAIN),
            ),
            'menu_icon' => 'dashicons-book-alt',
            'show_in_rest' => true,
            "menu_position" => 4,
            'show_in_menu' => MURUCA_CORE_COLLECTION_PLUGIN_NAME,
            'supports' => ["title"]
        );

        $post_types[$this->collection_post_type] = $args;

        $post_types = apply_filters( "mrc/collection_register_post_types", $post_types );
       
        foreach ($post_types as $post_name => $args) {
            register_post_type($post_name, $args);
            $this->add_custom_fields($post_name);
        }

        do_action("mrc/collection_post_types_after_register", $post_types);
    }

    public function disable_gutenberg($current_status, $post_type) {
        if ($post_type == $this->collection_post_type) return false;
        return $current_status;
    }

    public function editor_settings($args, $id)
    {
        global $current_screen;
        if ($this->time_post_type== $current_screen->post_type) {
            $args['media_buttons'] = false;
            $args['quicktags'] = false;
        }
        return $args;
    }

    public function tiny_editor_settings($args, $id)
    {
        global $current_screen;
        if ($this->time_post_type== $current_screen->post_type) {
            $args['toolbar1'] = "bold,italic,link";
            $args['toolbar2'] = "";
        }
        return $args;
    }

    public function register_rest_route() {
        register_rest_route( MURUCA_CORE_PLUGIN_NAME . "/" . MURUCA_CORE_V2_REST_VERSION, '/views/time-events', array(
            array(
                'methods' => 'GET',
                'callback' => array( $this, 'rest_response' ),
                'permission_callback' => function () {
                    return true;
                }
            )
        ));
    }

    public function rest_response( $data ) {

        $time_events = get_posts(array(
                "post_type" => $this->time_post_type,
                "post_number" => -1
            )
        );

        $slide_obj = [];

        foreach ($time_events as $time){
            $fields = get_fields($time->ID);

            $s = array();
            $s["id"] = $time->ID;
            $s["title"] = $time->post_title;
            $s["content"] = $time->post_content;
            $fieldClass = new MurucaField( '', '', $this->time_post_type );

            foreach ($fields as $name => $field) {

                    $cpm_name = $fieldClass->get_field_name($name, MURUCA_CORE_PREFIX . "_");
                    if( $field ){
                        $s[$cpm_name] = $field;
                    }


            };

            $slide_obj[] = $s;
        }
        return $slide_obj;
    }


    public function add_custom_fields( $post_name ){
        if (function_exists('acf_add_local_field_group') && $post_name == $this->collection_post_type && !empty($this->get_custom_fields())) :

            acf_add_local_field_group(array(
                'key' => 'group_mrc_collection_options',
                'title' => 'Muruca collection',
                //mrc/timeline_acf_custom_fields filter for timeline custom fields
                'fields' => $this->get_custom_fields(),
                'location' => array(
                    array(
                        array(
                            'param' => 'post_type',
                            'operator' => '==',
                            'value' => $this->collection_post_type,
                        ),
                    ),
                ),
                'active' => true
            ));
        endif;
    }

    private function get_custom_fields() {
        $fields = array();
        return array_values (apply_filters("mrc/collection_acf_custom_fields", $fields));
    }

}
