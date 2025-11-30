<?php
if (!defined('ABSPATH')) exit;

$post_type = 'ahbn_booking';
wp_enqueue_media(); // WordPress media uploader

// -------------------- Load Currency --------------------
$hotel_currency = get_option('ahbn_hotel_currency', 'USD');
$currency_symbols = [
    'USD'=>'$', 'EUR'=>'€', 'GBP'=>'£', 'BDT'=>'৳', 'INR'=>'₹'
];
$currency_symbol = isset($currency_symbols[$hotel_currency]) ? $currency_symbols[$hotel_currency] : '$';

// -------------------- Handle Form Submission --------------------
if (isset($_POST['ahbn_save_booking'])) {
    if (!check_admin_referer('ahbn_save_booking_verify')) {
        echo '<div class="notice notice-error"><p>Security check failed. Booking not saved.</p></div>';
    } else {
        $booking_id = intval($_POST['booking_id']);
        $data = [
            'post_title'  => sanitize_text_field($_POST['customer_name']),
            'post_type'   => $post_type,
            'post_status' => 'publish'
        ];

        if ($booking_id > 0) {
            wp_update_post(array_merge($data, ['ID' => $booking_id]));
        } else {
            $booking_id = wp_insert_post($data);
        }

        // Save meta
        update_post_meta($booking_id, 'ahbn_customer_email', sanitize_email($_POST['customer_email']));
        update_post_meta($booking_id, 'ahbn_customer_phone', sanitize_text_field($_POST['customer_phone']));
        update_post_meta($booking_id, 'ahbn_customer_address', sanitize_textarea_field($_POST['customer_address']));
        update_post_meta($booking_id, 'ahbn_room_id', intval($_POST['room_id']));
        update_post_meta($booking_id, 'ahbn_room_type', sanitize_text_field($_POST['room_type']));
        update_post_meta($booking_id, 'ahbn_check_in', sanitize_text_field($_POST['check_in']));
        update_post_meta($booking_id, 'ahbn_check_out', sanitize_text_field($_POST['check_out']));
        update_post_meta($booking_id, 'ahbn_status', sanitize_text_field($_POST['status']));
        update_post_meta($booking_id, 'ahbn_payment_method', sanitize_text_field($_POST['payment_method']));
        update_post_meta($booking_id, 'ahbn_transaction_phone', sanitize_text_field($_POST['transaction_phone'] ?? ''));
        update_post_meta($booking_id, 'ahbn_transaction_id', sanitize_text_field($_POST['transaction_id'] ?? ''));
        update_post_meta($booking_id, 'ahbn_bank_info', sanitize_text_field($_POST['bank_info'] ?? ''));
        update_post_meta($booking_id, 'ahbn_days', intval($_POST['days']));

        // Amount: store numeric value only
        $amount_numeric = floatval($_POST['amount']);
        update_post_meta($booking_id, 'ahbn_amount', $amount_numeric);

        // Guest media
        update_post_meta($booking_id, 'ahbn_guest_image', intval($_POST['guest_image_id']));
        update_post_meta($booking_id, 'ahbn_guest_nid', intval($_POST['guest_nid_id']));

        echo '<div class="notice notice-success"><p>Booking saved successfully!</p></div>';
    }
}

// -------------------- Load Existing Booking --------------------
$edit_booking = isset($_GET['edit_booking']) ? intval($_GET['edit_booking']) : 0;

$customer_name = $customer_email = $customer_phone = $room_id = $room_type = $check_in = $check_out = $status = '';
$payment_method = $guest_image_id = $guest_nid_id = '';
$transaction_phone = $transaction_id = $bank_info = '';
$days = $amount = '';

