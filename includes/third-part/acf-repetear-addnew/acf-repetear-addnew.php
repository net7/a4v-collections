<?php
/**
 * @package ACF Extension
 */
/*
 * Plugin Name: Acf Repeater add new
 * Description: Extension for ACF Relation Field to allow to add a new related entity
 * Author: Aiola Chiara
 */

class AcfRepeaterAddNew
{
    public function __construct()
    {
        add_action('admin_enqueue_scripts', array($this, 'init'));
        add_action('acf/render_field/type=relationship', array($this, 'add_acf_modal'));
        add_action('acf/render_field/type=post_object', array($this, 'add_acf_modal'));
        add_action('wp_ajax_prepare_related_object', array($this, 'prepare_related_object'));
        add_action('wp_ajax_save_related_object', array($this, 'save_related_object'));
    }

    public function init()
    {
        $screen = get_current_screen();
       if(is_admin() && ( $screen->base == 'post')){
           add_action('admin_footer', array($this, 'addModalTemplate'));
           wp_register_script('acf-repetear-addnew-js', plugins_url('assets/js/acf-repetear-addnew.js', __FILE__));
           wp_localize_script('acf-repetear-addnew-js', 'options', array(
               'ajaxurl' => admin_url('admin-ajax.php'),
               'create_new' => __("Create new")
            ));

            wp_enqueue_script('acf-repetear-addnew-js');
            wp_enqueue_style('bootstrap', plugins_url('assets/css/bootstrap.min.css', __FILE__));
            wp_enqueue_script('bootstrap-modal', plugins_url('assets/js/bootstrap.min.js', __FILE__));

        }
    }

    public function add_acf_modal($field)
    {
        //if (isset($field['enable_relation']) && $field['enable_relation']) {
            $text = sprintf(__('Add %s'), $field['label']);
            $subtype = isset($field['subtype']) ? $field['subtype'] : '';
            $key = $field['key'];
            $post_type = is_array( $field['post_type'])  ? reset($field['post_type']) : $field['post_type'];

            $button = <<<BTN
            <div style="margin-bottom:8px;"></div>
            <ul class="acf-actions acf-hl">
                <li>
                    <a class="acf-button button button-primary"
                      href="#" data-toggle="modal"
                        data-target="#modal-addPostObject"
                        data-parent=""
                        data-child="$post_type"
                        data-key="$key"
                        data-label="{$field['label']}" data-subtype="$subtype">$text</a>
                </li>
            </ul>
BTN;
            echo $button;
        //}
    }

    public function addModalTemplate()
    {
        include('templates/acf_modal.php');
    }

    public function prepare_related_object()
    {
        $post_type_parent = $_REQUEST['post_type_parent'];
        $post_type_child = $_REQUEST['post_type_child'];
        $label = $_REQUEST['label'];
        $subtype = $_REQUEST['subtype'];
        $postid = !empty($_REQUEST['postid'] ) ? $_REQUEST['postid']  : 'new_post';
        $edit = !empty($_REQUEST['edit'] ) ? $_REQUEST['edit']  : '';
        $inputElement = str_replace('muruca_', '', $post_type_child);
        $select = 'acf-field_' . $post_type_parent . '_' . $inputElement . $subtype;
        $field_key = $_REQUEST['field_key'];


        acf_form_head();
        acf_form(array(
            'id' => $post_type_child . '_form',
            'post_id' => $postid,
            'new_post' => array(
                'post_type' => $post_type_child,
                'post_status' => 'publish',
            ),
            'post_title' => apply_filters('acf-addnew-show-title', true, $post_type_child, $post_type_parent),
            'post_content' => apply_filters('acf-addnew-show-content', true, $post_type_child, $post_type_parent),
            'html_after_fields' => '
            <input type="hidden" name="parent" value="' . $post_type_parent . '">
            <input type="hidden" name="key" value="' . $field_key . '">
            <input type="hidden" name="edit" value="' . $edit . '">
            <input type="hidden" name="child" value="' . $post_type_child . '">',
            'submit_value' => $edit == "" ? sprintf(__('Create new %s'), $label) :  sprintf(__('Edit %s'), $label),
        ));
        die;
    }

    public function save_related_object()
    {
        $child = $_REQUEST['child'];
        $result['success'] = false;
        $name = 'acf[' . $_REQUEST['key'] . '][]';

        if (isset($child) && post_type_exists($child)) {
            if (!(is_user_logged_in() || current_user_can('publish_posts'))) {
                return;
            }

            $title = apply_filters('acf-repetear-addnew-title', $_REQUEST['acf']['_post_title'], $child);
            $content = apply_filters('acf-repetear-addnew-title', $_REQUEST['acf']['_post_content'], $child);
            $id = !empty( $_REQUEST['_acf_post_id'] ) ? $_REQUEST['_acf_post_id'] : 0;

            $post = array(
                'ID' => $id,
                'post_type' => $child,
                'post_status' => 'publish',
                'post_title' => $title,
                'post_content' => $content,
            );

            if (null !== $post) {
                try {
                    $post_id = wp_insert_post($post);
                    foreach ($_REQUEST['acf'] as $field_name => $field_value) {
                        update_field($field_name, $field_value, $post_id);
                    }

                    $result['success'] = true;
                    $result['post_id'] = $post_id;
                    $result['field_key'] = $_REQUEST['key'];
                    $result['edit'] = $_REQUEST['edit'];
                    $result['title'] = $title;
                    $result['name'] = $name;
                } catch (Exception $e) {
                    $result['success'] = false;
                    $result['error'] = $e->getMessage();
                }

                echo json_encode($result);
                exit;
            }
        }
    }
}

$acf_repeater_add_new = new AcfRepeaterAddNew();
