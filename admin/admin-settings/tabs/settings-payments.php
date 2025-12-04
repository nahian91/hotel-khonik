<?php
function ahbn_settings_payments() {

    // ----------------------------
    // Handle form submission
    // ----------------------------
    if (isset($_POST['save_payment'])) {

        // Security check
        if (!isset($_POST['ahbn_payment_nonce']) || !wp_verify_nonce($_POST['ahbn_payment_nonce'], 'ahbn_save_payment_action')) {
            echo '<div class="notice notice-error"><p>' . esc_html__('Security check failed. Payment settings not saved.', 'awesome-hotel-booking') . '</p></div>';
        } else {
            $bank_transfer = isset($_POST['payment_bank_transfer']) ? 1 : 0;
            $pay_later     = isset($_POST['payment_pay_later']) ? 1 : 0;

            update_option('ahbn_payment_bank_transfer', $bank_transfer);
            update_option('ahbn_payment_pay_later', $pay_later);

            echo '<div class="notice notice-success"><p>' . esc_html__('Payment settings saved successfully!', 'awesome-hotel-booking') . '</p></div>';
        }
    }

    // Get current option values
    $bank_transfer = get_option('ahbn_payment_bank_transfer', 1);
    $pay_later     = get_option('ahbn_payment_pay_later', 1);
    ?>

    <h2><?php esc_html_e('Payment Methods', 'awesome-hotel-booking'); ?></h2>
    <form method="post">
        <?php wp_nonce_field('ahbn_save_payment_action', 'ahbn_payment_nonce'); ?>

        <table class="form-table">
            <tr>
                <th><?php esc_html_e('Enable Bank Transfer', 'awesome-hotel-booking'); ?></th>
                <td>
                    <label class="switch">
                        <input type="checkbox" name="payment_bank_transfer" <?php checked(1, $bank_transfer); ?>>
                        <span class="slider round"></span>
                    </label>
                </td>
            </tr>

            <tr>
                <th><?php esc_html_e('Enable Book Now, Pay Later', 'awesome-hotel-booking'); ?></th>
                <td>
                    <label class="switch">
                        <input type="checkbox" name="payment_pay_later" <?php checked(1, $pay_later); ?>>
                        <span class="slider round"></span>
                    </label>
                </td>
            </tr>
        </table>

        <p class="submit">
            <button type="submit" name="save_payment" class="button button-primary"><?php esc_html_e('Save Payment Settings', 'awesome-hotel-booking'); ?></button>
        </p>
    </form>

    <style>
    /* WordPress-style toggle switch */
    .switch {
      position: relative;
      display: inline-block;
      width: 50px;
      height: 24px;
    }
    .switch input { 
      opacity: 0;
      width: 0;
      height: 0;
    }
    .slider {
      position: absolute;
      cursor: pointer;
      top: 0; left: 0; right: 0; bottom: 0;
      background-color: #ccc;
      transition: 0.4s;
      border-radius: 24px;
    }
    .slider:before {
      position: absolute;
      content: "";
      height: 18px;
      width: 18px;
      left: 3px;
      bottom: 3px;
      background-color: white;
      transition: 0.4s;
      border-radius: 50%;
    }
    input:checked + .slider {
      background-color: #0073aa;
    }
    input:checked + .slider:before {
      transform: translateX(26px);
    }
    </style>

<?php
}
?>
