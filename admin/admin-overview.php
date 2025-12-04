<?php 
function ahbn_overview_tab() {

    // Fetch room and booking data
    $total_rooms = wp_count_posts('ahbn_room')->publish;

    $available_rooms = count(get_posts([
        'post_type'   => 'ahbn_room',
        'numberposts' => -1,
        'meta_key'    => 'ahbn_availability',
        'meta_value'  => 'Available',
        'fields'      => 'ids',
    ]));

    $total_bookings = wp_count_posts('ahbn_booking')->publish;
    $bookings = get_posts([
        'post_type'   => 'ahbn_booking',
        'numberposts' => -1,
        'fields'      => 'ids',
    ]);

    $stats = [
        'total_revenue' => 0,
        'today_revenue' => 0,
        'pending'       => 0,
        'checked_in'    => 0,
        'checked_out'   => 0,
    ];

$today = current_time('Y-m-d'); // Use WP timezone-safe current time
foreach ($bookings as $booking_id) {
    $meta = get_post_meta($booking_id);
    $amount = floatval($meta['ahbn_total_amount'][0] ?? 0);
    $status = $meta['ahbn_booking_status'][0] ?? '';
    $checkin_date = $meta['ahbn_checkin_date'][0] ?? '';

    $stats['total_revenue'] += $amount;
    if ($checkin_date === $today) $stats['today_revenue'] += $amount;

    switch ($status) {
        case 'Pending': $stats['pending']++; break;
        case 'Checked-In': $stats['checked_in']++; break;
        case 'Checked-Out': $stats['checked_out']++; break;
    }
}

    $occupied_rooms = $total_rooms - $available_rooms;
    $occupancy_rate = $total_rooms > 0 ? round(($occupied_rooms / $total_rooms) * 100) : 0;

    $currency_code = get_option('ahbn_hotel_currency', 'USD');
    $currency_symbols = ['USD'=>'$', 'EUR'=>'€', 'GBP'=>'£', 'BDT'=>'৳','INR'=>'₹'];
    $currency_symbol = $currency_symbols[$currency_code] ?? '$';
    ?>

    <h2 class="ahbn-overview-title"><?php echo esc_html__('Hotel Dashboard Overview', 'awesome-hotel-booking'); ?></h2>

    <div class="ahbn-overview-grid">
        <?php 
        $cards = [
            ['icon'=>'🏨','title'=>'Total Rooms','value'=>$total_rooms,'color'=>'#6c5ce7'],
            ['icon'=>'🟢','title'=>'Available Rooms','value'=>$available_rooms,'color'=>'#00b894'],
            ['icon'=>'📋','title'=>'Total Bookings','value'=>$total_bookings,'color'=>'#0984e3'],
            ['icon'=>'💰','title'=>'Total Revenue','value'=>$currency_symbol . number_format($stats['total_revenue'],2),'color'=>'#fdcb6e'],
            ['icon'=>'⏳','title'=>'Pending Bookings','value'=>$stats['pending'],'color'=>'#fd79a8'],
            ['icon'=>'🏢','title'=>'Checked-In Guests','value'=>$stats['checked_in'],'color'=>'#00cec9'],
            ['icon'=>'✅','title'=>'Checked-Out Guests','value'=>$stats['checked_out'],'color'=>'#55efc4'],
            ['icon'=>'📅','title'=>"Today's Revenue",'value'=>$currency_symbol . number_format($stats['today_revenue'],2),'color'=>'#e17055'],
            ['icon'=>'📊','title'=>'Occupancy Rate','value'=>$occupancy_rate.'%','color'=>'#d63031'],
        ];

        foreach ($cards as $card) : ?>
            <div class="ahbn-card" style="border-top: 5px solid <?php echo esc_attr($card['color']); ?>;">
                <div class="ahbn-card-icon" style="background: <?php echo esc_attr($card['color']); ?>33;"><?php echo esc_html($card['icon']); ?></div>
                <div class="ahbn-card-content">
                    <h3 class="ahbn-card-title"><?php echo esc_html($card['title']); ?></h3>
                    <p class="ahbn-card-value"><?php echo esc_html($card['value']); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <style>
.ahbn-overview-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: #222;
    margin-bottom: 25px;
}
.ahbn-overview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit,minmax(220px,1fr));
    gap: 20px;
}
.ahbn-card {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    gap: 15px;
    transition: transform 0.3s, box-shadow 0.3s;
}
.ahbn-card:hover {
    transform: translateY(-7px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.15);
}
.ahbn-card-icon {
    font-size: 2.8rem;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    flex-shrink: 0;
}
.ahbn-card-content {
    flex: 1;
}
.ahbn-card-title {
    font-size: 0.9rem; /* smaller title */
    font-weight: 500;
    color: #555;
    margin: 0 0 5px;
}
.ahbn-card-value {
    font-size: 2.2rem; /* bigger value */
    font-weight: 800;
    color: #222;
    margin: 0;
}
@media (max-width:768px){
    .ahbn-overview-grid { grid-template-columns: repeat(auto-fit,minmax(180px,1fr)); }
    .ahbn-card-icon { width: 50px; height: 50px; font-size: 2.2rem; }
    .ahbn-card-value { font-size: 2rem; }
    .ahbn-card-title { font-size: 0.8rem; }
}
</style>

<?php
}
?>
