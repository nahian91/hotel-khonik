<?php 
function ahbn_overview_tab() {

    // Data
    $total_rooms      = wp_count_posts('ahbn_room')->publish;
    $available_rooms  = count(get_posts([
        'post_type'   => 'ahbn_room',
        'numberposts' => -1,
        'meta_key'    => 'ahbn_availability',
        'meta_value'  => 'Available',
    ]));

    $total_bookings   = wp_count_posts('ahbn_booking')->publish;
    $bookings         = get_posts(['post_type'=>'ahbn_booking','numberposts'=>-1]);
    
    $total_revenue    = 0;
    $pending_bookings = 0;
    $checked_in       = 0;
    $checked_out      = 0;
    $today_revenue    = 0;

    $today = date('Y-m-d');

    foreach ($bookings as $b) {
        $total_amount = floatval(get_post_meta($b->ID,'ahbn_total_amount',true));
        $total_revenue += $total_amount;

        $status = get_post_meta($b->ID, 'ahbn_booking_status', true); // e.g., Pending, Checked-In, Checked-Out
        $checkin_date  = get_post_meta($b->ID, 'ahbn_checkin_date', true);

        if ($status === 'Pending') $pending_bookings++;
        if ($status === 'Checked-In') $checked_in++;
        if ($status === 'Checked-Out') $checked_out++;
        if ($checkin_date === $today) $today_revenue += $total_amount;
    }

    // Occupancy Rate
    $occupied_rooms = $total_rooms - $available_rooms;
    $occupancy_rate = $total_rooms > 0 ? round(($occupied_rooms / $total_rooms) * 100) : 0;

    // Currency symbol
    $currency_code = get_option('ahbn_hotel_currency', 'USD');
    $currency_symbols = [
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'BDT' => '৳',
        'INR' => '₹',
    ];
    $currency_symbol = isset($currency_symbols[$currency_code]) ? $currency_symbols[$currency_code] : '$';
    ?>

    <h2 class="ahbn-overview-title"><?php echo esc_html__('Overview', 'awesome-hotel-booking'); ?></h2>

    <div class="ahbn-overview-cards">
        <div class="ahbn-overview-card"><div class="ahbn-card-icon">🏨</div><h3>Total Rooms</h3><p class="ahbn-overview-value"><?php echo intval($total_rooms); ?></p></div>
        <div class="ahbn-overview-card"><div class="ahbn-card-icon">🟢</div><h3>Available Rooms</h3><p class="ahbn-overview-value"><?php echo intval($available_rooms); ?></p></div>
        <div class="ahbn-overview-card"><div class="ahbn-card-icon">📋</div><h3>Total Bookings</h3><p class="ahbn-overview-value"><?php echo intval($total_bookings); ?></p></div>
        <div class="ahbn-overview-card"><div class="ahbn-card-icon">💰</div><h3>Total Revenue</h3><p class="ahbn-overview-value"><?php echo esc_html($currency_symbol . number_format($total_revenue,2)); ?></p></div>
        <div class="ahbn-overview-card"><div class="ahbn-card-icon">⏳</div><h3>Pending Bookings</h3><p class="ahbn-overview-value"><?php echo intval($pending_bookings); ?></p></div>
        <div class="ahbn-overview-card"><div class="ahbn-card-icon">🏢</div><h3>Checked-In Guests</h3><p class="ahbn-overview-value"><?php echo intval($checked_in); ?></p></div>
        <div class="ahbn-overview-card"><div class="ahbn-card-icon">✅</div><h3>Checked-Out Guests</h3><p class="ahbn-overview-value"><?php echo intval($checked_out); ?></p></div>
        <div class="ahbn-overview-card"><div class="ahbn-card-icon">📅</div><h3>Today's Revenue</h3><p class="ahbn-overview-value"><?php echo esc_html($currency_symbol . number_format($today_revenue,2)); ?></p></div>
        <div class="ahbn-overview-card"><div class="ahbn-card-icon">📊</div><h3>Occupancy Rate</h3><p class="ahbn-overview-value"><?php echo esc_html($occupancy_rate . '%'); ?></p></div>
    </div>

<?php
}
