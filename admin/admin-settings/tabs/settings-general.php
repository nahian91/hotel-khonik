<?php
function ahbn_settings_general() {

    // Currency options
    $currencies = [
        'USD' => 'USD ($)',
        'EUR' => 'EUR (€)',
        'GBP' => 'GBP (£)',
        'BDT' => 'BDT (৳)',
        'INR' => 'INR (₹)',
    ];

    // Handle form submission
    if (isset($_POST['ahbn_save_settings'])) {
        check_admin_referer('ahbn_save_settings_verify');

        update_option('ahbn_hotel_currency', sanitize_text_field($_POST['hotel_currency']));
        update_option('ahbn_checkin_default', sanitize_text_field($_POST['checkin_default']));
        update_option('ahbn_checkout_default', sanitize_text_field($_POST['checkout_default']));

        // Additional fields
        update_option('ahbn_hotel_phone', sanitize_text_field($_POST['hotel_phone']));
        update_option('ahbn_hotel_email', sanitize_email($_POST['hotel_email']));
        update_option('ahbn_hotel_address', sanitize_textarea_field($_POST['hotel_address']));
        update_option('ahbn_min_stay', intval($_POST['min_stay']));
        update_option('ahbn_booking_msg', sanitize_text_field($_POST['booking_msg']));

        echo '<div class="updated notice"><p>Settings saved!</p></div>';
    }

    // Load saved values
    $hotel_currency   = get_option('ahbn_hotel_currency', 'USD');
    $checkin_default  = get_option('ahbn_checkin_default', '14:00');
    $checkout_default = get_option('ahbn_checkout_default', '12:00');
    $hotel_phone      = get_option('ahbn_hotel_phone', '');
    $hotel_email      = get_option('ahbn_hotel_email', '');
    $hotel_address    = get_option('ahbn_hotel_address', '');
    $min_stay         = get_option('ahbn_min_stay', 1);
    $booking_msg      = get_option('ahbn_booking_msg', 'Thank you for booking!');

    // Enqueue WP admin timepicker
    wp_enqueue_script('jquery-ui-slider');
    wp_enqueue_script('jquery-ui-spinner');
    wp_enqueue_style('jquery-ui-style', '//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css');
    wp_enqueue_script('jquery-timepicker', 'https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js', ['jquery'], '1.3.5', true);
    wp_enqueue_style('jquery-timepicker-css', 'https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css');
    ?>

    <form method="post">
        <?php wp_nonce_field('ahbn_save_settings_verify'); ?>
        <table class="form-table">
            <tr>
                <th><label for="hotel_currency">Currency</label></th>
                <td>
                    <select name="hotel_currency" id="hotel_currency">
                        <?php foreach ($currencies as $key => $label) : ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($hotel_currency, $key); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="checkin_default">Default Check-in</label></th>
                <td><input type="text" id="checkin_default" name="checkin_default" value="<?php echo esc_attr($checkin_default); ?>"></td>
            </tr>
            <tr>
                <th><label for="checkout_default">Default Check-out</label></th>
                <td><input type="text" id="checkout_default" name="checkout_default" value="<?php echo esc_attr($checkout_default); ?>"></td>
            </tr>
            <tr>
                <th><label for="hotel_phone">Hotel Phone</label></th>
                <td><input type="text" name="hotel_phone" value="<?php echo esc_attr($hotel_phone); ?>"></td>
            </tr>
            <tr>
                <th><label for="hotel_email">Hotel Email</label></th>
                <td><input type="email" name="hotel_email" value="<?php echo esc_attr($hotel_email); ?>"></td>
            </tr>
            <tr>
                <th><label for="hotel_address">Hotel Address</label></th>
                <td><textarea name="hotel_address"><?php echo esc_textarea($hotel_address); ?></textarea></td>
            </tr>
            <tr>
                <th><label for="min_stay">Minimum Stay (nights)</label></th>
                <td><input type="number" name="min_stay" value="<?php echo esc_attr($min_stay); ?>" min="1"></td>
            </tr>
            <tr>
                <th><label for="booking_msg">Booking Confirmation Message</label></th>
                <td><input type="text" name="booking_msg" value="<?php echo esc_attr($booking_msg); ?>" style="width:100%;"></td>
            </tr>
        </table>

        <p class="submit"><button type="submit" name="ahbn_save_settings" class="button button-primary">Save Settings</button></p>
    </form>

    <script>
    jQuery(document).ready(function($){
        $('#checkin_default, #checkout_default').timepicker({
            timeFormat: 'HH:mm',
            interval: 30,
            dropdown: true,
            scrollbar: true
        });
    });
    </script>
<?php
}
?>
