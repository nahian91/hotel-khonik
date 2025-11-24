<?php
/* -----------------------------
   Expenses Page for Hotel Booking Plugin
----------------------------- */

$sub_tab = isset($_GET['sub_tab']) ? $_GET['sub_tab'] : 'add';

// ---------- NAV TABS ----------
echo '<h2 class="nav-tab-wrapper">';
echo '<a href="?page=hb_bookings&tab=expenses&sub_tab=add" class="nav-tab '.($sub_tab=='add'?'nav-tab-active':'').'">Add Expense</a>';
echo '<a href="?page=hb_bookings&tab=expenses&sub_tab=all" class="nav-tab '.($sub_tab=='all'?'nav-tab-active':'').'">All Expenses</a>';
echo '<a href="?page=hb_bookings&tab=expenses&sub_tab=settings" class="nav-tab '.($sub_tab=='settings'?'nav-tab-active':'').'">Settings</a>';
echo '</h2>';

// ---------- DELETE ----------
if(isset($_GET['hb_action'], $_GET['expense_id']) && $_GET['hb_action']=='delete'){
    $expense_id = intval($_GET['expense_id']);
    if(check_admin_referer('hb_delete_expense','hb_delete_expense_nonce')){
        wp_delete_post($expense_id,true);
        echo '<div class="notice notice-success is-dismissible"><p>Expense deleted successfully.</p></div>';
    } else {
        echo '<div class="notice notice-error"><p>Security check failed. Expense not deleted.</p></div>';
    }
}

// ---------- SETTINGS OPTIONS ----------
$categories   = get_option('hb_expense_categories', ['Food & Beverage','Maintenance','Staff','Utilities','Misc']);
$methods      = get_option('hb_payment_methods', ['Cash','Bkash','Nagad','Bank Transfer']);
$paid_to_list = get_option('hb_paid_to_list', ['Owner','Manager','Supplier']);

// ---------- SAVE SETTINGS ----------
if(isset($_POST['hb_save_settings']) && check_admin_referer('hb_settings_action','hb_settings_nonce')){
    $categories   = array_filter(array_map('sanitize_text_field', $_POST['categories'] ?? []));
    $methods      = array_filter(array_map('sanitize_text_field', $_POST['methods'] ?? []));
    $paid_to_list = array_filter(array_map('sanitize_text_field', $_POST['paid_to_list'] ?? []));

    update_option('hb_expense_categories', $categories);
    update_option('hb_payment_methods', $methods);
    update_option('hb_paid_to_list', $paid_to_list);

    echo '<div class="notice notice-success is-dismissible"><p>Settings saved successfully.</p></div>';
}

// ---------- ADD/EDIT EXPENSE ----------
if(isset($_POST['hb_submit_expense']) && check_admin_referer('hb_add_expense_action','hb_add_expense_nonce')){
    $title          = sanitize_text_field($_POST['title']);
    $amount         = floatval($_POST['amount']);
    $date           = sanitize_text_field($_POST['date']);
    $category       = sanitize_text_field($_POST['category']);
    $paid_to        = sanitize_text_field($_POST['paid_to']);
    $payment_method = sanitize_text_field($_POST['payment_method']);
    $notes          = sanitize_textarea_field($_POST['notes']);
    $expense_id     = intval($_POST['expense_id'] ?? 0);

    $expense_data = [
        'post_type'   => 'expense',
        'post_title'  => $title,
        'post_status' => 'publish',
    ];

    if($expense_id) $expense_data['ID'] = $expense_id;

    $new_id = wp_insert_post($expense_data);

    if($new_id){
        update_post_meta($new_id,'amount',$amount);
        update_post_meta($new_id,'date',$date);
        update_post_meta($new_id,'category',$category);
        update_post_meta($new_id,'paid_to',$paid_to);
        update_post_meta($new_id,'payment_method',$payment_method);
        update_post_meta($new_id,'notes',$notes);
        echo '<div class="notice notice-success is-dismissible"><p>Expense saved successfully.</p></div>';
    } else {
        echo '<div class="notice notice-error"><p>Failed to save expense.</p></div>';
    }
}

