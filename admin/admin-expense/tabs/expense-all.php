<?php
// ----------------------------
// All Expenses Tab
// ----------------------------
if (!defined('ABSPATH')) exit;

// Delete expense
if (isset($_GET['delete_expense'])) {
    $eid = intval($_GET['delete_expense']);
    if ($eid) {
        wp_delete_post($eid, true);
        echo '<div class="notice notice-success"><p>Expense deleted successfully!</p></div>';
    }
}

// Fetch all expenses
$expenses = get_posts([
    'post_type'   => 'ahbn_expense',
    'numberposts' => -1,
    'orderby'     => 'ID',
    'order'       => 'DESC'
]);

// Currency symbol
$currency = get_option('ahbn_default_currency', '$');
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
            <?php if (!empty($expenses)) { 
                foreach ($expenses as $e) { 
                    $amount   = get_post_meta($e->ID, 'ahbn_amount', true);
                    $category = get_post_meta($e->ID, 'ahbn_category', true);
                    $assign   = get_post_meta($e->ID, 'ahbn_assign', true);
                    $date     = get_post_meta($e->ID, 'ahbn_date', true);
                ?>
                <tr>
                    <td><?php echo esc_html($e->ID); ?></td>
                    <td><?php echo esc_html($e->post_title); ?></td>
                    <td><?php echo esc_html($currency . number_format((float)$amount, 2)); ?></td>
                    <td><?php echo esc_html($category); ?></td>
                    <td><?php echo esc_html($assign); ?></td>
                    <td><?php echo esc_html($date); ?></td>
                    <td>
                        <a href="?page=ahbn_booking_main&tab=expenses&sub_tab=add&edit_expense=<?php echo esc_attr($e->ID); ?>">Edit</a> | 
                        <a href="?page=ahbn_booking_main&tab=expenses&sub_tab=all&delete_expense=<?php echo esc_attr($e->ID); ?>" onclick="return confirm('Are you sure you want to delete this expense?')">Delete</a>
                    </td>
                </tr>
            <?php } 
            } else { ?>
                <tr><td colspan="7" style="text-align:center;">No expenses found.</td></tr>
            <?php } ?>
        </tbody>
    </table>
</div>