if ($edit_booking > 0) {
    $b = get_post($edit_booking);
    if ($b) {
        $customer_name  = $b->post_title;
        $customer_email = get_post_meta($edit_booking, 'ahbn_customer_email', true);
        $customer_phone = get_post_meta($edit_booking, 'ahbn_customer_phone', true);
        $room_id        = get_post_meta($edit_booking, 'ahbn_room_id', true);
        $room_type      = get_post_meta($edit_booking, 'ahbn_room_type', true);
        $check_in       = get_post_meta($edit_booking, 'ahbn_check_in', true);
        $check_out      = get_post_meta($edit_booking, 'ahbn_check_out', true);
        $status         = get_post_meta($edit_booking, 'ahbn_status', true);
        $payment_method = get_post_meta($edit_booking, 'ahbn_payment_method', true);
        $transaction_phone = get_post_meta($edit_booking, 'ahbn_transaction_phone', true);
        $transaction_id    = get_post_meta($edit_booking, 'ahbn_transaction_id', true);
        $bank_info         = get_post_meta($edit_booking, 'ahbn_bank_info', true);
        $days             = get_post_meta($edit_booking, 'ahbn_days', true);
        $amount           = get_post_meta($edit_booking, 'ahbn_amount', true);
        $guest_image_id = get_post_meta($edit_booking, 'ahbn_guest_image', true);
        $guest_nid_id   = get_post_meta($edit_booking, 'ahbn_guest_nid', true);
    }
} else {
    $status = get_option('ahbn_booking_default_status', 'pending');
    $check_in = date('Y-m-d');
    $check_out = date('Y-m-d', strtotime('+1 day'));
}

// -------------------- Load Rooms --------------------
$rooms = get_posts(['post_type' => 'ahbn_room', 'numberposts' => -1]);
?>

