<?php
if (!defined('ABSPATH')) exit;

$post_type = 'ahbn_booking';
wp_enqueue_media(); // WordPress media uploader

// -------------------- Load Currency --------------------
$ahbn_hotel_currency = get_option('ahbn_hotel_currency', 'USD');
$ahbn_currency_symbols = [
    'USD'=>'$', 'EUR'=>'€', 'GBP'=>'£', 'BDT'=>'৳', 'INR'=>'₹'
];
$ahbn_currency_symbol = isset($ahbn_currency_symbols[$ahbn_hotel_currency]) ? $ahbn_currency_symbols[$ahbn_hotel_currency] : '$';

// -------------------- Handle Form Submission --------------------
if (isset($_POST['ahbn_save_booking'])) {
    if (!check_admin_referer('ahbn_save_booking_verify')) {
        echo '<div class="notice notice-error"><p>' . esc_html__('Security check failed. Booking not saved.', 'awesome-hotel-booking') . '</p></div>';
    } else {

        // Safely retrieve POST values
        $ahbn_booking_id = isset($_POST['ahbn_booking_id']) ? intval($_POST['ahbn_booking_id']) : 0;
        $ahbn_customer_name  = isset($_POST['ahbn_customer_name']) ? sanitize_text_field(wp_unslash($_POST['ahbn_customer_name'])) : '';
        $ahbn_customer_email = isset($_POST['ahbn_customer_email']) ? sanitize_email(wp_unslash($_POST['ahbn_customer_email'])) : '';
        $ahbn_customer_phone = isset($_POST['ahbn_customer_phone']) ? sanitize_text_field(wp_unslash($_POST['ahbn_customer_phone'])) : '';
        $ahbn_customer_address = isset($_POST['ahbn_customer_address']) ? sanitize_textarea_field(wp_unslash($_POST['ahbn_customer_address'])) : '';
        $ahbn_room_id        = isset($_POST['ahbn_room_id']) ? intval($_POST['ahbn_room_id']) : 0;
        $ahbn_room_type      = isset($_POST['ahbn_room_type']) ? sanitize_text_field(wp_unslash($_POST['ahbn_room_type'])) : '';
        $ahbn_check_in       = isset($_POST['ahbn_check_in']) ? sanitize_text_field(wp_unslash($_POST['ahbn_check_in'])) : '';
        $ahbn_check_out      = isset($_POST['ahbn_check_out']) ? sanitize_text_field(wp_unslash($_POST['ahbn_check_out'])) : '';
        $ahbn_status         = isset($_POST['ahbn_status']) ? sanitize_text_field(wp_unslash($_POST['ahbn_status'])) : '';
        $ahbn_payment_method = isset($_POST['ahbn_payment_method']) ? sanitize_text_field(wp_unslash($_POST['ahbn_payment_method'])) : '';
        $ahbn_transaction_phone = isset($_POST['ahbn_transaction_phone']) ? sanitize_text_field(wp_unslash($_POST['ahbn_transaction_phone'])) : '';
        $ahbn_transaction_id    = isset($_POST['ahbn_transaction_id']) ? sanitize_text_field(wp_unslash($_POST['ahbn_transaction_id'])) : '';
        $ahbn_bank_info         = isset($_POST['ahbn_bank_info']) ? sanitize_textarea_field(wp_unslash($_POST['ahbn_bank_info'])) : '';
        $ahbn_days              = isset($_POST['ahbn_days']) ? intval($_POST['ahbn_days']) : 1;
        $ahbn_amount            = isset($_POST['ahbn_amount']) ? floatval($_POST['ahbn_amount']) : 0;
        $ahbn_guest_image_id    = isset($_POST['ahbn_guest_image_id']) ? intval($_POST['ahbn_guest_image_id']) : 0;
        $ahbn_guest_nid_id      = isset($_POST['ahbn_guest_nid_id']) ? intval($_POST['ahbn_guest_nid_id']) : 0;

        $ahbn_data = [
            'post_title'  => $ahbn_customer_name,
            'post_type'   => $post_type,
            'post_status' => 'publish'
        ];

        if ($ahbn_booking_id > 0) {
            wp_update_post(array_merge($ahbn_data, ['ID' => $ahbn_booking_id]));
        } else {
            $ahbn_booking_id = wp_insert_post($ahbn_data);
        }

        // Save meta
        update_post_meta($ahbn_booking_id, 'ahbn_customer_email', $ahbn_customer_email);
        update_post_meta($ahbn_booking_id, 'ahbn_customer_phone', $ahbn_customer_phone);
        update_post_meta($ahbn_booking_id, 'ahbn_customer_address', $ahbn_customer_address);
        update_post_meta($ahbn_booking_id, 'ahbn_room_id', $ahbn_room_id);
        update_post_meta($ahbn_booking_id, 'ahbn_room_type', $ahbn_room_type);
        update_post_meta($ahbn_booking_id, 'ahbn_check_in', $ahbn_check_in);
        update_post_meta($ahbn_booking_id, 'ahbn_check_out', $ahbn_check_out);
        update_post_meta($ahbn_booking_id, 'ahbn_status', $ahbn_status);
        update_post_meta($ahbn_booking_id, 'ahbn_payment_method', $ahbn_payment_method);
        update_post_meta($ahbn_booking_id, 'ahbn_transaction_phone', $ahbn_transaction_phone);
        update_post_meta($ahbn_booking_id, 'ahbn_transaction_id', $ahbn_transaction_id);
        update_post_meta($ahbn_booking_id, 'ahbn_bank_info', $ahbn_bank_info);
        update_post_meta($ahbn_booking_id, 'ahbn_days', $ahbn_days);
        update_post_meta($ahbn_booking_id, 'ahbn_amount', $ahbn_amount);
        update_post_meta($ahbn_booking_id, 'ahbn_guest_image', $ahbn_guest_image_id);
        update_post_meta($ahbn_booking_id, 'ahbn_guest_nid', $ahbn_guest_nid_id);

        echo '<div class="notice notice-success"><p>' . esc_html__('Booking saved successfully!', 'awesome-hotel-booking') . '</p></div>';
    }
}

