<?php
function ahbn_shortcodes_tab() { ?>
    <div class="wrap">
        <h1>📌 Available Shortcodes</h1>
        <table class="wp-list-table widefat fixed striped posts">
            <thead>
                <tr>
                    <th scope="col" class="manage-column column-title">Shortcode</th>
                    <th scope="col" class="manage-column column-title">Description</th>
                    <th scope="col" class="manage-column column-title">Copy</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>[ahbn_rooms]</code></td>
                    <td>Shows rooms grid.</td>
                    <td><button class="button button-secondary ahbn-copy-btn" data-shortcode="[ahbn_rooms]">Copy</button></td>
                </tr>
                <tr>
                    <td><code>[ahbn_rooms limit="6"]</code></td>
                    <td>Shows limited rooms.</td>
                    <td><button class="button button-secondary ahbn-copy-btn" data-shortcode='[ahbn_rooms limit="6"]'>Copy</button></td>
                </tr>
                <tr>
                    <td><code>[ahbn_rooms_list]</code></td>
                    <td>Shows rooms in list view.</td>
                    <td><button class="button button-secondary ahbn-copy-btn" data-shortcode="[ahbn_rooms_list]">Copy</button></td>
                </tr>
                <tr>
                    <td><code>[ahbn_room_search]</code></td>
                    <td>Room search form + results.</td>
                    <td><button class="button button-secondary ahbn-copy-btn" data-shortcode="[ahbn_room_search]">Copy</button></td>
                </tr>
                <tr>
                    <td><code>[ahbn_single_room room_id="123"]</code></td>
                    <td>Single room booking page by ID or room_id parameter.</td>
                    <td><button class="button button-secondary ahbn-copy-btn" data-shortcode='[ahbn_single_room room_id="123"]'>Copy</button></td>
                </tr>
                <tr>
                    <td><code>[ahbn_account_button]</code></td>
                    <td>Displays a My Account button. Shows user name if logged in, otherwise Login/Register links.</td>
                    <td><button class="button button-secondary ahbn-copy-btn" data-shortcode="[ahbn_account_button]">Copy</button></td>
                </tr>
            </tbody>
        </table>

        <script>
        document.addEventListener('DOMContentLoaded', function(){
            document.querySelectorAll('.ahbn-copy-btn').forEach(btn => {
                btn.addEventListener('click', function(){
                    navigator.clipboard.writeText(this.dataset.shortcode)
                    .then(() => alert('Shortcode copied!'));
                });
            });
        });
        </script>
    </div>
<?php }

// =====================================
// Include All Shortcode Files Directly
// =====================================
require plugin_dir_path(__FILE__) . 'inc/ajax-booking.php';
require plugin_dir_path(__FILE__) . 'inc/room-grid.php';
require plugin_dir_path(__FILE__) . 'inc/room-search.php';
require plugin_dir_path(__FILE__) . 'inc/room-list.php';
require plugin_dir_path(__FILE__) . 'inc/room-single.php'; 
require plugin_dir_path(__FILE__) . 'inc/account.php'; 

// ==========================================
// Shortcode: [ahbn_account_button]
// Displays My Account button if logged in, otherwise Login/Register link
// ==========================================
add_shortcode('ahbn_account_button', function() {

    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        $name = $user->first_name ? $user->first_name : $user->user_login;

        // Optional: Use My Account page if it exists
        $account_page = get_page_by_path('my-account'); 
        $url = $account_page ? get_permalink($account_page->ID) : '#';

        return '<a href="'.esc_url($url).'" class="button button-primary">Hello, '.esc_html($name).'</a>';

    } else {
        $login_page = get_page_by_path('my-account'); 
        $url = $login_page ? get_permalink($login_page->ID) : wp_login_url();

        return '<a href="'.esc_url($url).'" class="button button-primary">Login / Register</a>';
    }

});
