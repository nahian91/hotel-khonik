<?php
function ahbn_settings_rules() {

    // ----------------------------
    // Save Global Booking Rules
    // ----------------------------
    if ( isset($_POST['ahbn_save_rules']) ) {

        update_option('ahbn_min_stay', intval($_POST['min_stay']));
        update_option('ahbn_max_stay', intval($_POST['max_stay']));
        update_option('ahbn_child_allowed', isset($_POST['child_allowed']) ? 1 : 0);
        update_option('ahbn_pet_allowed', isset($_POST['pet_allowed']) ? 1 : 0);

        echo '<div class="updated notice"><p>' . esc_html__('Booking rules saved successfully!', 'awesome-hotel-booking') . '</p></div>';
    }

    // Get values
    $min_stay = get_option('ahbn_min_stay', 1);
    $max_stay = get_option('ahbn_max_stay', 30);
    $child_ok = get_option('ahbn_child_allowed', 1);
    $pet_ok   = get_option('ahbn_pet_allowed', 0);
    ?>

    <h2><?php echo esc_html__('Global Booking Rules', 'awesome-hotel-booking'); ?></h2>

    <form method="post">
        <table class="form-table">

            <tr>
                <th><?php echo esc_html__('Minimum Stay (days)', 'awesome-hotel-booking'); ?></th>
                <td>
                    <input type="number" name="min_stay" value="<?php echo esc_attr($min_stay); ?>" min="1">
                </td>
            </tr>

            <tr>
                <th><?php echo esc_html__('Maximum Stay (days)', 'awesome-hotel-booking'); ?></th>
                <td>
                    <input type="number" name="max_stay" value="<?php echo esc_attr($max_stay); ?>" min="1">
                </td>
            </tr>

            <tr>
                <th><?php echo esc_html__('Children Allowed?', 'awesome-hotel-booking'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="child_allowed" <?php checked(1, $child_ok); ?>>
                        <?php echo esc_html__('Yes', 'awesome-hotel-booking'); ?>
                    </label>
                </td>
            </tr>

            <tr>
                <th><?php echo esc_html__('Pets Allowed?', 'awesome-hotel-booking'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="pet_allowed" <?php checked(1, $pet_ok); ?>>
                        <?php echo esc_html__('Yes', 'awesome-hotel-booking'); ?>
                    </label>
                </td>
            </tr>

        </table>

        <p>
            <button type="submit" class="button button-primary" name="ahbn_save_rules">
                <?php echo esc_html__('Save Rules', 'awesome-hotel-booking'); ?>
            </button>
        </p>
    </form>

    <?php
}
