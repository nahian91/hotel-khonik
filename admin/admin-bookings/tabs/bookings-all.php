<?php
if (!defined('ABSPATH')) exit;

$post_type = 'ahbn_booking';

// -------------------- Handle Delete Booking --------------------
if (isset($_GET['delete_booking'])) {
    $delete_id = intval($_GET['delete_booking']);
    if ($delete_id > 0) {
        wp_delete_post($delete_id, true);
        echo '<div class="notice notice-success"><p>Booking deleted successfully!</p></div>';
    }
}

// -------------------- Load Currency --------------------
$currency_code = get_option('ahbn_hotel_currency', 'USD');
$currency_symbols = [
    'USD'=>'$', 'EUR'=>'€', 'GBP'=>'£', 'BDT'=>'৳', 'INR'=>'₹'
];
$currency_symbol = isset($currency_symbols[$currency_code]) ? $currency_symbols[$currency_code] : '$';

// -------------------- Load All Bookings --------------------
$bookings = get_posts([
    'post_type'   => $post_type,
    'numberposts' => -1,
    'orderby'     => 'ID',
    'order'       => 'DESC'
]);
?>

<div class="wrap">
    <h1>All Bookings</h1>

    <table class="widefat striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Room</th>
                <th>Room Type</th>
                <th>Days</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Check In</th>
                <th>Check Out</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($bookings): ?>
            <?php foreach ($bookings as $b): 
                $room_id      = get_post_meta($b->ID,'ahbn_room_id',true);
                $room_title   = $room_id ? get_the_title($room_id) : '-';
                $room_type    = get_post_meta($b->ID,'ahbn_room_type',true) ?: '-';
                $days         = get_post_meta($b->ID,'ahbn_days',true) ?: 1;
                $total_amount = get_post_meta($b->ID,'ahbn_amount',true) ?: 0;
                $status       = get_post_meta($b->ID,'ahbn_status',true) ?: 'pending';
                $check_in     = get_post_meta($b->ID,'ahbn_check_in',true);
                $check_out    = get_post_meta($b->ID,'ahbn_check_out',true);
            ?>
            <tr>
                <td><?php echo esc_html($b->ID); ?></td>
                <td><?php echo esc_html($b->post_title); ?></td>
                <td><?php echo esc_html($room_title); ?></td>
                <td><?php echo esc_html($room_type); ?></td>
                <td><?php echo esc_html($days); ?></td>
                <td><?php echo esc_html($currency_symbol . number_format((float)$total_amount,2)); ?></td>
                <td><?php echo esc_html(ucfirst($status)); ?></td>
                <td><?php echo esc_html($check_in); ?></td>
                <td><?php echo esc_html($check_out); ?></td>
                <td style="white-space: nowrap;">
                    <a class="button button-primary" href="?page=ahbn_booking_main&tab=bookings&sub_tab=view&view_booking=<?php echo esc_attr($b->ID); ?>">View</a>
                    <a class="button button-secondary" href="?page=ahbn_booking_main&tab=bookings&sub_tab=add&edit_booking=<?php echo esc_attr($b->ID); ?>">Edit</a>
                    <a class="button button-secondary" style="color:#b32d2e;border-color:#b32d2e;"
                       href="?page=ahbn_booking_main&tab=bookings&sub_tab=all&delete_booking=<?php echo esc_attr($b->ID); ?>"
                       onclick="return confirm('Are you sure to delete this booking?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="10">No bookings found.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