// ---------- EDIT DATA ----------
$edit_id       = intval($_GET['edit'] ?? 0);
$edit_expense  = $edit_id ? get_post($edit_id) : null;
$edit_amount   = $edit_expense ? get_post_meta($edit_id,'amount',true) : '';
$edit_date     = $edit_expense ? get_post_meta($edit_id,'date',true) : date('Y-m-d');
$edit_category = $edit_expense ? get_post_meta($edit_id,'category',true) : '';
$edit_paid_to  = $edit_expense ? get_post_meta($edit_id,'paid_to',true) : '';
$edit_payment_method = $edit_expense ? get_post_meta($edit_id,'payment_method',true) : '';
$edit_notes    = $edit_expense ? get_post_meta($edit_id,'notes',true) : '';

/* -----------------------------
   SUB TAB: ADD / EDIT EXPENSE
----------------------------- */
if($sub_tab=='add'):
?>
<div class="hb-expense-wrap">
    <h2><?php echo $edit_expense ? 'Edit Expense' : 'Add Expense'; ?></h2>
    <form method="post">
        <?php wp_nonce_field('hb_add_expense_action','hb_add_expense_nonce'); ?>
        <input type="hidden" name="expense_id" value="<?php echo esc_attr($edit_id); ?>">

        <table class="form-table">
            <tr><th>Title</th><td><input type="text" name="title" required class="regular-text" value="<?php echo esc_attr($edit_expense->post_title ?? ''); ?>"></td></tr>
            <tr><th>Amount</th><td><input type="number" step="0.01" name="amount" required value="<?php echo esc_attr($edit_amount); ?>"></td></tr>
            <tr><th>Date</th><td><input type="date" name="date" required value="<?php echo esc_attr($edit_date); ?>"></td></tr>
            <tr>
                <th>Category</th>
                <td>
                    <select name="category" required>
                        <option value="">Select Category</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?php echo esc_attr($cat); ?>" <?php selected($edit_category,$cat); ?>><?php echo esc_html($cat); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th>Paid To</th>
                <td>
                    <select name="paid_to" required>
                        <option value="">Select Person</option>
                        <?php foreach($paid_to_list as $p): ?>
                            <option value="<?php echo esc_attr($p); ?>" <?php selected($edit_paid_to,$p); ?>><?php echo esc_html($p); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th>Payment Method</th>
                <td>
                    <select name="payment_method" required>
                        <option value="">Select Method</option>
                        <?php foreach($methods as $m): ?>
                            <option value="<?php echo esc_attr($m); ?>" <?php selected($edit_payment_method,$m); ?>><?php echo esc_html($m); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr><th>Notes</th><td><textarea name="notes" rows="3" class="large-text"><?php echo esc_textarea($edit_notes); ?></textarea></td></tr>
        </table>

        <p><input type="submit" name="hb_submit_expense" class="button button-primary" value="<?php echo $edit_expense ? 'Update Expense' : 'Add Expense'; ?>"></p>
    </form>
</div>
<?php endif; ?>

<?php
/* -----------------------------
   SUB TAB: ALL EXPENSES
----------------------------- */
if($sub_tab=='all'):

$filter_category = $_GET['filter_category'] ?? '';
$filter_start    = $_GET['filter_start'] ?? '';
$filter_end      = $_GET['filter_end'] ?? '';

$args = [
    'post_type' => 'expense',
    'posts_per_page' => -1,
    'orderby' => 'date',
    'order' => 'DESC',
    'meta_query' => [],
];

if($filter_category) $args['meta_query'][] = ['key'=>'category','value'=>$filter_category,'compare'=>'='];

if($filter_start || $filter_end){
    $date_query = [];
    if($filter_start) $date_query['after'] = $filter_start;
    if($filter_end) $date_query['before'] = $filter_end;
    $args['date_query'] = [$date_query];
}

$expenses = get_posts($args);

// --- Report summary ---
$report_total = 0;
$report_category = [];
$report_method   = [];
$report_paid_to  = [];

foreach($expenses as $e){
    $amt = floatval(get_post_meta($e->ID,'amount',true));
    $report_total += $amt;

    $cat = get_post_meta($e->ID,'category',true);
    $report_category[$cat] = ($report_category[$cat] ?? 0) + $amt;

    $method = get_post_meta($e->ID,'payment_method',true);
    $report_method[$method] = ($report_method[$method] ?? 0) + $amt;

    $paid = get_post_meta($e->ID,'paid_to',true);
    $report_paid_to[$paid] = ($report_paid_to[$paid] ?? 0) + $amt;
}
?>

