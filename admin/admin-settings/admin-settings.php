<?php 

// Load sub-tabs files
require plugin_dir_path(__FILE__) . 'tabs/settings-general.php';
require plugin_dir_path(__FILE__) . 'tabs/settings-payments.php';
require plugin_dir_path(__FILE__) . 'tabs/settings-tax.php';
require plugin_dir_path(__FILE__) . 'tabs/settings-emails.php';
require plugin_dir_path(__FILE__) . 'tabs/settings-rules.php';

// =======================================
// MAIN SETTINGS TAB
// =======================================
function ahbn_settings_tab(){

    $sub_tab = isset($_GET['sub_tab']) ? sanitize_text_field($_GET['sub_tab']) : 'general';

    // TOP SUB TABS
    $tabs = [
        'general'       => 'General',
        'payments'      => 'Payments',
        'tax_fees'      => 'Tax & Fees',
        'emails'        => 'Email Templates',
        'booking_rules' => 'Booking Rules'
    ];

    echo '<h2 class="nav-tab-wrapper">';
    foreach ($tabs as $key => $label) {
        $active_class = ($sub_tab === $key) ? 'nav-tab-active' : '';
        $url = add_query_arg([
            'page'    => 'ahbn_booking_main',
            'tab'     => 'settings',
            'sub_tab' => $key
        ], admin_url('admin.php'));

        echo '<a href="' . esc_url($url) . '" class="nav-tab ' . esc_attr($active_class) . '">' . esc_html($label) . '</a>';
    }
    echo '</h2>';

    // Load selected sub-tab content
    switch ($sub_tab) {
        case 'general':        ahbn_settings_general(); break;
        case 'payments':       ahbn_settings_payments(); break;
        case 'tax_fees':       ahbn_settings_tax(); break;
        case 'emails':         ahbn_settings_emails(); break;
        case 'booking_rules':  ahbn_settings_rules(); break;
    }
}
