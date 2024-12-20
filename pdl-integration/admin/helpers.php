<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Call the API and store the response data.
 */
function pdl_generate_token($email, $password) {
    $api_url = PDL_API_BASE_URL . 'merchant/login';

    // API request payload.
    $body = [
        'email' => $email,
        'password' => $password,
    ];

    // Send API request using wp_remote_post.
    $response = wp_remote_post($api_url, [
        'body' => $body,
        'timeout' => 30, // Set a reasonable timeout.
    ]);

    // Check for errors.
    if (is_wp_error($response)) {
        return __('Failed to connect to the API.', 'pdl');
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);

    // Check if the API returned success.
    if (!isset($data['success']) || $data['success'] != 200) {
        return __('Invalid credentials or API error.', 'pdl');
    }

    // Store the response data, including email and password.
    $merchant_data = [
        'token' => $data['token'],
        'id' => $data['merchant']['id'],
        'm_id' => $data['merchant']['m_id'],
        'company_name' => $data['merchant']['company_name'],
        'address' => $data['merchant']['address'],
        'contact_number' => $data['merchant']['contact_number'],
        'status' => $data['merchant']['status'],
        'email' => $email,
        'password' => $password,
    ];
    update_option('pdl_merchant_data', $merchant_data);

    return true;
}

/**
 * Fetch stored merchant data.
 */
function pdl_get_stored_data() {
    return get_option('pdl_merchant_data', []);
}

/**
 * Delete stored merchant data.
 */
function pdl_delete_data() {
    delete_option('pdl_merchant_data');
}

/**
 * Store WooCommerce areas in batches.
 *
 * @param array $areas List of WooCommerce areas to save.
 */
function pdl_store_woocommerce_areas_in_batches($areas) {
    $batch_size = 100; // Number of entries per batch.
    $batches = array_chunk($areas, $batch_size); // Divide data into batches.

    foreach ($batches as $batch) {
        $existing_data = get_option('woocommerce_pdl_areas', []);
        $updated_data = array_merge($existing_data, $batch);
        update_option('woocommerce_pdl_areas', $updated_data); // Save each batch.
    }
}

/**
 * Fetch stored WooCommerce areas.
 *
 * @return array Stored WooCommerce areas.
 */
function pdl_get_woocommerce_areas() {
    return get_option('woocommerce_pdl_areas', []);
}

/**
 * Delete all WooCommerce areas.
 */
function pdl_delete_woocommerce_areas() {
    delete_option('woocommerce_pdl_areas');
}