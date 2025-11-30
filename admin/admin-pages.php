<?php
/**
 * ----------------------------
 * Admin Page: Modern Top Tabs (Dashboard Style)
 * ----------------------------
 * Tab Order:
 * Overview → Rooms → Bookings → Expense → Reports → Shortcodes → Settings
 */

function ahbn_admin_page() {

    // Get current tab from URL, default to 'overview'
    $tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'overview';

    // Define tabs array with Dashicons
    $tabs = [
        'overview'   => ['label' => __('Overview', 'awesome-hotel-booking'), 'icon' => 'dashicons-dashboard'],
        'rooms'      => ['label' => __('Rooms', 'awesome-hotel-booking'), 'icon' => 'dashicons-admin-home'],
        'bookings'   => ['label' => __('Bookings', 'awesome-hotel-booking'), 'icon' => 'dashicons-list-view'],
        'expenses'   => ['label' => __('Expense', 'awesome-hotel-booking'), 'icon' => 'dashicons-money'],
        'reports'    => ['label' => __('Reports', 'awesome-hotel-booking'), 'icon' => 'dashicons-chart-bar'],
        'shortcodes' => ['label' => __('Shortcodes', 'awesome-hotel-booking'), 'icon' => 'dashicons-editor-code'],
        'settings'   => ['label' => __('Settings', 'awesome-hotel-booking'), 'icon' => 'dashicons-admin-generic'],
    ];

    ?>
    <div class="wrap ahbn-admin-wrap">
        <!-- Page Title -->
        <h1 class="ahbn-admin-title"><?php echo esc_html__('Awesome Hotel Booking', 'awesome-hotel-booking'); ?></h1>

        <!-- Top Tab Navigation -->
        <div class="ahbn-tabs">
            <?php
            foreach ($tabs as $key => $tab_info) {
                $active = $tab === $key ? 'ahbn-tab-active' : '';
                ?>
                <a href="?page=ahbn_booking_main&tab=<?php echo esc_attr($key); ?>" class="ahbn-tab <?php echo esc_attr($active); ?>">
                    <span class="dashicons <?php echo esc_attr($tab_info['icon']); ?>" style="margin-right:5px;"></span>
                    <?php echo esc_html($tab_info['label']); ?>
                </a>
                <?php
            }
            ?>
        </div>

        <!-- Main Content Area -->
        <div class="ahbn-content">
            <?php
            // Dynamic callback function for the active tab
            $callback = "ahbn_{$tab}_tab";

            if (function_exists($callback)) {
                call_user_func($callback);
            } else {
                echo '<p>' . esc_html__('Invalid tab selected.', 'awesome-hotel-booking') . '</p>';
            }
            ?>
        </div>
    </div>
    <?php
}
