<?php
/*
Plugin Name: Awesome Hotel Booking
Plugin URI:  https://devnahian.com/awesome-hotel-booking
Description: A powerful WordPress hotel booking system. Manage bookings, rooms, expenses, reports, and settings easily from the admin panel.
Version:     1.0
Author:      Abdullah Nahian
Author URI:  https://devnahian.com/about-me
Text Domain: awesome-hotel-booking
Domain Path: /languages
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
*/

if ( ! defined('ABSPATH') ) exit;

// ----------------------------
// Constants
// ----------------------------
define('AHBN_VERSION', '1.0');
define('AHBN_PATH', plugin_dir_path(__FILE__));
define('AHBN_URL', plugin_dir_url(__FILE__));

// ----------------------------
// Load Text Domain
// ----------------------------
add_action('plugins_loaded', function() {
    load_plugin_textdomain('awesome-hotel-booking', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

// ----------------------------
// Includes
// ----------------------------
require AHBN_PATH . 'admin/admin-cpts.php';
require AHBN_PATH . 'admin/admin-menu.php';
require AHBN_PATH . 'admin/admin-assets.php';
require AHBN_PATH . 'admin/admin-pages.php';
require AHBN_PATH . 'admin/admin-overview.php';

require AHBN_PATH . 'admin/admin-expense/admin-expense.php';
require AHBN_PATH . 'admin/admin-rooms/admin-rooms.php';
require AHBN_PATH . 'admin/admin-bookings/admin-bookings.php';
require AHBN_PATH . 'admin/admin-settings/admin-settings.php';

require AHBN_PATH . 'admin/admin-report/admin-report.php';
require AHBN_PATH . 'admin/admin-shortcodes/admin-shortcodes.php';

// ----------------------------
// Enqueue Admin CSS & JS
// ----------------------------
add_action('admin_enqueue_scripts', function($hook) {
    // Only load on our plugin admin page
    if ($hook !== 'toplevel_page_ahbn_booking_main') return;

    // CSS
    wp_enqueue_style('ahbn-admin-style', AHBN_URL . 'assets/css/admin-style.css', [], AHBN_VERSION);

    // JS
    wp_enqueue_script('ahbn-admin-script', AHBN_URL . 'assets/js/admin-script.js', ['jquery'], AHBN_VERSION, true);
});

// ----------------------------
// Enqueue Frontend CSS & JS
// ----------------------------
add_action('wp_enqueue_scripts', function() {

    // CSS
    wp_enqueue_style(
        'ahbn-frontend-style', 
        AHBN_URL . 'assets/css/frontend-style.css', 
        [], 
        AHBN_VERSION
    );

    // JS
    wp_enqueue_script(
        'ahbn-frontend-script', 
        AHBN_URL . 'assets/js/frontend-script.js', 
        ['jquery'], 
        AHBN_VERSION, 
        true
    );

    // Optional: Localize script for dynamic data
    wp_localize_script('ahbn-frontend-script', 'ahbnFrontend', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('ahbn_frontend_nonce')
    ]);
});


// ----------------------------
// Activation Hook
// (Register CPTs, create default pages, flush permalinks)
// ----------------------------
function ahbn_activate() {

    // 1️⃣ Register CPTs Immediately
    require_once AHBN_PATH . 'admin/admin-cpts.php';
    if (function_exists('ahbn_register_cpts')) {
        ahbn_register_cpts();
    }

    // 2️⃣ Create Default Pages
$pages = [
    [
        'title'   => 'All Rooms - Grid',
        'content' => '[ahbn_rooms limit="-1"]',
        'slug'    => 'all-rooms-grid',
    ],
    [
        'title'   => 'All Rooms - List',
        'content' => '[ahbn_rooms_list limit="-1"]',
        'slug'    => 'all-rooms-list',
    ],
    [
        'title'   => 'Room Search',
        'content' => '[ahbn_room_search]',
        'slug'    => 'room-search',
    ],
    [
        'title'   => 'Single Room',
        'content' => '[ahbn_single_room]',
        'slug'    => 'single-room',
    ],
    [
        'title'   => 'My Account',
        'content' => '[ahbn_my_account]',
        'slug'    => 'my-account',
    ],
];


    foreach ($pages as $page) {

        // Check by slug
        $existing = get_page_by_path($page['slug']);

        if (!$existing) {
            // Create new page
            $page_id = wp_insert_post([
                'post_title'   => $page['title'],
                'post_content' => $page['content'],
                'post_name'    => $page['slug'],
                'post_status'  => 'publish',
                'post_type'    => 'page',
            ]);
        } else {
            // Use existing page
            $page_id = $existing->ID;
        }

        // Add plugin flag
        if ($page_id) {
            update_post_meta($page_id, '_ahbn_default_page', 1);

            // Optional: hide from menus
            update_post_meta($page_id, '_menu_item_visibility', 'hidden');
        }
    }

    // 3️⃣ Flush rewrite rules
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'ahbn_activate');


// ----------------------------
// Deactivation Hook
// ----------------------------
function ahbn_deactivate(){
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'ahbn_deactivate');


// ----------------------------
// Display AHBN Badge with Page Title
// ----------------------------
add_filter('display_post_states', function($states, $post){

    $is_default = get_post_meta($post->ID, '_ahbn_default_page', true);

    if ($is_default) {
        $page_title = get_the_title($post->ID);
        $states['ahbn_default'] = 'AHB Default – ' . $page_title;
    }

    return $states;
}, 10, 2);

// ----------------------------
// Admin CSS for Badge
// ----------------------------
add_action('admin_head', function(){
    echo '<style>
        .post-state.ahbn_default { 
            background: #0073aa; 
            color: #fff; 
            padding: 2px 6px; 
            border-radius: 3px; 
            font-size: 11px; 
            margin-left: 4px;
        }
    </style>';
});
