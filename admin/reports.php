<?php

/* -----------------------------
   Reports Tab - Customers with View
----------------------------- */
function hb_reports_tab(){
    $active_sub_tab = isset($_GET['sub_tab']) ? $_GET['sub_tab'] : 'revenue';

    // Sub-tabs navigation
    echo '<h2 class="nav-tab-wrapper">';
    echo '<a href="?page=hb_bookings&tab=reports&sub_tab=revenue" class="nav-tab '.($active_sub_tab=='revenue'?'nav-tab-active':'').'">Revenue</a>';
    echo '<a href="?page=hb_bookings&tab=reports&sub_tab=customers" class="nav-tab '.($active_sub_tab=='customers'?'nav-tab-active':'').'">Customers</a>';
    echo '</h2>';

    $bookings = get_posts(array(
        'post_type'=>'booking',
        'posts_per_page'=>-1
    ));

    if(!$bookings){ 
        echo '<p>No bookings to report.</p>'; 
        return; 
    }

    // Show only if not viewing a single customer
    if(!isset($_GET['view_customer'])){
        if($active_sub_tab == 'revenue') {

    $total_revenue = 0;
    $total_bookings = count($bookings);
    $customer_revenue = [];
    $daily_revenue = [];

    foreach($bookings as $b){
        $amount = floatval(get_post_meta($b->ID,'amount',true));
        $total_revenue += $amount;

        // Revenue per customer
        $name = $b->post_title;
        if(!isset($customer_revenue[$name])) $customer_revenue[$name] = 0;
        $customer_revenue[$name] += $amount;

        // Revenue per day (checkin date)
        $checkin = get_post_meta($b->ID,'checkin',true);
        if($checkin){
            $date = date('Y-m-d', strtotime($checkin));
            if(!isset($daily_revenue[$date])) $daily_revenue[$date] = 0;
            $daily_revenue[$date] += $amount;
        }
    }

    $average_revenue = $total_bookings ? round($total_revenue / $total_bookings, 2) : 0;

    // --- Summary Table ---
    echo '<h2>Revenue Summary</h2>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<tbody>';
    echo '<tr><th>Total Revenue</th><td>'.esc_html($total_revenue).'</td></tr>';
    echo '<tr><th>Total Bookings</th><td>'.esc_html($total_bookings).'</td></tr>';
    echo '<tr><th>Average Revenue per Booking</th><td>'.esc_html($average_revenue).'</td></tr>';
    echo '</tbody></table>';

    // --- Top 5 Customers ---
    arsort($customer_revenue);
    $top_customers = array_slice($customer_revenue, 0, 5, true);
    echo '<h2>Top 5 Customers by Revenue</h2>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Customer</th><th>Revenue</th></tr></thead><tbody>';
    foreach($top_customers as $name => $amt){
        echo '<tr><td>'.esc_html($name).'</td><td>'.esc_html($amt).'</td></tr>';
    }
    echo '</tbody></table>';

    // --- Revenue Trend (Last 7 Days) ---
    echo '<h2>Revenue Trend (Last 7 Days)</h2>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Date</th><th>Revenue</th></tr></thead><tbody>';
    for($i=6; $i>=0; $i--){
        $date = date('Y-m-d', strtotime("-$i days"));
        $amt = isset($daily_revenue[$date]) ? $daily_revenue[$date] : 0;
        echo '<tr><td>'.esc_html($date).'</td><td>'.esc_html($amt).'</td></tr>';
    }
    echo '</tbody></table>';
}
 elseif($active_sub_tab == 'customers'){

            // Collect unique customers
            $customers = [];
            foreach($bookings as $b){
                $phone = get_post_meta($b->ID,'customer_phone',true);
                if(!isset($customers[$phone])) {
                    $customers[$phone] = [
                        'name' => $b->post_title,
                        'phone' => $phone,
                        'bookings' => [$b->ID]
                    ];
                } else {
                    $customers[$phone]['bookings'][] = $b->ID;
                }
            }

            // Show table of customers
            echo '<h2>Customers</h2>';
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr><th>Name</th><th>Phone</th><th>Action</th></tr></thead><tbody>';

            foreach($customers as $phone => $c){
                // View link - pass first booking ID to view details
                $view_url = admin_url('admin.php?page=hb_bookings&tab=reports&sub_tab=customers&view_customer='.$c['bookings'][0]);
                echo '<tr>
                    <td>'.esc_html($c['name']).'</td>
                    <td>'.esc_html($c['phone']).'</td>
                    <td><a href="'.esc_url($view_url).'" class="button">View</a></td>
                </tr>';
            }

            echo '</tbody></table>';
        }
    } else {
        // Show single customer view
        hb_view_customer_page(intval($_GET['view_customer']));
    }
}

/* -----------------------------
   View Customer Page
----------------------------- */
function hb_view_customer_page($booking_id){
    $b = get_post($booking_id);
    if(!$b){ echo '<p>Customer not found.</p>'; return; }

    $customer_image_id = get_post_meta($booking_id,'customer_image',true);
    $nid_upload_id = get_post_meta($booking_id,'custom_nid_upload',true);
    ?>
    <h2>Customer Details</h2>
    <table class="wp-list-table widefat fixed striped">
        <tr><th>Name</th><td><?php echo esc_html($b->post_title); ?></td></tr>
        <tr><th>Phone</th><td><?php echo esc_html(get_post_meta($booking_id,'customer_phone',true)); ?></td></tr>
        <tr><th>Address</th><td><?php echo esc_html(get_post_meta($booking_id,'customer_address',true)); ?></td></tr>
        <tr><th>Customer Image</th>
            <td>
                <?php 
                if($customer_image_id){
                    echo wp_get_attachment_image($customer_image_id,'medium', false, [
                        'style' => 'max-width:100px;width:100%;height:auto;'
                    ]);
                } else {
                    echo 'N/A';
                }
                ?>
            </td>
        </tr>
        <tr><th>Custom NID</th>
            <td>
                <?php 
                if($nid_upload_id){
                    echo wp_get_attachment_image($nid_upload_id,'medium', false, [
                        'style' => 'max-width:100px;width:100%;height:auto;'
                    ]);
                } else {
                    echo 'N/A';
                }
                ?>
            </td>
        </tr>
        <tr><th>Total Bookings</th>
            <td>
                <?php 
                echo count(get_posts([
                    'post_type'=>'booking',
                    'meta_key'=>'customer_phone',
                    'meta_value'=>get_post_meta($booking_id,'customer_phone',true)
                ])); 
                ?>
            </td>
        </tr>
    </table>
    <p>
        <a href="<?php echo admin_url('admin.php?page=hb_bookings&tab=reports&sub_tab=customers'); ?>" class="button">Back</a>
    </p>
    <?php
}
