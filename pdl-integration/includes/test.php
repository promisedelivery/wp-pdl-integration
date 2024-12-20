<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Add "Test Area" submenu under Promise Delivery menu.
 */
function pdl_test_area_submenu() {
    add_submenu_page(
        'pdl-settings', // Parent menu slug.
        __('Test Area', 'pdl'), // Page title.
        __('Test Area', 'pdl'), // Menu title.
        'manage_options', // Capability.
        'pdl-test-area', // Menu slug.
        'pdl_test_area_page' // Callback function.
    );
}

/**
 * Callback function for Test Area page.
 */
function pdl_test_area_page() {
    $woocommerce_area_id = isset($_POST['woocommerce_area_id']) ? sanitize_text_field($_POST['woocommerce_area_id']) : '';
    $result = [];
    $all_data = get_option('woocommerce_pdl_areas', []);

    // If WooCommerce Area ID is entered, filter the result.
    if (!empty($woocommerce_area_id)) {
        $result = array_filter($all_data, function ($area) use ($woocommerce_area_id) {
            return $area['woocommerce_area_id'] === $woocommerce_area_id;
        });
        $result = reset($result);
    }

    echo '<div class="wrap">';
    echo '<h1>' . __('Test Area', 'pdl') . '</h1>';
    
    // Form to input WooCommerce Area ID.
    echo '<form method="post">';
    echo '<table class="form-table">';
    echo '<tr>';
    echo '<th><label for="woocommerce_area_id">' . __('WooCommerce Area ID', 'pdl') . '</label></th>';
    echo '<td><input type="text" id="woocommerce_area_id" name="woocommerce_area_id" value="' . esc_attr($woocommerce_area_id) . '" class="regular-text"></td>';
    echo '</tr>';
    echo '</table>';
    echo '<p><button type="submit" class="button button-primary">' . __('Submit', 'pdl') . '</button></p>';
    echo '</form>';

    // Display data for the entered WooCommerce Area ID.
    if (!empty($result)) {
        echo '<h2>' . __('Result', 'pdl') . '</h2>';
        echo '<table class="widefat striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>' . __('WooCommerce Area ID', 'pdl') . '</th>';
        echo '<th>' . __('WooCommerce Area', 'pdl') . '</th>';
        echo '<th>' . __('WooCommerce State ID', 'pdl') . '</th>';
        echo '<th>' . __('PDL Area ID', 'pdl') . '</th>';
        echo '<th>' . __('PDL District ID', 'pdl') . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        echo '<tr>';
        echo '<td>' . esc_html($result['woocommerce_area_id'] ?? 'N/A') . '</td>';
        echo '<td>' . esc_html($result['woocommerce_area'] ?? 'N/A') . '</td>';
        echo '<td>' . esc_html($result['woocommerce_state_id'] ?? 'N/A') . '</td>';
        echo '<td>' . esc_html($result['pdl_area_id'] ?? 'N/A') . '</td>';
        echo '<td>' . esc_html($result['pdl_district_id'] ?? 'N/A') . '</td>';
        echo '</tr>';
        echo '</tbody>';
        echo '</table>';
    } elseif (!empty($woocommerce_area_id)) {
        echo '<p>' . __('No data found for the given WooCommerce Area ID.', 'pdl') . '</p>';
    }

    // Display all data in the database.
    if (!empty($all_data)) {
        echo '<h2>' . __('All Data', 'pdl') . '</h2>';
        echo '<table class="widefat striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>' . __('WooCommerce Area ID', 'pdl') . '</th>';
        echo '<th>' . __('WooCommerce Area', 'pdl') . '</th>';
        echo '<th>' . __('WooCommerce State ID', 'pdl') . '</th>';
        echo '<th>' . __('PDL Area ID', 'pdl') . '</th>';
        echo '<th>' . __('PDL District ID', 'pdl') . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        foreach ($all_data as $area) {
            echo '<tr>';
            echo '<td>' . esc_html($area['woocommerce_area_id']) . '</td>';
            echo '<td>' . esc_html($area['woocommerce_area']) . '</td>';
            echo '<td>' . esc_html($area['woocommerce_state_id']) . '</td>';
            echo '<td>' . esc_html($area['pdl_area_id']) . '</td>';
            echo '<td>' . esc_html($area['pdl_district_id']) . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<p>' . __('No data found in the database.', 'pdl') . '</p>';
    }

    echo '</div>';
}
