<?php
if (!defined('ABSPATH')) exit;

// =====================================
// SINGLE ROOM VIEW + ADVANCED BOOKING FORM
// =====================================
add_shortcode('ahbn_single_room', function($atts){
    $atts = shortcode_atts([
        'room_id' => 0
    ], $atts);

    // Get room_id from URL if not in shortcode
    $room_id = isset($_GET['room_id']) && intval($_GET['room_id']) > 0 ? intval($_GET['room_id']) : intval($atts['room_id']);
    if(!$room_id) return '<p>'.esc_html__('Room not found.', 'awesome-hotel-booking').'</p>';

    $room_post = get_post($room_id);
    if(!$room_post || $room_post->post_type !== 'ahbn_room') return '<p>'.esc_html__('Room not found.', 'awesome-hotel-booking').'</p>';

    // Currency
    $hotel_currency = get_option('ahbn_hotel_currency', 'USD');
    $currency_symbols = ['USD'=>'$', 'EUR'=>'€', 'GBP'=>'£', 'BDT'=>'৳', 'INR'=>'₹'];
    $currency_symbol = $currency_symbols[$hotel_currency] ?? '$';

    // Room meta
    $price       = floatval(get_post_meta($room_id, 'ahbn_price', true));
    $room_type   = get_post_meta($room_id, 'ahbn_room_type', true);
    $amenities   = get_post_meta($room_id, 'ahbn_amenities', true);
    
    // Use plugin-prefixed content filter to avoid 'the_content' warnings
    $description = apply_filters('ahbn_room_content', $room_post->post_content);

    $amenities_arr = !empty($amenities) ? (is_array($amenities) ? $amenities : explode(',', $amenities)) : [];

    // Featured image
    $img_url = has_post_thumbnail($room_id) ? get_the_post_thumbnail_url($room_id, 'large') : 'https://placehold.co/800x600?text=Room+Image';

    // Handle booking form submission
    $booking_message = '';
    if(isset($_POST['ahbn_save_booking'])){
        if(isset($_POST['_wpnonce']) && check_admin_referer('ahbn_save_booking_verify')){
            $booking_id        = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
            $customer_name     = isset($_POST['customer_name']) ? sanitize_text_field(wp_unslash($_POST['customer_name'])) : '';
            $customer_email    = isset($_POST['customer_email']) ? sanitize_email(wp_unslash($_POST['customer_email'])) : '';
            $customer_phone    = isset($_POST['customer_phone']) ? sanitize_text_field(wp_unslash($_POST['customer_phone'])) : '';
            $customer_address  = isset($_POST['customer_address']) ? sanitize_textarea_field(wp_unslash($_POST['customer_address'])) : '';
            $room_id_post      = isset($_POST['room_id']) ? intval($_POST['room_id']) : $room_id;
            $room_type_post    = isset($_POST['room_type']) ? sanitize_text_field(wp_unslash($_POST['room_type'])) : '';
            $check_in          = isset($_POST['check_in']) ? sanitize_text_field(wp_unslash($_POST['check_in'])) : '';
            $check_out         = isset($_POST['check_out']) ? sanitize_text_field(wp_unslash($_POST['check_out'])) : '';
            $status            = isset($_POST['status']) ? sanitize_text_field(wp_unslash($_POST['status'])) : '';
            $payment_method    = isset($_POST['payment_method']) ? sanitize_text_field(wp_unslash($_POST['payment_method'])) : '';
            $transaction_phone = isset($_POST['transaction_phone']) ? sanitize_text_field(wp_unslash($_POST['transaction_phone'])) : '';
            $transaction_id    = isset($_POST['transaction_id']) ? sanitize_text_field(wp_unslash($_POST['transaction_id'])) : '';
            $bank_info         = isset($_POST['bank_info']) ? sanitize_textarea_field(wp_unslash($_POST['bank_info'])) : '';
            $days              = isset($_POST['days']) ? intval($_POST['days']) : 0;
            $amount            = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
            $guest_image_id    = isset($_POST['guest_image_id']) ? intval($_POST['guest_image_id']) : 0;
            $guest_nid_id      = isset($_POST['guest_nid_id']) ? intval($_POST['guest_nid_id']) : 0;

            $data = [
                'post_title'  => $customer_name,
                'post_type'   => 'ahbn_booking',
                'post_status' => 'publish'
            ];

            if($booking_id > 0){
                wp_update_post(array_merge($data, ['ID'=>$booking_id]));
            } else {
                $booking_id = wp_insert_post($data);
            }

            // Save meta
            update_post_meta($booking_id,'ahbn_customer_email',$customer_email);
            update_post_meta($booking_id,'ahbn_customer_phone',$customer_phone);
            update_post_meta($booking_id,'ahbn_customer_address',$customer_address);
            update_post_meta($booking_id,'ahbn_room_id',$room_id_post);
            update_post_meta($booking_id,'ahbn_room_type',$room_type_post);
            update_post_meta($booking_id,'ahbn_check_in',$check_in);
            update_post_meta($booking_id,'ahbn_check_out',$check_out);
            update_post_meta($booking_id,'ahbn_status',$status);
            update_post_meta($booking_id,'ahbn_payment_method',$payment_method);
            update_post_meta($booking_id,'ahbn_transaction_phone',$transaction_phone);
            update_post_meta($booking_id,'ahbn_transaction_id',$transaction_id);
            update_post_meta($booking_id,'ahbn_bank_info',$bank_info);
            update_post_meta($booking_id,'ahbn_days',$days);
            update_post_meta($booking_id,'ahbn_amount',$amount);
            update_post_meta($booking_id,'ahbn_guest_image',$guest_image_id);
            update_post_meta($booking_id,'ahbn_guest_nid',$guest_nid_id);

            $booking_message = '<div class="ahbn-success">'.esc_html__('Booking saved successfully!', 'awesome-hotel-booking').'</div>';

        } else {
            $booking_message = '<div class="ahbn-error">'.esc_html__('Security check failed. Booking not saved.', 'awesome-hotel-booking').'</div>';
        }
    }

    // Load rooms for dropdown
    $rooms = get_posts(['post_type'=>'ahbn_room','numberposts'=>-1]);

    ob_start();
    ?>
    <div class="ahbn-single-room">
        <div class="ahbn-single-room-image">
            <img src="<?php echo esc_url($img_url); ?>" style="width:100%;border-radius:5px;">
        </div>
        <h2><?php echo esc_html($room_post->post_title); ?></h2>
        <span><?php echo esc_html__('Price: ', 'awesome-hotel-booking') . esc_html($currency_symbol.number_format($price,2)); ?></span>
        <span><?php echo esc_html__('Type: ', 'awesome-hotel-booking') . esc_html($room_type); ?></span>
        <div><?php echo esc_html($description); ?></div>
        <h4><?php echo esc_html__('Amenities', 'awesome-hotel-booking'); ?></h4>
        <ul>
            <?php foreach($amenities_arr as $a) echo '<li>'.esc_html(trim($a)).'</li>'; ?>
        </ul>

        <h3><?php echo esc_html__('Book This Room', 'awesome-hotel-booking'); ?></h3>
        <?php echo esc_html($booking_message); ?>
        <form method="post">
            <?php wp_nonce_field('ahbn_save_booking_verify'); ?>
            <input type="hidden" name="booking_id" value="">
            <table class="form-table">
                <tbody>
                    <tr><th><?php echo esc_html__('Name', 'awesome-hotel-booking'); ?></th><td><input type="text" name="customer_name" required></td></tr>
                    <tr><th><?php echo esc_html__('Email', 'awesome-hotel-booking'); ?></th><td><input type="email" name="customer_email"></td></tr>
                    <tr><th><?php echo esc_html__('Phone', 'awesome-hotel-booking'); ?></th><td><input type="text" name="customer_phone"></td></tr>
                    <tr><th><?php echo esc_html__('Address', 'awesome-hotel-booking'); ?></th><td><textarea name="customer_address" rows="3"></textarea></td></tr>
                    <tr>
                        <th><?php echo esc_html__('Room', 'awesome-hotel-booking'); ?></th>
                        <td>
                            <select name="room_id" id="room_id">
                                <?php foreach($rooms as $r):
                                    $r_price = get_post_meta($r->ID,'ahbn_price',true);
                                    $r_type  = get_post_meta($r->ID,'ahbn_room_type',true);
                                ?>
                                <option value="<?php echo esc_attr($r->ID); ?>" data-price="<?php echo esc_attr($r_price); ?>" data-type="<?php echo esc_attr($r_type); ?>" <?php selected($room_id,$r->ID); ?>>
                                    <?php echo esc_html($r->post_title . ' (' . $r_type . ') - '.$currency_symbol.number_format($r_price,2)); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div id="room_price_display" style="font-weight:bold;margin-top:5px;"></div>
                        </td>
                    </tr>
                    <tr><th><?php echo esc_html__('Room Type', 'awesome-hotel-booking'); ?></th><td><input type="text" name="room_type" id="room_type" readonly></td></tr>
                    <tr><th><?php echo esc_html__('Check In', 'awesome-hotel-booking'); ?></th><td><input type="date" name="check_in" id="check_in"></td></tr>
                    <tr><th><?php echo esc_html__('Check Out', 'awesome-hotel-booking'); ?></th><td><input type="date" name="check_out" id="check_out"></td></tr>
                    <tr><th><?php echo esc_html__('Status', 'awesome-hotel-booking'); ?></th><td>
                        <select name="status">
                            <option value="pending"><?php echo esc_html__('Pending', 'awesome-hotel-booking'); ?></option>
                            <option value="confirmed"><?php echo esc_html__('Confirmed', 'awesome-hotel-booking'); ?></option>
                            <option value="canceled"><?php echo esc_html__('Canceled', 'awesome-hotel-booking'); ?></option>
                        </select>
                    </td></tr>
                    <tr><th><?php echo esc_html__('Days', 'awesome-hotel-booking'); ?></th><td><input type="number" name="days" id="days" readonly></td></tr>
                    <tr><th><?php echo esc_html__('Amount', 'awesome-hotel-booking'); ?></th><td><input type="text" name="amount" id="amount" readonly></td></tr>
                    <tr><th><?php echo esc_html__('Payment Method', 'awesome-hotel-booking'); ?></th><td>
                        <select name="payment_method" id="payment_method">
                            <option value=""><?php echo esc_html__('Select Method', 'awesome-hotel-booking'); ?></option>
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="online">Online</option>
                            <option value="bkash">Bkash</option>
                            <option value="nagad">Nagad</option>
                            <option value="rocket">Rocket</option>
                            <option value="bank">Bank</option>
                        </select>
                    </td></tr>
                    <tr class="payment-bkash-nagad-rocket"><th><?php echo esc_html__('Transaction Phone', 'awesome-hotel-booking'); ?></th><td><input type="text" name="transaction_phone" id="transaction_phone"></td></tr>
                    <tr class="payment-bkash-nagad-rocket"><th><?php echo esc_html__('Transaction ID', 'awesome-hotel-booking'); ?></th><td><input type="text" name="transaction_id" id="transaction_id"></td></tr>
                    <tr class="payment-bank"><th><?php echo esc_html__('Bank Info', 'awesome-hotel-booking'); ?></th><td><textarea name="bank_info" id="bank_info"></textarea></td></tr>
                    <tr><th><?php echo esc_html__('Guest Image', 'awesome-hotel-booking'); ?></th><td>
                        <input type="hidden" name="guest_image_id" id="guest_image_id">
                        <img id="guest_image_preview" style="max-width:150px; display:block; margin-bottom:5px;">
                        <button class="button" id="guest_image_button"><?php echo esc_html__('Select/Upload Image', 'awesome-hotel-booking'); ?></button>
                        <button class="button" id="guest_image_remove"><?php echo esc_html__('Remove', 'awesome-hotel-booking'); ?></button>
                    </td></tr>
                    <tr><th><?php echo esc_html__('Guest NID / Passport', 'awesome-hotel-booking'); ?></th><td>
                        <input type="hidden" name="guest_nid_id" id="guest_nid_id">
                        <img id="guest_nid_preview" style="max-width:150px; display:block; margin-bottom:5px;">
                        <button class="button" id="guest_nid_button"><?php echo esc_html__('Select/Upload NID', 'awesome-hotel-booking'); ?></button>
                        <button class="button" id="guest_nid_remove"><?php echo esc_html__('Remove', 'awesome-hotel-booking'); ?></button>
                    </td></tr>
                </tbody>
            </table>
            <p><input type="submit" name="ahbn_save_booking" class="button button-primary" value="<?php echo esc_attr__('Book Now', 'awesome-hotel-booking'); ?>"></p>
        </form>
    </div>

    <script>
    jQuery(document).ready(function($){
        const currency = "<?php echo esc_js($currency_symbol); ?>";

        function updateRoomTypeAndAmount(){
            const selected = $('#room_id option:selected');
            const price = parseFloat(selected.data('price')) || 0;
            $('#room_type').val(selected.data('type') || '');

            let checkInDate = new Date($('#check_in').val());
            let checkOutDate = new Date($('#check_out').val());
            if(isNaN(checkInDate)) checkInDate = new Date();
            if(isNaN(checkOutDate) || checkOutDate<=checkInDate) checkOutDate = new Date(checkInDate.getTime()+86400000);

            let diffDays = Math.ceil((checkOutDate-checkInDate)/(1000*60*60*24));
            if(diffDays<1) diffDays=1;

            $('#days').val(diffDays);
            $('#amount').val((price*diffDays).toFixed(2));
            $('#room_price_display').text('Price: '+currency+price.toFixed(2));
        }

        $('#room_id, #check_in, #check_out').on('change', updateRoomTypeAndAmount);
        updateRoomTypeAndAmount();

        // Payment method fields
        function updatePaymentFields(){
            const method = $('#payment_method').val();
            if(['bkash','nagad','rocket'].includes(method)){
                $('.payment-bkash-nagad-rocket').show();
                $('.payment-bank').hide();
            } else if(method==='bank'){
                $('.payment-bkash-nagad-rocket').hide();
                $('.payment-bank').show();
            } else{
                $('.payment-bkash-nagad-rocket,.payment-bank').hide();
            }
        }
        $('#payment_method').on('change', updatePaymentFields);
        updatePaymentFields();

        // Media uploader
        function mediaUploader(buttonId, hiddenInput, previewImg){
            var frame;
            $(buttonId).on('click', function(e){
                e.preventDefault();
                if(frame) frame.open();
                frame = wp.media({ title:'Select', button:{text:'Use this'}, multiple:false });
                frame.on('select', function(){
                    var attachment = frame.state().get('selection').first().toJSON();
                    $(hiddenInput).val(attachment.id);
                    $(previewImg).attr('src', attachment.url);
                });
                frame.open();
            });
        }
        mediaUploader('#guest_image_button','#guest_image_id','#guest_image_preview');
        mediaUploader('#guest_nid_button','#guest_nid_id','#guest_nid_preview');

        $('#guest_image_remove').on('click', function(e){ e.preventDefault(); $('#guest_image_id').val(''); $('#guest_image_preview').attr('src',''); });
        $('#guest_nid_remove').on('click', function(e){ e.preventDefault(); $('#guest_nid_id').val(''); $('#guest_nid_preview').attr('src',''); });
    });
    </script>
    <?php
    return ob_get_clean();
});
?>