// -------------------- Load Existing Booking --------------------
$ahbn_edit_booking = isset($_GET['edit_booking']) ? intval($_GET['edit_booking']) : 0;

$ahbn_customer_name = $ahbn_customer_email = $ahbn_customer_phone = $ahbn_room_id = $ahbn_room_type = $ahbn_check_in = $ahbn_check_out = $ahbn_status = '';
$ahbn_payment_method = $ahbn_guest_image_id = $ahbn_guest_nid_id = '';
$ahbn_transaction_phone = $ahbn_transaction_id = $ahbn_bank_info = '';
$ahbn_days = $ahbn_amount = '';

if ($ahbn_edit_booking > 0) {
    $ahbn_b = get_post($ahbn_edit_booking);
    if ($ahbn_b) {
        $ahbn_customer_name  = $ahbn_b->post_title;
        $ahbn_customer_email = get_post_meta($ahbn_edit_booking, 'ahbn_customer_email', true);
        $ahbn_customer_phone = get_post_meta($ahbn_edit_booking, 'ahbn_customer_phone', true);
        $ahbn_room_id        = get_post_meta($ahbn_edit_booking, 'ahbn_room_id', true);
        $ahbn_room_type      = get_post_meta($ahbn_edit_booking, 'ahbn_room_type', true);
        $ahbn_check_in       = get_post_meta($ahbn_edit_booking, 'ahbn_check_in', true);
        $ahbn_check_out      = get_post_meta($ahbn_edit_booking, 'ahbn_check_out', true);
        $ahbn_status         = get_post_meta($ahbn_edit_booking, 'ahbn_status', true);
        $ahbn_payment_method = get_post_meta($ahbn_edit_booking, 'ahbn_payment_method', true);
        $ahbn_transaction_phone = get_post_meta($ahbn_edit_booking, 'ahbn_transaction_phone', true);
        $ahbn_transaction_id    = get_post_meta($ahbn_edit_booking, 'ahbn_transaction_id', true);
        $ahbn_bank_info         = get_post_meta($ahbn_edit_booking, 'ahbn_bank_info', true);
        $ahbn_days             = get_post_meta($ahbn_edit_booking, 'ahbn_days', true);
        $ahbn_amount           = get_post_meta($ahbn_edit_booking, 'ahbn_amount', true);
        $ahbn_guest_image_id = get_post_meta($ahbn_edit_booking, 'ahbn_guest_image', true);
        $ahbn_guest_nid_id   = get_post_meta($ahbn_edit_booking, 'ahbn_guest_nid', true);
    }
} else {
    $ahbn_status = get_option('ahbn_booking_default_status', 'pending');
    $ahbn_check_in = gmdate('Y-m-d');
    $ahbn_check_out = gmdate('Y-m-d', strtotime('+1 day'));
}

