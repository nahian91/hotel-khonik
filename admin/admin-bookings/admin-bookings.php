<?php
function ahbn_bookings_tab() {
    $sub_tab = isset($_GET['sub_tab']) ? $_GET['sub_tab'] : 'settings';

    echo '<h2 class="nav-tab-wrapper">';
    echo '<a href="?page=ahbn_booking_main&tab=bookings&sub_tab=settings" class="nav-tab '.($sub_tab=='settings'?'nav-tab-active':'').'">Settings</a>';
    echo '<a href="?page=ahbn_booking_main&tab=bookings&sub_tab=add" class="nav-tab '.($sub_tab=='add'?'nav-tab-active':'').'">Add Booking</a>';
    echo '<a href="?page=ahbn_booking_main&tab=bookings&sub_tab=all" class="nav-tab '.($sub_tab=='all'?'nav-tab-active':'').'">All Bookings</a>';
    echo '</h2>';

    // Load sub-tab files
    if($sub_tab=='settings') require plugin_dir_path(__FILE__).'tabs/bookings-settings.php';
    if($sub_tab=='add') require plugin_dir_path(__FILE__).'tabs/bookings-add.php';
    if($sub_tab=='all') require plugin_dir_path(__FILE__).'tabs/bookings-all.php';
    if($sub_tab=='view') require plugin_dir_path(__FILE__).'tabs/bookings-view.php';
}
