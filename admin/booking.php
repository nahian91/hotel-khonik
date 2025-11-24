<?php 

/* -----------------------------
   Add/Edit Booking Tab (updated)
   - NID is file upload only
   - Select Room shows Room Type only
   - Room No dropdown updates per room type via AJAX
----------------------------- */
function hb_add_booking_tab($edit_booking_id = 0){
    // Pre-fill data if editing
    if($edit_booking_id){
        $booking = get_post($edit_booking_id);
        $customer_name   = $booking->post_title;
        $customer_address= get_post_meta($edit_booking_id,'customer_address',true);
        $customer_phone  = get_post_meta($edit_booking_id,'customer_phone',true);
        $nid_upload      = get_post_meta($edit_booking_id,'custom_nid_upload',true); // attachment ID
        $room_id         = get_post_meta($edit_booking_id,'room',true);
        $room_no         = get_post_meta($edit_booking_id,'room_no',true);
        $checkin         = get_post_meta($edit_booking_id,'checkin',true);
        $checkout        = get_post_meta($edit_booking_id,'checkout',true);
        $days            = get_post_meta($edit_booking_id,'days',true);
        $amount          = get_post_meta($edit_booking_id,'amount',true);
        $payment         = get_post_meta($edit_booking_id,'payment',true);
        $txn_no          = get_post_meta($edit_booking_id,'txn_no',true);
        $payment_phone   = get_post_meta($edit_booking_id,'payment_phone',true);
        $customer_image  = get_post_meta($edit_booking_id,'customer_image',true);
    } else {
        $customer_name = $customer_address = $customer_phone = $nid_upload = $room_id = $room_no = '';
        $checkin = date('Y-m-d'); // today
        $checkout = date('Y-m-d', strtotime('+1 day')); // tomorrow
        $days = 1;
        $amount = 0;
        $payment = $txn_no = $payment_phone = $customer_image = '';
    }

    // Handle form submission
    if( isset($_POST['hb_submit_booking']) && check_admin_referer('hb_add_booking_action','hb_add_booking_nonce') ){
        $customer_name    = sanitize_text_field($_POST['customer_name']);
        $customer_address = sanitize_textarea_field($_POST['customer_address']);
        $customer_phone   = sanitize_text_field($_POST['customer_phone']);
        // NOTE: no text field for NID anymore; we handle file upload below
        $room_id          = intval($_POST['room']);
        $room_no          = sanitize_text_field($_POST['room_no']);
        $checkin          = sanitize_text_field($_POST['checkin']);
        $checkout         = sanitize_text_field($_POST['checkout']);

        // Recalculate days & amount server-side for safety
        $days = 1;
        if( !empty($checkin) && !empty($checkout) && strtotime($checkout) > strtotime($checkin) ){
            $days = intval( (strtotime($checkout) - strtotime($checkin)) / DAY_IN_SECONDS );
            if($days < 1) $days = 1;
        }
        // fetch room price server-side
        $room_price = floatval( get_post_meta($room_id,'room_price',true) );
        $amount = $room_price * $days;

        $payment = sanitize_text_field($_POST['payment']);
        $txn_no = sanitize_text_field($_POST['txn_no']);
        $payment_phone = sanitize_text_field($_POST['payment_phone']);

        $booking_data = [
            'post_type'=>'booking',
            'post_title'=>$customer_name,
            'post_status'=>'publish'
        ];
        if($edit_booking_id) $booking_data['ID'] = $edit_booking_id;

        $booking_id = wp_insert_post($booking_data);

        if($booking_id){
            update_post_meta($booking_id,'customer_address',$customer_address);
            update_post_meta($booking_id,'customer_phone',$customer_phone);
            update_post_meta($booking_id,'room',$room_id);
            update_post_meta($booking_id,'room_no',$room_no);
            update_post_meta($booking_id,'checkin',$checkin);
            update_post_meta($booking_id,'checkout',$checkout);
            update_post_meta($booking_id,'days',$days);
            update_post_meta($booking_id,'amount',$amount);
            update_post_meta($booking_id,'payment',$payment);
            update_post_meta($booking_id,'txn_no',$txn_no);
            update_post_meta($booking_id,'payment_phone',$payment_phone);

            // Handle Customer Image upload (optional)
            if( !empty($_FILES['customer_image']['name']) ){
                require_once(ABSPATH.'wp-admin/includes/file.php');
                require_once(ABSPATH.'wp-admin/includes/media.php');
                require_once(ABSPATH.'wp-admin/includes/image.php');
                $attachment_id = media_handle_upload('customer_image',$booking_id);
                if(is_numeric($attachment_id)) update_post_meta($booking_id,'customer_image',$attachment_id);
            }

            // Handle Customer NID upload (file only, no text)
            if( !empty($_FILES['custom_nid_upload']['name']) ){
                require_once(ABSPATH.'wp-admin/includes/file.php');
                require_once(ABSPATH.'wp-admin/includes/media.php');
                require_once(ABSPATH.'wp-admin/includes/image.php');
                $nid_attachment = media_handle_upload('custom_nid_upload',$booking_id);
                if(is_numeric($nid_attachment)) update_post_meta($booking_id,'custom_nid_upload',$nid_attachment);
            }

            echo '<div class="notice notice-success is-dismissible"><p>Booking saved successfully.</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>Failed to save booking (wp_insert_post returned false).</p></div>';
        }
    }

    // Load rooms
    $rooms = get_posts(['post_type'=>'room','posts_per_page'=>-1]);

    // If editing and room_id set, get its numbers (to pre-fill the dropdown)
    $initial_room_nos = [];
    if($room_id){
        $room_nos_meta = get_post_meta($room_id,'room_no',true);
        if($room_nos_meta) $initial_room_nos = array_map('trim', explode(',', $room_nos_meta) );
    }
    ?>
    <form method="post" enctype="multipart/form-data">
        <?php wp_nonce_field('hb_add_booking_action','hb_add_booking_nonce'); ?>
        <table class="form-table">
            <tr><th>Customer Name</th>
                <td><input type="text" name="customer_name" required class="regular-text" value="<?php echo esc_attr($customer_name); ?>"></td>
            </tr>

            <tr><th>Customer Address</th>
                <td><textarea name="customer_address" class="large-text" rows="3"><?php echo esc_textarea($customer_address); ?></textarea></td>
            </tr>

            <tr><th>Customer Phone</th>
                <td><input type="text" name="customer_phone" class="regular-text" value="<?php echo esc_attr($customer_phone); ?>"></td>
            </tr>

            <tr><th>Customer Image</th>
                <td>
                    <?php if($edit_booking_id && !empty($customer_image)) echo wp_get_attachment_image($customer_image,'medium').'<br>'; ?>
                    <input type="file" name="customer_image" accept="image/*">
                </td>
            </tr>

            <tr><th>Customer NID (upload)</th>
                <td>
                    <?php if($edit_booking_id && !empty($nid_upload)) echo wp_get_attachment_image($nid_upload,'medium').'<br>'; ?>
                    <input type="file" name="custom_nid_upload" accept="image/*,application/pdf">
                    <p class="description">Upload NID image or PDF — no text field required.</p>
                </td>
            </tr>

            <tr><th>Select Room</th>
                <td>
                    <select name="room" id="hb_room_select">
                        <option value="">Select Room Type</option>
                        <?php
                        // Show room_type meta (if you store it); if not, fallback to post_title
                        foreach($rooms as $r):
                            $room_type = get_post_meta($r->ID,'room_type',true);
                            $label = $room_type ? $room_type : $r->post_title;
                            $price = get_post_meta($r->ID,'room_price',true);
                        ?>
                        <option value="<?php echo intval($r->ID); ?>" data-price="<?php echo esc_attr($price); ?>" <?php selected($room_id,$r->ID); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>

            <tr><th>Select Room No</th>
                <td>
                    <select name="room_no" id="hb_room_no">
                        <option value="">Select Room No</option>
                        <?php
                        if(!empty($initial_room_nos)){
                            foreach($initial_room_nos as $no){
                                echo '<option value="'.esc_attr($no).'" '.selected($room_no,$no,false).'>'.esc_html($no).'</option>';
                            }
                        }
                        ?>
                    </select>
                    <p class="description">Room numbers will update when you choose a Room Type.</p>
                </td>
            </tr>

            <tr><th>Check-In</th>
                <td><input type="date" name="checkin" id="hb_checkin" value="<?php echo esc_attr($checkin); ?>" min="<?php echo date('Y-m-d'); ?>"></td>
            </tr>

            <tr><th>Check-Out</th>
                <td><input type="date" name="checkout" id="hb_checkout" value="<?php echo esc_attr($checkout); ?>" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"></td>
            </tr>

            <tr><th>Days</th>
                <td><input type="number" name="days" id="hb_days" readonly value="<?php echo esc_attr($days); ?>"></td>
            </tr>

            <tr><th>Amount</th>
                <td><input type="number" name="amount" id="hb_amount" readonly value="<?php echo esc_attr($amount); ?>"></td>
            </tr>

            <tr><th>Payment</th>
                <td>
                    <select name="payment" id="hb_payment">
                        <option value="Cash" <?php selected($payment,'Cash'); ?>>Cash</option>
                        <option value="Bkash" <?php selected($payment,'Bkash'); ?>>Bkash</option>
                        <option value="Nagad" <?php selected($payment,'Nagad'); ?>>Nagad</option>
                    </select>
                </td>
            </tr>

            <tr id="hb_transaction_wrap" style="display:<?php echo ($payment=='Bkash' || $payment=='Nagad') ? 'table-row' : 'none'; ?>;">
                <th>Phone Number</th>
                <td><input type="text" name="payment_phone" value="<?php echo esc_attr($payment_phone); ?>"></td>
            </tr>

            <tr id="hb_txn_wrap" style="display:<?php echo ($payment=='Bkash' || $payment=='Nagad') ? 'table-row' : 'none'; ?>;">
                <th>Transaction No</th>
                <td><input type="text" name="txn_no" value="<?php echo esc_attr($txn_no); ?>"></td>
            </tr>
        </table>

        <p><input type="submit" name="hb_submit_booking" class="button button-primary" value="Save Booking"></p>
    </form>

    <script>
    jQuery(function($){
        // toggle payment fields
        function togglePayment(){
            var v = $('#hb_payment').val();
            if(v==='Bkash' || v==='Nagad'){ $('#hb_transaction_wrap,#hb_txn_wrap').show(); }
            else { $('#hb_transaction_wrap,#hb_txn_wrap').hide(); }
        }
        $('#hb_payment').on('change', togglePayment);
        togglePayment();

        // load room numbers via AJAX when room changes
        function loadRoomNumbers(roomId, selected){
            $('#hb_room_no').html('<option>Loading...</option>');
            if(!roomId){ $('#hb_room_no').html('<option value="">Select Room No</option>'); return; }
            $.post(ajaxurl, { action:'hb_get_room_numbers', room_id:roomId }, function(res){
                // if server returns <option>... we replace, else expect JSON
                if(res.trim().substr(0,8) === '<option ' || res.trim().substr(0,1)==='<'){
                    $('#hb_room_no').html(res);
                    if(selected){
                        $('#hb_room_no').val(selected);
                    }
                } else {
                    // handle JSON array fallback
                    try{
                        var arr = JSON.parse(res);
                        var html = '<option value="">Select Room No</option>';
                        arr.forEach(function(n){ html += '<option value="'+n+'">'+n+'</option>'; });
                        $('#hb_room_no').html(html);
                        if(selected) $('#hb_room_no').val(selected);
                    }catch(e){
                        $('#hb_room_no').html('<option value="">No numbers</option>');
                    }
                }
            });
        }

        // on room select change
        $('#hb_room_select').on('change', function(){
            var roomId = $(this).val();
            loadRoomNumbers(roomId, '');
            // update amount calculation too
            calculateDaysAndAmount();
        });

        // calculate days & amount
        function calculateDaysAndAmount(){
            var ci = $('#hb_checkin').val();
            var co = $('#hb_checkout').val();
            var price = parseFloat($('#hb_room_select option:selected').data('price')) || 0;
            if(ci && co && new Date(co) > new Date(ci)){
                var diff = Math.ceil((new Date(co) - new Date(ci))/(1000*60*60*24));
                if(diff < 1) diff = 1;
                $('#hb_days').val(diff);
                $('#hb_amount').val(diff * price);
            } else {
                $('#hb_days').val(0);
                $('#hb_amount').val(0);
            }
        }
        $('#hb_checkin,#hb_checkout').on('change', function(){
            // ensure checkout min = checkin + 1
            var ci = $('#hb_checkin').val();
            if(ci){
                var minco = new Date(ci);
                minco.setDate(minco.getDate() + 1);
                var y = minco.getFullYear();
                var m = ('0'+(minco.getMonth()+1)).slice(-2);
                var d = ('0'+minco.getDate()).slice(-2);
                $('#hb_checkout').attr('min', y+'-'+m+'-'+d);
            }
            calculateDaysAndAmount();
        });

        // initial load of room numbers if editing
        <?php if(!empty($room_id)): ?>
            loadRoomNumbers(<?php echo intval($room_id); ?>, <?php echo $room_no ? json_encode($room_no) : '""'; ?>);
        <?php endif; ?>
    });
    </script>
    <?php
}

