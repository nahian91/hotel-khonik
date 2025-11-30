<?php
$bookings = get_posts([
    'post_type'=>'ahbn_booking',
    'numberposts'=>-1,
    'post_status'=>'publish',
    'orderby'=>'ID',
    'order'=>'DESC'
]);

$currency = get_option('ahbn_room_currency','$');

$total_revenue = 0;
$total_bookings = count($bookings);
$total_days = 0;

foreach($bookings as $b){
    $total_revenue += floatval(get_post_meta($b->ID,'amount',true));
    $total_days += intval(get_post_meta($b->ID,'days',true));
}

$avg_revenue = $total_bookings ? $total_revenue / $total_bookings : 0;

echo '<div style="display:flex; gap:20px; flex-wrap:wrap;">';
$cards = [
    ['label'=>'Total Revenue', 'value'=>$currency.number_format($total_revenue,2), 'bg'=>'#28a745'],
    ['label'=>'Total Bookings', 'value'=>$total_bookings, 'bg'=>'#0073aa'],
    ['label'=>'Average Revenue per Booking', 'value'=>$currency.number_format($avg_revenue,2), 'bg'=>'#ffc107'],
];
foreach($cards as $card){
    echo '<div style="flex:1; min-width:200px; background:'.$card['bg'].'; color:#fff; padding:20px; border-radius:10px; text-align:center; box-shadow:0 4px 8px rgba(0,0,0,0.1);">
            <h3 style="margin:0;font-size:2em;">'.esc_html($card['value']).'</h3>
            <p style="margin-top:5px;font-weight:500;">'.esc_html($card['label']).'</p>
          </div>';
}
echo '</div>';
