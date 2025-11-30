<?php
if (!defined('ABSPATH')) exit;

function ahbn_get_unavailable_rooms($checkin, $checkout) {
    if(empty($checkin) || empty($checkout)) return [];
    if(strtotime($checkin) >= strtotime($checkout)) return [];

    $bookings = get_posts([
        'post_type' => 'ahbn_booking',
        'posts_per_page' => -1,
        'meta_query' => [
            'relation' => 'AND',
            [
                'key' => 'ahbn_checkin',
                'value' => $checkout,
                'compare' => '<',
                'type' => 'DATE'
            ],
            [
                'key' => 'ahbn_checkout',
                'value' => $checkin,
                'compare' => '>',
                'type' => 'DATE'
            ]
        ]
    ]);

    $unavailable = [];
    foreach($bookings as $b){
        $room_id = get_post_meta($b->ID, 'ahbn_room_id', true);
        if($room_id) $unavailable[] = $room_id;
    }

    return array_unique($unavailable);
}

add_shortcode('ahbn_room_search', function(){
    wp_enqueue_style('ahbn-room-search-css', plugin_dir_url(__FILE__) . 'assets/room-search.css');

    $currency = get_option('ahbn_room_currency', '$');
    $today = date('Y-m-d');
    $tomorrow = date('Y-m-d', strtotime('+1 day'));

    $all_rooms = get_posts([
        'post_type' => 'ahbn_room',
        'posts_per_page' => -1,
        'fields' => 'ids',
    ]);

    $room_types = [];
    $amenities = [];
    $prices = [];

    foreach($all_rooms as $room_id){
        $terms = wp_get_post_terms($room_id, 'ahbn_room_type');
        if(!is_wp_error($terms) && !empty($terms)){
            foreach($terms as $t){
                $slug = is_object($t)? ($t->slug ?? '') : ($t['slug'] ?? '');
                $name = is_object($t)? ($t->name ?? '') : ($t['name'] ?? '');
                if($slug && $name) $room_types[$slug] = $name;
            }
        }

        $room_amenities = get_post_meta($room_id,'ahbn_amenities',true);
        if(!empty($room_amenities)){
            $room_amenities = is_array($room_amenities)? $room_amenities : explode(',',$room_amenities);
            $amenities = array_merge($amenities, $room_amenities);
        }

        $price = get_post_meta($room_id,'ahbn_price',true);
        if(is_numeric($price)) $prices[] = $price;
    }

    $amenities = array_unique($amenities);
    $min_price = !empty($prices)? min($prices) : 500;
    $max_price = !empty($prices)? max($prices) : 10000;
    $default_price = $max_price;

    // Get search parameters if form submitted
    $checkin = sanitize_text_field($_GET['checkin'] ?? '');
    $checkout = sanitize_text_field($_GET['checkout'] ?? '');
    $adults = intval($_GET['adults'] ?? 0);
    $type = sanitize_text_field($_GET['type'] ?? '');
    $price = intval($_GET['price'] ?? $max_price);
    $selected_amenities = $_GET['amenities'] ?? [];

    // Default: show all rooms
    $filtered_rooms = $all_rooms;

    if(!empty($_GET)){ // If form submitted, filter
        $unavailable_rooms = ahbn_get_unavailable_rooms($checkin,$checkout);

        $args = [
            'post_type'=>'ahbn_room',
            'posts_per_page'=>-1,
            'orderby'=>'menu_order',
            'order'=>'ASC',
            'meta_query'=>['relation'=>'AND'],
        ];

        if($adults) $args['meta_query'][] = ['key'=>'ahbn_max_guests','value'=>$adults,'compare'=>'>=','type'=>'NUMERIC'];
        if($price) $args['meta_query'][] = ['key'=>'ahbn_price','value'=>$price,'compare'=>'<=','type'=>'NUMERIC'];

        if(!empty($selected_amenities)){
            foreach($selected_amenities as $a){
                $args['meta_query'][] = ['key'=>'ahbn_amenities','value'=>strtolower($a),'compare'=>'LIKE'];
            }
        }

        if($type){
            $args['tax_query'] = [['taxonomy'=>'ahbn_room_type','field'=>'slug','terms'=>$type]];
        }

        if(!empty($unavailable_rooms)) $args['post__not_in'] = $unavailable_rooms;

        $filtered_rooms = get_posts($args);
    }

    ob_start();
    ?>

    <div class="ahb-grid-row">
        <!-- Search Form -->
        <div class="ahb-grid-desktop-3 ahb-grid-tablet-12 ahb-grid-mobile-12">
            <div class="ahbn-search-form">
                <form method="GET">
                    <div class="field">
                        <label>Check-in</label>
                        <input type="date" name="checkin" value="<?php echo esc_attr($checkin ?: $today); ?>">
                    </div>
                    <div class="field">
                        <label>Check-out</label>
                        <input type="date" name="checkout" value="<?php echo esc_attr($checkout ?: $tomorrow); ?>">
                    </div>
                    <div class="field">
                        <label>Adults</label>
                        <input type="number" name="adults" min="1" value="<?php echo esc_attr($adults ?: 1); ?>">
                    </div>
                    <div class="field">
                        <label>Room Type</label>
                        <select name="type">
                            <option value="">Any Type</option>
                            <?php foreach($room_types as $slug => $name): ?>
                                <option value="<?php echo esc_attr($slug); ?>" <?php selected($type,$slug); ?>><?php echo esc_html($name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field price-range">
                        <label>Max Price: <span id="priceValue"><?php echo esc_html($price); ?></span></label>
                        <input type="range" name="price" min="<?php echo esc_attr($min_price); ?>" max="<?php echo esc_attr($max_price); ?>" value="<?php echo esc_attr($price); ?>" step="100" id="priceRange">
                    </div>
                    <div class="amenities-box">
                        <?php foreach($amenities as $a): ?>
                            <label><input type="checkbox" name="amenities[]" value="<?php echo esc_attr($a); ?>" <?php echo in_array($a,$selected_amenities)? 'checked' : ''; ?>> <?php echo esc_html(ucfirst($a)); ?></label>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" class="search-btn">Search</button>
                </form>
            </div>
        </div>

        <!-- Results -->
        <div class="ahb-grid-desktop-9 ahb-grid-tablet-12 ahb-grid-mobile-12">
            <div class="ahbn-results">
                <?php
                if(!$filtered_rooms){
                    echo '<p class="no-results">No rooms match your search.</p>';
                } else {
                    echo '<div class="ahb-grid-row">';
                    $unique_titles = [];
                    foreach($filtered_rooms as $room_id){
                        $title = get_the_title($room_id);
                        if(in_array($title,$unique_titles)) continue;
                        $unique_titles[] = $title;

                        $img = has_post_thumbnail($room_id)? get_the_post_thumbnail($room_id,'medium') : '<div class="ahbn-room-no-img">No Image</div>';
                        $price_val = get_post_meta($room_id,'ahbn_price',true);

                        $room_type_terms = wp_get_post_terms($room_id,'ahbn_room_type');
                        $room_type = (!is_wp_error($room_type_terms) && !empty($room_type_terms)) ? $room_type_terms[0]->name : '';

                        $room_amenities = get_post_meta($room_id,'ahbn_amenities',true);
                        $room_amenities = !empty($room_amenities)? (is_array($room_amenities)? $room_amenities : explode(',',$room_amenities)) : [];

                        $view_link = get_permalink($room_id);

                        echo '<div class="ahb-grid-desktop-4 ahb-grid-tablet-6 ahb-grid-mobile-12">
                            <div class="ahbn-room-item">
                                <div class="ahbn-room-image">'.$img.'</div>
                                <div class="ahbn-room-info">
                                    <h4><a href="'.esc_url($view_link).'">'.esc_html($title).'</a></h4>
                                    <p>Type: '.esc_html($room_type).'</p>
                                    <p>Price: '.$currency.esc_html($price_val).'</p>
                                    <ul class="ahbn-room-amenities">';
                                        foreach($room_amenities as $a) echo '<li>'.esc_html(trim($a)).'</li>';
                        echo '</ul>
                                    <a href="'.esc_url($view_link).'" class="ahbn-room-view-btn">View Details</a>
                                </div>
                            </div>
                        </div>';
                    }
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </div>

    <script>
        jQuery(function($){
            $('#priceRange').on('input', function(){
                $('#priceValue').text($(this).val());
            });
        });
    </script>

    <?php
    return ob_get_clean();
});