// -------------------- Load Rooms --------------------
$ahbn_rooms = get_posts(['post_type' => 'ahbn_room', 'numberposts' => -1]);
?>

<!-- Booking Form HTML -->
<div class="wrap">
    <h1><?php echo $ahbn_edit_booking ? esc_html__('Edit Booking', 'awesome-hotel-booking') : esc_html__('Add Booking', 'awesome-hotel-booking'); ?></h1>
    <form method="post">
        <?php wp_nonce_field('ahbn_save_booking_verify'); ?>
        <input type="hidden" name="ahbn_booking_id" value="<?php echo esc_attr($ahbn_edit_booking); ?>">

        <table class="form-table">
            <tbody>
                <!-- Customer Info -->
                <tr>
                    <th><label for="ahbn_customer_name"><?php esc_html_e('Customer Name', 'awesome-hotel-booking'); ?></label></th>
                    <td><input type="text" name="ahbn_customer_name" value="<?php echo esc_attr($ahbn_customer_name); ?>" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Email', 'awesome-hotel-booking'); ?></th>
                    <td><input type="email" name="ahbn_customer_email" value="<?php echo esc_attr($ahbn_customer_email); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Phone', 'awesome-hotel-booking'); ?></th>
                    <td><input type="text" name="ahbn_customer_phone" value="<?php echo esc_attr($ahbn_customer_phone); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Address', 'awesome-hotel-booking'); ?></th>
                    <td><textarea name="ahbn_customer_address" class="large-text" rows="3"><?php echo esc_textarea($ahbn_customer_address); ?></textarea></td>
                </tr>

                <!-- Room Selection -->
                <tr>
                    <th><?php esc_html_e('Room', 'awesome-hotel-booking'); ?></th>
                    <td>
                        <select name="ahbn_room_id" id="ahbn_room_id">
                            <option value=""><?php esc_html_e('Select Room', 'awesome-hotel-booking'); ?></option>
                            <?php foreach($ahbn_rooms as $ahbn_r):
                                $ahbn_room_number = get_post_meta($ahbn_r->ID,'ahbn_room_number',true);
                                $ahbn_type   = get_post_meta($ahbn_r->ID,'ahbn_room_type',true);
                                $ahbn_price  = get_post_meta($ahbn_r->ID,'ahbn_price',true);

                                $ahbn_display = $ahbn_r->post_title;
                                if(!empty($ahbn_room_number)){
                                    if(is_array($ahbn_room_number)){
                                        $ahbn_display .= ' - ' . implode(', ', $ahbn_room_number);
                                    } else {
                                        $ahbn_display .= ' - ' . $ahbn_room_number;
                                    }
                                }
                                if(!empty($ahbn_type)) $ahbn_display .= ' (' . esc_html($ahbn_type) . ')';
                                if(!empty($ahbn_price)) $ahbn_display .= ' - ' . esc_html($ahbn_currency_symbol) . esc_html(number_format((float)$ahbn_price,2));
                            ?>
                                <option value="<?php echo esc_attr($ahbn_r->ID); ?>" 
                                        data-type="<?php echo esc_attr($ahbn_type); ?>" 
                                        data-price="<?php echo esc_attr($ahbn_price); ?>"
                                        <?php selected($ahbn_room_id,$ahbn_r->ID); ?>>
                                    <?php echo esc_html($ahbn_display); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="ahbn_room_price_display" style="margin-top:5px; font-weight:bold;">
                            <?php 
                                if($ahbn_room_id) {
                                    $ahbn_selected_price = get_post_meta($ahbn_room_id,'ahbn_price',true);
                                    echo esc_html__('Price: ', 'awesome-hotel-booking') . esc_html($ahbn_currency_symbol) . esc_html(number_format((float)$ahbn_selected_price,2));
                                }
                            ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Room Type', 'awesome-hotel-booking'); ?></th>
                    <td><input type="text" name="ahbn_room_type" id="ahbn_room_type" value="<?php echo esc_attr($ahbn_room_type); ?>" readonly></td>
                </tr>

                <!-- Dates -->
                <tr>
                    <th><?php esc_html_e('Check In', 'awesome-hotel-booking'); ?></th>
                    <td><input type="date" name="ahbn_check_in" id="ahbn_check_in" value="<?php echo esc_attr($ahbn_check_in); ?>"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Check Out', 'awesome-hotel-booking'); ?></th>
                    <td><input type="date" name="ahbn_check_out" id="ahbn_check_out" value="<?php echo esc_attr($ahbn_check_out); ?>"></td>
                </tr>

                <!-- Status -->
                <tr>
                    <th><?php esc_html_e('Status', 'awesome-hotel-booking'); ?></th>
                    <td>
                        <select name="ahbn_status">
                            <option value="pending" <?php selected($ahbn_status,'pending'); ?>><?php esc_html_e('Pending', 'awesome-hotel-booking'); ?></option>
                            <option value="confirmed" <?php selected($ahbn_status,'confirmed'); ?>><?php esc_html_e('Confirmed', 'awesome-hotel-booking'); ?></option>
                            <option value="canceled" <?php selected($ahbn_status,'canceled'); ?>><?php esc_html_e('Canceled', 'awesome-hotel-booking'); ?></option>
                        </select>
                    </td>
                </tr>

                <!-- Days & Amount -->
                <tr>
                    <th><?php esc_html_e('Days', 'awesome-hotel-booking'); ?></th>
                    <td><input type="number" name="ahbn_days" id="ahbn_days" value="<?php echo esc_attr($ahbn_days); ?>" readonly></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Amount', 'awesome-hotel-booking'); ?></th>
                    <td><input type="text" name="ahbn_amount" id="ahbn_amount" value="<?php echo esc_attr($ahbn_amount); ?>" readonly></td>
                </tr>

                <!-- Payment Method -->
                <tr>
                    <th><?php esc_html_e('Payment Method', 'awesome-hotel-booking'); ?></th>
                    <td>
                        <select name="ahbn_payment_method" id="ahbn_payment_method">
                            <option value=""><?php esc_html_e('Select Method', 'awesome-hotel-booking'); ?></option>
                            <option value="cash" <?php selected($ahbn_payment_method,'cash'); ?>><?php esc_html_e('Cash', 'awesome-hotel-booking'); ?></option>
                            <option value="card" <?php selected($ahbn_payment_method,'card'); ?>><?php esc_html_e('Card', 'awesome-hotel-booking'); ?></option>
                            <option value="online" <?php selected($ahbn_payment_method,'online'); ?>><?php esc_html_e('Online', 'awesome-hotel-booking'); ?></option>
                            <option value="bkash" <?php selected($ahbn_payment_method,'bkash'); ?>><?php esc_html_e('Bkash', 'awesome-hotel-booking'); ?></option>
                            <option value="nagad" <?php selected($ahbn_payment_method,'nagad'); ?>><?php esc_html_e('Nagad', 'awesome-hotel-booking'); ?></option>
                            <option value="rocket" <?php selected($ahbn_payment_method,'rocket'); ?>><?php esc_html_e('Rocket', 'awesome-hotel-booking'); ?></option>
                            <option value="bank" <?php selected($ahbn_payment_method,'bank'); ?>><?php esc_html_e('Bank', 'awesome-hotel-booking'); ?></option>
                        </select>
                    </td>
                </tr>

                <!-- Transaction / Bank Info -->
                <tr class="ahbn_payment_bkash_nagad_rocket">
                    <th><?php esc_html_e('Transaction Phone', 'awesome-hotel-booking'); ?></th>
                    <td><input type="text" name="ahbn_transaction_phone" id="ahbn_transaction_phone" value="<?php echo esc_attr($ahbn_transaction_phone); ?>"></td>
                </tr>
                <tr class="ahbn_payment_bkash_nagad_rocket">
                    <th><?php esc_html_e('Transaction ID', 'awesome-hotel-booking'); ?></th>
                    <td><input type="text" name="ahbn_transaction_id" id="ahbn_transaction_id" value="<?php echo esc_attr($ahbn_transaction_id); ?>"></td>
                </tr>
                <tr class="ahbn_payment_bank">
                    <th><?php esc_html_e('Bank Information', 'awesome-hotel-booking'); ?></th>
                    <td><textarea name="ahbn_bank_info" id="ahbn_bank_info"><?php echo esc_textarea($ahbn_bank_info); ?></textarea></td>
                </tr>
            </tbody>
        </table>

        <!-- Guest Media -->
        <table class="form-table">
            <tbody>
                <tr>
                    <th><?php esc_html_e('Guest Image', 'awesome-hotel-booking'); ?></th>
                    <td>
                        <input type="hidden" name="ahbn_guest_image_id" id="ahbn_guest_image_id" value="<?php echo esc_attr($ahbn_guest_image_id); ?>">
                        <img id="ahbn_guest_image_preview" src="<?php echo $ahbn_guest_image_id ? esc_url(wp_get_attachment_url($ahbn_guest_image_id)) : ''; ?>" style="max-width:150px;"><br>
                        <button type="button" class="button" id="ahbn_guest_image_upload"><?php esc_html_e('Upload Image', 'awesome-hotel-booking'); ?></button>
                        <button type="button" class="button" id="ahbn_guest_image_remove"><?php esc_html_e('Remove', 'awesome-hotel-booking'); ?></button>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Guest NID Image', 'awesome-hotel-booking'); ?></th>
                    <td>
                        <input type="hidden" name="ahbn_guest_nid_id" id="ahbn_guest_nid_id" value="<?php echo esc_attr($ahbn_guest_nid_id); ?>">
                        <img id="ahbn_guest_nid_preview" src="<?php echo $ahbn_guest_nid_id ? esc_url(wp_get_attachment_url($ahbn_guest_nid_id)) : ''; ?>" style="max-width:150px;"><br>
                        <button type="button" class="button" id="ahbn_guest_nid_upload"><?php esc_html_e('Upload NID', 'awesome-hotel-booking'); ?></button>
                        <button type="button" class="button" id="ahbn_guest_nid_remove"><?php esc_html_e('Remove', 'awesome-hotel-booking'); ?></button>
                    </td>
                </tr>
            </tbody>
        </table>

        <?php submit_button($ahbn_edit_booking ? esc_html__('Update Booking', 'awesome-hotel-booking') : esc_html__('Save Booking', 'awesome-hotel-booking'), 'primary', 'ahbn_save_booking'); ?>
    </form>