<div class="wrap">
    <h1><?php echo $edit_booking ? 'Edit Booking' : 'Add Booking'; ?></h1>

    <form method="post">
        <?php wp_nonce_field('ahbn_save_booking_verify'); ?>
        <input type="hidden" name="booking_id" value="<?php echo esc_attr($edit_booking); ?>">

        <table class="form-table">
            <tbody>
                <!-- Customer Info -->
                <tr>
                    <th><label for="customer_name">Customer Name</label></th>
                    <td><input type="text" name="customer_name" value="<?php echo esc_attr($customer_name); ?>" class="regular-text" required></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><input type="email" name="customer_email" value="<?php echo esc_attr($customer_email); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th>Phone</th>
                    <td><input type="text" name="customer_phone" value="<?php echo esc_attr($customer_phone); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th>Address</th>
                    <td><textarea name="customer_address" class="large-text" rows="3"><?php echo esc_textarea(get_post_meta($edit_booking,'ahbn_customer_address',true)); ?></textarea></td>
                </tr>

                <!-- Room Selection -->
                <tr>
                    <th>Room</th>
                    <td>
                        <select name="room_id" id="room_id">
                            <option value="">Select Room</option>
                            <?php foreach($rooms as $r):
                                $room_number = get_post_meta($r->ID,'ahbn_room_number',true);
                                $type   = get_post_meta($r->ID,'ahbn_room_type',true);
                                $price  = get_post_meta($r->ID,'ahbn_price',true);

                                $display = $r->post_title;
                                if(!empty($room_number)){
                                    if(is_array($room_number)){
                                        $display .= ' - ' . implode(', ', $room_number);
                                    } else {
                                        $display .= ' - ' . $room_number;
                                    }
                                }
                                if(!empty($type)) $display .= ' (' . $type . ')';
                                if(!empty($price)) $display .= ' - ' . $currency_symbol . number_format((float)$price,2);
                            ?>
                                <option value="<?php echo $r->ID; ?>" 
                                        data-type="<?php echo esc_attr($type); ?>" 
                                        data-price="<?php echo esc_attr($price); ?>"
                                        <?php selected($room_id,$r->ID); ?>>
                                    <?php echo esc_html($display); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="room_price_display" style="margin-top:5px; font-weight:bold;">
                            <?php 
                                if($room_id) {
                                    $selected_price = get_post_meta($room_id,'ahbn_price',true);
                                    echo "Price: ".$currency_symbol . esc_html(number_format((float)$selected_price,2));
                                }
                            ?>
                        </div>
                    </td>
                </tr>

                <tr>
                    <th>Room Type</th>
                    <td><input type="text" name="room_type" id="room_type" value="<?php echo esc_attr($room_type); ?>" readonly></td>
                </tr>

                <!-- Dates -->
                <tr>
                    <th>Check In</th>
                    <td><input type="date" name="check_in" id="check_in" value="<?php echo esc_attr($check_in); ?>"></td>
                </tr>
                <tr>
                    <th>Check Out</th>
                    <td><input type="date" name="check_out" id="check_out" value="<?php echo esc_attr($check_out); ?>"></td>
                </tr>

                <!-- Status -->
                <tr>
                    <th>Status</th>
                    <td>
                        <select name="status">
                            <option value="pending" <?php selected($status,'pending'); ?>>Pending</option>
                            <option value="confirmed" <?php selected($status,'confirmed'); ?>>Confirmed</option>
                            <option value="canceled" <?php selected($status,'canceled'); ?>>Canceled</option>
                        </select>
                    </td>
                </tr>

                <!-- Days & Amount -->
                <tr>
                    <th>Days</th>
                    <td><input type="number" name="days" id="days" value="<?php echo esc_attr($days); ?>" readonly></td>
                </tr>
                <tr>
                    <th>Amount</th>
                    <td><input type="text" name="amount" id="amount" value="<?php echo esc_attr($amount); ?>" readonly></td>
                </tr>

                <!-- Payment Method -->
                <tr>
                    <th>Payment Method</th>
                    <td>
                        <select name="payment_method" id="payment_method">
                            <option value="">Select Method</option>
                            <option value="cash" <?php selected($payment_method,'cash'); ?>>Cash</option>
                            <option value="card" <?php selected($payment_method,'card'); ?>>Card</option>
                            <option value="online" <?php selected($payment_method,'online'); ?>>Online</option>
                            <option value="bkash" <?php selected($payment_method,'bkash'); ?>>Bkash</option>
                            <option value="nagad" <?php selected($payment_method,'nagad'); ?>>Nagad</option>
                            <option value="rocket" <?php selected($payment_method,'rocket'); ?>>Rocket</option>
                            <option value="bank" <?php selected($payment_method,'bank'); ?>>Bank</option>
                        </select>
                    </td>
                </tr>

                <!-- Transaction / Bank Info -->
                <tr class="payment-bkash-nagad-rocket">
                    <th>Transaction Phone</th>
                    <td><input type="text" name="transaction_phone" id="transaction_phone" value="<?php echo esc_attr($transaction_phone); ?>"></td>
                </tr>
                <tr class="payment-bkash-nagad-rocket">
                    <th>Transaction ID</th>
                    <td><input type="text" name="transaction_id" id="transaction_id" value="<?php echo esc_attr($transaction_id); ?>"></td>
                </tr>
                <tr class="payment-bank">
                    <th>Bank Information</th>
                    <td><textarea name="bank_info" id="bank_info"><?php echo esc_textarea($bank_info); ?></textarea></td>
                </tr>
            </tbody>
        </table>

        <!-- Guest Media -->
        <table class="form-table">
            <tbody>
                <tr>
                    <th>Guest Image</th>
                    <td>
                        <input type="hidden" name="guest_image_id" id="guest_image_id" value="<?php echo esc_attr($guest_image_id); ?>">
                        <img id="guest_image_preview" src="<?php echo $guest_image_id ? wp_get_attachment_url($guest_image_id) : ''; ?>" style="max-width:150px; display:block; margin-bottom:5px;">
                        <button class="button" id="guest_image_button">Select/Upload Image</button>
                        <button class="button" id="guest_image_remove">Remove</button>
                    </td>
                </tr>
                <tr>
                    <th>Guest NID / Passport</th>
                    <td>
                        <input type="hidden" name="guest_nid_id" id="guest_nid_id" value="<?php echo esc_attr($guest_nid_id); ?>">
                        <img id="guest_nid_preview" src="<?php echo $guest_nid_id ? wp_get_attachment_url($guest_nid_id) : ''; ?>" style="max-width:150px; display:block; margin-bottom:5px;">
                        <button class="button" id="guest_nid_button">Select/Upload NID</button>
                        <button class="button" id="guest_nid_remove">Remove</button>
                    </td>
                </tr>
            </tbody>
        </table>

        <p class="submit">
            <input type="submit" class="button button-primary" name="ahbn_save_booking" value="Save Booking">
        </p>
    </form>
