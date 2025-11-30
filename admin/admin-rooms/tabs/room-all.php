<?php
$post_type = 'ahbn_room';

// -------------------------------
// DELETE ROOM
// -------------------------------
if ( isset($_GET['delete_room']) ) {
    wp_delete_post(intval($_GET['delete_room']), true);
    echo '<div class="updated notice"><p>Room deleted!</p></div>';
}

// -------------------------------
// FILTERS
// -------------------------------
$filter_type   = isset($_GET['filter_type']) ? sanitize_text_field($_GET['filter_type']) : '';
$filter_status = isset($_GET['filter_status']) ? sanitize_text_field($_GET['filter_status']) : '';
$search_name   = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
$per_page      = isset($_GET['per_page']) ? intval($_GET['per_page']) : 20;
$paged         = isset($_GET['paged']) ? intval($_GET['paged']) : 1;

// -------------------------------
// WP_Query ARGS
// -------------------------------
$args = [
    'post_type'      => $post_type,
    'posts_per_page' => $per_page,
    'orderby'        => 'ID',
    'order'          => 'ASC',
    'paged'          => $paged,
];

$meta_query = [];
if($filter_type)   $meta_query[] = ['key'=>'ahbn_room_type','value'=>$filter_type];
if($filter_status) $meta_query[] = ['key'=>'ahbn_availability','value'=>$filter_status];
if($meta_query)    $args['meta_query'] = $meta_query;

if($search_name){
    $args['s'] = $search_name;
}

$query = new WP_Query($args);
$rooms = $query->posts;

// -------------------------------
// Currency
// -------------------------------
$currency_code    = get_option('ahbn_room_currency', 'USD');
$currency_symbols = ['USD'=>'$', 'EUR'=>'€', 'GBP'=>'£', 'BDT'=>'৳','INR'=>'₹'];
$currency_symbol  = isset($currency_symbols[$currency_code]) ? $currency_symbols[$currency_code] : '$';

$room_types = get_option('ahbn_room_types', ['Standard','Deluxe','Suite']);
$statuses   = ['Available','Booked','Maintenance'];
?>

<!-- FILTER FORM -->
<form method="get" style="margin-bottom:15px;">
    <input type="hidden" name="page" value="ahbn_booking_main">
    <input type="hidden" name="tab" value="rooms">
    <input type="hidden" name="sub_tab" value="all">

    <select name="filter_type">
        <option value="">All Types</option>
        <?php foreach($room_types as $type): ?>
            <option value="<?php echo esc_attr($type); ?>" <?php selected($filter_type, $type); ?>>
                <?php echo esc_html($type); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="filter_status">
        <option value="">All Status</option>
        <?php foreach($statuses as $status): ?>
            <option value="<?php echo esc_attr($status); ?>" <?php selected($filter_status, $status); ?>>
                <?php echo esc_html($status); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <input type="text" name="s" value="<?php echo esc_attr($search_name); ?>" placeholder="Search Room Name">

    <select name="per_page">
        <?php foreach([10,20,50,100] as $num): ?>
            <option value="<?php echo $num; ?>" <?php selected($per_page, $num); ?>><?php echo $num; ?> per page</option>
        <?php endforeach; ?>
    </select>

    <input type="submit" class="button button-primary" value="Filter">
</form>

<!-- ALL ROOMS TABLE -->
<div class="table-responsive">
<table class="widefat striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Room Name</th>
            <th>Room Number(s)</th>
            <th>Type</th>
            <th>Price</th>
            <th>Status</th>
            <th>Featured</th>
            <th>Amenities</th>
            <th>Extras</th>
            <th>Featured Image</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($rooms as $r): 
        
        $room_nos     = get_post_meta($r->ID,'ahbn_room_number',true);
        $room_type    = get_post_meta($r->ID,'ahbn_room_type',true);
        $price        = floatval(get_post_meta($r->ID,'ahbn_price',true)); // ensure numeric
        $availability = get_post_meta($r->ID,'ahbn_availability',true);
        $featured     = get_post_meta($r->ID,'ahbn_featured',true);
        $amenities    = get_post_meta($r->ID,'ahbn_amenities',true);
        $extras       = get_post_meta($r->ID,'ahbn_extras',true);

        $room_nos_text = is_array($room_nos) ? implode(', ', $room_nos) : ($room_nos ?: '-');

        $extras_html = '-';
        if(is_array($extras) && count($extras)){
            $extras_html = '<ul style="margin:0; padding-left:20px;">';
            foreach($extras as $ex){
                $extras_html .= '<li>'.esc_html($ex).'</li>';
            }
            $extras_html .= '</ul>';
        }

        $amenities_html = '-';
        if(is_array($amenities) && count($amenities)){
            $amenities_html = '<ul style="margin:0; padding-left:20px;">';
            foreach($amenities as $am){
                $amenities_html .= '<li>'.esc_html($am).'</li>';
            }
            $amenities_html .= '</ul>';
        }

        $featured_img = has_post_thumbnail($r->ID) ? get_the_post_thumbnail($r->ID,[50,50]) : '-';

    ?>
        <tr>
            <td><?php echo $r->ID; ?></td>
            <td><?php echo esc_html($r->post_title); ?></td>
            <td><?php echo esc_html($room_nos_text); ?></td>
            <td><?php echo esc_html($room_type); ?></td>
            <td><?php echo esc_html($currency_symbol . number_format($price, 2)); ?></td>
            <td><?php echo esc_html($availability); ?></td>
            <td><?php echo $featured ? '<span style="color:green;font-weight:bold;">Yes</span>' : 'No'; ?></td>
            <td><?php echo $amenities_html; ?></td>
            <td><?php echo $extras_html; ?></td>
            <td><?php echo $featured_img; ?></td>
            <td>
                <a href="?page=ahbn_booking_main&tab=rooms&sub_tab=add&edit_room=<?php echo $r->ID; ?>" class="button">Edit</a>
                <a href="?page=ahbn_booking_main&tab=rooms&sub_tab=all&delete_room=<?php echo $r->ID; ?>" class="button button-danger" onclick="return confirm('Delete this room?')">Delete</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>

<!-- PAGINATION -->
<div class="tablenav">
<?php
$total_pages = $query->max_num_pages;
if ($total_pages > 1){
    $current_page = max(1, $paged);
    echo paginate_links([
        'base'      => add_query_arg('paged','%#%'),
        'format'    => '',
        'current'   => $current_page,
        'total'     => $total_pages,
        'prev_text' => '&laquo;',
        'next_text' => '&raquo;',
    ]);
}
?>
</div>

<style>
.table-responsive { overflow-x:auto; }
.widefat th, .widefat td { vertical-align: middle; }
button.button { margin: 2px 2px 2px 0; }
.tablenav { margin-top:15px; }
</style>
