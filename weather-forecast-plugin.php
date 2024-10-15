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
// Function to get weather data with caching
function get_weather_data_with_cache($api_url) {
    $cache_key = 'weather_data_cache_' . md5($api_url);
    $weather_data = get_transient($cache_key);

    // Check if cached data is valid JSON
    if ($weather_data !== false && json_decode($weather_data) !== null) {
        error_log('Fetching weather data from cache');
        return json_encode(['source' => 'cache', 'data' => json_decode($weather_data)]);
    }

    // Fetch new data if the cache is empty or invalid
    error_log('Fetching weather data from live API');
    $response = wp_remote_get($api_url);
    if (is_wp_error($response)) {
        error_log('Error fetching weather data: ' . $response->get_error_message());
        return false;
    }

    $weather_data = wp_remote_retrieve_body($response);
    // Only set the cache if the fetched data is valid JSON
    if (json_decode($weather_data) !== null) {
        set_transient($cache_key, $weather_data, 30 * MINUTE_IN_SECONDS); // Cache for 30 minutes
    }

    return json_encode(['source' => 'live', 'data' => json_decode($weather_data)]);
}

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
