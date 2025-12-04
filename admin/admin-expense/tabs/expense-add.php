<?php
if (!defined('ABSPATH')) exit;

// Load saved categories
$ahbn_expense_categories = get_option('ahbn_expense_categories', ['Food','Utilities','Maintenance','Cleaning','Other']);

if (isset($_POST['ahbn_save_expense'])) {
    check_admin_referer('ahbn_expense_form');

    $ahbn_expense_id = isset($_POST['ahbn_expense_id']) ? intval($_POST['ahbn_expense_id']) : 0;
    $ahbn_title      = isset($_POST['ahbn_expense_title']) ? sanitize_text_field(wp_unslash($_POST['ahbn_expense_title'])) : '';
    $ahbn_amount     = isset($_POST['ahbn_expense_amount']) ? floatval($_POST['ahbn_expense_amount']) : 0;
    $ahbn_category   = isset($_POST['ahbn_expense_category']) ? sanitize_text_field(wp_unslash($_POST['ahbn_expense_category'])) : '';
    $ahbn_date       = isset($_POST['ahbn_expense_date']) ? sanitize_text_field(wp_unslash($_POST['ahbn_expense_date'])) : '';
    $ahbn_desc       = isset($_POST['ahbn_expense_desc']) ? sanitize_textarea_field(wp_unslash($_POST['ahbn_expense_desc'])) : '';
    $ahbn_assign     = isset($_POST['ahbn_expense_assign']) ? sanitize_text_field(wp_unslash($_POST['ahbn_expense_assign'])) : '';

    $ahbn_post_data = [
        'post_title'   => $ahbn_title,
        'post_type'    => 'ahbn_expense',
        'post_status'  => 'publish',
        'post_content' => $ahbn_desc,
    ];

    if ($ahbn_expense_id) {
        $ahbn_post_data['ID'] = $ahbn_expense_id;
        wp_update_post($ahbn_post_data);
    } else {
        $ahbn_expense_id = wp_insert_post($ahbn_post_data);
    }

    update_post_meta($ahbn_expense_id, 'ahbn_amount', $ahbn_amount);
    update_post_meta($ahbn_expense_id, 'ahbn_category', $ahbn_category);
    update_post_meta($ahbn_expense_id, 'ahbn_date', $ahbn_date);
    update_post_meta($ahbn_expense_id, 'ahbn_assign', $ahbn_assign);

    echo '<div class="notice notice-success"><p><strong>Expense saved successfully!</strong></p></div>';
}

// Edit mode
$ahbn_edit_id = isset($_GET['ahbn_edit_expense']) ? intval($_GET['ahbn_edit_expense']) : 0;
$ahbn_title = $ahbn_amount = $ahbn_category = $ahbn_date = $ahbn_desc = $ahbn_assign = '';
if ($ahbn_edit_id) {
    $ahbn_e        = get_post($ahbn_edit_id);
    $ahbn_title    = $ahbn_e->post_title;
    $ahbn_desc     = $ahbn_e->post_content;
    $ahbn_amount   = get_post_meta($ahbn_edit_id, 'ahbn_amount', true);
    $ahbn_category = get_post_meta($ahbn_edit_id, 'ahbn_category', true);
    $ahbn_date     = get_post_meta($ahbn_edit_id, 'ahbn_date', true);
    $ahbn_assign   = get_post_meta($ahbn_edit_id, 'ahbn_assign', true);
}

// Load Assign options
$ahbn_assign_options = get_option('ahbn_expense_assign', ['Reception','Housekeeping','Kitchen','Manager','Security']);
?>

<div class="wrap">
    <h2><?php echo $ahbn_edit_id ? 'Edit Expense' : 'Add Expense'; ?></h2>
    <form method="post">
        <?php wp_nonce_field('ahbn_expense_form'); ?>
        <input type="hidden" name="ahbn_expense_id" value="<?php echo esc_attr($ahbn_edit_id); ?>">
        <table class="form-table">
            <tr>
                <th><label for="ahbn_expense_title">Title</label></th>
                <td><input type="text" id="ahbn_expense_title" name="ahbn_expense_title" class="regular-text" required value="<?php echo esc_attr($ahbn_title); ?>"></td>
            </tr>
            <tr>
                <th><label for="ahbn_expense_amount">Amount</label></th>
                <td><input type="number" step="0.01" id="ahbn_expense_amount" name="ahbn_expense_amount" class="regular-text" required value="<?php echo esc_attr($ahbn_amount); ?>"></td>
            </tr>
            <tr>
                <th><label for="ahbn_expense_category">Category</label></th>
                <td>
                    <select id="ahbn_expense_category" name="ahbn_expense_category" style="width:50%">
                        <?php foreach($ahbn_expense_categories as $ahbn_cat): ?>
                            <option value="<?php echo esc_attr($ahbn_cat); ?>" <?php selected($ahbn_category, $ahbn_cat); ?>><?php echo esc_html($ahbn_cat); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="ahbn_expense_assign">Assign To</label></th>
                <td>
                    <select id="ahbn_expense_assign" name="ahbn_expense_assign" style="width:50%">
                        <?php foreach($ahbn_assign_options as $ahbn_as): ?>
                            <option value="<?php echo esc_attr($ahbn_as); ?>" <?php selected($ahbn_assign, $ahbn_as); ?>><?php echo esc_html($ahbn_as); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="ahbn_expense_date">Date</label></th>
                <td><input type="date" id="ahbn_expense_date" name="ahbn_expense_date" class="regular-text" value="<?php echo esc_attr($ahbn_date); ?>"></td>
            </tr>
            <tr>
                <th><label for="ahbn_expense_desc">Description</label></th>
                <td><textarea id="ahbn_expense_desc" name="ahbn_expense_desc" class="large-text" rows="4"><?php echo esc_textarea($ahbn_desc); ?></textarea></td>
            </tr>
        </table>
        <p><input type="submit" class="button button-primary" name="ahbn_save_expense" value="Save Expense"></p>
    </form>
</div>
