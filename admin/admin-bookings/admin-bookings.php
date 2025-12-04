<?php
function ahbn_bookings_tab() {

    // Default sub_tab
    $sub_tab = 'settings';

    // Check nonce and GET parameter
    if ( isset($_GET['sub_tab'], $_GET['_wpnonce']) ) {

        // Unsash and sanitize nonce first
        $nonce = sanitize_text_field(wp_unslash($_GET['_wpnonce']));

        if ( wp_verify_nonce($nonce, 'ahbn_bookings_sub_tab') ) {
            $requested_tab = sanitize_text_field(wp_unslash($_GET['sub_tab']));

            // Allowed sub-tabs
            $allowed_tabs = ['settings', 'add', 'all', 'view'];
            if ( in_array($requested_tab, $allowed_tabs, true) ) {
                $sub_tab = $requested_tab;
            }
        }
    }

    // Tabs navigation with nonce in URL
    $base_url = admin_url('admin.php?page=ahbn_booking_main&tab=bookings');
    $nonce    = wp_create_nonce('ahbn_bookings_sub_tab');

    echo '<h2 class="nav-tab-wrapper">';
    echo '<a href="' . esc_url(add_query_arg(['sub_tab' => 'settings', '_wpnonce' => $nonce], $base_url)) . '" class="nav-tab ' . ($sub_tab=='settings' ? 'nav-tab-active' : '') . '">Settings</a>';
    echo '<a href="' . esc_url(add_query_arg(['sub_tab' => 'add', '_wpnonce' => $nonce], $base_url)) . '" class="nav-tab ' . ($sub_tab=='add' ? 'nav-tab-active' : '') . '">Add Booking</a>';
    echo '<a href="' . esc_url(add_query_arg(['sub_tab' => 'all', '_wpnonce' => $nonce], $base_url)) . '" class="nav-tab ' . ($sub_tab=='all' ? 'nav-tab-active' : '') . '">All Bookings</a>';
    echo '</h2>';

    // Load sub-tab files safely
    switch ($sub_tab) {
        case 'settings':
            require plugin_dir_path(__FILE__) . 'tabs/bookings-settings.php';
            break;
        case 'add':
            require plugin_dir_path(__FILE__) . 'tabs/bookings-add.php';
            break;
        case 'all':
            require plugin_dir_path(__FILE__) . 'tabs/bookings-all.php';
            break;
        case 'view':
            require plugin_dir_path(__FILE__) . 'tabs/bookings-view.php';
            break;
    }
}
