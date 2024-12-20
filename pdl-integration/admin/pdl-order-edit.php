<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

add_action('woocommerce_admin_order_data_after_billing_address', 'pdl_display_billing_area_in_order_meta', 10, 1);
function pdl_display_billing_area_in_order_meta($order) {
    $billing_area = get_post_meta($order->get_id(), '_billing_area', true);
    echo '<p><strong>' . __('Billing Area ID:', 'pdl') . '</strong> ' . esc_html($billing_area) . '</p>';
}

add_action('woocommerce_admin_order_data_after_shipping_address', 'pdl_display_shipping_area_in_order_meta', 10, 1);
function pdl_display_shipping_area_in_order_meta($order) {
    $shipping_area = get_post_meta($order->get_id(), '_shipping_area', true);
    echo '<p><strong>' . __('Shipping Area ID:', 'pdl') . '</strong> ' . esc_html($shipping_area) . '</p>';
}
