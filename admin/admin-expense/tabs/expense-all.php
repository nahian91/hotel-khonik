<?php
// ----------------------------
// All Expenses Tab
// ----------------------------
if (!defined('ABSPATH')) exit;

// Delete expense
if (isset($_GET['ahbn_delete_expense'])) {
    $ahbn_eid = intval($_GET['ahbn_delete_expense']);
    if ($ahbn_eid) {
        wp_delete_post($ahbn_eid, true);
        echo '<div class="notice notice-success"><p>Expense deleted successfully!</p></div>';
    }
}

// Fetch all expenses
$ahbn_expenses = get_posts([
    'post_type'   => 'ahbn_expense',
    'numberposts' => -1,
    'orderby'     => 'ID',
    'order'       => 'DESC'
]);

// Currency symbol mapping
$ahbn_currency_symbols = [
    'USD' => '$',
    'EUR' => '€',
    'GBP' => '£',
    'BDT' => '৳',
    'INR' => '₹',
];

// Get currency from General Settings
$ahbn_currency_code = get_option('ahbn_hotel_currency', 'USD');
$ahbn_currency      = $ahbn_currency_symbols[$ahbn_currency_code] ?? '$';
?>

<div class="wrap">
    <h2>All Expenses</h2>
    <table class="widefat striped">
        <thead>
            <tr>
                <th width="50">ID</th>
                <th>Title</th>
                <th width="100">Amount</th>
                <th>Category</th>
                <th>Assign</th>
                <th width="120">Date</th>
                <th width="120">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($ahbn_expenses)) { 
                foreach ($ahbn_expenses as $ahbn_e) { 
                    $ahbn_amount   = floatval(get_post_meta($ahbn_e->ID, 'ahbn_amount', true));
                    $ahbn_category = get_post_meta($ahbn_e->ID, 'ahbn_category', true) ?: '-';
                    $ahbn_assign   = get_post_meta($ahbn_e->ID, 'ahbn_assign', true) ?: '-';
                    $ahbn_date     = get_post_meta($ahbn_e->ID, 'ahbn_date', true) ?: '-';
                ?>
                <tr>
                    <td><?php echo esc_html($ahbn_e->ID); ?></td>
                    <td><?php echo esc_html($ahbn_e->post_title); ?></td>
                    <td><?php echo esc_html($ahbn_currency . number_format($ahbn_amount, 2)); ?></td>
                    <td><?php echo esc_html($ahbn_category); ?></td>
                    <td><?php echo esc_html($ahbn_assign); ?></td>
                    <td><?php echo esc_html($ahbn_date); ?></td>
                    <td>
                        <a href="?page=ahbn_booking_main&tab=expenses&sub_tab=add&ahbn_edit_expense=<?php echo esc_attr($ahbn_e->ID); ?>">Edit</a> | 
                        <a href="?page=ahbn_booking_main&tab=expenses&sub_tab=all&ahbn_delete_expense=<?php echo esc_attr($ahbn_e->ID); ?>" onclick="return confirm('Are you sure you want to delete this expense?')">Delete</a>
                    </td>
                </tr>
            <?php } 
            } else { ?>
                <tr><td colspan="7" style="text-align:center;">No expenses found.</td></tr>
            <?php } ?>
        </tbody>
    </table>
</div>
