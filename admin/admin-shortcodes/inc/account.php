<?php
if (!defined('ABSPATH')) exit;

add_shortcode('ahbn_my_account', function() {
    if (!is_user_logged_in()) {
        /* translators: %s is replaced with a login link */
        $message = sprintf(
            esc_html__('Please %s to access your account.', 'awesome-hotel-booking'),
            '<a href="' . esc_url(wp_login_url(get_permalink())) . '">' . esc_html__('login', 'awesome-hotel-booking') . '</a>'
        );
        echo '<p>' . wp_kses_post($message) . '</p>';
        return '';
    }

    $user_id = get_current_user_id();
    $user    = wp_get_current_user();

    // ---------------- Handle Profile Save ----------------
    if (isset($_POST['ahbn_save_profile'])) {
        $nonce = isset($_POST['ahbn_profile_nonce']) ? wp_unslash($_POST['ahbn_profile_nonce']) : '';
        if (!wp_verify_nonce($nonce, 'ahbn_profile_save')) {
            echo '<div class="ahbn-notice ahbn-error">' . esc_html__('Security check failed!', 'awesome-hotel-booking') . '</div>';
        } else {

            // Name
            $name = isset($_POST['ahbn_name']) ? sanitize_text_field(wp_unslash($_POST['ahbn_name'])) : '';
            if ($name) {
                wp_update_user([
                    'ID'            => $user_id,
                    'display_name'  => $name,
                    'user_nicename' => sanitize_title($name),
                ]);
            }

            // Password
            $password         = isset($_POST['ahbn_password']) ? wp_unslash($_POST['ahbn_password']) : '';
            $password_confirm = isset($_POST['ahbn_password_confirm']) ? wp_unslash($_POST['ahbn_password_confirm']) : '';
            if ($password) {
                if ($password === $password_confirm) {
                    wp_set_password(sanitize_text_field($password), $user_id);
                } else {
                    echo '<div class="ahbn-notice ahbn-error">' . esc_html__('Passwords do not match!', 'awesome-hotel-booking') . '</div>';
                }
            }

            // Images
            $profile_image_id = isset($_POST['ahbn_profile_image']) ? intval($_POST['ahbn_profile_image']) : 0;
            $nid_image_id     = isset($_POST['ahbn_profile_nid']) ? intval($_POST['ahbn_profile_nid']) : 0;
            update_user_meta($user_id, 'ahbn_profile_image', $profile_image_id);
            update_user_meta($user_id, 'ahbn_profile_nid', $nid_image_id);

            echo '<div class="ahbn-notice ahbn-success">' . esc_html__('Profile saved successfully!', 'awesome-hotel-booking') . '</div>';
        }
    }

    // ---------------- User Meta ----------------
    $profile_image_id = get_user_meta($user_id, 'ahbn_profile_image', true);
    $nid_image_id     = get_user_meta($user_id, 'ahbn_profile_nid', true);

    $profile_img_url = $profile_image_id ? wp_get_attachment_url($profile_image_id) : '';
    $nid_img_url     = $nid_image_id ? wp_get_attachment_url($nid_image_id) : '';

    // ---------------- User Bookings ----------------
    $bookings = get_posts([
        'post_type'   => 'ahbn_booking',
        'author'      => $user_id,
        'numberposts' => -1,
        'orderby'     => 'ID',
        'order'       => 'DESC',
    ]);

    ob_start();
    ?>
    <div class="ahbn-my-account-tabs">
        <ul class="ahbn-tabs-nav">
            <li class="active" data-tab="profile"><?php esc_html_e('Profile', 'awesome-hotel-booking'); ?></li>
            <li data-tab="bookings"><?php esc_html_e('Bookings', 'awesome-hotel-booking'); ?></li>
            <li><a href="<?php echo esc_url(wp_logout_url(get_permalink())); ?>"><?php esc_html_e('Logout', 'awesome-hotel-booking'); ?></a></li>
        </ul>

        <!-- Profile Tab -->
        <div class="ahbn-tab-content active" id="tab-profile">
            <form method="post">
                <?php wp_nonce_field('ahbn_profile_save','ahbn_profile_nonce'); ?>

                <p>
                    <label><?php esc_html_e('Name', 'awesome-hotel-booking'); ?></label><br>
                    <input type="text" name="ahbn_name" value="<?php echo esc_attr($user->display_name); ?>" required>
                </p>

                <p>
                    <label><?php esc_html_e('Password', 'awesome-hotel-booking'); ?></label><br>
                    <input type="password" name="ahbn_password">
                </p>

                <p>
                    <label><?php esc_html_e('Confirm Password', 'awesome-hotel-booking'); ?></label><br>
                    <input type="password" name="ahbn_password_confirm">
                </p>

                <!-- Profile Image -->
                <p>
                    <label><?php esc_html_e('Profile Photo', 'awesome-hotel-booking'); ?></label><br>
                    <input type="hidden" name="ahbn_profile_image" id="ahbn_profile_image" value="<?php echo esc_attr($profile_image_id); ?>">
                    <img id="ahbn_profile_image_preview" src="<?php echo esc_url($profile_img_url); ?>" style="max-width:150px;display:block;margin-bottom:5px;">
                    <button id="ahbn_profile_image_button" class="button"><?php esc_html_e('Select/Upload Image', 'awesome-hotel-booking'); ?></button>
                    <button id="ahbn_profile_image_remove" class="button"><?php esc_html_e('Remove', 'awesome-hotel-booking'); ?></button>
                </p>

                <!-- NID Image -->
                <p>
                    <label><?php esc_html_e('NID / Passport', 'awesome-hotel-booking'); ?></label><br>
                    <input type="hidden" name="ahbn_profile_nid" id="ahbn_profile_nid" value="<?php echo esc_attr($nid_image_id); ?>">
                    <img id="ahbn_profile_nid_preview" src="<?php echo esc_url($nid_img_url); ?>" style="max-width:150px;display:block;margin-bottom:5px;">
                    <button id="ahbn_profile_nid_button" class="button"><?php esc_html_e('Select/Upload NID', 'awesome-hotel-booking'); ?></button>
                    <button id="ahbn_profile_nid_remove" class="button"><?php esc_html_e('Remove', 'awesome-hotel-booking'); ?></button>
                </p>

                <p><input type="submit" name="ahbn_save_profile" value="<?php esc_attr_e('Save Profile', 'awesome-hotel-booking'); ?>" class="button button-primary"></p>
            </form>
        </div>

        <!-- Bookings Tab -->
        <div class="ahbn-tab-content" id="tab-bookings">
            <?php if ($bookings): ?>
                <table class="ahbn-bookings-table">
                    <tr>
                        <th><?php esc_html_e('Booking ID', 'awesome-hotel-booking'); ?></th>
                        <th><?php esc_html_e('Room', 'awesome-hotel-booking'); ?></th>
                        <th><?php esc_html_e('Check In', 'awesome-hotel-booking'); ?></th>
                        <th><?php esc_html_e('Check Out', 'awesome-hotel-booking'); ?></th>
                        <th><?php esc_html_e('Status', 'awesome-hotel-booking'); ?></th>
                        <th><?php esc_html_e('Amount', 'awesome-hotel-booking'); ?></th>
                    </tr>
                    <?php foreach ($bookings as $b):
                        $room_id    = get_post_meta($b->ID,'ahbn_room_id',true);
                        $room_title = $room_id ? get_the_title($room_id) : '';
                        $check_in   = get_post_meta($b->ID,'ahbn_check_in',true);
                        $check_out  = get_post_meta($b->ID,'ahbn_check_out',true);
                        $status     = get_post_meta($b->ID,'ahbn_status',true);
                        $amount     = get_post_meta($b->ID,'ahbn_amount',true);
                    ?>
                        <tr>
                            <td><?php echo esc_html($b->ID); ?></td>
                            <td><?php echo esc_html($room_title); ?></td>
                            <td><?php echo esc_html($check_in); ?></td>
                            <td><?php echo esc_html($check_out); ?></td>
                            <td><?php echo esc_html(ucfirst($status)); ?></td>
                            <td><?php echo esc_html($amount); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p><?php esc_html_e('No bookings found.', 'awesome-hotel-booking'); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <style>
        .ahbn-tabs-nav { list-style:none; padding:0; display:flex; gap:10px; margin-bottom:20px; }
        .ahbn-tabs-nav li { cursor:pointer; padding:5px 10px; background:#eee; border-radius:3px; }
        .ahbn-tabs-nav li.active { background:#0073aa; color:#fff; }
        .ahbn-tab-content { display:none; }
        .ahbn-tab-content.active { display:block; }
        .ahbn-bookings-table { width:100%; border-collapse:collapse; }
        .ahbn-bookings-table th, .ahbn-bookings-table td { border:1px solid #ccc; padding:5px; text-align:left; }
    </style>

    <script>
    jQuery(document).ready(function($){
        // Tabs
        $('.ahbn-tabs-nav li').click(function(){
            var tab = $(this).data('tab');
            if(!tab) return;
            $('.ahbn-tabs-nav li').removeClass('active');
            $(this).addClass('active');
            $('.ahbn-tab-content').removeClass('active');
            $('#tab-'+tab).addClass('active');
        });

        // Media uploader
        if (typeof wp !== 'undefined' && wp.media) {
            var profileFrame, nidFrame;

            $('#ahbn_profile_image_button').click(function(e){
                e.preventDefault();
                if(profileFrame) profileFrame.open();
                profileFrame = wp.media({ title:'Select Profile Image', button:{text:'Use Image'}, multiple:false });
                profileFrame.on('select', function(){
                    var attachment = profileFrame.state().get('selection').first().toJSON();
                    $('#ahbn_profile_image').val(attachment.id);
                    $('#ahbn_profile_image_preview').attr('src', attachment.url);
                });
                profileFrame.open();
            });
            $('#ahbn_profile_image_remove').click(function(e){ e.preventDefault(); $('#ahbn_profile_image').val(''); $('#ahbn_profile_image_preview').attr('src',''); });

            $('#ahbn_profile_nid_button').click(function(e){
                e.preventDefault();
                if(nidFrame) nidFrame.open();
                nidFrame = wp.media({ title:'Select NID / Passport', button:{text:'Use Image'}, multiple:false });
                nidFrame.on('select', function(){
                    var attachment = nidFrame.state().get('selection').first().toJSON();
                    $('#ahbn_profile_nid').val(attachment.id);
                    $('#ahbn_profile_nid_preview').attr('src', attachment.url);
                });
                nidFrame.open();
            });
            $('#ahbn_profile_nid_remove').click(function(e){ e.preventDefault(); $('#ahbn_profile_nid').val(''); $('#ahbn_profile_nid_preview').attr('src',''); });
        }
    });
    </script>
    <?php
    return ob_get_clean();
});
