<?php
function ahbn_settings_emails() {

    // ----------------------------
    // Handle form submission
    // ----------------------------
    if (isset($_POST['ahbn_save_emails'])) {

        // Security check
        if (!isset($_POST['ahbn_email_nonce']) || !wp_verify_nonce($_POST['ahbn_email_nonce'], 'ahbn_save_emails_action')) {
            echo '<div class="notice notice-error"><p>' . esc_html__('Security check failed. Settings not saved.', 'awesome-hotel-booking') . '</p></div>';
        } else {

            // Email types
            $email_types = ['confirmation', 'cancellation', 'reminder'];

            foreach ($email_types as $type) {
                $enabled  = isset($_POST["email_{$type}_enabled"]) ? 1 : 0;
                $from     = sanitize_email($_POST["email_{$type}_from"] ?? '');
                $subject  = sanitize_text_field($_POST["email_{$type}_subject"] ?? '');
                $template = wp_kses_post($_POST["email_{$type}_template"] ?? '');

                update_option("ahbn_email_{$type}_enabled", $enabled);
                update_option("ahbn_email_{$type}_from", $from);
                update_option("ahbn_email_{$type}_subject", $subject);
                update_option("ahbn_email_{$type}_template", $template);
            }

            echo '<div class="notice notice-success"><p>' . esc_html__('Email settings saved successfully!', 'awesome-hotel-booking') . '</p></div>';
        }
    }

    // Define email types and labels
    $email_types = [
        'confirmation' => esc_html__('Booking Confirmation', 'awesome-hotel-booking'),
        'cancellation' => esc_html__('Booking Cancellation', 'awesome-hotel-booking'),
        'reminder'     => esc_html__('Booking Reminder', 'awesome-hotel-booking')
    ];
    ?>

    <h2><?php esc_html_e('Email Settings', 'awesome-hotel-booking'); ?></h2>
    <form method="post">
        <?php wp_nonce_field('ahbn_save_emails_action', 'ahbn_email_nonce'); ?>

        <table class="form-table">
            <tr>
                <th><?php esc_html_e('Available Shortcodes', 'awesome-hotel-booking'); ?></th>
                <td>
                    <code>{customer_name}</code>,
                    <code>{checkin}</code>,
                    <code>{checkout}</code>,
                    <code>{room}</code>,
                    <code>{amount}</code>
                </td>
            </tr>
        </table>

        <hr>

        <?php foreach ($email_types as $type => $label) :

            $enabled  = get_option("ahbn_email_{$type}_enabled", 1);
            $from     = get_option("ahbn_email_{$type}_from", 'noreply@hotel.com');

            // translators: %s is the email type label (e.g., Booking Confirmation)
            $subject  = get_option("ahbn_email_{$type}_subject", sprintf(esc_html__('%s Notification', 'awesome-hotel-booking'), $label));

            // translators: %s is the email type label (e.g., Booking Confirmation)
            $template = get_option("ahbn_email_{$type}_template", sprintf(esc_html__('Hello {customer_name}, your booking %s.', 'awesome-hotel-booking'), $label));

            ?>
            <h3><?php echo esc_html($label) . ' ' . esc_html__('Email', 'awesome-hotel-booking'); ?></h3>
            <table class="form-table">
                <tr>
                    <th><?php esc_html_e('Enable Email?', 'awesome-hotel-booking'); ?></th>
                    <td>
                        <label class="switch">
                            <input type="checkbox" name="email_<?php echo esc_attr($type); ?>_enabled" <?php checked(1, $enabled); ?> class="ahbn-email-toggle" data-target="<?php echo esc_attr($type); ?>">
                            <span class="slider round"></span>
                        </label>
                    </td>
                </tr>

                <tr>
                    <th><?php esc_html_e('From Email', 'awesome-hotel-booking'); ?></th>
                    <td><input type="email" name="email_<?php echo esc_attr($type); ?>_from" value="<?php echo esc_attr($from); ?>" class="regular-text email-field-<?php echo esc_attr($type); ?>" <?php echo $enabled ? '' : 'disabled'; ?>></td>
                </tr>

                <tr>
                    <th><?php esc_html_e('Email Subject', 'awesome-hotel-booking'); ?></th>
                    <td><input type="text" name="email_<?php echo esc_attr($type); ?>_subject" value="<?php echo esc_attr($subject); ?>" class="regular-text email-field-<?php echo esc_attr($type); ?>" <?php echo $enabled ? '' : 'disabled'; ?>></td>
                </tr>

                <tr>
                    <th><?php esc_html_e('Email Template', 'awesome-hotel-booking'); ?></th>
                    <td>
                        <?php
                        wp_editor(
                            wp_kses_post($template),
                            "email_{$type}_template",
                            [
                                'textarea_name' => "email_{$type}_template",
                                'textarea_rows' => 6,
                                'media_buttons' => false
                            ]
                        );
                        ?>
                        <p class="description"><?php esc_html_e('Use available shortcodes to personalize the email.', 'awesome-hotel-booking'); ?></p>
                    </td>
                </tr>
            </table>
            <hr>
        <?php endforeach; ?>

        <p class="submit">
            <button type="submit" name="ahbn_save_emails" class="button button-primary"><?php esc_html_e('Save Email Settings', 'awesome-hotel-booking'); ?></button>
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

    <script>
    jQuery(document).ready(function($){
        $('.ahbn-email-toggle').on('change', function(){
            var type = $(this).data('target');
            var checked = $(this).is(':checked');
            $('.email-field-' + type).prop('disabled', !checked);
        });
    });
    </script>

<?php
}
?>
