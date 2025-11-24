<?php 

/* -----------------------------
   Admin Menu
----------------------------- */
function hb_admin_menu(){
    // Main menu
    add_menu_page('Hotel Bookings','Hotel Bookings','manage_options','hb_bookings','hb_admin_page','dashicons-admin-home',6);

    // Hidden pages for view/edit booking
    add_submenu_page(null,'View Booking','View Booking','manage_options','hb_view_booking','hb_view_booking_page');
    add_submenu_page(null,'Edit Booking','Edit Booking','manage_options','hb_edit_booking','hb_edit_booking_page');

    // Hidden page for viewing customer details
    add_submenu_page(null,'View Customer','View Customer','manage_options','hb_view_customer','hb_view_customer_page');
}
add_action('admin_menu','hb_admin_menu');

/* -----------------------------
   Admin Page & Tabs
----------------------------- */
function hb_admin_page(){
    // Delete booking
    if(isset($_GET['hb_action']) && $_GET['hb_action']=='delete' && isset($_GET['booking'])){
        $booking_id = intval($_GET['booking']);
        if(check_admin_referer('hb_delete_booking','hb_delete_nonce')){
            wp_delete_post($booking_id,true);
            echo '<div class="notice notice-success is-dismissible"><p>Booking deleted successfully.</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>Security check failed. Booking not deleted.</p></div>';
        }
    }

    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'add_booking';
    ?>
    <div class="wrap">
        <h1>Hotel Bookings</h1>
        <h2 class="nav-tab-wrapper">
            <a href="?page=hb_bookings&tab=add_booking" class="nav-tab <?php echo $active_tab=='add_booking'?'nav-tab-active':''; ?>">Add Booking</a>
            <a href="?page=hb_bookings&tab=all_bookings" class="nav-tab <?php echo $active_tab=='all_bookings'?'nav-tab-active':''; ?>">All Bookings</a>
            <a href="?page=hb_bookings&tab=reports" class="nav-tab <?php echo $active_tab=='reports'?'nav-tab-active':''; ?>">Reports</a>
            <a href="?page=hb_bookings&tab=settings" class="nav-tab <?php echo $active_tab=='settings'?'nav-tab-active':''; ?>">Settings</a>
        </h2>
        <?php
        if($active_tab=='add_booking') {
            hb_add_booking_tab();
        } elseif($active_tab=='all_bookings') {
            hb_all_bookings_tab();
        } elseif($active_tab=='reports') {
            hb_reports_tab(); // This now handles sub-tabs for Revenue & Customers
        } elseif($active_tab=='settings') {
            hb_settings_tab();
        }
        ?>
    </div>
    <script>
    jQuery(document).ready(function($){
        // Payment fields toggle
        function togglePaymentFields(){
            var val = $('#payment').val();
            if(val=='Bkash' || val=='Nagad'){ $('#transaction_wrap').show(); }
            else { $('#transaction_wrap').hide(); }
        }
        togglePaymentFields();
        $('#payment').change(togglePaymentFields);

        // Amount calculation
        function calculateAmount(){
            var checkin = $('#checkin').val();
            var checkout = $('#checkout').val();
            var roomPrice = parseFloat($('#room option:selected').data('price')) || 0;
            if(checkin && checkout){
                var date1 = new Date(checkin);
                var date2 = new Date(checkout);
                var diffTime = date2 - date1;
                var diffDays = Math.ceil(diffTime/(1000*60*60*24));
                diffDays = diffDays>0?diffDays:0;
                $('#days').val(diffDays);
                $('#amount').val(diffDays*roomPrice);
            }
        }
        $('#checkin,#checkout,#room').change(calculateAmount);

        // Delete confirmation
        $('.hb-delete').click(function(e){
            if(!confirm('Are you sure you want to delete this booking?')) e.preventDefault();
        });
    });
    </script>
    <?php
}
