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
class A4v_Portale_Matrice_Collection
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
        $this->collection_post_type = [MURUCA_CORE_PREFIX . "_collection-item", MURUCA_CORE_PREFIX . "_a4v-item"];        
		$this->load_dependencies();

        
    }

    public function run(){

        add_action("init", array($this, "register_collection"));
        add_action('rest_api_init', array($this, 'register_rest_route'));
        add_filter('use_block_editor_for_post_type', array($this, 'disable_gutenberg'),  10, 2);
        add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
        add_action( 'admin_menu', array($this, 'collection_menu'));
    }

    public function collection_menu() {
        add_menu_page(
            "A4v Collection",
            'A4v Collection',
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
                'name'              => _x('Collection items', 'post type general name', MURUCA_CORE_TEXTDOMAIN),
                'singular_name'     => _x('Collection item', 'post type singular name', MURUCA_CORE_TEXTDOMAIN),
                'add_new_item'      => __('Add New Collection Item', MURUCA_CORE_TEXTDOMAIN),
                'new_item'          => __('New Collection Item', MURUCA_CORE_TEXTDOMAIN),
            ),
            'show_in_rest' => true,
            "menu_position" => 4,
            'show_in_menu' => MURUCA_CORE_COLLECTION_PLUGIN_NAME,
            'supports' => ["title", "editor", "thumbnail"]
        );

        $post_types[$this->collection_post_type[0]] = $args;

        $args = array(
            'public' => true,
            'labels' =>  array(
                'name'              => _x('A4v items', 'post type general name', MURUCA_CORE_TEXTDOMAIN),
                'singular_name'     => _x('A4v item', 'post type singular name', MURUCA_CORE_TEXTDOMAIN),
                'add_new_item'      => __('Add New A4w Item', MURUCA_CORE_TEXTDOMAIN),
                'new_item'          => __('New A4w Item', MURUCA_CORE_TEXTDOMAIN),
            ),
            'show_in_rest' => true,
            "menu_position" => 4,
            'show_in_menu' => MURUCA_CORE_COLLECTION_PLUGIN_NAME,
            'supports' => ["title", "editor", "thumbnail", "custom-fields"]
        );

        $post_types[$this->collection_post_type[1]] = $args;

        $post_types = apply_filters( "mrc/collection_register_post_types", $post_types );
       
        foreach ($post_types as $post_name => $args) {
            register_post_type($post_name, $args);
        }
        $this->add_custom_fields($post_name);

        do_action("mrc/collection_post_types_after_register", $post_types);
    }

    private function load_dependencies() {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/third-part/acf-repetear-addnew/acf-repetear-addnew.php';
    }

    public function disable_gutenberg($current_status, $post_type) {
        if ( in_array($post_type, $this->collection_post_type)) return false;
        return $current_status;
    }

    public function add_custom_fields( $post_name ){
        if (function_exists('acf_add_local_field_group')) :

            acf_add_local_field_group(array(
                'key' => 'group_' . MURUCA_CORE_PREFIX . "_collection_resources",
                'title' => 'Collection resources',
                'fields' => array(
                    array(
                        'key' => self::ACF_PREFIX . 'collection_type',
                        'label' => 'collection type',
                        'name' => MURUCA_CORE_PREFIX . '_collection_type',
                        'type' => 'radio',
                        'choices' => array(
                            'single' => 'Selezione manuale',
                            'auto' => 'Selezione automatica',
                        ),
                        'allow_null' => 0,
                        'return_format' => 'value'
                    ),
                    array(
                        'key' => self::ACF_PREFIX . 'collection_items',
                        'label' => 'collection items',
                        'name' => MURUCA_CORE_PREFIX . '_collection_items',
                        'type' => 'flexible_content',                        
                        'conditional_logic' => array(
                            array(
                                array(
                                    'field' => self::ACF_PREFIX . 'collection_type',
                                    'operator' => '==',
                                    'value' => 'single',
                                ),
                            ),
                        ),
                        'layouts' => array(
                            'layout_a4v_resource' => array(
                                'key' => 'layout_a4v_resources',
                                'name' => MURUCA_CORE_PREFIX . '_a4v_resources',
                                'label' => 'Cerca in Arianna',
                                'display' => 'block',
                                'sub_fields' => array(
                                    array(
                                        'key' => self::ACF_PREFIX . 'a4v_resource',
                                        'label' => 'Arianna4V Item',
                                        'name' => MURUCA_CORE_PREFIX . '_a4v_resource',
                                        'type' => 'a4v_field',
                                        'arianna_graphql_url' => get_option(MURUCA_CORE_PREFIX . "_graphql_url"),
                                        'arianna_graphql_token' => '',
                                        'arianna_post_type' => 'a4v_a4v-item',
                                        'arianna_select_resource' => 1,
                                        'max' => 1,
                                    ),
                                )
                            ),
                            'layout_wp_resource' => array(
                                'key' => 'layout_wp_resources',
                                'name' => MURUCA_CORE_PREFIX . '_wp_resources',
                                'label' => 'Elemento di Wordpress',
                                'display' => 'block',
                                'sub_fields' => array(
                                    array(
                                        'key' => self::ACF_PREFIX . 'wp_resource',
                                        'label' => 'Collection items',
                                        'name' => MURUCA_CORE_PREFIX . '_wp_resource',
                                        'type' => 'post_object',
                                        'post_type' => array(
                                            0 => 'a4v_collection-item',
                                        ),
                                        'return_format' => 'object',
                                        'ui' => 1,
                                    ),
                                ),
                            ),
                        ),
                        'button_label' => 'Aggiungi risorsa'
                    ),
                    array(
                        'key' => self::ACF_PREFIX . 'collection_search_url',
                        'label' => 'search url',
                        'name' => MURUCA_CORE_PREFIX . '_collection_search_url',
                        'type' => 'url',    
                        'conditional_logic' => array(
                            array(
                                array(
                                    'field' => self::ACF_PREFIX . 'collection_type',
                                    'operator' => '==',
                                    'value' => 'auto',
                                ),
                            ),
                        ), 
                    ),
                    array(
                        'key' => self::ACF_PREFIX . 'collection_max_items',
                        'label' => 'max items to show',
                        'name' => MURUCA_CORE_PREFIX . '_collection_max_items',
                        'type' => 'number',
                        'min' => 1,  
                        'conditional_logic' => array(
                            array(
                                array(
                                    'field' => self::ACF_PREFIX . 'collection_type',
                                    'operator' => '==',
                                    'value' => 'auto',
                                ),
                            ),
                        ), 
                        "wrapper" => [
                            "width" => "10%"
                        ]
                    )
                ),
                'label_placement' => 'left',
                'location' => array(
                    array(
                        array(
                            'param' => 'post_type',
                            'operator' => '==',
                            'value' => 'a4v_collection',
                        ),
                    ),
                )
            ));

            acf_add_local_field_group(array(
                'key' => 'group_a4v_collection_item_options',
                'title' => 'Collection item',
                //mrc/timeline_acf_custom_fields filter for timeline custom fields
                'fields' => array(
                    array(
                        'key' => self::ACF_PREFIX . 'collection_item_url',
                        'label' => 'url',
                        'name' => MURUCA_CORE_PREFIX . '_collection_item_url',
                        'type' => 'text',                       
                    ),
                    array(
                        'key' => self::ACF_PREFIX . 'collection_item_thumbnail_id',
                        'label' => 'image',
                        'name' => '_thumbnail_id',
                        'type' => 'image',                        
                        'return_format' => 'url'
                    ),
                    array(
                        'key' => self::ACF_PREFIX . 'collection_item_background_color',
                        'label' => 'background color',
                        'name' => MURUCA_CORE_PREFIX . '_collection_item_background_color',
                        'type' => 'color_picker',                       
                    )                    
                ),
                'location' => array(
                    array(
                        array(
                            'param' => 'post_type',
                            'operator' => '==',
                            'value' => MURUCA_CORE_PREFIX . "_collection-item",
                        )                       
                    ),
                    array(
                        array(
                            'param' => 'post_type',
                            'operator' => '==',
                            'value' => MURUCA_CORE_PREFIX . "_a4v-item"
                        ),                     
                    ),
                ),
                'hide_on_screen' => array(
                    0 => 'featured_image',
                ),
                'active' => true
            ));
        endif;
    }

    private function get_custom_fields() {
        $fields = array();
        return array_values (apply_filters("mrc/collection_acf_custom_fields", $fields));
    }

    public function add_meta_box( $post_type ) {
        $post_types = array( MURUCA_CORE_PREFIX . "_a4v-item" );
 
        if ( in_array( $post_type, $post_types ) ) {
            add_meta_box(
                'a4v_fields',
                __( 'Arianna4V Custom fields', 'textdomain' ),
                array( $this, 'render_meta_box_content' ),
                $post_type,
                'advanced',
                'high'
            );
        }
    }

      /**
     * Render Meta Box content.
     *
     * @param WP_Post $post The post object.
     */
    public function render_meta_box_content( $post ) {
 
        // Add an nonce field so we can check for it later.
        wp_nonce_field( 'myplugin_inner_custom_box', 'myplugin_inner_custom_box_nonce' );
 
        // Use get_post_meta to retrieve an existing value from the database.      
        $metafields = a4v_get_arianna_item_metafields($post->ID);
        foreach( $metafields as $key => $val):
        ?>
            <p>
                <label for="<?php echo $key; ?>">
                    <?php echo str_replace( MURUCA_CORE_PREFIX . "_", "", $key); ?>
                </label>
                <input type="text" name="<? $key ?>" readonly value="<?php echo esc_attr( $val ); ?>" size="100"/>
            </p>

        <?php endforeach; ?>
        <?php
    }

    public function register_rest_route() {
        register_rest_route( MURUCA_CORE_PLUGIN_NAME . "/" . MURUCA_CORE_V2_REST_VERSION, '/collections/(?P<id>\d+)', array(
            array(
                'methods' => 'GET',
                'callback' => array( $this, 'rest_response' ),
                'permission_callback' => function () {
                    return true;
                }
            )
        ));
        register_rest_route( MURUCA_CORE_PLUGIN_NAME . "/" . MURUCA_CORE_V2_REST_VERSION, '/collections', array(
            array(
                'methods' => 'GET',
                'callback' => array( $this, 'collections_response' ),
                'permission_callback' => function () {
                    return true;
                }
            )
        ));
    }

    public function rest_response( $data ) {

        $offset = isset($data['offset']) ? (int) $data['offset'] : 0;
        $limit = isset($data['limit']) ? (int) $data['limit'] + $offset : -1;
        $collection = get_post($data['id']);   

        if (!$collection) return ["error" => "No collection found"];

        $results =  [ 
            "title" => $collection->post_title,
            "text" => $collection->post_content,
         ];
        

        $collection_type = get_field( MURUCA_CORE_PREFIX . "_collection_type", $collection->ID);
        if( $collection_type == "single" ):
            if( have_rows(MURUCA_CORE_PREFIX . '_collection_items', $collection->ID) ):
                // Loop through rows.
                $response = [];
                $count = 0;
                $total =  count(get_field(MURUCA_CORE_PREFIX . '_collection_items', $collection->ID));
                while ( have_rows(MURUCA_CORE_PREFIX . '_collection_items', $collection->ID) 
                && ( ($limit < 0 ) || ($limit > 0 && $count < $limit) ) ) : 
                    the_row();                
                    $count++;
                    if( $offset > 0 && $count <= $offset  ) continue;
                    $post_id = "";
                    if( get_row_layout() == 'a4v_a4v_resources' ):
                        $post_id = get_sub_field(MURUCA_CORE_PREFIX . '_a4v_resource');                    
                    elseif( get_row_layout() == 'a4v_wp_resources' ): 
                        $post_id = get_sub_field(MURUCA_CORE_PREFIX . '_wp_resource');
                    endif;

                    $post_id = is_array($post_id) ? $post_id[0] : $post_id;
                    $item_post = get_post($post_id);
                    if($item_post){
                        $meta = a4v_get_arianna_item_metafields($post_id);
                        $thumb = get_the_post_thumbnail_url($item_post);
                        if( $thumb == "" ){
                            $thumb = $meta[COLLECTION_ITEM_FIELD_IMAGE] ? $meta[COLLECTION_ITEM_FIELD_IMAGE] : null;
                        }

                        $response[] = [
                            "title" => $item_post->post_title,
                            "content" => $item_post->post_content,
                            "background" => get_field(MURUCA_CORE_PREFIX . '_collection_item_background_color', $post_id),
                            "image" => $thumb != "" ? $thumb : $meta[COLLECTION_ITEM_FIELD_IMAGE],
                            "url" => get_field(MURUCA_CORE_PREFIX . '_collection_item_url', $post_id),
                            "a4vId" => $meta[COLLECTION_ITEM_FIELD_ID] ? $meta[COLLECTION_ITEM_FIELD_ID] : null,
                            "type" => $meta[COLLECTION_ITEM_FIELD_TYPE] ? $meta[COLLECTION_ITEM_FIELD_TYPE] : null,
                            "classification" => $meta[COLLECTION_ITEM_FIELD_CLASSIFICATION] ? $meta[COLLECTION_ITEM_FIELD_CLASSIFICATION] : null,
                        ];
                    }
                endwhile;                  
                $results["total"] = $total;
                $results["items"] = $response;
            endif;

        elseif ($collection_type == "auto"):
            $results["search_url"] = get_field(MURUCA_CORE_PREFIX . "_collection_search_url", $collection->ID);
            $results["max"] = get_field(MURUCA_CORE_PREFIX . "_collection_max_items", $collection->ID);
        endif;

        return $results;
    }

    public function collections_response(){
        $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : -1;
        $limit = isset($_GET['limit']) ? (int) $_GET['limit']  : -1;
        $collections = get_posts([
                "post_type" => MURUCA_CORE_PREFIX . "_collection",
                "posts_per_page" => $limit,
                "offset" => $offset
            ]);
        $response = [];        
        foreach ($collections as $post ){
            $response[] = ["title" => $post->post_title];
        }
        
        return $response;
    }
}
