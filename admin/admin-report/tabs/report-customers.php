<?php
if (!defined('ABSPATH')) exit;

// Fetch all bookings
$ahbn_bookings = get_posts([
    'post_type'   => 'ahbn_booking',
    'numberposts' => -1,
    'post_status' => 'publish',
    'orderby'     => 'ID',
    'order'       => 'DESC'
]);

$ahbn_currency = get_option('ahbn_hotel_currency', '$');

echo '<div class="wrap">';
echo '<h2>All Customers</h2>';
echo '<h3>Total Customers: ' . count($ahbn_bookings) . '</h3>';

echo '<table class="widefat striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Address</th>
                <th>Room</th>
                <th>Check-In</th>
                <th>Check-Out</th>
                <th>Nights</th>
                <th>Total Amount</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>';

if (!empty($ahbn_bookings)) {
    foreach ($ahbn_bookings as $ahbn_b) {
        $ahbn_phone     = get_post_meta($ahbn_b->ID, 'ahbn_customer_phone', true) ?: '-';
        $ahbn_email     = get_post_meta($ahbn_b->ID, 'ahbn_customer_email', true) ?: '-';
        $ahbn_address   = get_post_meta($ahbn_b->ID, 'ahbn_customer_address', true) ?: '-';
        $ahbn_room_id   = get_post_meta($ahbn_b->ID, 'ahbn_room_id', true);
        $ahbn_room_name = $ahbn_room_id ? get_the_title($ahbn_room_id) : '-';
        $ahbn_checkin   = get_post_meta($ahbn_b->ID, 'ahbn_check_in', true) ?: '-';
        $ahbn_checkout  = get_post_meta($ahbn_b->ID, 'ahbn_check_out', true) ?: '-';
        $ahbn_nights    = intval(get_post_meta($ahbn_b->ID, 'ahbn_days', true));
        $ahbn_amount    = floatval(get_post_meta($ahbn_b->ID, 'ahbn_amount', true));
        $ahbn_status    = get_post_meta($ahbn_b->ID, 'ahbn_status', true) ?: 'pending';

        echo '<tr>
                <td>' . esc_html($ahbn_b->ID) . '</td>
                <td>' . esc_html($ahbn_b->post_title) . '</td>
                <td>' . esc_html($ahbn_phone) . '</td>
                <td>' . esc_html($ahbn_email) . '</td>
                <td>' . esc_html($ahbn_address) . '</td>
                <td>' . esc_html($ahbn_room_name) . '</td>
                <td>' . esc_html($ahbn_checkin) . '</td>
                <td>' . esc_html($ahbn_checkout) . '</td>
                <td>' . esc_html($ahbn_nights) . '</td>
                <td>' . esc_html($ahbn_currency . number_format($ahbn_amount, 2)) . '</td>
                <td>' . esc_html(ucfirst($ahbn_status)) . '</td>
              </tr>';
    }
} else {
    echo '<tr><td colspan="11">No bookings found.</td></tr>';
}

echo '</tbody></table>';
echo '</div>';
?>
