<?php
if (!defined('ABSPATH')) exit;

// Default values
$default_categories = ['Food','Utilities','Maintenance','Cleaning','Other'];
$default_items      = ['Soap','Towel','Water Bottle','Coffee','Snack'];
$default_assign     = ['Reception','Housekeeping','Kitchen','Manager','Security'];

// Load saved options
$categories = get_option('ahbn_expense_categories', $default_categories);
$items      = get_option('ahbn_expense_items', $default_items);
$assign     = get_option('ahbn_expense_assign', $default_assign);

// Save form
if (isset($_POST['ahbn_save_expense_settings'])) {
    check_admin_referer('ahbn_expense_settings_form');

    update_option('ahbn_expense_categories', array_map('sanitize_text_field', $_POST['categories'] ?? []));
    update_option('ahbn_expense_items', array_map('sanitize_text_field', $_POST['items'] ?? []));
    update_option('ahbn_expense_assign', array_map('sanitize_text_field', $_POST['assign'] ?? []));

    echo '<div class="notice notice-success is-dismissible"><p>Expense settings saved!</p></div>';

    $categories = $_POST['categories'] ?? [];
    $items      = $_POST['items'] ?? [];
    $assign     = $_POST['assign'] ?? [];
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Hotel Expense Settings</h1>
    <hr class="wp-header-end">

    <form method="post">
        <?php wp_nonce_field('ahbn_expense_settings_form'); ?>

        <table class="form-table" role="presentation">

            <!-- Categories -->
            <tr>
                <th scope="row"><label>Expense Categories</label></th>
                <td>
                    <div id="categories_wrapper">
                        <?php foreach ($categories as $cat): ?>
                            <div class="ahbn-row">
                                <input type="text" name="categories[]" value="<?php echo esc_attr($cat); ?>" class="regular-text">
                                <button type="button" class="button remove-row">Remove</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <p><button type="button" class="button add-row" data-target="categories_wrapper">+ Add Category</button></p>
                </td>
            </tr>

            <!-- Items -->
            <tr>
                <th scope="row"><label>Expense Items</label></th>
                <td>
                    <div id="items_wrapper">
                        <?php foreach ($items as $it): ?>
                            <div class="ahbn-row">
                                <input type="text" name="items[]" value="<?php echo esc_attr($it); ?>" class="regular-text">
                                <button type="button" class="button remove-row">Remove</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <p><button type="button" class="button add-row" data-target="items_wrapper">+ Add Item</button></p>
                </td>
            </tr>

            <!-- Assign -->
            <tr>
                <th scope="row"><label>Department Assign</label></th>
                <td>
                    <div id="assign_wrapper">
                        <?php foreach ($assign as $as): ?>
                            <div class="ahbn-row">
                                <input type="text" name="assign[]" value="<?php echo esc_attr($as); ?>" class="regular-text">
                                <button type="button" class="button remove-row">Remove</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <p><button type="button" class="button add-row" data-target="assign_wrapper">+ Add Department</button></p>
                </td>
            </tr>

        </table>

        <p class="submit">
            <button type="submit" name="ahbn_save_expense_settings" class="button button-primary">Save Settings</button>
        </p>

    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){

    // Add row
    document.querySelectorAll('.add-row').forEach(function(btn){
        btn.addEventListener('click', function(){
            let wrapper = document.getElementById(btn.dataset.target);
            let field = btn.dataset.target.replace('_wrapper','');

            let div = document.createElement('div');
            div.className = 'ahbn-row';
            div.innerHTML =
                '<input type="text" name="'+field+'[]" class="regular-text"> ' +
                '<button type="button" class="button remove-row">Remove</button>';

            wrapper.appendChild(div);
        });
    });

    // Remove row
    document.addEventListener('click', function(e){
        if (e.target.classList.contains('remove-row')) {
            e.target.closest('.ahbn-row').remove();
        }
    });

});
</script>

<style>
.ahbn-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 6px;
}
</style>