<!-- Filters Form -->
<form method="get" class="hb-filters-form" style="margin-bottom:20px;">
    <input type="hidden" name="page" value="hb_bookings">
    <input type="hidden" name="tab" value="expenses">
    <input type="hidden" name="sub_tab" value="all">

    <label>Category:
        <select name="filter_category">
            <option value="">All</option>
            <?php foreach($categories as $cat): ?>
                <option value="<?php echo esc_attr($cat); ?>" <?php selected($filter_category,$cat); ?>><?php echo esc_html($cat); ?></option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>From:
        <input type="date" name="filter_start" value="<?php echo esc_attr($filter_start); ?>">
    </label>
    <label>To:
        <input type="date" name="filter_end" value="<?php echo esc_attr($filter_end); ?>">
    </label>
    <input type="submit" class="button button-primary" value="Filter">
    <a href="<?php echo admin_url('admin.php?page=hb_bookings&tab=expenses&sub_tab=all'); ?>" class="button">Reset</a>
</form>

<!-- Report Summary -->
<div class="hb-expense-report" style="margin-bottom:20px; padding:15px; border:1px solid #ddd; border-radius:5px; background:#f9f9f9;">
    <h3 style="margin-top:0;">Expense Summary</h3>
    <p><strong>Total Expenses:</strong> <?php echo esc_html($report_total); ?> ৳</p>

    <div style="display:flex; gap:30px; flex-wrap:wrap;">
        <div>
            <h4>By Category</h4>
            <ul><?php foreach($report_category as $cat=>$amt): ?><li><?php echo esc_html($cat); ?>: <?php echo esc_html($amt); ?> ৳</li><?php endforeach; ?></ul>
        </div>
        <div>
            <h4>By Payment Method</h4>
            <ul><?php foreach($report_method as $m=>$amt): ?><li><?php echo esc_html($m); ?>: <?php echo esc_html($amt); ?> ৳</li><?php endforeach; ?></ul>
        </div>
        <div>
            <h4>By Paid To</h4>
            <ul><?php foreach($report_paid_to as $p=>$amt): ?><li><?php echo esc_html($p); ?>: <?php echo esc_html($amt); ?> ৳</li><?php endforeach; ?></ul>
        </div>
    </div>
</div>

<?php if($expenses): ?>
<table class="wp-list-table widefat fixed striped">
    <thead>
        <tr>
            <th>Title</th>
            <th>Amount</th>
            <th>Date</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($expenses as $e):
            $amt  = get_post_meta($e->ID,'amount',true);
            $date = get_post_meta($e->ID,'date',true);
            $category = get_post_meta($e->ID,'category',true);
            $paid_to = get_post_meta($e->ID,'paid_to',true);
            $payment_method = get_post_meta($e->ID,'payment_method',true);
            $notes = get_post_meta($e->ID,'notes',true);

            $edit_url = admin_url('admin.php?page=hb_bookings&tab=expenses&sub_tab=add&edit='.$e->ID);
            $del_url  = wp_nonce_url(admin_url('admin.php?page=hb_bookings&tab=expenses&sub_tab=all&hb_action=delete&expense_id='.$e->ID),'hb_delete_expense','hb_delete_expense_nonce');
            $view_url = admin_url('admin.php?page=hb_bookings&tab=expenses&sub_tab=view&expense_id='.$e->ID);
        ?>
        <tr>
            <td><?php echo esc_html($e->post_title); ?></td>
            <td><?php echo esc_html($amt); ?> ৳</td>
            <td><?php echo esc_html($date); ?></td>
            <td>
                <a href="<?php echo esc_url($view_url); ?>" class="button button-secondary">View</a>
                <a href="<?php echo esc_url($edit_url); ?>" class="button button-primary">Edit</a>
                <a href="<?php echo esc_url($del_url); ?>" class="button button-danger hb-delete">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
<p>No expenses found for the selected filters.</p>
<?php endif; ?>