</div>

<!-- JS for dynamic price and media upload -->
<script>
jQuery(document).ready(function($){
    function updateRoomDetails() {
        var room = $('#ahbn_room_id option:selected');
        $('#ahbn_room_type').val(room.data('type'));
        var price = room.data('price') || 0;
        var days = Math.ceil((new Date($('#ahbn_check_out').val()) - new Date($('#ahbn_check_in').val())) / (1000*60*60*24)) || 1;
        $('#ahbn_days').val(days);
        $('#ahbn_amount').val((price*days).toFixed(2));
        $('#ahbn_room_price_display').text('Price: <?php echo esc_js($ahbn_currency_symbol); ?>' + price);
    }

    $('#ahbn_room_id, #ahbn_check_in, #ahbn_check_out').change(updateRoomDetails);
    updateRoomDetails();

    // Media upload buttons
    function mediaUploader(button_id, input_id, preview_id){
        var file_frame;
        $(button_id).click(function(e){
            e.preventDefault();
            if(file_frame){ file_frame.open(); return; }
            file_frame = wp.media.frames.file_frame = wp.media({
                title: 'Select or Upload',
                button: { text: 'Use this image' }, multiple: false
            });
            file_frame.on('select', function(){
                var attachment = file_frame.state().get('selection').first().toJSON();
                $(input_id).val(attachment.id);
                $(preview_id).attr('src', attachment.url);
            });
            file_frame.open();
        });
    }
    mediaUploader('#ahbn_guest_image_upload','#ahbn_guest_image_id','#ahbn_guest_image_preview');
    mediaUploader('#ahbn_guest_nid_upload','#ahbn_guest_nid_id','#ahbn_guest_nid_preview');

    $('#ahbn_guest_image_remove').click(function(){
        $('#ahbn_guest_image_id').val('');
        $('#ahbn_guest_image_preview').attr('src','');
    });
    $('#ahbn_guest_nid_remove').click(function(){
        $('#ahbn_guest_nid_id').val('');
        $('#ahbn_guest_nid_preview').attr('src','');
    });
});
</script>
