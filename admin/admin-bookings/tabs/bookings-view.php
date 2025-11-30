<?php
// ----------------------------
// View Booking Page
// ----------------------------
if (!defined('ABSPATH')) exit;

$booking_id = isset($_GET['view_booking']) ? intval($_GET['view_booking']) : 0;

if ($booking_id <= 0) {
    echo '<div class="notice notice-error"><p>Invalid booking ID.</p></div>';
    return;
}

// Load booking data
$b = get_post($booking_id);
if (!$b || $b->post_type !== 'ahbn_booking') {
    echo '<div class="notice notice-error"><p>Booking not found.</p></div>';
    return;
}

// Booking meta
$room_id        = get_post_meta($booking_id,'ahbn_room_id',true);
$room_name      = $room_id ? get_the_title($room_id) : '-';
$room_type      = get_post_meta($booking_id,'ahbn_room_type',true) ?: '-';
$check_in       = get_post_meta($booking_id,'ahbn_check_in',true) ?: '-';
$check_out      = get_post_meta($booking_id,'ahbn_check_out',true) ?: '-';
$days           = get_post_meta($booking_id,'ahbn_days',true) ?: 0;
$amount         = get_post_meta($booking_id,'ahbn_amount',true) ?: 0;
$status         = get_post_meta($booking_id,'ahbn_status',true) ?: 'pending';
$payment_method = get_post_meta($booking_id,'ahbn_payment_method',true) ?: '-';
$transaction_phone = get_post_meta($booking_id,'ahbn_transaction_phone',true) ?: '-';
$transaction_id    = get_post_meta($booking_id,'ahbn_transaction_id',true) ?: '-';
$bank_info         = get_post_meta($booking_id,'ahbn_bank_info',true) ?: '-';
$customer_email = get_post_meta($booking_id,'ahbn_customer_email',true) ?: '-';
$customer_phone = get_post_meta($booking_id,'ahbn_customer_phone',true) ?: '-';
$customer_address = get_post_meta($booking_id,'ahbn_customer_address',true) ?: '-';
$guest_image_id = get_post_meta($booking_id,'ahbn_guest_image',true);
$guest_nid_id   = get_post_meta($booking_id,'ahbn_guest_nid',true);
$extras         = get_post_meta($booking_id,'ahbn_extras',true) ?: [];
?>

<div class="wrap">
    <h1>View Booking #<?php echo esc_html($booking_id); ?></h1>

    <table class="widefat striped">
        <tbody>
            <tr>
                <th>Customer Name</th>
                <td><?php echo esc_html($b->post_title); ?></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><?php echo esc_html($customer_email); ?></td>
            </tr>
            <tr>
                <th>Phone</th>
                <td><?php echo esc_html($customer_phone); ?></td>
            </tr>
            <tr>
                <th>Address</th>
                <td><?php echo esc_html($customer_address); ?></td>
            </tr>
            <tr>
                <th>Room</th>
                <td><?php echo esc_html($room_name); ?></td>
            </tr>
            <tr>
                <th>Room Type</th>
                <td><?php echo esc_html($room_type); ?></td>
            </tr>
            <tr>
                <th>Extras</th>
                <td>
                    <?php 
                    if($extras && is_array($extras) && count($extras) > 0){
                        echo esc_html(implode(', ', $extras));
                    } else {
                        echo '-';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th>Check In</th>
                <td><?php echo esc_html($check_in); ?></td>
            </tr>
            <tr>
                <th>Check Out</th>
                <td><?php echo esc_html($check_out); ?></td>
            </tr>
            <tr>
                <th>Nights</th>
                <td><?php echo esc_html($days); ?></td>
            </tr>
            <tr>
                <th>Total Amount</th>
                <td><?php echo esc_html(get_option('ahbn_hotel_currency','$') . number_format($amount,2)); ?></td>
            </tr>
            <tr>
                <th>Status</th>
                <td><?php echo esc_html(ucfirst($status)); ?></td>
            </tr>
            <tr>
                <th>Payment Method</th>
                <td><?php echo esc_html(ucfirst($payment_method)); ?></td>
            </tr>
            <?php if(in_array($payment_method,['bkash','nagad','rocket'])): ?>
            <tr>
                <th>Transaction Phone</th>
                <td><?php echo esc_html($transaction_phone); ?></td>
            </tr>
            <tr>
                <th>Transaction ID</th>
                <td><?php echo esc_html($transaction_id); ?></td>
            </tr>
            <?php elseif($payment_method === 'bank'): ?>
            <tr>
                <th>Bank Info</th>
                <td><?php echo esc_html($bank_info); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <th>Guest Image</th>
                <td>
                    <?php if($guest_image_id): ?>
                        <img src="<?php echo esc_url(wp_get_attachment_url($guest_image_id)); ?>" style="max-width:150px;">
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Guest NID / Passport</th>
                <td>
                    <?php if($guest_nid_id): ?>
                        <img src="<?php echo esc_url(wp_get_attachment_url($guest_nid_id)); ?>" style="max-width:150px;">
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
