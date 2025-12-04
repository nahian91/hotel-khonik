<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$post_type = 'ahbn_booking';

// -------------------- Handle Delete Booking --------------------
if ( isset( $_GET['ahbn_delete_booking'], $_GET['_wpnonce'] ) ) {
    $ahbn_delete_id        = intval( $_GET['ahbn_delete_booking'] );
    $ahbn_delete_nonce_check = sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) );

    if ( wp_verify_nonce( $ahbn_delete_nonce_check, 'ahbn_delete_booking_' . $ahbn_delete_id ) ) {
        if ( $ahbn_delete_id > 0 ) {
            wp_delete_post( $ahbn_delete_id, true );
            echo '<div class="notice notice-success"><p>Booking deleted successfully!</p></div>';
        }
    } else {
        echo '<div class="notice notice-error"><p>Security check failed. Booking not deleted.</p></div>';
    }
}

// -------------------- Load Currency --------------------
$ahbn_currency_code    = get_option( 'ahbn_hotel_currency', 'USD' );
$ahbn_currency_symbols = [
    'USD' => '$', 'EUR' => '€', 'GBP' => '£', 'BDT' => '৳', 'INR' => '₹'
];
$ahbn_currency_symbol = $ahbn_currency_symbols[ $ahbn_currency_code ] ?? '$';

// -------------------- Load All Bookings --------------------
$ahbn_bookings = get_posts([
    'post_type'   => $post_type,
    'numberposts' => -1,
    'orderby'     => 'ID',
    'order'       => 'DESC',
]);
?>

<div class="wrap">
    <h1>All Bookings</h1>

    <table class="widefat striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Room</th>
                <th>Room Type</th>
                <th>Days</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Check In</th>
                <th>Check Out</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ( $ahbn_bookings ) : ?>
            <?php foreach ( $ahbn_bookings as $ahbn_booking ) : 
                $ahbn_room_id      = get_post_meta( $ahbn_booking->ID, 'ahbn_room_id', true );
                $ahbn_room_title   = $ahbn_room_id ? get_the_title( $ahbn_room_id ) : '-';
                $ahbn_room_type    = get_post_meta( $ahbn_booking->ID, 'ahbn_room_type', true ) ?: '-';
                $ahbn_days         = get_post_meta( $ahbn_booking->ID, 'ahbn_days', true ) ?: 1;
                $ahbn_total_amount = get_post_meta( $ahbn_booking->ID, 'ahbn_amount', true ) ?: 0;
                $ahbn_status       = get_post_meta( $ahbn_booking->ID, 'ahbn_status', true ) ?: 'pending';
                $ahbn_check_in     = get_post_meta( $ahbn_booking->ID, 'ahbn_check_in', true );
                $ahbn_check_out    = get_post_meta( $ahbn_booking->ID, 'ahbn_check_out', true );

                // Generate nonce for delete action
                $ahbn_delete_nonce = wp_create_nonce( 'ahbn_delete_booking_' . $ahbn_booking->ID );

                // Base URL
                $ahbn_base_url = admin_url( 'admin.php?page=ahbn_booking_main&tab=bookings' );
            ?>
            <tr>
                <td><?php echo esc_html( $ahbn_booking->ID ); ?></td>
                <td><?php echo esc_html( $ahbn_booking->post_title ); ?></td>
                <td><?php echo esc_html( $ahbn_room_title ); ?></td>
                <td><?php echo esc_html( $ahbn_room_type ); ?></td>
                <td><?php echo esc_html( $ahbn_days ); ?></td>
                <td><?php echo esc_html( $ahbn_currency_symbol . number_format( (float) $ahbn_total_amount, 2 ) ); ?></td>
                <td><?php echo esc_html( ucfirst( $ahbn_status ) ); ?></td>
                <td><?php echo esc_html( $ahbn_check_in ); ?></td>
                <td><?php echo esc_html( $ahbn_check_out ); ?></td>
                <td style="white-space: nowrap;">
                    <a class="button button-primary" href="<?php echo esc_url( add_query_arg( [
                        'sub_tab'      => 'view',
                        'view_booking' => $ahbn_booking->ID
                    ], $ahbn_base_url ) ); ?>">View</a>

                    <a class="button button-secondary" href="<?php echo esc_url( add_query_arg( [
                        'sub_tab'      => 'add',
                        'edit_booking' => $ahbn_booking->ID
                    ], $ahbn_base_url ) ); ?>">Edit</a>

                    <a class="button button-secondary" style="color:#b32d2e;border-color:#b32d2e;"
                       href="<?php echo esc_url( add_query_arg( [
                           'sub_tab'            => 'all',
                           'ahbn_delete_booking' => $ahbn_booking->ID,
                           '_wpnonce'           => $ahbn_delete_nonce
                       ], $ahbn_base_url ) ); ?>"
                       onclick="return confirm('Are you sure to delete this booking?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else : ?>
            <tr>
                <td colspan="10">No bookings found.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