/* -----------------------------
   AJAX: return room numbers for a room (options HTML)
   Place this in your plugin once (no duplicate)
----------------------------- */
add_action('wp_ajax_hb_get_room_numbers', 'hb_get_room_numbers_ajax');
function hb_get_room_numbers_ajax(){
    $room_id = intval($_POST['room_id'] ?? 0);
    if(!$room_id){ echo '<option value="">Select Room No</option>'; wp_die(); }
    $meta = get_post_meta($room_id,'room_no',true);
    $nos = $meta ? array_map('trim', explode(',', $meta)) : [];
    if(!$nos){ echo '<option value="">No room numbers</option>'; wp_die(); }
    echo '<option value="">Select Room No</option>';
    foreach($nos as $n){
        if($n==='') continue;
        echo '<option value="'.esc_attr($n).'">'.esc_html($n).'</option>';
    }
    wp_die();
}

/* -----------------------------
   All Bookings Tab
----------------------------- */
function hb_all_bookings_tab(){
    $bookings = get_posts(array(
        'post_type'=>'booking',
        'posts_per_page'=>-1,
        'orderby'=>'date',
        'order'=>'DESC'
    ));

    if($bookings){
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr>
            <th>Name</th><th>Check-In</th><th>Actions</th>
        </tr></thead><tbody>';

        foreach($bookings as $b){
            $booking_id = $b->ID;
            $checkin = get_post_meta($booking_id,'checkin',true);

            $view_url = admin_url('admin.php?page=hb_view_booking&booking='.$booking_id);
            $edit_url = admin_url('admin.php?page=hb_edit_booking&booking='.$booking_id);
            $delete_url = wp_nonce_url(admin_url('admin.php?page=hb_bookings&tab=all_bookings&hb_action=delete&booking='.$booking_id),'hb_delete_booking','hb_delete_nonce');

            echo '<tr>
                <td>'.esc_html($b->post_title).'</td>
                <td>'.esc_html($checkin).'</td>
                <td>
                    <a href="'.esc_url($view_url).'" class="button">View</a>
                    <a href="'.esc_url($edit_url).'" class="button">Edit</a>
                    <a href="'.esc_url($delete_url).'" class="button hb-delete">Delete</a>
                </td>
            </tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p>No bookings found.</p>';
    }
    ?>
    <script>
    jQuery(document).ready(function($){
        $('.hb-delete').click(function(e){
            if(!confirm('Are you sure you want to delete this booking?')) e.preventDefault();
        });
    });
    </script>
    <?php
}

/* -----------------------------
   View Booking Page with Tabs
----------------------------- */
function hb_view_booking_page(){
    $booking_id = intval($_GET['booking']);
    $b = get_post($booking_id);

    if(!$b){
        echo '<div class="notice notice-error"><p>Booking not found.</p></div>';
        return;
    }

    $customer_image_id = get_post_meta($booking_id,'customer_image',true);
    $nid_upload_id     = get_post_meta($booking_id,'custom_nid_upload',true);

    echo '<div class="wrap">';
    echo '<h1 class="wp-heading-inline">Booking Details</h1>';

    // Top Tabs
    $active_tab = 'view_booking';
    echo '<h2 class="nav-tab-wrapper">';
        echo '<a href="'.admin_url('admin.php?page=hb_bookings&tab=all_bookings').'" class="nav-tab '.($active_tab=='all_bookings' ? 'nav-tab-active':'').'">All Bookings</a>';
        echo '<a href="'.admin_url('admin.php?page=hb_view_booking&booking='.$booking_id).'" class="nav-tab '.($active_tab=='view_booking' ? 'nav-tab-active':'').'">View Booking</a>';
    echo '</h2>';

    echo '<hr class="wp-header-end">';

    echo '<table class="form-table widefat striped" style="max-width:700px;">';

    echo '<tr><th style="width:180px;">Customer Name</th>
              <td>'.esc_html($b->post_title).'</td></tr>';

    echo '<tr><th>Address</th>
              <td>'.esc_html(get_post_meta($booking_id,'customer_address',true)).'</td></tr>';

    echo '<tr><th>Phone</th>
              <td>'.esc_html(get_post_meta($booking_id,'customer_phone',true)).'</td></tr>';

    echo '<tr><th>Customer Image</th><td>';
        if($customer_image_id){
            echo wp_get_attachment_image($customer_image_id,'medium');
        } else {
            echo '<span style="color:#777;">N/A</span>';
        }
    echo '</td></tr>';

    echo '<tr><th>Custom NID Upload</th><td>';
        if($nid_upload_id){
            echo wp_get_attachment_image($nid_upload_id,'medium');
        } else {
            echo '<span style="color:#777;">N/A</span>';
        }
    echo '</td></tr>';

    echo '<tr><th>Room Type</th>
              <td>'.esc_html(get_the_title(get_post_meta($booking_id,'room',true))).'</td></tr>';

    echo '<tr><th>Check-In</th>
              <td>'.esc_html(get_post_meta($booking_id,'checkin',true)).'</td></tr>';

    echo '<tr><th>Check-Out</th>
              <td>'.esc_html(get_post_meta($booking_id,'checkout',true)).'</td></tr>';

    echo '<tr><th>Total Days</th>
              <td>'.esc_html(get_post_meta($booking_id,'days',true)).'</td></tr>';

    echo '<tr><th>Total Amount</th>
              <td><strong>'.esc_html(get_post_meta($booking_id,'amount',true)).' ৳</strong></td></tr>';

    echo '<tr><th>Payment Method</th>
              <td>'.esc_html(get_post_meta($booking_id,'payment',true)).'</td></tr>';

    echo '<tr><th>Payment Phone</th>
              <td>'.esc_html(get_post_meta($booking_id,'payment_phone',true)).'</td></tr>';

    echo '<tr><th>Transaction No</th>
              <td>'.esc_html(get_post_meta($booking_id,'txn_no',true)).'</td></tr>';

    echo '</table>';

    echo '<p><a href="'.admin_url('admin.php?page=hb_bookings&tab=all_bookings').'" class="button button-primary">Back to All Bookings</a></p>';

    echo '</div>'; // .wrap
}



/* -----------------------------
   Edit Booking Page with Tabs
----------------------------- */
function hb_edit_booking_page(){
    $booking_id = intval($_GET['booking']);
    $b = get_post($booking_id);

    if(!$b){
        echo '<div class="notice notice-error"><p>Booking not found.</p></div>';
        return;
    }

    echo '<div class="wrap">';
    echo '<h1 class="wp-heading-inline">Edit Booking</h1>';

    // Top Tabs
    $active_tab = 'edit_booking';
    echo '<h2 class="nav-tab-wrapper">';
        echo '<a href="'.admin_url('admin.php?page=hb_bookings&tab=all_bookings').'" class="nav-tab '.($active_tab=='all_bookings' ? 'nav-tab-active':'').'">All Bookings</a>';
        echo '<a href="'.admin_url('admin.php?page=hb_edit_booking&booking='.$booking_id).'" class="nav-tab '.($active_tab=='edit_booking' ? 'nav-tab-active':'').'">Edit Booking</a>';
    echo '</h2>';

    echo '<hr class="wp-header-end">';

    // Show Add/Edit Booking form (pre-filled)
    hb_add_booking_tab($booking_id);

    echo '</div>'; // .wrap
}
