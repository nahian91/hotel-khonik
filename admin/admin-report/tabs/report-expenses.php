<?php
$expenses = get_posts([
    'post_type'=>'ahbn_expense',
    'numberposts'=>-1,
    'orderby'=>'ID',
    'order'=>'DESC'
]);

$currency = get_option('ahbn_room_currency','$');

echo '<h3>Total Expenses: '.count($expenses).'</h3>';
echo '<table class="widefat striped"><thead>
        <tr>
            <th>ID</th><th>Title</th><th>Amount</th><th>Category</th><th>Assign</th><th>Date</th>
        </tr></thead><tbody>';

if(!empty($expenses)){
    foreach($expenses as $e){
        $amount   = floatval(get_post_meta($e->ID,'ahbn_amount',true));
        $category = get_post_meta($e->ID,'ahbn_category',true) ?: '-';
        $assign   = get_post_meta($e->ID,'ahbn_assign',true) ?: '-';
        $date     = get_post_meta($e->ID,'ahbn_date',true) ?: '-';
        echo '<tr>
                <td>'.esc_html($e->ID).'</td>
                <td>'.esc_html($e->post_title).'</td>
                <td>'.$currency.number_format($amount,2).'</td>
                <td>'.esc_html($category).'</td>
                <td>'.esc_html($assign).'</td>
                <td>'.esc_html($date).'</td>
              </tr>';
    }
} else {
    echo '<tr><td colspan="6">No expenses found.</td></tr>';
}

echo '</tbody></table>';
