<?php
function ahbn_settings_payments() {

    // ----------------------------
    // Handle form submission
    // ----------------------------
    if (isset($_POST['ahbn_save_payments'])) {

        if (!isset($_POST['ahbn_payment_nonce']) || !wp_verify_nonce($_POST['ahbn_payment_nonce'], 'ahbn_save_payments_action')) {
            echo '<div class="notice notice-error"><p>Security check failed. Settings not saved.</p></div>';
        } else {
            // Define payment gateways
            $payments = [
                'bkash'  => ['label' => 'bKash', 'type' => 'text', 'placeholder' => 'bKash Number'],
                'nagad'  => ['label' => 'Nagad', 'type' => 'text', 'placeholder' => 'Nagad Number'],
                'rocket' => ['label' => 'Rocket', 'type' => 'text', 'placeholder' => 'Rocket Number'],
                'bank'   => ['label' => 'Bank Transfer', 'type' => 'textarea', 'placeholder' => 'Bank Information'],
                'cod'    => ['label' => 'Cash on Delivery', 'type' => 'none']
            ];

            foreach ($payments as $key => $config) {
                update_option("ahbn_payment_{$key}_enabled", isset($_POST["{$key}_enabled"]) ? 1 : 0);
                if ($config['type'] === 'text') {
                    update_option("ahbn_payment_{$key}_number", sanitize_text_field($_POST["{$key}_number"]));
                } elseif ($config['type'] === 'textarea') {
                    update_option("ahbn_payment_{$key}_info", sanitize_textarea_field($_POST["{$key}_info"]));
                }
            }

            echo '<div class="notice notice-success"><p>Payment settings saved successfully!</p></div>';
        }
    }

    // ----------------------------
    // Load saved options
    // ----------------------------
    $payments = [
        'bkash'  => ['label' => 'bKash', 'type' => 'text', 'placeholder' => 'bKash Number'],
        'nagad'  => ['label' => 'Nagad', 'type' => 'text', 'placeholder' => 'Nagad Number'],
        'rocket' => ['label' => 'Rocket', 'type' => 'text', 'placeholder' => 'Rocket Number'],
        'bank'   => ['label' => 'Bank Transfer', 'type' => 'textarea', 'placeholder' => 'Bank Information'],
        'cod'    => ['label' => 'Cash on Delivery', 'type' => 'none']
    ];
    ?>

    <h2>Payment Methods</h2>
    <form method="post">
        <?php wp_nonce_field('ahbn_save_payments_action', 'ahbn_payment_nonce'); ?>

        <table class="form-table">
            <?php foreach ($payments as $key => $config) :

                $enabled = get_option("ahbn_payment_{$key}_enabled", $key==='cod'?1:0);
                $value   = $config['type']==='text' ? get_option("ahbn_payment_{$key}_number", '') : get_option("ahbn_payment_{$key}_info", '');
                $field_id = "{$key}_field";
                ?>
                <tr>
                    <th scope="row"><?php echo esc_html($config['label']); ?></th>
                    <td>
                        <label class="switch">
                            <input type="checkbox" name="<?php echo $key; ?>_enabled" class="ahbn-toggle" data-target="<?php echo $field_id; ?>" <?php checked(1,$enabled); ?> <?php echo $config['type']==='none'?'disabled':''; ?>>
                            <span class="slider round"></span>
                        </label>
                        <?php if($config['type']!=='none'): ?>
                            <div id="<?php echo $field_id; ?>" style="<?php echo $enabled ? '' : 'display:none;'; ?> margin-top:10px;">
                                <?php if($config['type']==='text'): ?>
                                    <input type="text" name="<?php echo $key; ?>_number" value="<?php echo esc_attr($value); ?>" placeholder="<?php echo esc_attr($config['placeholder']); ?>" class="regular-text">
                                <?php elseif($config['type']==='textarea'): ?>
                                    <textarea name="<?php echo $key; ?>_info" rows="4" style="width:100%;" placeholder="<?php echo esc_attr($config['placeholder']); ?>"><?php echo esc_textarea($value); ?></textarea>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <p class="submit">
            <button type="submit" class="button button-primary" name="ahbn_save_payments">Save Settings</button>
        </p>
    </form>

    <style>
    /* WordPress style toggle */
    .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
        vertical-align: middle;
        margin-right: 10px;
    }
    .switch input { display:none; }
    .slider {
        position: absolute;
        cursor: pointer;
        top:0; left:0; right:0; bottom:0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 24px;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 2px;
        bottom: 2px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    input:checked + .slider { background-color: #0073aa; }
    input:checked + .slider:before { transform: translateX(26px); }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function(){
        document.querySelectorAll('.ahbn-toggle').forEach(function(checkbox){
            checkbox.addEventListener('change', function(){
                var target = document.getElementById(this.dataset.target);
                if(target) target.style.display = this.checked ? '' : 'none';
            });
        });
    });
    </script>

<?php
}
?>
