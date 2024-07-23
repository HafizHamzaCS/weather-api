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
    wp_enqueue_script('wfp-admin-scripts', plugins_url('assets/script.js', __FILE__), array('jquery'), null, true);
}
add_action('admin_enqueue_scripts', 'wfp_admin_enqueue_scripts');


// Enqueue the necessary scripts and styles
// function enqueue_weather_forecast_scripts() {
//     wp_enqueue_script('weather-forecast-script', plugins_url('assets/script.js', __FILE__), array('jquery'), null, true);
//     wp_localize_script('weather-forecast-script', 'wfp_vars', array(
//         'api_url' => 'https://api.met.no/weatherapi/locationforecast/2.0/compact',
//         'latitude' => '60.10', // Replace with dynamic latitude if needed
//         'longitude' => '9.58', // Replace with dynamic longitude if needed
//         'plugin_url' => plugins_url('assets/', __FILE__)
//     ));
// }
// add_action('wp_enqueue_scripts', 'enqueue_weather_forecast_scripts');



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
        'plugin_url' => plugins_url('/', __FILE__)
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


function yr_meteogram_shortcode($atts) {
    // Extract the attributes passed to the shortcode

     // Get options from the settings page
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

    // Build the URL based on the provided attributes
    $base_url = "https://www.yr.no/{$atts['language']}/content/{$atts['location_id']}/meteogram.svg";
    if ($atts['mode'] === 'dark') {
        $base_url .= '?mode=dark';
    }

    // Return the iframe code
    return '<iframe src="' . esc_url($base_url) . '" width="100%" height="400" frameborder="0"></iframe>';
}
add_shortcode('hj_meteogram', 'yr_meteogram_shortcode');
// Create a shortcode to display the forecast
function wfc_custom_forecast_shortcode() {
    ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <title></title>
    <style type="text/css">
        *{
            margin: 0;
            padding: 0;
        }
        body{
            font-family: Arial, sans-serif;
        }
        .bh-Weersver-heading{
            color: #E43030;
            font-size: 2em;
            font-family: 'Roboto', sans-serif;
            font-weight: 700;
        }
        .bh-Webcams-link{
            background-color: #e2f1ff;
            font-size: 16px;
            padding: 1px 8px;
            display: inline-flex;
            color: #278ac6;
            text-decoration: none;
        }
        .bah-Vandaag{
            background: #7AD7F0;
            padding: 7px 0;
        }

        @media only screen and (min-width: 769px) {
          .bh-col-bdr {
            border-right: 1px solid black;
          }
        }
        @media only screen and (max-width: 768px) {
          .bh-col-bdr2 {
            border-top: 1px solid black;
          }
        }
    </style>
</head>
<body>
    <div class="container-fluid">


        <!-- 8-daagse section -->
        <div class="container-fluid">
            <div class="row mt-5">
                <div class="col-12" style="color: #3251a0;">
                    <!-- <h2 class="fw-bold text-center">8-daagse weersverwachting</h2> -->
                </div>              
            </div>
            <!-- weather section 1-->
            <div class="row mt-3">
                <div class="col-12 text-center bah-Vandaag">
                    <span class="fw-bold fs-5 text-white">Vandaag Donderdag 20 Juni</span>
                </div>
                <!-- col-1 -->
                <div class="col-6 col-md-3 col-sm-6 text-center mt-3 border-end border-dark">
                    <p>Ochtend</p>
                    <img src="https://www.ski-livigno.nl/wp-content/uploads/2024/06/cloud.jpg" width="65px">
                    <p>
                        <span class="text-danger">28 °C</span><br>
                        <span class="text-info">23 °C</span>
                    </p>
                    <p>
                        <span class="text-secondary">NEERSLAG</span> <br> 2.5 mm
                    </p>
                </div>
                <!-- col-2 -->
                <div class="col-6 col-md-3 col-sm-6 text-center mt-3 bh-col-bdr">
                    <p>Middag</p>
                    <img src="https://www.ski-livigno.nl/wp-content/uploads/2024/06/cloud.jpg" width="65px">
                    <p>
                        <span class="text-danger">28 °C</span><br>
                        <span class="text-info">23 °C</span>
                    </p>
                    <p>
                        <span class="text-secondary">NEERSLAG</span> <br> 2.5 mm
                    </p>
                </div>
                <!-- col-3 -->
                <div class="col-6 col-md-3 col-sm-6 text-center mt-3 bh-col-bdr2 border-end border-dark">
                    <p>Avond</p>
                    <img src="https://www.ski-livigno.nl/wp-content/uploads/2024/06/cloud.jpg" width="65px">
                    <p>
                        <span class="text-danger">28 °C</span><br>
                        <span class="text-info">23 °C</span>
                    </p>
                    <p>
                        <span class="text-secondary">NEERSLAG</span> <br> 2.5 mm
                    </p>
                </div>
                <!-- col-4 -->
                <div class="col-6 col-md-3 col-sm-6 text-center bh-col-bdr2 mt-3">
                    <p>Nacht</p>
                    <img src="https://www.ski-livigno.nl/wp-content/uploads/2024/06/cloud.jpg" width="65px">
                    <p>
                        <span class="text-danger">28 °C</span><br>
                        <span class="text-info">23 °C</span>
                    </p>
                    <p>
                        <span class="text-secondary">NEERSLAG</span> <br> 2.5 mm
                    </p>
                </div>
            </div>
            <!-- weather section 2-->
            <div class="row mt-3">
                <div class="col-12 text-center bah-Vandaag">
                    <span class="fw-bold fs-5 text-white">Vandaag Donderdag 20 Juni</span>
                </div>
                <!-- col-1 -->
                <div class="col-6 col-md-3 col-sm-6 text-center mt-3 border-end border-dark">
                    <p>Ochtend</p>
                    <img src="https://www.ski-livigno.nl/wp-content/uploads/2024/06/cloud.jpg" width="65px">
                    <p>
                        <span class="text-danger">28 °C</span><br>
                        <span class="text-info">23 °C</span>
                    </p>
                    <p>
                        <span class="text-secondary">NEERSLAG</span> <br> 2.5 mm
                    </p>
                </div>
                <!-- col-2 -->
                <div class="col-6 col-md-3 col-sm-6 text-center mt-3 bh-col-bdr">
                    <p>Middag</p>
                    <img src="https://www.ski-livigno.nl/wp-content/uploads/2024/06/cloud.jpg" width="65px">
                    <p>
                        <span class="text-danger">28 °C</span><br>
                        <span class="text-info">23 °C</span>
                    </p>
                    <p>
                        <span class="text-secondary">NEERSLAG</span> <br> 2.5 mm
                    </p>
                </div>
                <!-- col-3 -->
                <div class="col-6 col-md-3 col-sm-6 text-center mt-3 bh-col-bdr2 border-end border-dark">
                    <p>Avond</p>
                    <img src="https://www.ski-livigno.nl/wp-content/uploads/2024/06/cloud.jpg" width="65px">
                    <p>
                        <span class="text-danger">28 °C</span><br>
                        <span class="text-info">23 °C</span>
                    </p>
                    <p>
                        <span class="text-secondary">NEERSLAG</span> <br> 2.5 mm
                    </p>
                </div>
                <!-- col-4 -->
                <div class="col-6 col-md-3 col-sm-6 text-center bh-col-bdr2 mt-3">
                    <p>Nacht</p>
                    <img src="https://www.ski-livigno.nl/wp-content/uploads/2024/06/cloud.jpg" width="65px">
                    <p>
                        <span class="text-danger">28 °C</span><br>
                        <span class="text-info">23 °C</span>
                    </p>
                    <p>
                        <span class="text-secondary">NEERSLAG</span> <br> 2.5 mm
                    </p>
                </div>
            </div>
            <!-- weatther section 3 -->
            <div class="row mt-3">
                <div class="col-12 text-center bah-Vandaag">
                    <span class="fw-bold fs-5 text-white">Vandaag Donderdag 20 Juni</span>
                </div>
                <!-- col-1 -->
                <div class="col-6 col-md-3 col-sm-6 text-center mt-3 border-end border-dark">
                    <p>Ochtend</p>
                    <img src="https://www.ski-livigno.nl/wp-content/uploads/2024/06/cloud.jpg" width="65px">
                    <p>
                        <span class="text-danger">28 °C</span><br>
                        <span class="text-info">23 °C</span>
                    </p>
                    <p>
                        <span class="text-secondary">NEERSLAG</span> <br> 2.5 mm
                    </p>
                </div>
                <!-- col-2 -->
                <div class="col-6 col-md-3 col-sm-6 text-center mt-3 bh-col-bdr">
                    <p>Middag</p>
                    <img src="https://www.ski-livigno.nl/wp-content/uploads/2024/06/cloud.jpg" width="65px">
                    <p>
                        <span class="text-danger">28 °C</span><br>
                        <span class="text-info">23 °C</span>
                    </p>
                    <p>
                        <span class="text-secondary">NEERSLAG</span> <br> 2.5 mm
                    </p>
                </div>
                <!-- col-3 -->
                <div class="col-6 col-md-3 col-sm-6 text-center mt-3 bh-col-bdr2 border-end border-dark">
                    <p>Avond</p>
                    <img src="https://www.ski-livigno.nl/wp-content/uploads/2024/06/cloud.jpg" width="65px">
                    <p>
                        <span class="text-danger">28 °C</span><br>
                        <span class="text-info">23 °C</span>
                    </p>
                    <p>
                        <span class="text-secondary">NEERSLAG</span> <br> 2.5 mm
                    </p>
                </div>
                <!-- col-4 -->
                <div class="col-6 col-md-3 col-sm-6 text-center bh-col-bdr2 mt-3">
                    <p>Nacht</p>
                    <img src="https://www.ski-livigno.nl/wp-content/uploads/2024/06/cloud.jpg" width="65px">
                    <p>
                        <span class="text-danger">28 °C</span><br>
                        <span class="text-info">23 °C</span>
                    </p>
                    <p>
                        <span class="text-secondary">NEERSLAG</span> <br> 2.5 mm
                    </p>
                </div>
            </div>
        </div>
    </div>

</body>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</html>
<?php 
}
add_shortcode('custom_weather_forecast', 'wfc_custom_forecast_shortcode');