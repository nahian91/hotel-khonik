<?php
if (!defined('ABSPATH')) exit;

// Default values
$ahbn_default_categories = ['Food','Utilities','Maintenance','Cleaning','Other'];
$ahbn_default_items      = ['Soap','Towel','Water Bottle','Coffee','Snack'];
$ahbn_default_assign     = ['Reception','Housekeeping','Kitchen','Manager','Security'];

// Load saved options
$ahbn_categories = get_option('ahbn_expense_categories', $ahbn_default_categories);
$ahbn_items      = get_option('ahbn_expense_items', $ahbn_default_items);
$ahbn_assign     = get_option('ahbn_expense_assign', $ahbn_default_assign);

// ------------------ Save Form ------------------
if (isset($_POST['ahbn_save_expense_settings'])) {
    check_admin_referer('ahbn_expense_settings_form');

    // Unsplash and sanitize POST inputs
    $ahbn_categories_post = isset($_POST['ahbn_categories']) ? wp_unslash($_POST['ahbn_categories']) : [];
    $ahbn_items_post      = isset($_POST['ahbn_items']) ? wp_unslash($_POST['ahbn_items']) : [];
    $ahbn_assign_post     = isset($_POST['ahbn_assign']) ? wp_unslash($_POST['ahbn_assign']) : [];

    update_option('ahbn_expense_categories', array_map('sanitize_text_field', $ahbn_categories_post));
    update_option('ahbn_expense_items', array_map('sanitize_text_field', $ahbn_items_post));
    update_option('ahbn_expense_assign', array_map('sanitize_text_field', $ahbn_assign_post));

    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Expense settings saved!', 'awesome-hotel-booking') . '</p></div>';

    // Update local variables for form display
    $ahbn_categories = $ahbn_categories_post;
    $ahbn_items      = $ahbn_items_post;
    $ahbn_assign     = $ahbn_assign_post;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Hotel Expense Settings', 'awesome-hotel-booking'); ?></h1>
    <hr class="wp-header-end">

    <form method="post">
        <?php wp_nonce_field('ahbn_expense_settings_form'); ?>

        <table class="form-table" role="presentation">

            <!-- Categories -->
            <tr>
                <th scope="row"><label><?php esc_html_e('Expense Categories', 'awesome-hotel-booking'); ?></label></th>
                <td>
                    <div id="ahbn_categories_wrapper">
                        <?php foreach ($ahbn_categories as $ahbn_cat): ?>
                            <div class="ahbn-row">
                                <input type="text" name="ahbn_categories[]" value="<?php echo esc_attr($ahbn_cat); ?>" class="regular-text">
                                <button type="button" class="button remove-row"><?php esc_html_e('Remove', 'awesome-hotel-booking'); ?></button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <p><button type="button" class="button add-row" data-target="ahbn_categories_wrapper">+ <?php esc_html_e('Add Category', 'awesome-hotel-booking'); ?></button></p>
                </td>
            </tr>

            <!-- Items -->
            <tr>
                <th scope="row"><label><?php esc_html_e('Expense Items', 'awesome-hotel-booking'); ?></label></th>
                <td>
                    <div id="ahbn_items_wrapper">
                        <?php foreach ($ahbn_items as $ahbn_it): ?>
                            <div class="ahbn-row">
                                <input type="text" name="ahbn_items[]" value="<?php echo esc_attr($ahbn_it); ?>" class="regular-text">
                                <button type="button" class="button remove-row"><?php esc_html_e('Remove', 'awesome-hotel-booking'); ?></button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <p><button type="button" class="button add-row" data-target="ahbn_items_wrapper">+ <?php esc_html_e('Add Item', 'awesome-hotel-booking'); ?></button></p>
                </td>
            </tr>

            <!-- Assign -->
            <tr>
                <th scope="row"><label><?php esc_html_e('Department Assign', 'awesome-hotel-booking'); ?></label></th>
                <td>
                    <div id="ahbn_assign_wrapper">
                        <?php foreach ($ahbn_assign as $ahbn_as): ?>
                            <div class="ahbn-row">
                                <input type="text" name="ahbn_assign[]" value="<?php echo esc_attr($ahbn_as); ?>" class="regular-text">
                                <button type="button" class="button remove-row"><?php esc_html_e('Remove', 'awesome-hotel-booking'); ?></button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <p><button type="button" class="button add-row" data-target="ahbn_assign_wrapper">+ <?php esc_html_e('Add Department', 'awesome-hotel-booking'); ?></button></p>
                </td>
            </tr>

        </table>

        <p class="submit">
            <button type="submit" name="ahbn_save_expense_settings" class="button button-primary"><?php esc_html_e('Save Settings', 'awesome-hotel-booking'); ?></button>
        </p>

    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){

    // Add row
    document.querySelectorAll('.add-row').forEach(function(btn){
        btn.addEventListener('click', function(){
            let wrapper = document.getElementById(btn.dataset.target);
            let field = btn.dataset.target.replace('ahbn_','').replace('_wrapper','');

            let div = document.createElement('div');
            div.className = 'ahbn-row';
            div.innerHTML =
                '<input type="text" name="ahbn_' + field + '[]" class="regular-text"> ' +
                '<button type="button" class="button remove-row"><?php echo esc_js(esc_html__('Remove', 'awesome-hotel-booking')); ?></button>';

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
