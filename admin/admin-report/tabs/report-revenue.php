<?php
// Fetch all bookings
$ahbn_bookings = get_posts([
    'post_type'   => 'ahbn_booking',
    'numberposts' => -1,
    'post_status' => 'publish',
    'orderby'     => 'ID',
    'order'       => 'DESC'
]);

// Currency mapping from settings
$ahbn_currency_codes = [
    'USD' => '$',
    'EUR' => '€',
    'GBP' => '£',
    'BDT' => '৳',
    'INR' => '₹',
];

// Get selected currency from General Settings
$ahbn_currency_code = get_option('ahbn_hotel_currency', 'USD'); 
$ahbn_currency = $ahbn_currency_codes[$ahbn_currency_code] ?? '$';

// Initialize totals
$ahbn_total_revenue   = 0;
$ahbn_total_bookings  = count($ahbn_bookings);
$ahbn_total_days      = 0;

// Calculate totals
foreach ($ahbn_bookings as $ahbn_b) {
    $ahbn_total_revenue += floatval(get_post_meta($ahbn_b->ID, 'amount', true));
    $ahbn_total_days     += intval(get_post_meta($ahbn_b->ID, 'days', true));
}

// Calculate average revenue per booking
$ahbn_avg_revenue = $ahbn_total_bookings ? $ahbn_total_revenue / $ahbn_total_bookings : 0;

// Display cards
echo '<div style="display:flex; gap:20px; flex-wrap:wrap;">';

$ahbn_cards = [
    ['label' => 'Total Revenue', 'value' => $ahbn_currency . number_format($ahbn_total_revenue, 2), 'bg' => '#28a745'],
    ['label' => 'Total Bookings', 'value' => $ahbn_total_bookings, 'bg' => '#0073aa'],
    ['label' => 'Average Revenue per Booking', 'value' => $ahbn_currency . number_format($ahbn_avg_revenue, 2), 'bg' => '#ffc107'],
];

foreach ($ahbn_cards as $ahbn_card) {
    echo '<div style="flex:1; min-width:200px; background:' . esc_attr($ahbn_card['bg']) . '; color:#fff; padding:20px; border-radius:10px; text-align:center; box-shadow:0 4px 8px rgba(0,0,0,0.1);">
            <h3 style="margin:0;font-size:2em;">' . esc_html($ahbn_card['value']) . '</h3>
            <p style="margin-top:5px;font-weight:500;">' . esc_html($ahbn_card['label']) . '</p>
          </div>';
}

echo '</div>';
?>
