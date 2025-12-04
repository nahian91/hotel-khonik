<?php
function ahbn_settings_general() {

    // Currency options (International Standard ISO 4217)
    $currencies = [
        'USD' => esc_html__('USD – United States Dollar ($)', 'awesome-hotel-booking'),
        'EUR' => esc_html__('EUR – Euro (€)', 'awesome-hotel-booking'),
        'GBP' => esc_html__('GBP – British Pound (£)', 'awesome-hotel-booking'),
        'BDT' => esc_html__('BDT – Bangladeshi Taka (৳)', 'awesome-hotel-booking'),
        'INR' => esc_html__('INR – Indian Rupee (₹)', 'awesome-hotel-booking'),
        'JPY' => esc_html__('JPY – Japanese Yen (¥)', 'awesome-hotel-booking'),
        'AUD' => esc_html__('AUD – Australian Dollar (A$)', 'awesome-hotel-booking'),
        'CAD' => esc_html__('CAD – Canadian Dollar (C$)', 'awesome-hotel-booking'),
        'CHF' => esc_html__('CHF – Swiss Franc (CHF)', 'awesome-hotel-booking'),
        'CNY' => esc_html__('CNY – Chinese Yuan (¥)', 'awesome-hotel-booking'),
    ];

    // Handle form submission
    if (isset($_POST['ahbn_save_settings'])) {
        check_admin_referer('ahbn_save_settings_verify');

        update_option('ahbn_hotel_currency', sanitize_text_field($_POST['hotel_currency'] ?? 'USD'));
        update_option('ahbn_checkin_default', sanitize_text_field($_POST['checkin_default'] ?? '14:00'));
        update_option('ahbn_checkout_default', sanitize_text_field($_POST['checkout_default'] ?? '12:00'));

        // Additional fields
        update_option('ahbn_hotel_phone', sanitize_text_field($_POST['hotel_phone'] ?? ''));
        update_option('ahbn_hotel_email', sanitize_email($_POST['hotel_email'] ?? ''));
        update_option('ahbn_hotel_address', sanitize_textarea_field($_POST['hotel_address'] ?? ''));
        update_option('ahbn_min_stay', intval($_POST['min_stay'] ?? 1));
        update_option('ahbn_booking_msg', sanitize_text_field($_POST['booking_msg'] ?? ''));

        echo '<div class="updated notice"><p>' . esc_html__('Settings saved!', 'awesome-hotel-booking') . '</p></div>';
    }

    // Load saved values
    $hotel_currency   = get_option('ahbn_hotel_currency', 'USD');
    $checkin_default  = get_option('ahbn_checkin_default', '14:00');
    $checkout_default = get_option('ahbn_checkout_default', '12:00');
    $hotel_phone      = get_option('ahbn_hotel_phone', '');
    $hotel_email      = get_option('ahbn_hotel_email', '');
    $hotel_address    = get_option('ahbn_hotel_address', '');
    $min_stay         = get_option('ahbn_min_stay', 1);
    $booking_msg      = get_option('ahbn_booking_msg', esc_html__('Thank you for booking!', 'awesome-hotel-booking'));

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
                <th><label for="hotel_currency"><?php esc_html_e('Currency', 'awesome-hotel-booking'); ?></label></th>
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
                <th><label for="checkin_default"><?php esc_html_e('Default Check-in', 'awesome-hotel-booking'); ?></label></th>
                <td><input type="text" id="checkin_default" name="checkin_default" value="<?php echo esc_attr($checkin_default); ?>"></td>
            </tr>

            <tr>
                <th><label for="checkout_default"><?php esc_html_e('Default Check-out', 'awesome-hotel-booking'); ?></label></th>
                <td><input type="text" id="checkout_default" name="checkout_default" value="<?php echo esc_attr($checkout_default); ?>"></td>
            </tr>

            <tr>
                <th><label for="hotel_phone"><?php esc_html_e('Hotel Phone', 'awesome-hotel-booking'); ?></label></th>
                <td><input type="text" name="hotel_phone" value="<?php echo esc_attr($hotel_phone); ?>"></td>
            </tr>

            <tr>
                <th><label for="hotel_email"><?php esc_html_e('Hotel Email', 'awesome-hotel-booking'); ?></label></th>
                <td><input type="email" name="hotel_email" value="<?php echo esc_attr($hotel_email); ?>"></td>
            </tr>

            <tr>
                <th><label for="hotel_address"><?php esc_html_e('Hotel Address', 'awesome-hotel-booking'); ?></label></th>
                <td><textarea name="hotel_address"><?php echo esc_textarea($hotel_address); ?></textarea></td>
            </tr>

            <tr>
                <th><label for="min_stay"><?php esc_html_e('Minimum Stay (nights)', 'awesome-hotel-booking'); ?></label></th>
                <td><input type="number" name="min_stay" value="<?php echo esc_attr($min_stay); ?>" min="1"></td>
            </tr>

            <tr>
                <th><label for="booking_msg"><?php esc_html_e('Booking Confirmation Message', 'awesome-hotel-booking'); ?></label></th>
                <td><input type="text" name="booking_msg" value="<?php echo esc_attr($booking_msg); ?>" style="width:100%;"></td>
            </tr>
        </table>

        <p class="submit">
            <button type="submit" name="ahbn_save_settings" class="button button-primary"><?php esc_html_e('Save Settings', 'awesome-hotel-booking'); ?></button>
        </p>
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
