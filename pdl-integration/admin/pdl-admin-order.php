<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Add "Promise Delivery" column to WooCommerce Orders List (HPOS enabled).
 */
add_filter('manage_woocommerce_page_wc-orders_columns', 'pdl_add_promise_delivery_column_hpos');
function pdl_add_promise_delivery_column_hpos($columns) {
    $reordered_columns = [];

    // Insert the "Promise Delivery" column after the "Status" column.
    foreach ($columns as $key => $column) {
        $reordered_columns[$key] = $column;
        if ('order_status' === $key) { // Add after the status column.
            $reordered_columns['promise_delivery'] = __('Promise Delivery', 'pdl');
        }
    }

    return $reordered_columns;
}

/**
 * Populate the "Promise Delivery" column with shipment details or a button.
 */
add_action('manage_woocommerce_page_wc-orders_custom_column', 'pdl_display_promise_delivery_column_hpos', 10, 2);
function pdl_display_promise_delivery_column_hpos($column, $order) {
    if ('promise_delivery' === $column) {
        $shipment_meta = $order->get_meta('shipped_pdl');
        $parcel_invoice = $order->get_meta('pdl_parcel_invoice');
        $parcel_status = $order->get_meta('pdl_status');

        if (!empty($shipment_meta)) {
            // Show shipment details if already shipped.
            echo '<strong>Invoice:</strong> ' . esc_html($parcel_invoice) . '<br>';
            echo '<strong>Status:</strong> ' . esc_html($parcel_status ?? __('Pending', 'pdl'));
        } else {
            // Show "Click to Ship" button.
            echo '<button class="button-primary pdl-ship-order" data-order-id="' . esc_attr($order->get_id()) . '">';
            echo __('Click to Ship', 'pdl');
            echo '</button>';
        }
    }
}

/**
 * Enqueue admin scripts for handling API requests on button click.
 */
add_action('admin_enqueue_scripts', 'pdl_enqueue_admin_scripts');
function pdl_enqueue_admin_scripts($hook) {
    if ('woocommerce_page_wc-orders' !== $hook) {
        return;
    }

    wp_enqueue_script(
        'pdl-admin-order',
        plugins_url('../assets/js/pdl-admin-order.js', __FILE__),
        ['jquery'],
        '1.0.0',
        true
    );

    wp_localize_script('pdl-admin-order', 'pdl_ajax_data', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('pdl_create_parcel'),
    ]);
}

/**
 * AJAX handler to create a parcel via Promise Delivery API.
 */
add_action('wp_ajax_pdl_create_parcel', 'pdl_create_parcel');
function pdl_create_parcel() {
    check_ajax_referer('pdl_create_parcel', 'nonce');

    $order_id = absint($_POST['order_id']);
    if (!$order_id) {
        wp_send_json_error(['message' => __('Invalid order ID.', 'pdl')]);
    }

    $order = wc_get_order($order_id);
    if (!$order) {
        wp_send_json_error(['message' => __('Order not found.', 'pdl')]);
    }

    // Retrieve token from database.
    $merchant_data = get_option('pdl_merchant_data', []);
    $token = $merchant_data['token'] ?? '';

    if (empty($token)) {
        wp_send_json_error(['message' => __('Token is missing or invalid.', 'pdl')]);
    }

    // Prepare the payload.
    $payload = [
        'merchant_order_id' => $order_id,
        'weight_package_id' => 2,
        'customer_name' => $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(),
        'customer_contact_number' => $order->get_billing_phone(),
        'customer_address' => $order->get_shipping_address_1() . ', ' . $order->get_shipping_address_2(),
        'area_id' => pdl_get_pdl_area_id($order->get_meta('_shipping_area')),
        'district_id' => pdl_get_pdl_district_id($order->get_meta('_shipping_area')),
        'parcel_note' => $order->get_customer_note(),
        'total_collect_amount' => $order->get_total(),
    ];

    // API request.
    $api_url = PDL_API_BASE_URL . 'merchant/createParcel';
    $response = wp_remote_post($api_url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ],
        'body' => wp_json_encode($payload),
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error(['message' => $response->get_error_message()]);
    }

    $response_body = json_decode(wp_remote_retrieve_body($response), true);

    if (!isset($response_body['success']) || $response_body['success'] !== 200) {
        wp_send_json_error(['message' => $response_body['message'] ?? __('API error.', 'pdl')]);
    }

    // Save parcel details to the order meta.
    $order->update_meta_data('shipped_pdl', 'yes');
    $order->update_meta_data('pdl_parcel_invoice', $response_body['parcel_invoice'] ?? 'N/A');
    $order->update_meta_data('pdl_status', $response_body['parcel_status'] ?? 'Pending');
    $order->save();

    wp_send_json_success(['message' => __('Parcel created successfully.', 'pdl')]);
}

/**
 * Helper function to get PDL area ID from WooCommerce area ID.
 */
function pdl_get_pdl_area_id($woocommerce_area_id) {
    $areas = get_option('woocommerce_pdl_areas', []);
    foreach ($areas as $area) {
        if ($area['woocommerce_area_id'] === $woocommerce_area_id) {
            return $area['pdl_area_id'];
        }
    }
    return null;
}

/**
 * Helper function to get PDL district ID from WooCommerce area ID.
 */
function pdl_get_pdl_district_id($woocommerce_area_id) {
    $areas = get_option('woocommerce_pdl_areas', []);
    foreach ($areas as $area) {
        if ($area['woocommerce_area_id'] === $woocommerce_area_id) {
            return $area['pdl_district_id'];
        }
    }
    return null;
}
