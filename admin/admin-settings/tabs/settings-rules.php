<?php
function ahbn_settings_rules() {

    // ----------------------------
    // Save global rules
    // ----------------------------
    if (isset($_POST['ahbn_save_rules'])) {
        update_option('ahbn_min_stay', intval($_POST['min_stay']));
        update_option('ahbn_max_stay', intval($_POST['max_stay']));
        update_option('ahbn_child_allowed', isset($_POST['child_allowed']) ? 1 : 0);
        update_option('ahbn_pet_allowed', isset($_POST['pet_allowed']) ? 1 : 0);

        echo '<div class="updated"><p>Booking rules saved!</p></div>';
    }

    $min_stay = get_option('ahbn_min_stay', 1);
    $max_stay = get_option('ahbn_max_stay', 30);
    $child_ok = get_option('ahbn_child_allowed', 1);
    $pet_ok   = get_option('ahbn_pet_allowed', 0);
    ?>

    <h2>Global Booking Rules</h2>
    <form method="post">
        <table class="form-table">
            <tr>
                <th>Minimum Stay (days)</th>
                <td><input type="number" name="min_stay" value="<?php echo esc_attr($min_stay); ?>"></td>
            </tr>
            <tr>
                <th>Maximum Stay (days)</th>
                <td><input type="number" name="max_stay" value="<?php echo esc_attr($max_stay); ?>"></td>
            </tr>
            <tr>
                <th>Children Allowed?</th>
                <td><input type="checkbox" name="child_allowed" <?php checked(1, $child_ok); ?>></td>
            </tr>
            <tr>
                <th>Pets Allowed?</th>
                <td><input type="checkbox" name="pet_allowed" <?php checked(1, $pet_ok); ?>></td>
            </tr>
        </table>
        <p><button class="button button-primary" name="ahbn_save_rules">Save Rules</button></p>
    </form>

    <?php
    // ----------------------------
    // Save per-room rules
    // ----------------------------
    if (isset($_POST['ahbn_save_room_rules'])) {
        if (!isset($_POST['ahbn_room_rules_nonce']) || !wp_verify_nonce($_POST['ahbn_room_rules_nonce'], 'ahbn_save_room_rules_action')) {
            echo '<div class="notice notice-error"><p>Security check failed. Room rules not saved.</p></div>';
        } else {
            if (!empty($_POST['room_rules']) && is_array($_POST['room_rules'])) {
                foreach ($_POST['room_rules'] as $room_id => $rules) {
                    $repeater = array_map('sanitize_text_field', $rules['rules'] ?? []);
                    update_post_meta($room_id, 'ahbn_room_rules', $repeater);
                }
            }
            echo '<div class="updated"><p>Room rules saved successfully!</p></div>';
        }
    }

    // ----------------------------
    // Display per-room rules in WP table layout
    // ----------------------------
    $rooms = get_posts(['post_type' => 'ahbn_room', 'numberposts' => -1]);
    if ($rooms) :
    ?>
        <h2>Per-Room Rules</h2>
        <form method="post">
            <?php wp_nonce_field('ahbn_save_room_rules_action', 'ahbn_room_rules_nonce'); ?>
            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th>Room</th>
                        <th>Rule</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($rooms as $room) :
                    $room_rules = get_post_meta($room->ID, 'ahbn_room_rules', true) ?: [''];
                    foreach ($room_rules as $rule) : ?>
                        <tr class="ahbn-repeater-row">
                            <td><?php echo esc_html($room->post_title); ?></td>
                            <td><input type="text" name="room_rules[<?php echo $room->ID; ?>][rules][]" value="<?php echo esc_attr($rule); ?>" class="regular-text"></td>
                            <td><button class="button ahbn-remove-row">Remove</button></td>
                        </tr>
                    <?php endforeach; ?>
                    <!-- Add button row for new rules -->
                    <tr>
                        <td colspan="3">
                            <button class="button ahbn-add-row" type="button" data-room="<?php echo esc_attr($room->ID); ?>">Add Rule</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <p><button class="button button-primary" name="ahbn_save_room_rules">Save Room Rules</button></p>
        </form>

        <script>
        jQuery(document).ready(function($){
            $('.ahbn-add-row').click(function(){
                var roomID = $(this).data('room');
                var newRow = '<tr class="ahbn-repeater-row">'+
                                '<td>'+$(this).closest('table').find('tr:first td:first').text()+'</td>'+
                                '<td><input type="text" name="room_rules['+roomID+'][rules][]" class="regular-text"></td>'+
                                '<td><button class="button ahbn-remove-row">Remove</button></td>'+
                             '</tr>';
                $(this).closest('tr').before(newRow);
            });

            $(document).on('click','.ahbn-remove-row', function(e){
                e.preventDefault();
                $(this).closest('tr').remove();
            });
        });
        </script>
    <?php
    endif;
}
