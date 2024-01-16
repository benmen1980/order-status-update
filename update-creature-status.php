<?php
/*
Plugin Name: Update creature status Plugin
Plugin URI:  https://simplyct.co.il
Description: Plugin to update creature status using a shortcode.
 * Version:           1.0.0
 * Author:            Roy BenMenachem
 * Author URI:        https://simplyct.co.il
*/

// check for PriorityAPI
include_once(ABSPATH . 'wp-admin/includes/plugin.php');
if (is_plugin_active('PriorityAPI/priority18-api.php')) {

} else {
    add_action('admin_notices', function () {
        printf('<div class="notice notice-error"><p>%s</p></div>', __('In order to use Priority Custom API extension, Priority WooCommerce API must be activated', 'p18a'));
    });

}


// Function to redirect users if not logged in
function redirect_users_if_not_logged_in() {
    if (!is_user_logged_in()) {
        // Get the login URL
        $login_url = wp_login_url($_SERVER['REQUEST_URI']);
        
        // Redirect to the login page
        wp_redirect($login_url);
        exit;
    }
}
//add_action('template_redirect', 'redirect_users_if_not_logged_in');


// Enqueue CSS and JS files
function enqueue_update_status_files() {
    // Enqueue CSS file
    wp_enqueue_style('custom-styles-update-status', plugins_url('css/custom-styles.css', __FILE__));
    wp_enqueue_script('jquery', 'https://code.jquery.com/jquery-3.7.1.min.js');
    wp_enqueue_script('jquery-ui', 'https://code.jquery.com/ui/1.13.2/jquery-ui.min.js');
    wp_enqueue_style( 'jquery-ui', 'https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css' );
    // Enqueue JS file
    wp_enqueue_script('ajax-script-update-status', plugins_url('/js/ajax-scripts.js', __FILE__), array('jquery'));
    // Localize the script to use AJAX
    //wp_localize_script('custom-script', 'ajax_obj', array('ajax_url' => admin_url('admin-ajax.php')));
    wp_localize_script('ajax-script-update-status', 'ajax_obj', array('ajax_url' => admin_url('admin-ajax.php')));

}
add_action('wp_enqueue_scripts', 'enqueue_update_status_files');

// Add custom field to user profile
function custom_user_profile_fields($user) {
    ?>
    <h3><?php _e('Custom User Fields', 'your_textdomain'); ?></h3>
    
    <table class="form-table">
        <tr>
            <th><label for="user_department"><?php _e('User Department', 'carpentry'); ?></label></th>
            <td>
                <input type="text" name="user_department" id="user_department" value="<?php echo esc_attr(get_the_author_meta('user_department', $user->ID)); ?>" class="regular-text" /><br />
            </td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'custom_user_profile_fields');
add_action('edit_user_profile', 'custom_user_profile_fields');

// Save custom field data
function save_custom_user_profile_fields($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    update_user_meta($user_id, 'user_department', $_POST['user_department']);
}
add_action('personal_options_update', 'save_custom_user_profile_fields');
add_action('edit_user_profile_update', 'save_custom_user_profile_fields');


// Add your AJAX action
add_action('wp_ajax_update_order_creative_status', 'update_order_creative_status');
add_action('wp_ajax_nopriv_update_order_creative_status', 'update_order_creative_status');

// AJAX callback function
function update_order_creative_status() {
    if(isset($_REQUEST['order_num_to_update']) && $_REQUEST['order_num_to_update'] != '') {
        $order_num = $_REQUEST["order_num_to_update"];
    }
    $current_user_id = get_current_user_id();
    $user_department = get_user_meta($current_user_id, 'user_department', true);
    $current_user = wp_get_current_user();
    $username = $current_user->user_login;
    PriorityAPI\API::instance()->run();
	// make request
    $url_addition = 'ORDERS(\''.$order_num.'\')' ;
    $data = [
        'ROYY_STATUSDES' => $user_department,
        'ROYY_WEBUSERNAME' => $username
    ];
	$response = PriorityAPI\API::instance()->makeRequest('PATCH', $url_addition, ['body' => json_encode($data)],true);
    $table_row['order_num'] = $order_num;
    $table_row['scan_time'] = date('Y-m-d H:i:s');
    if ($response['code'] == '200') {
        $table_row['message'] = 'עודכן בהצלחה';
        $response = array(
            'update_status' => 'success',
            //'message' => 'עדכון יצור סטטוס הזמנה בוצע בהצלחה',
            'order_data' => $table_row
            // Add more data to the response if needed
        );
    }
    else{
        $msg_error = json_decode($response['body'], true); // Convert JSON to PHP array

        if (isset($msg_error['FORM']['InterfaceErrors']['text'])) {
            $error_text = $msg_error['FORM']['InterfaceErrors']['text'];
        } else {
            $error_text = "ERROR";
        }
        $table_row['message'] = $error_text;
        $response = array(
            'update_status' => 'fail',
            'order_data' => $table_row
            // Add more data to the response if needed
        );
	}
    // Return JSON response
    wp_send_json($response);

    // Always exit to avoid further execution
    wp_die();
}



// Function to generate the custom form HTML
function update_creature_status_form_shortcode() {
    if (!is_user_logged_in()) {
        // Get the login URL
        $login_url = wp_login_url($_SERVER['REQUEST_URI']);
        
        // Redirect to the login page
        wp_redirect($login_url);
        exit;
    }
    ob_start(); // Start output buffering

    // Form HTML - Customize this according to your requirements
    ?>
    <div class="update_creature_status_wrapper">
        <div class="update_creature_status_content">
            <h1><?php esc_html_e( 'תהליך  עדכון סטטוס יצור הזמנה:', 'carpentry' ); ?></h1>
            <form id="update_status_form">
                <!-- STEP 1 SECTION -->
                <section class="step_1">
                    <div class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                        <label for="order_num_to_update"><?php esc_html_e( 'מספר הזמנה', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
                        <div class="input_wrapper">
                            <input class="woocommerce-Input woocommerce-Input--text input-text" type="text" name="order_num_to_update" id="order_num_to_update" />
                            <div class="loader_wrap">
                                <div class="loader_spinner">
                                    <img src="<?php echo plugins_url('/images/loader.svg', __FILE__); ?>" alt="">
                                </div>
                            </div>
                        </div>
                        
                    </div>
         
                    <?php if(false): ?>
                        <div class="validation_btn">
                            <button type="button" name="check_order_update_status" class="check_order_update_status button-secondary">
                                <?php esc_html_e( 'סירוק סטטוס הזמנה', 'carpentry' ); ?>
                                <div class="loader_wrap">
                                    <div class="loader_spinner">
                                        <img src="<?php echo plugins_url('/images/loader.svg', __FILE__); ?>" alt="">
                                    </div>
                                </div>
                            </button>
                        </div>
                    <?php endif; ?>
                </section>
                <section class="step_2">
                    <h2><?php esc_html_e( 'סריקת הזמנות:', 'carpentry' ); ?></h2>
                    <table>
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'מספר הזמנה', 'carpentry' ); ?></th>
                                <th><?php esc_html_e( 'שעת סריקה', 'carpentry' ); ?></th>
                                <th><?php esc_html_e( 'הודעה', 'carpentry' ); ?></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    <h3 class="send_msg_wrapper">

                    </h3>
                </section>
            </form>
        </div>
    </div>
    <?php

    return ob_get_clean(); // Return the buffered content



}
add_shortcode('update_creature_status', 'update_creature_status_form_shortcode'); // Register shortcode with the name 'custom_form'


