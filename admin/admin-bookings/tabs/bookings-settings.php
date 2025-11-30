<?php
// ----------------------------
// Booking Settings Tab - WordPress Standard Layout
// ----------------------------

// Helper: Display admin notice
function ahbn_admin_notice($message = '', $type = 'success') {
    if (!$message) return;
    $class = ($type === 'error') ? 'notice notice-error' : 'notice notice-success';
    printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
}

// Handle form submission
if (isset($_POST['ahbn_save_booking_settings'])) {
    if (!check_admin_referer('ahbn_booking_settings_verify')) {
        ahbn_admin_notice('Security check failed. Settings not saved.', 'error');
    } else {
        // Save booking settings
        update_option('ahbn_booking_default_status', sanitize_text_field($_POST['default_status'] ?? 'pending'));
        update_option('ahbn_booking_auto_approve', isset($_POST['auto_approve']) ? 1 : 0);
        update_option('ahbn_booking_max_rooms', intval($_POST['max_rooms'] ?? 1));
        update_option('ahbn_booking_cutoff_hours', intval($_POST['cutoff_hours'] ?? 24));
        update_option('ahbn_booking_cancellation_policy', sanitize_textarea_field($_POST['cancellation_policy'] ?? ''));

        ahbn_admin_notice('Booking settings saved successfully!', 'success');
    }
}

// Load saved values
$default_status         = get_option('ahbn_booking_default_status','pending');
$auto_approve           = get_option('ahbn_booking_auto_approve',0);
$max_rooms              = get_option('ahbn_booking_max_rooms',1);
$cutoff_hours           = get_option('ahbn_booking_cutoff_hours',24);
$cancellation_policy    = get_option('ahbn_booking_cancellation_policy','');

// Booking statuses
$booking_statuses = apply_filters('ahbn_booking_statuses', [
    'pending'   => 'Pending',
    'confirmed' => 'Confirmed',
    'canceled'  => 'Canceled',
]);
?>

<div class="wrap">
    <h1><?php esc_html_e('Booking Settings', 'awesome-hotel-booking'); ?></h1>

    <form method="post" action="">
        <?php wp_nonce_field('ahbn_booking_settings_verify'); ?>
        <table class="form-table">
            <tbody>
                <!-- Default Booking Status -->
                <tr>
                    <th scope="row"><label for="default_status">Default Booking Status</label></th>
                    <td>
                        <select id="default_status" name="default_status">
                            <?php foreach ($booking_statuses as $key => $label) : ?>
                                <option value="<?php echo esc_attr($key); ?>" <?php selected($default_status, $key); ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">Select the default status for new bookings.</p>
                    </td>
                </tr>

                <!-- Auto Approve Bookings -->
                <tr>
                    <th scope="row">Auto Approve Bookings</th>
                    <td>
                        <label>
                            <input type="checkbox" name="auto_approve" value="1" <?php checked($auto_approve,1); ?>>
                            Automatically confirm bookings without admin approval.
                        </label>
                    </td>
                </tr>

                <!-- Max Rooms per Booking -->
                <tr>
                    <th scope="row"><label for="max_rooms">Maximum Rooms per Booking</label></th>
                    <td>
                        <input type="number" name="max_rooms" id="max_rooms" value="<?php echo esc_attr($max_rooms); ?>" min="1">
                        <p class="description">Limit how many rooms a guest can book in one booking.</p>
                    </td>
                </tr>

                <!-- Booking Cutoff Time -->
                <tr>
                    <th scope="row"><label for="cutoff_hours">Booking Cutoff (hours before check-in)</label></th>
                    <td>
                        <input type="number" name="cutoff_hours" id="cutoff_hours" value="<?php echo esc_attr($cutoff_hours); ?>" min="1">
                        <p class="description">Bookings cannot be made within this many hours before check-in.</p>
                    </td>
                </tr>

                <!-- Cancellation Policy -->
                <tr>
                    <th scope="row"><label for="cancellation_policy">Cancellation Policy</label></th>
                    <td>
                        <textarea name="cancellation_policy" id="cancellation_policy" rows="5" class="large-text"><?php echo esc_textarea($cancellation_policy); ?></textarea>
                        <p class="description">Set your default cancellation policy. It will appear in booking confirmation emails.</p>
                    </td>
                </tr>
            </tbody>
        </table>

        <p class="submit">
            <input type="submit" name="ahbn_save_booking_settings" class="button button-primary" value="Save Settings">
        </p>
    </form>
</div>
