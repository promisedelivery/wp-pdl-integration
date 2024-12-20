<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Check if WooCommerce is activated and add Bangladesh states
 */
function pdl_check_woocommerce_and_add_states() {
    // Check if WooCommerce is active
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'pdl_show_woocommerce_notice');
        return;
    }

    // Add custom WooCommerce states for Bangladesh
    add_filter('woocommerce_states', 'pdl_add_woocommerce_states');
}

/**
 * Add WooCommerce States for Bangladesh
 */
function pdl_add_woocommerce_states($states) {
    // Define the new states to be added
    $new_states = [
        'BD-65' => 'Savar',
        'BD-66' => 'Keraniganj',
        'BD-67' => 'Chakaria',
    ];

    // Ensure Bangladesh states exist in the $states array
    if (!isset($states['BD'])) {
        $states['BD'] = [];
    }

    // Merge new states with existing states
    $states['BD'] = array_merge($states['BD'], $new_states);

    return $states;
}

/**
 * Show admin notice if WooCommerce is not active
 */
function pdl_show_woocommerce_notice() {
    ?>
    <div class="notice notice-error">
        <p>
            <?php
            echo wp_kses(
                sprintf(
                    __('The PDL Integration plugin requires WooCommerce to be installed and activated. <a href="%s">Click here</a> to install WooCommerce.', 'pdl'),
                    esc_url(admin_url('plugin-install.php?s=woocommerce&tab=search&type=term'))
                ),
                [
                    'a' => ['href' => [], 'target' => []],
                ]
            );
            ?>
        </p>
    </div>
    <?php
}

// Hook to run on admin initialization
add_action('admin_init', 'pdl_check_woocommerce_and_add_states');
