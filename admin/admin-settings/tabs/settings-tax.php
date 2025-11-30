<?php
function ahbn_settings_tax(){

    if(isset($_POST['ahbn_save_tax'])){
        
        update_option('ahbn_tax_percentage', floatval($_POST['tax_percentage']));
        update_option('ahbn_service_charge', floatval($_POST['service_charge']));
        update_option('ahbn_cleaning_fee', floatval($_POST['cleaning_fee']));
        update_option('ahbn_extra_guest_fee', floatval($_POST['extra_guest_fee']));

        echo '<div class="updated"><p>Tax & Fees settings saved!</p></div>';
    }

    $tax            = get_option('ahbn_tax_percentage',0);
    $service        = get_option('ahbn_service_charge',0);
    $cleaning       = get_option('ahbn_cleaning_fee',0);
    $extra_guest    = get_option('ahbn_extra_guest_fee',0);
?>
<form method="post">
    <table class="form-table">
        <tr><th>Tax (%)</th><td><input type="number" step="0.01" name="tax_percentage" value="<?php echo esc_attr($tax); ?>"></td></tr>
        <tr><th>Service Charge (%)</th><td><input type="number" step="0.01" name="service_charge" value="<?php echo esc_attr($service); ?>"></td></tr>
        <tr><th>Cleaning Fee</th><td><input type="number" step="0.01" name="cleaning_fee" value="<?php echo esc_attr($cleaning); ?>"></td></tr>
        <tr><th>Extra Guest Fee</th><td><input type="number" step="0.01" name="extra_guest_fee" value="<?php echo esc_attr($extra_guest); ?>"></td></tr>
    </table>

    <p><button class="button button-primary" name="ahbn_save_tax">Save Settings</button></p>
</form>
<?php
}
