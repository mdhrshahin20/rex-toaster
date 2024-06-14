<?php
/*
Plugin Name: EDD Order Toasts for RexTheme
Plugin URI: http://rextheme.com
Description: Display random order toast notifications from the last three days.
Version: 1.0.0
Author: RexTheme
Author URI: http://rextheme.com
*/

// Enqueue scripts and styles
function edd_order_toasts_scripts() {
    wp_enqueue_script('edd-toasts-js', plugin_dir_url(__FILE__) . 'js/edd-toasts.js', array('jquery'), '1.0', true);
    wp_enqueue_style('edd-toasts-css', plugin_dir_url(__FILE__) . 'css/edd-toasts.css');
    wp_localize_script('edd-toasts-js', 'eddOrders',
        array(
            'orders' => edd_get_recent_orders(),
            'rex_edd_toast_delay' =>  get_option('rex_edd_toast_delay', 3000),
            'rex_edd_toast_between_delay' =>  get_option('rex_edd_toast_between_delay', 3000)
        )
    );
}
add_action('wp_enqueue_scripts', 'edd_order_toasts_scripts');

function edd_get_recent_orders() {
    // Fetch current settings
    $edd_toast_days = get_option('rex_edd_toast_days', 3);
    $edd_toast_delay = get_option('rex_edd_toast_delay', 3000);
    $edd_toast_max_orders = get_option('rex_edd_toast_max_orders', 5);
    $edd_toast_between_delay = get_option('rex_edd_toast_between_delay', 1000);  // Default delay between toasts is 1000 ms

    $date = new DateTime();
    $date->modify('-' . $edd_toast_days . ' days');

    $payments_query = new EDD_Payments_Query(array(
        'start_date' => $date->format('Y-m-d'),
        'number' => $edd_toast_max_orders,
        'status' => 'publish', // or 'complete' depending on your setup
    ));
    $payments = $payments_query->get_payments();

    $orders = array();
    foreach ($payments as $payment) {
        $product_details = array();
        $cart_items = edd_get_payment_meta_cart_details($payment->ID);
        $currency_symbol = edd_currency_symbol(); // Get the currency symbol for the store

        foreach ($cart_items as $item) {
            $item_name = $item['name'];
            $item_plan = isset($item['item_number']['options']['price_id']) ? edd_get_price_option_name($item['id'], $item['item_number']['options']['price_id']) : '';
            $image_id = get_post_thumbnail_id($item['id']);
            $image_url = wp_get_attachment_url($image_id);
            $product_url = get_permalink($item['id']);
            $product_details[] = array(
                'name' => $item_name,
                'image_url' => $image_url,
                'product_url' => $product_url
            );
        }

        $payment_time = new DateTime($payment->date);
        $current_time = new DateTime();
        $interval = $payment_time->diff($current_time);
        $time_ago = format_interval($interval);

        $orders[] = array(
            'id' => $payment->ID,
            'total' => $currency_symbol . edd_get_payment_amount($payment->ID),
            'date' => $payment->date,
            'customer' => $payment->user_info['first_name'] . ' ' . $payment->user_info['last_name'],
            'products' => $product_details,
            'time_ago' => $time_ago
        );
    }
    return $orders;
}



function format_interval(DateInterval $interval) {
    if ($interval->y !== 0) {
        return $interval->format('%y years ago');
    } elseif ($interval->m !== 0) {
        return $interval->format('%m months ago');
    } elseif ($interval->d !== 0) {
        return $interval->format('%a days ago');
    } elseif ($interval->h !== 0) {
        return $interval->format('%h hrs ago');
    } elseif ($interval->i !== 0) {
        return $interval->format('%i min ago');
    } else {
        return $interval->format('%s sec ago');
    }
}


function edd_toast_notifications_menu() {
    add_menu_page(
        'EDD Toast Notifications Settings', // Page title
        'EDD Toasts', // Menu title
        'manage_options', // Capability
        'edd-toast-notifications', // Menu slug
        'edd_toast_notifications_settings_page', // Callback function
        'dashicons-bell', // Icon
        6 // Position
    );
}
add_action('admin_menu', 'edd_toast_notifications_menu');


function edd_toast_notifications_settings_page() {
    // Save settings
    if (isset($_POST['save_settings'])) {
        update_option('rex_edd_toast_days', sanitize_text_field($_POST['rex_edd_toast_days']));
        update_option('rex_edd_toast_delay', sanitize_text_field($_POST['rex_edd_toast_delay']));
        update_option('rex_edd_toast_max_orders', sanitize_text_field($_POST['rex_edd_toast_max_orders']));
        update_option('rex_edd_toast_between_delay', sanitize_text_field($_POST['rex_edd_toast_between_delay']));  // New setting for delay between toasts
        echo '<div class="updated"><p>Settings saved.</p></div>';
    }

    // Fetch current settings
    $edd_toast_days = get_option('rex_edd_toast_days', 3);
    $edd_toast_delay = get_option('rex_edd_toast_delay', 3000);
    $edd_toast_max_orders = get_option('rex_edd_toast_max_orders', 5);
    $edd_toast_between_delay = get_option('rex_edd_toast_between_delay', 1000);  // Default delay between toasts is 1000 ms

    // Settings form
    ?>
    <div class="wrap">
        <h2>EDD Toast Notifications Settings</h2>
        <form method="post">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Number of Days:</th>
                    <td><input type="number" name="rex_edd_toast_days" value="<?php echo esc_attr($edd_toast_days); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Delay Time (ms) for Each Order:</th>
                    <td><input type="number" name="rex_edd_toast_delay" value="<?php echo esc_attr($edd_toast_delay); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Max Number of Orders:</th>
                    <td><input type="number" name="rex_edd_toast_max_orders" value="<?php echo esc_attr($edd_toast_max_orders); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Delay Between Toasts (ms):</th>
                    <td><input type="number" name="rex_edd_toast_between_delay" value="<?php echo esc_attr($edd_toast_between_delay); ?>" /></td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" class="button-primary" name="save_settings" value="Save Changes" />
            </p>
        </form>
    </div>
    <?php
}

