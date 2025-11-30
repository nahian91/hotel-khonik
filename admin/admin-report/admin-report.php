<?php
// ----------------------------
// Reports Tab – Customers, Revenue & Expenses
// ----------------------------
function ahbn_reports_tab() {

    // Determine active sub-tab
    $sub_tab = isset($_GET['sub_tab']) ? sanitize_text_field(wp_unslash($_GET['sub_tab'])) : 'customers';

    // ---------- Sub Tab Navigation ----------
    echo '<h2 class="nav-tab-wrapper">';
    echo '<a href="?page=ahbn_booking_main&tab=reports&sub_tab=customers" class="nav-tab '.($sub_tab=='customers'?'nav-tab-active':'').'">Customers</a>';
    echo '<a href="?page=ahbn_booking_main&tab=reports&sub_tab=revenue" class="nav-tab '.($sub_tab=='revenue'?'nav-tab-active':'').'">Revenue</a>';
    echo '<a href="?page=ahbn_booking_main&tab=reports&sub_tab=expenses" class="nav-tab '.($sub_tab=='expenses'?'nav-tab-active':'').'">Expenses</a>';
    echo '</h2>';

    echo '<div style="margin-top:20px;">';

    // Include tab-specific files
    $tab_file = plugin_dir_path(__FILE__) . "tabs/report-{$sub_tab}.php";
    if(file_exists($tab_file)){
        require $tab_file;
    } else {
        echo '<p>Invalid sub-tab selected.</p>';
    }

    echo '</div>';
}
