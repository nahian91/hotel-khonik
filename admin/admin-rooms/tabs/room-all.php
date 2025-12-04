<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$ahbn_post_type = 'ahbn_room';

// -------------------------------
// DELETE ROOM
// -------------------------------
if ( isset( $_GET['ahbn_delete_room'], $_GET['_wpnonce'] ) ) {
    $ahbn_delete_room_id = intval( $_GET['ahbn_delete_room'] );
    $ahbn_delete_nonce   = sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) );

    if ( wp_verify_nonce( $ahbn_delete_nonce, 'ahbn_delete_room_' . $ahbn_delete_room_id ) ) {
        if ( $ahbn_delete_room_id > 0 ) {
            wp_delete_post( $ahbn_delete_room_id, true );
            echo '<div class="updated notice"><p>' . esc_html__( 'Room deleted!', 'awesome-hotel-booking' ) . '</p></div>';
        }
    } else {
        echo '<div class="notice notice-error"><p>' . esc_html__( 'Security check failed. Room not deleted.', 'awesome-hotel-booking' ) . '</p></div>';
    }
}

// -------------------------------
// FILTERS
// -------------------------------
$ahbn_filter_type   = isset( $_GET['filter_type'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_type'] ) ) : '';
$ahbn_filter_status = isset( $_GET['filter_status'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_status'] ) ) : '';
$ahbn_search_name   = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
$ahbn_per_page      = isset( $_GET['per_page'] ) ? intval( $_GET['per_page'] ) : 20;
$ahbn_paged         = isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 1;

// -------------------------------
// WP_Query ARGS
// -------------------------------
$ahbn_args = [
    'post_type'      => $ahbn_post_type,
    'posts_per_page' => $ahbn_per_page,
    'orderby'        => 'ID',
    'order'          => 'ASC',
    'paged'          => $ahbn_paged,
];

$ahbn_meta_query = [];
if ( $ahbn_filter_type )   $ahbn_meta_query[] = [ 'key' => 'ahbn_room_type', 'value' => $ahbn_filter_type ];
if ( $ahbn_filter_status ) $ahbn_meta_query[] = [ 'key' => 'ahbn_availability', 'value' => $ahbn_filter_status ];
if ( $ahbn_meta_query )    $ahbn_args['meta_query'] = $ahbn_meta_query;

if ( $ahbn_search_name ) {
    $ahbn_args['s'] = $ahbn_search_name;
}

$ahbn_query = new WP_Query( $ahbn_args );
$ahbn_rooms = $ahbn_query->posts;

// -------------------------------
// Get Currency Symbol from Settings
// -------------------------------
$ahbn_currency_code    = get_option( 'ahbn_payment_symbol', 'USD' );
$ahbn_currency_symbols = [ 'USD' => '$', 'EUR' => '€', 'GBP' => '£', 'BDT' => '৳', 'INR' => '₹' ];
$ahbn_currency_symbol  = $ahbn_currency_symbols[ $ahbn_currency_code ] ?? '$';

// Room types & statuses
$ahbn_room_types = get_option( 'ahbn_room_types', [ 'Standard','Deluxe','Suite' ] );
$ahbn_statuses   = [ 'Available','Booked','Maintenance' ];
?>

<!-- FILTER FORM -->
<form method="get" style="margin-bottom:15px;">
    <input type="hidden" name="page" value="ahbn_booking_main">
    <input type="hidden" name="tab" value="rooms">
    <input type="hidden" name="sub_tab" value="all">

    <select name="filter_type">
        <option value=""><?php esc_html_e( 'All Types', 'awesome-hotel-booking' ); ?></option>
        <?php foreach ( $ahbn_room_types as $ahbn_type ) : ?>
            <option value="<?php echo esc_attr( $ahbn_type ); ?>" <?php selected( $ahbn_filter_type, $ahbn_type ); ?>>
                <?php echo esc_html( $ahbn_type ); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="filter_status">
        <option value=""><?php esc_html_e( 'All Status', 'awesome-hotel-booking' ); ?></option>
        <?php foreach ( $ahbn_statuses as $ahbn_status ) : ?>
            <option value="<?php echo esc_attr( $ahbn_status ); ?>" <?php selected( $ahbn_filter_status, $ahbn_status ); ?>>
                <?php echo esc_html( $ahbn_status ); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <input type="text" name="s" value="<?php echo esc_attr( $ahbn_search_name ); ?>" placeholder="<?php esc_attr_e( 'Search Room Name', 'awesome-hotel-booking' ); ?>">

    <select name="per_page">
        <?php foreach ( [10,20,50,100] as $ahbn_num ) : ?>
            <option value="<?php echo esc_attr( $ahbn_num ); ?>" <?php selected( $ahbn_per_page, $ahbn_num ); ?>>
                <?php echo esc_html( $ahbn_num ); ?> <?php esc_html_e( 'per page', 'awesome-hotel-booking' ); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Filter', 'awesome-hotel-booking' ); ?>">
    <?php wp_nonce_field( 'ahbn_room_filters', 'ahbn_room_filters_nonce' ); ?>
</form>

<!-- ALL ROOMS TABLE -->
<div class="table-responsive">
<table class="widefat striped">
    <thead>
        <tr>
            <th><?php esc_html_e('ID', 'awesome-hotel-booking'); ?></th>
            <th><?php esc_html_e('Room Name', 'awesome-hotel-booking'); ?></th>
            <th><?php esc_html_e('Room Number(s)', 'awesome-hotel-booking'); ?></th>
            <th><?php esc_html_e('Type', 'awesome-hotel-booking'); ?></th>
            <th><?php esc_html_e('Price', 'awesome-hotel-booking'); ?></th>
            <th><?php esc_html_e('Status', 'awesome-hotel-booking'); ?></th>
            <th><?php esc_html_e('Featured', 'awesome-hotel-booking'); ?></th>
            <th><?php esc_html_e('Amenities', 'awesome-hotel-booking'); ?></th>
            <th><?php esc_html_e('Extras', 'awesome-hotel-booking'); ?></th>
            <th><?php esc_html_e('Featured Image', 'awesome-hotel-booking'); ?></th>
            <th><?php esc_html_e('Actions', 'awesome-hotel-booking'); ?></th>
        </tr>
    </thead>
    <tbody>
<?php foreach( $ahbn_rooms as $ahbn_r ): 
    $ahbn_room_nos     = get_post_meta($ahbn_r->ID,'ahbn_room_number',true);
    $ahbn_room_type    = get_post_meta($ahbn_r->ID,'ahbn_room_type',true);
    $ahbn_price        = floatval(get_post_meta($ahbn_r->ID,'ahbn_price',true));
    $ahbn_availability = get_post_meta($ahbn_r->ID,'ahbn_availability',true);
    $ahbn_featured     = get_post_meta($ahbn_r->ID,'ahbn_featured',true);
    $ahbn_amenities    = get_post_meta($ahbn_r->ID,'ahbn_amenities',true);
    $ahbn_extras       = get_post_meta($ahbn_r->ID,'ahbn_extras',true);

    $ahbn_room_nos_text = is_array($ahbn_room_nos) ? implode(', ', array_map('esc_html',$ahbn_room_nos)) : ($ahbn_room_nos ? esc_html($ahbn_room_nos) : '-');

    // Amenities list HTML
    $ahbn_amenities_html = '-';
    if(is_array($ahbn_amenities) && count($ahbn_amenities)){
        $ahbn_amenities_html = '<ul style="margin:0; padding-left:20px;">';
        foreach($ahbn_amenities as $ahbn_am){
            $ahbn_amenities_html .= '<li>' . esc_html($ahbn_am) . '</li>';
        }
        $ahbn_amenities_html .= '</ul>';
    }

    // Extras list HTML
    $ahbn_extras_html = '-';
    if(is_array($ahbn_extras) && count($ahbn_extras)){
        $ahbn_extras_html = '<ul style="margin:0; padding-left:20px;">';
        foreach($ahbn_extras as $ahbn_ex){
            $ahbn_extras_html .= '<li>' . esc_html($ahbn_ex) . '</li>';
        }
        $ahbn_extras_html .= '</ul>';
    }

    // Featured image
    $ahbn_featured_img = has_post_thumbnail($ahbn_r->ID) ? wp_get_attachment_image(get_post_thumbnail_id($ahbn_r->ID), [50,50], false, ['alt'=>esc_attr($ahbn_r->post_title)]) : '-';

    // Nonce for delete
    $ahbn_delete_room_nonce = wp_create_nonce( 'ahbn_delete_room_' . $ahbn_r->ID );
?>
    <tr>
        <td><?php echo esc_html($ahbn_r->ID); ?></td>
        <td><?php echo esc_html($ahbn_r->post_title); ?></td>
        <td><?php echo esc_html($ahbn_room_nos_text); ?></td>
        <td><?php echo esc_html($ahbn_room_type); ?></td>
        <td><?php echo esc_html($ahbn_currency_symbol . number_format($ahbn_price, 2)); ?></td>
        <td><?php echo esc_html($ahbn_availability); ?></td>
        <td><?php echo $ahbn_featured ? '<span style="color:green;font-weight:bold;">' . esc_html__('Yes','awesome-hotel-booking') . '</span>' : esc_html__('No','awesome-hotel-booking'); ?></td>
        <td><?php echo wp_kses_post($ahbn_amenities_html); ?></td>
        <td><?php echo wp_kses_post($ahbn_extras_html); ?></td>
        <td><?php echo wp_kses_post($ahbn_featured_img); ?></td>
        <td>
            <a href="<?php echo esc_url(add_query_arg([
                'page'=>'ahbn_booking_main',
                'tab'=>'rooms',
                'sub_tab'=>'add',
                'edit_room'=>$ahbn_r->ID
            ], admin_url('admin.php'))); ?>" class="button"><?php esc_html_e('Edit', 'awesome-hotel-booking'); ?></a>

            <a href="<?php echo esc_url(add_query_arg([
                'page'=>'ahbn_booking_main',
                'tab'=>'rooms',
                'sub_tab'=>'all',
                'ahbn_delete_room'=>$ahbn_r->ID,
                '_wpnonce'=>$ahbn_delete_room_nonce
            ], admin_url('admin.php'))); ?>" class="button button-danger"
               onclick="return confirm('<?php echo esc_js( esc_html__('Delete this room?', 'awesome-hotel-booking') ); ?>')"><?php esc_html_e('Delete', 'awesome-hotel-booking'); ?></a>
        </td>
    </tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<!-- PAGINATION -->
<div class="tablenav">
<?php
$ahbn_total_pages = $ahbn_query->max_num_pages;
if ( $ahbn_total_pages > 1 ) {
    $ahbn_current_page = max( 1, $ahbn_paged );
    echo wp_kses_post( paginate_links([
        'base'      => esc_url_raw(add_query_arg('paged','%#%')),
        'format'    => '',
        'current'   => $ahbn_current_page,
        'total'     => $ahbn_total_pages,
        'prev_text' => '&laquo;',
        'next_text' => '&raquo;',
    ]) );
}
?>
</div>

<style>
.table-responsive { overflow-x:auto; }
.widefat th, .widefat td { vertical-align: middle; }
button.button { margin: 2px 2px 2px 0; }
.tablenav { margin-top:15px; }
</style>
