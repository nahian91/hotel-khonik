<?php
/*
Plugin Name: Hotel Bookings
Description: Complete hotel booking system with bookings, rooms, reports and settings.
Version: 2.0
Author: Abdullah Nahian
*/

if(!defined('ABSPATH')) exit;

include plugin_dir_path(__FILE__) . 'admin/cpt.php';
include plugin_dir_path(__FILE__) . 'admin/menu.php';
include plugin_dir_path(__FILE__) . 'admin/booking.php';
include plugin_dir_path(__FILE__) . 'admin/reports.php';
include plugin_dir_path(__FILE__) . 'admin/settings.php';


// Redirect after login to custom bookings page
add_filter('login_redirect', 'hb_custom_login_redirect', 10, 3);
function hb_custom_login_redirect($redirect_to, $request, $user) {
    // Only redirect admins
    if (isset($user->roles) && in_array('administrator', $user->roles)) {
        return admin_url('admin.php?page=hb_bookings');
    }
    return $redirect_to;
}
