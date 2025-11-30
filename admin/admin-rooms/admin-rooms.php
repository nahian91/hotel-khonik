<?php
// Load CSS/JS
function ahbn_enqueue_admin_assets($hook) {
    if(strpos($hook,'ahbn_booking_main')===false) return;
    wp_enqueue_style('ahbn-admin-css', plugin_dir_url(__FILE__) . '../assets/css/admin-style.css');
    wp_enqueue_script('ahbn-admin-js', plugin_dir_url(__FILE__) . '../assets/js/admin-scripts.js', ['jquery'], false, true);
}
add_action('admin_enqueue_scripts', 'ahbn_enqueue_admin_assets');

// Rooms Tab Main Function
function ahbn_rooms_tab(){
    $sub_tab = isset($_GET['sub_tab']) ? $_GET['sub_tab'] : 'settings';

    echo '<h2 class="nav-tab-wrapper">';
    echo '<a href="?page=ahbn_booking_main&tab=rooms&sub_tab=settings" class="nav-tab '.($sub_tab=='settings'?'nav-tab-active':'').'">Settings</a>';
    echo '<a href="?page=ahbn_booking_main&tab=rooms&sub_tab=add" class="nav-tab '.($sub_tab=='add'?'nav-tab-active':'').'">Add Room</a>';
    echo '<a href="?page=ahbn_booking_main&tab=rooms&sub_tab=all" class="nav-tab '.($sub_tab=='all'?'nav-tab-active':'').'">All Rooms</a>';
    echo '</h2>';

    // Load sub-tab files
    // Load sub-tab files
switch($sub_tab){
    case 'settings':
        require plugin_dir_path(__FILE__) . 'tabs/room-settings.php';
        break;

    case 'add':
        require plugin_dir_path(__FILE__) . 'tabs/room-add.php';
        break;

    case 'all':
        require plugin_dir_path(__FILE__) . 'tabs/room-all.php';
        break;

    case 'edit':
        require plugin_dir_path(__FILE__) . 'tabs/room-edit.php';
        break;
}

}
