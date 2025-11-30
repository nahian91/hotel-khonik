<?php
if (!function_exists('ahbn_register_cpts')) :

    // Hook into WordPress 'init' action
    add_action('init', 'ahbn_register_cpts');

    function ahbn_register_cpts() {

        // ----------------------------
        // Register 'Room' CPT
        // ----------------------------
        $room_labels = [
            'name'          => __('Rooms', 'awesome-hotel-booking'),
            'singular_name' => __('Room', 'awesome-hotel-booking'),
            'add_new'       => __('Add New Room', 'awesome-hotel-booking'),
            'add_new_item'  => __('Add New Room', 'awesome-hotel-booking'),
            'edit_item'     => __('Edit Room', 'awesome-hotel-booking'),
            'new_item'      => __('New Room', 'awesome-hotel-booking'),
            'view_item'     => __('View Room', 'awesome-hotel-booking'),
            'search_items'  => __('Search Rooms', 'awesome-hotel-booking'),
            'not_found'     => __('No rooms found', 'awesome-hotel-booking'),
            'not_found_in_trash' => __('No rooms found in Trash', 'awesome-hotel-booking'),
        ];

        register_post_type('ahbn_room', [
            'labels'        => $room_labels,
            'public'        => false,
            'show_ui'       => true,    // Admin edit screens accessible
            'show_in_menu'  => false,   // Hide from menu
            'supports'      => ['title', 'editor', 'thumbnail'],
        ]);

        // ----------------------------
        // Register 'Booking' CPT
        // ----------------------------
        $booking_labels = [
            'name'          => __('Bookings', 'awesome-hotel-booking'),
            'singular_name' => __('Booking', 'awesome-hotel-booking'),
            'add_new'       => __('Add New Booking', 'awesome-hotel-booking'),
            'add_new_item'  => __('Add New Booking', 'awesome-hotel-booking'),
            'edit_item'     => __('Edit Booking', 'awesome-hotel-booking'),
            'new_item'      => __('New Booking', 'awesome-hotel-booking'),
            'view_item'     => __('View Booking', 'awesome-hotel-booking'),
            'search_items'  => __('Search Bookings', 'awesome-hotel-booking'),
            'not_found'     => __('No bookings found', 'awesome-hotel-booking'),
            'not_found_in_trash' => __('No bookings found in Trash', 'awesome-hotel-booking'),
        ];

        register_post_type('ahbn_booking', [
            'labels'        => $booking_labels,
            'public'        => false,
            'show_ui'       => true,
            'show_in_menu'  => false,   // Hide from menu
            'supports'      => ['title'],
        ]);

    }

endif;
