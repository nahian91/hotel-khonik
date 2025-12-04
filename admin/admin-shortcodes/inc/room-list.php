<?php
// =====================================
// FULL ROOM LIST SHORTCODE (Same Grid Classes, Vertical List)
// =====================================
add_shortcode('ahbn_rooms_list', function($atts){
    $atts = shortcode_atts(['limit'=>-1], $atts);
    $currency = get_option('ahbn_room_currency', '$');
    $single_room_page = site_url('/single-room/');

    $rooms = get_posts([
        'post_type'   => 'ahbn_room',
        'numberposts' => intval($atts['limit']),
        'orderby'     => 'menu_order',
        'order'       => 'ASC'
    ]);

    if(empty($rooms)) return '<p>No rooms available.</p>';

    $unique_titles = [];
    ob_start();
    ?>

    <div class="ahbn-room-list-wrapper">

        <?php foreach($rooms as $r):
            $title = $r->post_title;
            if(in_array($title, $unique_titles)) continue; // skip duplicates
            $unique_titles[] = $title;

            $room_id   = $r->ID;
            $view_link = add_query_arg('room_id', $room_id, $single_room_page);

            // Room meta
            $price     = get_post_meta($room_id, 'ahbn_price', true);
            $room_type = get_post_meta($room_id, 'ahbn_room_type', true);
            $amenities = get_post_meta($room_id, 'ahbn_amenities', true);

            // Featured image
            if(has_post_thumbnail($room_id)){
                $img_html = get_the_post_thumbnail($room_id,'medium');
            } else {
                $img_html = '<div class="ahbn-room-no-img">No Image</div>';
            }

            // Amenities array
            $amenities_arr = !empty($amenities) ? (is_array($amenities) ? $amenities : explode(',', $amenities)) : [];
        ?>

        <div class="ahbn-rooms-grid">
            
            <!-- Room Image -->
            <div class="ahbn-rooms-grid-image">
                <a href="<?php echo esc_url($view_link); ?>">
                    <?php echo esc_html($img_html); ?>
                </a>
            </div>

            <!-- Room Meta -->
            <div class="ahbn-rooms-grid-meta">
                <span><?php echo esc_html($currency . $price); ?></span>
                <span><?php echo esc_html($room_type); ?></span>
            </div>

            <!-- Room Content -->
            <div class="ahbn-rooms-grid-content">
                <h4><a href="<?php echo esc_url($view_link); ?>"><?php echo esc_html($title); ?></a></h4>
                <ul class="ahbn-room-amenities">
                    <?php foreach($amenities_arr as $a): ?>
                        <li><?php echo esc_html(trim($a)); ?></li>
                    <?php endforeach; ?>
                    <?php if(empty($amenities_arr)) echo '<li>No amenities listed</li>'; ?>
                </ul>
                <a href="<?php echo esc_url($view_link); ?>" class="btn-bg">View Details</a>
            </div>

        </div>

        <?php endforeach; ?>

    </div>

    <?php
    return ob_get_clean();
});
?>
