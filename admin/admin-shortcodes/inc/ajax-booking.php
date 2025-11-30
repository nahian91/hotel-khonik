<?php
// =====================================
// AJAX BOOKING HANDLER
// =====================================
add_action('wp_ajax_nopriv_ahbn_ajax_booking','ahbn_ajax_booking');
add_action('wp_ajax_ahbn_ajax_booking','ahbn_ajax_booking');

function ahbn_ajax_booking(){
    check_ajax_referer('hb_add_booking_action','hb_add_booking_nonce');

    $room_id = intval($_POST['room'] ?? 0);
    $customer_name = sanitize_text_field($_POST['customer_name'] ?? '');
    $customer_phone = sanitize_text_field($_POST['customer_phone'] ?? '');
    $checkin = sanitize_text_field($_POST['checkin'] ?? '');
    $checkout = sanitize_text_field($_POST['checkout'] ?? '');
    $payment = sanitize_text_field($_POST['payment'] ?? '');
    $txn_no = sanitize_text_field($_POST['txn_no'] ?? '');

    if(!$room_id || !$customer_name || !$customer_phone){
        wp_send_json(['success'=>false,'message'=>'Please fill in all required fields.']);
    }

    $days = max(1,intval((strtotime($checkout)-strtotime($checkin))/DAY_IN_SECONDS));
    $room_price = floatval(get_post_meta($room_id,'ahbn_price',true));
    $amount = $room_price * $days;

    $booking_data = [
        'post_type'=>'ahbn_booking',
        'post_title'=>$customer_name,
        'post_status'=>'publish'
    ];

    $booking_id = wp_insert_post($booking_data);

    if($booking_id){
        update_post_meta($booking_id,'customer_phone',$customer_phone);
        update_post_meta($booking_id,'room',$room_id);
        update_post_meta($booking_id,'checkin',$checkin);
        update_post_meta($booking_id,'checkout',$checkout);
        update_post_meta($booking_id,'days',$days);
        update_post_meta($booking_id,'amount',$amount);
        update_post_meta($booking_id,'payment',$payment);
        update_post_meta($booking_id,'txn_no',$txn_no);

        wp_send_json(['success'=>true,'message'=>'Booking successful!']);
    }

    wp_send_json(['success'=>false,'message'=>'Failed to save booking.']);
}
