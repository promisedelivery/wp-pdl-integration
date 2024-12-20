<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Enqueue JavaScript and styles for the Area fields on WooCommerce checkout page.
 */
add_action('wp_enqueue_scripts', 'pdl_enqueue_area_scripts_for_checkout');
function pdl_enqueue_area_scripts_for_checkout() {
    if (is_checkout()) {
        // Enqueue Select2 if not already loaded.
        wp_enqueue_script('select2');

        // Enqueue custom Area dropdown script.
        wp_enqueue_script(
            'pdl-checkout-js',
            plugins_url('../assets/js/pdl-checkout.js', __FILE__), // Updated JS file name.
            ['jquery', 'select2'], // Dependencies include Select2.
            '1.0.0',
            true // Load in footer.
        );

        // Enqueue Select2 CSS.
        wp_enqueue_style('select2');

        // Localize script for AJAX and validation message.
        wp_localize_script('pdl-checkout-js', 'pdl_checkout_ajax_data', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'area_required_message' => __('Please select an area before placing the order.', 'pdl'),
        ]);
    }
}

/**
 * Add Area fields dynamically for billing and shipping sections.
 */
add_filter('woocommerce_checkout_fields', 'pdl_add_area_fields_to_checkout');
function pdl_add_area_fields_to_checkout($fields) {
    // Add Area field to billing.
    $fields['billing']['billing_area'] = [
        'type'        => 'select',
        'class'       => ['pdl-area-dropdown form-row-wide wc-enhanced-select'], // Added Select2 class.
        'label'       => __('Billing Area', 'pdl'),
        'required'    => true,
        'options'     => ['' => __('Select a billing area...', 'pdl')],
        'priority'    => 80, // Position it below the billing_state field.
    ];

    // Add Area field to shipping.
    $fields['shipping']['shipping_area'] = [
        'type'        => 'select',
        'class'       => ['pdl-area-dropdown form-row-wide wc-enhanced-select'], // Added Select2 class.
        'label'       => __('Shipping Area', 'pdl'),
        'required'    => true,
        'options'     => ['' => __('Select a shipping area...', 'pdl')],
        'priority'    => 80, // Position it below the shipping_state field.
    ];

    return $fields;
}

/**
 * Save the selected Area fields with the order meta.
 */
add_action('woocommerce_checkout_update_order_meta', 'pdl_save_area_fields_to_order_meta');
function pdl_save_area_fields_to_order_meta($order_id) {
    // Save billing area.
    if (!empty($_POST['billing_area'])) {
        update_post_meta($order_id, '_billing_area', sanitize_text_field($_POST['billing_area']));
    }

    // Save shipping area.
    if (!empty($_POST['shipping_area'])) {
        update_post_meta($order_id, '_shipping_area', sanitize_text_field($_POST['shipping_area']));
    }
}

/**
 * Display billing and shipping areas on the order confirmation page.
 */
add_filter('woocommerce_order_get_formatted_billing_address', 'pdl_add_billing_area_to_address', 10, 2);
function pdl_add_billing_area_to_address($address, $order) {
    if (is_object($order) && method_exists($order, 'get_id')) {
        $billing_area = get_post_meta($order->get_id(), '_billing_area', true);

        if ($billing_area) {
            $address .= sprintf('<br>%s: %s', __('Billing Area', 'pdl'), esc_html($billing_area));
        }
    }

    return $address;
}

add_filter('woocommerce_order_get_formatted_shipping_address', 'pdl_add_shipping_area_to_address', 10, 2);
function pdl_add_shipping_area_to_address($address, $order) {
    if (is_object($order) && method_exists($order, 'get_id')) {
        $shipping_area = get_post_meta($order->get_id(), '_shipping_area', true);

        if ($shipping_area) {
            $address .= sprintf('<br>%s: %s', __('Shipping Area', 'pdl'), esc_html($shipping_area));
        }
    }

    return $address;
}

/**
 * Optionally Add a custom text before the billing and shipping address sections.
 */
add_action('woocommerce_order_details_before_billing_address', 'pdl_custom_address_verification_text');
add_action('woocommerce_order_details_before_shipping_address', 'pdl_custom_address_verification_text');
function pdl_custom_address_verification_text($order) {
    echo '<p class="address-verification">' . __('Please verify your details:', 'pdl') . '</p>';
}

/**
 * AJAX handler to fetch areas based on the selected district (state).
 */
add_action('wp_ajax_pdl_get_checkout_woocommerce_areas', 'pdl_get_checkout_woocommerce_areas_ajax');
add_action('wp_ajax_nopriv_pdl_get_checkout_woocommerce_areas', 'pdl_get_checkout_woocommerce_areas_ajax');
function pdl_get_checkout_woocommerce_areas_ajax() {
    $state_id = sanitize_text_field($_POST['state_id']);
    $areas = pdl_get_woocommerce_areas_by_state($state_id);

    $response = [];
    foreach ($areas as $area) {
        $response[$area['woocommerce_area_id']] = $area['woocommerce_area'];
    }

    wp_send_json_success($response);
}

/**
 * Helper function to get WooCommerce areas by state ID.
 */
if (!function_exists('pdl_get_woocommerce_areas_by_state')) {
    function pdl_get_woocommerce_areas_by_state($woocommerce_state_id) {
        $areas = get_option('woocommerce_pdl_areas', []);
        return array_filter($areas, function ($area) use ($woocommerce_state_id) {
            return trim($area['woocommerce_state_id']) === trim($woocommerce_state_id);
        });
    }
}
