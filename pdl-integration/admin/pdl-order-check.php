<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Function to check PDL status and update order meta (HPOS compatible).
 */
function pdl_check_pdl_status_hpos() {
    // Fetch processing orders
    $orders = wc_get_orders([
        'status' => 'processing',
        'limit' => -1,
    ]);

    if (empty($orders)) {
        return;
    }

    // Retrieve token from the database
    $merchant_data = get_option('pdl_merchant_data', []);
    $token = $merchant_data['token'] ?? '';

    if (empty($token)) {
        return;
    }

    foreach ($orders as $order) {
        $parcel_invoice = $order->get_meta('pdl_parcel_invoice');

        if (empty($parcel_invoice)) {
            continue;
        }

        // Prepare API URL
        $api_url = PDL_API_BASE_URL . 'merchant/parcelStatus';
        $api_url = add_query_arg([
            'parcel_invoice' => $parcel_invoice,
            'merchant_order_id' => $order->get_id(),
        ], $api_url);

        // API request
        $response = wp_remote_get($api_url, [
            'headers' => ['Authorization' => 'Bearer ' . $token],
        ]);

        if (is_wp_error($response)) {
            continue;
        }

        $response_body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($response_body['success']) && $response_body['success'] === 200) {
            $status = $response_body['parcel_info']['status'] ?? 'Unknown';

            // Update order meta with the new status
            $order->update_meta_data('pdl_status', $status);
            $order->save();
        }
    }
}

/**
 * Run status check on admin head or schedule.
 */
add_action('admin_head', function () {
    $screen = get_current_screen();
    if (
        ($screen && $screen->id === 'woocommerce_page_wc-orders') || 
        (isset($_GET['page']) && $_GET['page'] === 'wc-orders')
    ) {
        pdl_check_pdl_status_hpos();
    }
});

/**
 * Schedule a cron job to check PDL statuses every 30 minutes.
 */
if (!wp_next_scheduled('pdl_check_and_update_status_cron')) {
    wp_schedule_event(time(), 'thirty_minutes', 'pdl_check_and_update_status_cron');
}
add_action('pdl_check_and_update_status_cron', 'pdl_check_pdl_status_hpos');

/**
 * Add a custom cron schedule for 30 minutes.
 */
add_filter('cron_schedules', function ($schedules) {
    $schedules['thirty_minutes'] = [
        'interval' => 1800,
        'display'  => __('Every 30 Minutes', 'pdl'),
    ];
    return $schedules;
});
