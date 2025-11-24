<?php 

/* -----------------------------
   Register Room CPT
----------------------------- */
function hb_register_room_cpt(){
    register_post_type('room',[
        'labels'=>[
            'name'=>'Rooms',
            'singular_name'=>'Room',
            'menu_name'=>'Rooms',
            'add_new'=>'Add Room',
            'add_new_item'=>'Add New Room',
            'edit_item'=>'Edit Room'
        ],
        'public'=>false,
        'show_ui'=>true,
        'supports'=>['title','custom-fields'],
        'menu_icon'=>'dashicons-building',
    ]);
}
add_action('init','hb_register_room_cpt');

/* -----------------------------
   Register Booking CPT
----------------------------- */
function hb_register_booking_cpt(){
    register_post_type('booking',[
        'labels'=>[
            'name'=>'Bookings',
            'singular_name'=>'Booking',
            'menu_name'=>'Bookings',
            'add_new'=>'Add Booking',
        ],
        'public'=>false,
        'show_ui'=>false,
        'supports'=>['title'],
        'menu_icon'=>'dashicons-admin-home',
    ]);
}
add_action('init','hb_register_booking_cpt');