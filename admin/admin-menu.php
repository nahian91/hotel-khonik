<?php
/**
 * ----------------------------
 * Admin Menu
 * ----------------------------
 * Adds a top-level menu page for the Awesome Hotel Booking plugin.
 * Icon: Dashicons building
 * Capability: filterable (default: manage_options)
 * Position: 6 (near top of menu)
 */

add_action('admin_menu', 'ahbn_admin_menu');

function ahbn_admin_menu() {

    // Allow filtering the required capability
    $capability = apply_filters('ahbn_admin_capability', 'manage_options');

    // Top-level menu only
    add_menu_page(
        __('Hotel Booking', 'awesome-hotel-booking'), // Page title
        __('Booking', 'awesome-hotel-booking'),       // Menu title
        $capability,                                 // Capability
        'ahbn_booking_main',                          // Menu slug
        'ahbn_admin_page',                            // Callback function
        'dashicons-building',                         // Icon
        6                                             // Position
    );
}
