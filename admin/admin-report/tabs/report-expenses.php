<?php
// Fetch all expenses
$ahbn_expenses = get_posts([
    'post_type'   => 'ahbn_expense',
    'numberposts' => -1,
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

// Display total expenses
echo '<h3>Total Expenses: ' . count($ahbn_expenses) . '</h3>';

// Table
echo '<table class="widefat striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Amount</th>
                <th>Category</th>
                <th>Assign</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>';

if (!empty($ahbn_expenses)) {
    foreach ($ahbn_expenses as $ahbn_e) {
        $ahbn_amount   = floatval(get_post_meta($ahbn_e->ID, 'ahbn_amount', true));
        $ahbn_category = get_post_meta($ahbn_e->ID, 'ahbn_category', true) ?: '-';
        $ahbn_assign   = get_post_meta($ahbn_e->ID, 'ahbn_assign', true) ?: '-';
        $ahbn_date     = get_post_meta($ahbn_e->ID, 'ahbn_date', true) ?: '-';

        echo '<tr>
                <td>' . esc_html($ahbn_e->ID) . '</td>
                <td>' . esc_html($ahbn_e->post_title) . '</td>
                <td>' . esc_html($ahbn_currency . number_format($ahbn_amount, 2)) . '</td>
                <td>' . esc_html($ahbn_category) . '</td>
                <td>' . esc_html($ahbn_assign) . '</td>
                <td>' . esc_html($ahbn_date) . '</td>
              </tr>';
    }
} else {
    echo '<tr><td colspan="6">No expenses found.</td></tr>';
}

echo '</tbody></table>';
?>
