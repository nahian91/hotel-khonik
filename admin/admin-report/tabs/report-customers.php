<?php
if (!defined('ABSPATH')) exit;

// Fetch all bookings
$bookings = get_posts([
    'post_type'   => 'ahbn_booking',
    'numberposts' => -1,
    'post_status' => 'publish',
    'orderby'     => 'ID',
    'order'       => 'DESC'
]);

$currency = get_option('ahbn_hotel_currency', '$');

echo '<div class="wrap">';
echo '<h2>All Customers</h2>';
echo '<h3>Total Customers: ' . count($bookings) . '</h3>';

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

if (!empty($bookings)) {
    foreach ($bookings as $b) {
        $phone     = get_post_meta($b->ID, 'ahbn_customer_phone', true) ?: '-';
        $email     = get_post_meta($b->ID, 'ahbn_customer_email', true) ?: '-';
        $address   = get_post_meta($b->ID, 'ahbn_customer_address', true) ?: '-';
        $room_id   = get_post_meta($b->ID, 'ahbn_room_id', true);
        $room_name = $room_id ? get_the_title($room_id) : '-';
        $checkin   = get_post_meta($b->ID, 'ahbn_check_in', true) ?: '-';
        $checkout  = get_post_meta($b->ID, 'ahbn_check_out', true) ?: '-';
        $nights    = intval(get_post_meta($b->ID, 'ahbn_days', true));
        $amount    = floatval(get_post_meta($b->ID, 'ahbn_amount', true));
        $status    = get_post_meta($b->ID, 'ahbn_status', true) ?: 'pending';

        echo '<tr>
                <td>' . esc_html($b->ID) . '</td>
                <td>' . esc_html($b->post_title) . '</td>
                <td>' . esc_html($phone) . '</td>
                <td>' . esc_html($email) . '</td>
                <td>' . esc_html($address) . '</td>
                <td>' . esc_html($room_name) . '</td>
                <td>' . esc_html($checkin) . '</td>
                <td>' . esc_html($checkout) . '</td>
                <td>' . esc_html($nights) . '</td>
                <td>' . esc_html($currency . number_format($amount, 2)) . '</td>
                <td>' . esc_html(ucfirst($status)) . '</td>
              </tr>';
    }
} else {
    echo '<tr><td colspan="11">No bookings found.</td></tr>';
}

echo '</tbody></table>';
echo '</div>';
?>
