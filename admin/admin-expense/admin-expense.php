<?php
// ----------------------------
// Expenses + Settings Tabs (Professional Version)
// ----------------------------
function ahbn_expenses_tab() {

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
        $active = ($sub_tab === $key) ? 'nav-tab-active' : '';
        echo '<a href="?page=ahbn_booking_main&tab=expenses&sub_tab=' . esc_attr($key) . '" class="nav-tab ' . $active . '">' . esc_html($label) . '</a>';
    }
    echo '</h2>';

    // -----------------------------
    // Include the tab file only when needed
    // -----------------------------
    if ($sub_tab === 'settings') {
        require plugin_dir_path(__FILE__) . 'tabs/expense-settings.php';
    } elseif ($sub_tab === 'add') {
        require plugin_dir_path(__FILE__) . 'tabs/expense-add.php';
    } elseif ($sub_tab === 'all') {
        require plugin_dir_path(__FILE__) . 'tabs/expense-all.php';
    }
}
