<?php
// ----------------------------
// View Booking Page
// ----------------------------
if (!defined('ABSPATH')) exit;

$ahbn_booking_id = isset($_GET['view_booking']) ? intval($_GET['view_booking']) : 0;

if ($ahbn_booking_id <= 0) {
    echo '<div class="notice notice-error"><p>Invalid booking ID.</p></div>';
    return;
}

// Load booking data
$ahbn_b = get_post($ahbn_booking_id);
if (!$ahbn_b || $ahbn_b->post_type !== 'ahbn_booking') {
    echo '<div class="notice notice-error"><p>Booking not found.</p></div>';
    return;
}

// Booking meta
$ahbn_room_id        = get_post_meta($ahbn_booking_id, 'ahbn_room_id', true);
$ahbn_room_name      = $ahbn_room_id ? get_the_title($ahbn_room_id) : '-';
$ahbn_room_type      = get_post_meta($ahbn_booking_id, 'ahbn_room_type', true) ?: '-';
$ahbn_check_in       = get_post_meta($ahbn_booking_id, 'ahbn_check_in', true) ?: '-';
$ahbn_check_out      = get_post_meta($ahbn_booking_id, 'ahbn_check_out', true) ?: '-';
$ahbn_days           = get_post_meta($ahbn_booking_id, 'ahbn_days', true) ?: 0;
$ahbn_amount         = get_post_meta($ahbn_booking_id, 'ahbn_amount', true) ?: 0;
$ahbn_status         = get_post_meta($ahbn_booking_id, 'ahbn_status', true) ?: 'pending';
$ahbn_payment_method = get_post_meta($ahbn_booking_id, 'ahbn_payment_method', true) ?: '-';
$ahbn_transaction_phone = get_post_meta($ahbn_booking_id, 'ahbn_transaction_phone', true) ?: '-';
$ahbn_transaction_id    = get_post_meta($ahbn_booking_id, 'ahbn_transaction_id', true) ?: '-';
$ahbn_bank_info         = get_post_meta($ahbn_booking_id, 'ahbn_bank_info', true) ?: '-';
$ahbn_customer_email    = get_post_meta($ahbn_booking_id, 'ahbn_customer_email', true) ?: '-';
$ahbn_customer_phone    = get_post_meta($ahbn_booking_id, 'ahbn_customer_phone', true) ?: '-';
$ahbn_customer_address  = get_post_meta($ahbn_booking_id, 'ahbn_customer_address', true) ?: '-';
$ahbn_guest_image_id    = get_post_meta($ahbn_booking_id, 'ahbn_guest_image', true);
$ahbn_guest_nid_id      = get_post_meta($ahbn_booking_id, 'ahbn_guest_nid', true);
$ahbn_extras            = get_post_meta($ahbn_booking_id, 'ahbn_extras', true) ?: [];
?>

<div class="wrap">
    <h1>View Booking #<?php echo esc_html($ahbn_booking_id); ?></h1>

    <table class="widefat striped">
        <tbody>
            <tr>
                <th>Customer Name</th>
                <td><?php echo esc_html($ahbn_b->post_title); ?></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><?php echo esc_html($ahbn_customer_email); ?></td>
            </tr>
            <tr>
                <th>Phone</th>
                <td><?php echo esc_html($ahbn_customer_phone); ?></td>
            </tr>
            <tr>
                <th>Address</th>
                <td><?php echo esc_html($ahbn_customer_address); ?></td>
            </tr>
            <tr>
                <th>Room</th>
                <td><?php echo esc_html($ahbn_room_name); ?></td>
            </tr>
            <tr>
                <th>Room Type</th>
                <td><?php echo esc_html($ahbn_room_type); ?></td>
            </tr>
            <tr>
                <th>Extras</th>
                <td>
                    <?php 
                    if ($ahbn_extras && is_array($ahbn_extras) && count($ahbn_extras) > 0) {
                        echo esc_html(implode(', ', $ahbn_extras));
                    } else {
                        echo '-';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th>Check In</th>
                <td><?php echo esc_html($ahbn_check_in); ?></td>
            </tr>
            <tr>
                <th>Check Out</th>
                <td><?php echo esc_html($ahbn_check_out); ?></td>
            </tr>
            <tr>
                <th>Nights</th>
                <td><?php echo esc_html($ahbn_days); ?></td>
            </tr>
            <tr>
                <th>Total Amount</th>
                <td><?php echo esc_html(get_option('ahbn_hotel_currency', '$') . number_format($ahbn_amount, 2)); ?></td>
            </tr>
            <tr>
                <th>Status</th>
                <td><?php echo esc_html(ucfirst($ahbn_status)); ?></td>
            </tr>
            <tr>
                <th>Payment Method</th>
                <td><?php echo esc_html(ucfirst($ahbn_payment_method)); ?></td>
            </tr>
            <?php if (in_array($ahbn_payment_method, ['bkash','nagad','rocket'])): ?>
            <tr>
                <th>Transaction Phone</th>
                <td><?php echo esc_html($ahbn_transaction_phone); ?></td>
            </tr>
            <tr>
                <th>Transaction ID</th>
                <td><?php echo esc_html($ahbn_transaction_id); ?></td>
            </tr>
            <?php elseif ($ahbn_payment_method === 'bank'): ?>
            <tr>
                <th>Bank Info</th>
                <td><?php echo esc_html($ahbn_bank_info); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <th>Guest Image</th>
                <td>
                    <?php if ($ahbn_guest_image_id): ?>
                        <img src="<?php echo esc_url(wp_get_attachment_url($ahbn_guest_image_id)); ?>" style="max-width:150px;">
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Guest NID / Passport</th>
                <td>
                    <?php if ($ahbn_guest_nid_id): ?>
                        <img src="<?php echo esc_url(wp_get_attachment_url($ahbn_guest_nid_id)); ?>" style="max-width:150px;">
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            </tr>
        </tbody>
    </table>

    <p>
        <a href="?page=ahbn_booking_main&tab=bookings&sub_tab=all" class="button">Back to All Bookings</a>
    </p>
</div>