</div>

<script>
jQuery(document).ready(function($){
    const currency = "<?php echo esc_js($currency_symbol); ?>";

    // ---------------- Guest Image ----------------
    var guestImageFrame;
    $('#guest_image_button').on('click', function(e){
        e.preventDefault();
        if(guestImageFrame) guestImageFrame.open();
        guestImageFrame = wp.media({ title:'Select Guest Image', button:{text:'Use this image'}, multiple:false });
        guestImageFrame.on('select', function(){
            var attachment = guestImageFrame.state().get('selection').first().toJSON();
            $('#guest_image_id').val(attachment.id);
            $('#guest_image_preview').attr('src', attachment.url);
        });
        guestImageFrame.open();
    });
    $('#guest_image_remove').on('click', function(e){ e.preventDefault(); $('#guest_image_id').val(''); $('#guest_image_preview').attr('src',''); });

    // ---------------- Guest NID ----------------
    var guestNidFrame;
    $('#guest_nid_button').on('click', function(e){
        e.preventDefault();
        if(guestNidFrame) guestNidFrame.open();
        guestNidFrame = wp.media({ title:'Select Guest NID', button:{text:'Use this file'}, multiple:false });
        guestNidFrame.on('select', function(){
            var attachment = guestNidFrame.state().get('selection').first().toJSON();
            $('#guest_nid_id').val(attachment.id);
            $('#guest_nid_preview').attr('src', attachment.url);
        });
        guestNidFrame.open();
    });
    $('#guest_nid_remove').on('click', function(e){ e.preventDefault(); $('#guest_nid_id').val(''); $('#guest_nid_preview').attr('src',''); });

    // ---------------- Room & Amount ----------------
    function updateRoomTypeAndAmount(){
        const selected = $('#room_id option:selected');
        const price = parseFloat(selected.data('price')) || 0;
        $('#room_type').val(selected.data('type') || '');

        let checkInDate = new Date($('#check_in').val());
        let checkOutDate = new Date($('#check_out').val());

        if(isNaN(checkInDate.getTime())) checkInDate = new Date();
        if(isNaN(checkOutDate.getTime()) || checkOutDate <= checkInDate) 
            checkOutDate = new Date(checkInDate.getTime() + 86400000);

        let diffDays = Math.ceil((checkOutDate - checkInDate)/(1000*60*60*24));
        if(diffDays < 1) diffDays = 1;

        $('#days').val(diffDays);
        $('#amount').val((price * diffDays).toFixed(2));
        $('#room_price_display').text('Price: ' + currency + price.toFixed(2));
    }
    $('#room_id, #check_in, #check_out').on('change', updateRoomTypeAndAmount);

    // ---------------- Payment Method ----------------
    function updatePaymentFields(){
        const method = $('#payment_method').val();
        if(['bkash','nagad','rocket'].includes(method)){
            $('.payment-bkash-nagad-rocket').show();
            $('.payment-bank').hide();
        } else if(method === 'bank'){
            $('.payment-bkash-nagad-rocket').hide();
            $('.payment-bank').show();
        } else{
            $('.payment-bkash-nagad-rocket, .payment-bank').hide();
        }
    }
    $('#payment_method').on('change', updatePaymentFields);

    // ---------------- Initial Setup ----------------
    function initializeForm(){
        updateRoomTypeAndAmount();
        updatePaymentFields();
        if(!$('#check_in').val()) $('#check_in').val(new Date().toISOString().split('T')[0]);
        if(!$('#check_out').val()){
            let tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            $('#check_out').val(tomorrow.toISOString().split('T')[0]);
        }
    }
    initializeForm();
});
</script>
