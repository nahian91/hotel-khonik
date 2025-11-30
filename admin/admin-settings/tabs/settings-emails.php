<?php
function ahbn_settings_emails() {

    // ----------------------------
    // Handle form submission
    // ----------------------------
    if (isset($_POST['ahbn_save_emails'])) {

        // Security check
        if (!isset($_POST['ahbn_email_nonce']) || !wp_verify_nonce($_POST['ahbn_email_nonce'], 'ahbn_save_emails_action')) {
            echo '<div class="notice notice-error"><p>Security check failed. Settings not saved.</p></div>';
        } else {

            // Email types
            $email_types = ['confirmation', 'cancellation', 'reminder'];

            foreach ($email_types as $type) {
                $enabled = isset($_POST["email_{$type}_enabled"]) ? 1 : 0;
                update_option("ahbn_email_{$type}_enabled", $enabled);
                update_option("ahbn_email_{$type}_from", sanitize_email($_POST["email_{$type}_from"]));
                update_option("ahbn_email_{$type}_subject", sanitize_text_field($_POST["email_{$type}_subject"]));
                update_option("ahbn_email_{$type}_template", wp_kses_post($_POST["email_{$type}_template"]));
            }

            echo '<div class="notice notice-success"><p>Email settings saved successfully!</p></div>';
        }
    }

    $email_types = [
        'confirmation' => 'Booking Confirmation',
        'cancellation' => 'Booking Cancellation',
        'reminder'     => 'Booking Reminder'
    ];
    ?>

    <h2>Email Settings</h2>
    <form method="post">
        <?php wp_nonce_field('ahbn_save_emails_action', 'ahbn_email_nonce'); ?>

        <table class="form-table">
            <tr>
                <th>Available Shortcodes</th>
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
            $subject  = get_option("ahbn_email_{$type}_subject", "{$label} Notification");
            $template = get_option("ahbn_email_{$type}_template", "Hello {customer_name}, your booking {$label}.");

            ?>
            <h3><?php echo esc_html($label); ?> Email</h3>
            <table class="form-table">
                <tr>
                    <th>Enable Email?</th>
                    <td>
                        <label class="switch">
                            <input type="checkbox" name="email_<?php echo $type; ?>_enabled" <?php checked(1, $enabled); ?> class="ahbn-email-toggle" data-target="<?php echo $type; ?>">
                            <span class="slider round"></span>
                        </label>
                    </td>
                </tr>

                <tr>
                    <th>From Email</th>
                    <td><input type="email" name="email_<?php echo $type; ?>_from" value="<?php echo esc_attr($from); ?>" class="regular-text email-field-<?php echo $type; ?>" <?php echo $enabled ? '' : 'disabled'; ?>></td>
                </tr>

                <tr>
                    <th>Email Subject</th>
                    <td><input type="text" name="email_<?php echo $type; ?>_subject" value="<?php echo esc_attr($subject); ?>" class="regular-text email-field-<?php echo $type; ?>" <?php echo $enabled ? '' : 'disabled'; ?>></td>
                </tr>

                <tr>
                    <th>Email Template</th>
                    <td>
                        <?php
                        wp_editor(
                            $template,
                            "email_{$type}_template",
                            [
                                'textarea_name' => "email_{$type}_template",
                                'textarea_rows' => 6,
                                'media_buttons' => false
                            ]
                        );
                        ?>
                        <p class="description">Use available shortcodes to personalize the email.</p>
                    </td>
                </tr>
            </table>
            <hr>
        <?php endforeach; ?>

        <p class="submit">
            <button type="submit" name="ahbn_save_emails" class="button button-primary">Save Email Settings</button>
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
