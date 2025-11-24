<?php 

/* -----------------------------
   Settings Tab
----------------------------- */
function hb_settings_tab(){
    $sub_tab = isset($_GET['sub_tab'])?$_GET['sub_tab']:'add_room';
    ?>
    <h2 class="nav-tab-wrapper">
        <a href="?page=hb_bookings&tab=settings&sub_tab=add_room" class="nav-tab <?php echo $sub_tab=='add_room'?'nav-tab-active':''; ?>">Add Room</a>
        <a href="?page=hb_bookings&tab=settings&sub_tab=all_rooms" class="nav-tab <?php echo $sub_tab=='all_rooms'?'nav-tab-active':''; ?>">All Rooms</a>
    </h2>
    <?php
    if($sub_tab=='add_room'){
        $edit_room_id = isset($_GET['edit_room']) ? intval($_GET['edit_room']) : 0;
        hb_add_room_tab($edit_room_id);
    }
    else hb_all_rooms_tab();
}

/* -----------------------------
   Add/Edit Room Tab
----------------------------- */
function hb_add_room_tab($edit_room_id=0){
    if($edit_room_id){
        $room_type = get_post_meta($edit_room_id,'room_type',true);
        $room_nos = get_post_meta($edit_room_id,'room_no',true);
        $room_price = get_post_meta($edit_room_id,'room_price',true);
        $room_facilities = get_post_meta($edit_room_id,'room_facilities',true);
    } else {
        $room_type = $room_nos = $room_price = $room_facilities = '';
    }

    if(isset($_POST['hb_add_room']) && check_admin_referer('hb_add_room_action','hb_add_room_nonce')){
        $room_type = sanitize_text_field($_POST['room_type']);
        $room_nos = sanitize_text_field($_POST['room_no']);
        $room_price = floatval($_POST['room_price']);
        $room_facilities = sanitize_textarea_field($_POST['room_facilities']);

        $room_title = $room_type.' - '.$room_nos;

        $post_data = [
            'post_type'=>'room',
            'post_title'=>$room_title,
            'post_status'=>'publish'
        ];
        if($edit_room_id) $post_data['ID']=$edit_room_id;

        $room_id = wp_insert_post($post_data);

        if($room_id){
            update_post_meta($room_id,'room_type',$room_type);
            update_post_meta($room_id,'room_no',$room_nos);
            update_post_meta($room_id,'room_price',$room_price);
            update_post_meta($room_id,'room_facilities',$room_facilities);
            echo '<div class="notice notice-success is-dismissible"><p>Room saved successfully.</p></div>';
        }
    }
    ?>
    <form method="post">
        <?php wp_nonce_field('hb_add_room_action','hb_add_room_nonce'); ?>
        <table class="form-table">
            <tr>
                <th>Room Type</th>
                <td><input type="text" name="room_type" class="regular-text" required value="<?php echo esc_attr($room_type); ?>"></td>
            </tr>
            <tr>
                <th>Room Numbers</th>
                <td><input type="text" name="room_no" class="regular-text" placeholder="e.g., 101,102" required value="<?php echo esc_attr($room_nos); ?>">
                <p class="description">Enter multiple room numbers separated by commas.</p></td>
            </tr>
            <tr>
                <th>Price per Day</th>
                <td><input type="number" name="room_price" class="regular-text" required value="<?php echo esc_attr($room_price); ?>"></td>
            </tr>
            <tr>
                <th>Room Facilities</th>
                <td><textarea name="room_facilities" class="large-text" rows="4"><?php echo esc_textarea($room_facilities); ?></textarea></td>
            </tr>
        </table>
        <p><input type="submit" name="hb_add_room" class="button button-primary" value="<?php echo $edit_room_id?'Update Room':'Add Room'; ?>"></p>
    </form>
    <?php
}

/* -----------------------------
   All Rooms Tab
----------------------------- */
function hb_all_rooms_tab(){
    $rooms = get_posts(['post_type'=>'room','posts_per_page'=>-1]);
    if(!$rooms){ echo '<p>No rooms found.</p>'; return; }

    echo '<table class="wp-list-table widefat fixed striped">
    <thead><tr>
        <th>Room Type</th>
        <th>Room Numbers</th>
        <th>Price</th>
        <th>Actions</th>
    </tr></thead><tbody>';

    foreach($rooms as $r){
        $room_id = $r->ID;
        $room_type = get_post_meta($room_id,'room_type',true);
        $room_nos = get_post_meta($room_id,'room_no',true);
        $room_price = get_post_meta($room_id,'room_price',true);

        $edit_url = admin_url('admin.php?page=hb_bookings&tab=settings&sub_tab=add_room&edit_room='.$room_id);
        $delete_url = wp_nonce_url(admin_url('admin.php?page=hb_bookings&tab=settings&sub_tab=all_rooms&hb_action=delete_room&room='.$room_id),'hb_delete_room','hb_delete_nonce');

        echo '<tr>
            <td>'.esc_html($room_type).'</td>
            <td>'.esc_html($room_nos).'</td>
            <td>'.esc_html($room_price).'</td>
            <td>
                <a href="'.esc_url($edit_url).'" class="button">Edit</a>
                <a href="'.esc_url($delete_url).'" class="button hb-delete-room">Delete</a>
            </td>
        </tr>';
    }

    echo '</tbody></table>';
    ?>
    <script>
    jQuery(document).ready(function($){
        $('.hb-delete-room').click(function(e){
            if(!confirm('Are you sure you want to delete this room?')) e.preventDefault();
        });
    });
    </script>
    <?php
}

/* -----------------------------
   Delete Room Handler
----------------------------- */
add_action('admin_init',function(){
    if(isset($_GET['hb_action']) && $_GET['hb_action']=='delete_room' && isset($_GET['room'])){
        $room_id = intval($_GET['room']);
        if(check_admin_referer('hb_delete_room','hb_delete_nonce')){
            wp_delete_post($room_id,true);
            wp_redirect(admin_url('admin.php?page=hb_bookings&tab=settings&sub_tab=all_rooms'));
            exit;
        }
    }
});
