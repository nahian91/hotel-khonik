<?php
// ----------------------------
// Expenses + Settings Tabs (Professional Version)
// ----------------------------
function ahbn_expenses_tab() {

    // Get current sub-tab
    $sub_tab = isset($_GET['sub_tab']) ? sanitize_text_field($_GET['sub_tab']) : 'add';

    // -----------------------------
    // Sub-navigation
    // -----------------------------
    echo '<h2 class="nav-tab-wrapper">';
    $tabs = [
        'settings' => 'Settings',
        'add'      => 'Add Expense',
        'all'      => 'All Expenses',
    ];

    foreach ($tabs as $key => $label) {
        // Determine active tab
        $active = ($sub_tab === $key) ? 'nav-tab-active' : '';
        
        // Properly escape all output
        echo '<a href="?page=' . esc_attr('ahbn_booking_main') . '&tab=' . esc_attr('expenses') . '&sub_tab=' . esc_attr($key) . '" class="nav-tab ' . esc_attr($active) . '">' . esc_html($label) . '</a>';
    }
    echo '</h2>';

    // -----------------------------
    // Include the tab file only when needed
    // -----------------------------
    if ($sub_tab === 'settings') {
        $file = plugin_dir_path(__FILE__) . 'tabs/expense-settings.php';
        if (file_exists($file)) {
            require $file;
        }
    } elseif ($sub_tab === 'add') {
        $file = plugin_dir_path(__FILE__) . 'tabs/expense-add.php';
        if (file_exists($file)) {
            require $file;
        }
    } elseif ($sub_tab === 'all') {
        $file = plugin_dir_path(__FILE__) . 'tabs/expense-all.php';
        if (file_exists($file)) {
            require $file;
        }
    }
}