<script>
jQuery(document).ready(function($){
    $('.hb-delete').click(function(e){
        if(!confirm('Are you sure you want to delete this expense?')) e.preventDefault();
    });
});
</script>

<?php endif; // END of $sub_tab=='all' ?>

<?php
/* -----------------------------
   SUB TAB: VIEW EXPENSE
----------------------------- */
if($sub_tab=='view' && isset($_GET['expense_id'])):
    $expense_id = intval($_GET['expense_id']);
    $expense = get_post($expense_id);
    if($expense):
        $amount = get_post_meta($expense_id,'amount',true);
        $date = get_post_meta($expense_id,'date',true);
        $category = get_post_meta($expense_id,'category',true);
        $paid_to = get_post_meta($expense_id,'paid_to',true);
        $payment_method = get_post_meta($expense_id,'payment_method',true);
        $notes = get_post_meta($expense_id,'notes',true);
?>
<div class="hb-expense-wrap">
    <h2>View Expense</h2>
    <table class="form-table">
        <tr><th>Title</th><td><?php echo esc_html($expense->post_title); ?></td></tr>
        <tr><th>Amount</th><td><?php echo esc_html($amount); ?> ৳</td></tr>
        <tr><th>Date</th><td><?php echo esc_html($date); ?></td></tr>
        <tr><th>Category</th><td><?php echo esc_html($category); ?></td></tr>
        <tr><th>Paid To</th><td><?php echo esc_html($paid_to); ?></td></tr>
        <tr><th>Payment Method</th><td><?php echo esc_html($payment_method); ?></td></tr>
        <tr><th>Notes</th><td><?php echo esc_html($notes); ?></td></tr>
    </table>
    <p><a href="<?php echo admin_url('admin.php?page=hb_bookings&tab=expenses&sub_tab=all'); ?>" class="button button-secondary">Back to All Expenses</a></p>
</div>
<?php
    else:
        echo '<div class="notice notice-error"><p>Expense not found.</p></div>';
    endif;
endif;
?>

<?php
/* -----------------------------
   SUB TAB: SETTINGS
----------------------------- */
if($sub_tab=='settings'):
?>
<div class="hb-expense-wrap">
    <h2>Expense Settings</h2>
    <form method="post">
        <?php wp_nonce_field('hb_settings_action','hb_settings_nonce'); ?>

        <h3>Expense Categories</h3>
        <div id="categories_wrap">
            <?php foreach($categories as $cat): ?>
                <input type="text" name="categories[]" value="<?php echo esc_attr($cat); ?>" class="regular-text" style="margin-bottom:5px;">
            <?php endforeach; ?>
        </div>
        <button type="button" class="button" id="add_category">Add Category</button>

        <h3>Payment Methods</h3>
        <div id="methods_wrap">
            <?php foreach($methods as $m): ?>
                <input type="text" name="methods[]" value="<?php echo esc_attr($m); ?>" class="regular-text" style="margin-bottom:5px;">
            <?php endforeach; ?>
        </div>
        <button type="button" class="button" id="add_method">Add Method</button>

        <h3>Paid To</h3>
        <div id="paid_to_wrap">
            <?php foreach($paid_to_list as $p): ?>
                <input type="text" name="paid_to_list[]" value="<?php echo esc_attr($p); ?>" class="regular-text" style="margin-bottom:5px;">
            <?php endforeach; ?>
        </div>
        <button type="button" class="button" id="add_paid_to">Add Person</button>

        <p><input type="submit" name="hb_save_settings" class="button button-primary" value="Save Settings"></p>
    </form>
</div>

<script>
jQuery(document).ready(function($){
    $('#add_category').click(function(){
        $('#categories_wrap').append('<input type="text" name="categories[]" class="regular-text" style="margin-bottom:5px;">');
    });
    $('#add_method').click(function(){
        $('#methods_wrap').append('<input type="text" name="methods[]" class="regular-text" style="margin-bottom:5px;">');
    });
    $('#add_paid_to').click(function(){
        $('#paid_to_wrap').append('<input type="text" name="paid_to_list[]" class="regular-text" style="margin-bottom:5px;">');
    });

    $('.hb-delete').click(function(e){
        if(!confirm('Are you sure you want to delete this expense?')) e.preventDefault();
    });
});
</script>
<?php endif; ?>
