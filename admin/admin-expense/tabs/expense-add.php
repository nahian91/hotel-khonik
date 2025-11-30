<?php
// ----------------------------
// Add / Edit Expense Tab
// ----------------------------
if (!defined('ABSPATH')) exit;

// Load saved categories
$expense_categories = get_option('ahbn_expense_categories', ['Food','Utilities','Maintenance','Cleaning','Other']);

if (isset($_POST['ahbn_save_expense'])) {
    check_admin_referer('ahbn_expense_form');

    $expense_id = intval($_POST['expense_id']);
    $title      = sanitize_text_field($_POST['expense_title']);
    $amount     = floatval($_POST['expense_amount']);
    $category   = sanitize_text_field($_POST['expense_category']);
    $date       = sanitize_text_field($_POST['expense_date']);
    $desc       = sanitize_textarea_field($_POST['expense_desc']);
    $assign     = sanitize_text_field($_POST['expense_assign'] ?? '');

    $post_data = [
        'post_title'   => $title,
        'post_type'    => 'ahbn_expense',
        'post_status'  => 'publish',
        'post_content' => $desc,
    ];

    if ($expense_id) {
        $post_data['ID'] = $expense_id;
        wp_update_post($post_data);
    } else {
        $expense_id = wp_insert_post($post_data);
    }

    update_post_meta($expense_id, 'ahbn_amount', $amount);
    update_post_meta($expense_id, 'ahbn_category', $category);
    update_post_meta($expense_id, 'ahbn_date', $date);
    update_post_meta($expense_id, 'ahbn_assign', $assign);

    echo '<div class="notice notice-success"><p><strong>Expense saved successfully!</strong></p></div>';
}

// Edit mode
$edit_id = isset($_GET['edit_expense']) ? intval($_GET['edit_expense']) : 0;
$title = $amount = $category = $date = $desc = $assign = '';
if ($edit_id) {
    $e        = get_post($edit_id);
    $title    = $e->post_title;
    $desc     = $e->post_content;
    $amount   = get_post_meta($edit_id, 'ahbn_amount', true);
    $category = get_post_meta($edit_id, 'ahbn_category', true);
    $date     = get_post_meta($edit_id, 'ahbn_date', true);
    $assign   = get_post_meta($edit_id, 'ahbn_assign', true);
}

// Load Assign options
$assign_options = get_option('ahbn_expense_assign', ['Reception','Housekeeping','Kitchen','Manager','Security']);
?>

<div class="wrap">
    <h2><?php echo $edit_id ? 'Edit Expense' : 'Add Expense'; ?></h2>
    <form method="post">
        <?php wp_nonce_field('ahbn_expense_form'); ?>
        <input type="hidden" name="expense_id" value="<?php echo esc_attr($edit_id); ?>">
        <table class="form-table">
            <tr>
                <th><label for="expense_title">Title</label></th>
                <td><input type="text" id="expense_title" name="expense_title" class="regular-text" required value="<?php echo esc_attr($title); ?>"></td>
            </tr>
            <tr>
                <th><label for="expense_amount">Amount</label></th>
                <td><input type="number" step="0.01" id="expense_amount" name="expense_amount" class="regular-text" required value="<?php echo esc_attr($amount); ?>"></td>
            </tr>
            <tr>
                <th><label for="expense_category">Category</label></th>
                <td>
                    <select id="expense_category" name="expense_category" style="width:50%">
                        <?php foreach($expense_categories as $cat): ?>
                            <option value="<?php echo esc_attr($cat); ?>" <?php selected($category,$cat); ?>><?php echo esc_html($cat); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="expense_assign">Assign To</label></th>
                <td>
                    <select id="expense_assign" name="expense_assign" style="width:50%">
                        <?php foreach($assign_options as $as): ?>
                            <option value="<?php echo esc_attr($as); ?>" <?php selected($assign,$as); ?>><?php echo esc_html($as); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="expense_date">Date</label></th>
                <td><input type="date" id="expense_date" name="expense_date" class="regular-text" value="<?php echo esc_attr($date); ?>"></td>
            </tr>
            <tr>
                <th><label for="expense_desc">Description</label></th>
                <td><textarea id="expense_desc" name="expense_desc" class="large-text" rows="4"><?php echo esc_textarea($desc); ?></textarea></td>
            </tr>
        </table>
        <p><input type="submit" class="button button-primary" name="ahbn_save_expense" value="Save Expense"></p>
    </form>
</div>
