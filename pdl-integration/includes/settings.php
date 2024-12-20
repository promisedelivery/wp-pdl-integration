<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Include helpers file.
require_once plugin_dir_path(__FILE__) . '../admin/helpers.php';

// Function to add admin menu and submenus.
function pdl_add_admin_menu() {
    add_menu_page(
        __('Promise Delivery', 'pdl'),
        __('Promise Delivery', 'pdl'),
        'manage_options',
        'pdl-settings',
        'pdl_settings_page',
        'dashicons-airplane',
        56
    );

    add_submenu_page(
        'pdl-settings',
        __('Settings', 'pdl'),
        __('Settings', 'pdl'),
        'manage_options',
        'pdl-settings',
        'pdl_settings_page'
    );
}

// Callback for settings page.
function pdl_settings_page() {
    // Retrieve stored data.
    $pdl_data = pdl_get_stored_data();
    $email = isset($pdl_data['email']) ? $pdl_data['email'] : '';
    $password = isset($pdl_data['password']) ? $pdl_data['password'] : '';

    // Handle form submission.
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['generate'])) {
            $email = sanitize_email($_POST['api-email']);
            $password = sanitize_text_field($_POST['api-password']);
            $result = pdl_generate_token($email, $password);

            if ($result !== true) {
                echo '<div class="notice notice-error is-dismissible">';
                echo '<p>' . esc_html($result) . '</p>';
                echo '</div>';
            }

            $pdl_data = pdl_get_stored_data(); // Refresh data after generation.
            $email = $pdl_data['email'];
            $password = $pdl_data['password'];
        } elseif (isset($_POST['delete'])) {
            pdl_delete_data();
            $pdl_data = []; // Clear local data after deletion.
            $email = '';
            $password = '';
        }
    }

    // Display the settings page content.
    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('Promise Delivery Settings', 'pdl') . '</h1>';
    echo '<form method="POST" action="">';
    echo '<table class="form-table">';
    echo '<tr>';
    echo '<th><label for="api-email">' . esc_html__('Merchant Email', 'pdl') . '</label></th>';
    echo '<td><input type="email" id="api-email" name="api-email" value="' . esc_attr($email) . '" class="regular-text" required></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th><label for="api-password">' . esc_html__('Merchant Password', 'pdl') . '</label></th>';
    echo '<td>';
    echo '<input type="password" id="api-password" name="api-password" value="' . esc_attr($password) . '" class="regular-text" required>';
    echo '<button type="button" id="toggle-password" style="margin-left: 10px;">' . esc_html__('View', 'pdl') . '</button>';
    echo '</td>';
    echo '</tr>';
    echo '</table>';
    echo '<p>';
    echo '<button type="submit" name="generate" class="button button-primary">' . esc_html__('Generate', 'pdl') . '</button>';
    echo '<button type="submit" name="delete" class="button button-secondary">' . esc_html__('Delete', 'pdl') . '</button>';
    echo '</p>';
    echo '</form>';

    // Display stored data if available.
    if (!empty($pdl_data)) {
        echo '<h2>' . esc_html__('Merchant Information', 'pdl') . '</h2>';
        echo '<table class="form-table">';
        echo '<tr>';
        echo '<th>' . esc_html__('Token', 'pdl') . '</th>';
        echo '<td style="word-wrap: break-word; word-break: break-word; white-space: pre-wrap;">' . esc_html($pdl_data['token']) . '</td>';
        echo '</tr>';
        echo '<tr><th>' . esc_html__('ID', 'pdl') . '</th><td>' . esc_html($pdl_data['id']) . '</td></tr>';
        echo '<tr><th>' . esc_html__('Merchant ID', 'pdl') . '</th><td>' . esc_html($pdl_data['m_id']) . '</td></tr>';
        echo '<tr><th>' . esc_html__('Company Name', 'pdl') . '</th><td>' . esc_html($pdl_data['company_name']) . '</td></tr>';
        echo '<tr><th>' . esc_html__('Address', 'pdl') . '</th><td>' . esc_html($pdl_data['address']) . '</td></tr>';
        echo '<tr><th>' . esc_html__('Contact Number', 'pdl') . '</th><td>' . esc_html($pdl_data['contact_number']) . '</td></tr>';
        echo '<tr><th>' . esc_html__('Status', 'pdl') . '</th><td>' . esc_html($pdl_data['status'] === 1 ? 'Active' : 'Deactivated') . '</td></tr>';
        echo '</table>';
    } else {
        echo '<p>' . esc_html__('No data set.', 'pdl') . '</p>';
    }
    echo '</div>';

    // Add JavaScript for the password toggle functionality.
    ?>
    <script>
        (function () {
            const togglePassword = document.getElementById('toggle-password');
            const passwordField = document.getElementById('api-password');

            togglePassword.addEventListener('click', function () {
                const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordField.setAttribute('type', type);
                this.textContent = type === 'password' ? '<?php esc_html_e("View", "pdl"); ?>' : '<?php esc_html_e("Hide", "pdl"); ?>';
            });
        })();
    </script>
    <?php
}
