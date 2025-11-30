<?php
// Ensure scripts/styles only load in this tab
add_action('admin_footer', function() {
    ?>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.7/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.7/main.min.js"></script>
    <?php
});

// Fetch bookings and rooms
$bookings = get_posts(['post_type'=>'ahbn_booking','numberposts'=>-1,'post_status'=>'publish']);
$rooms = get_posts(['post_type'=>'ahbn_room','numberposts'=>-1]);

$room_colors = [];
$color_palette = ['#1abc9c','#3498db','#9b59b6','#e67e22','#e74c3c','#f1c40f','#2ecc71','#34495e','#16a085','#c0392b'];
foreach($rooms as $i => $room){
    $room_colors[$room->ID] = $color_palette[$i % count($color_palette)];
}

$events = [];
foreach($bookings as $b){
    $room_id = get_post_meta($b->ID,'ahbn_room_id',true);
    $room_name = get_post($room_id) ? get_post($room_id)->post_title : 'Unknown Room';
    $events[] = [
        'title' => $b->post_title . ' - ' . $room_name,
        'start' => get_post_meta($b->ID,'ahbn_check_in',true),
        'end'   => get_post_meta($b->ID,'ahbn_check_out',true),
        'url'   => admin_url('post.php?post='.$b->ID.'&action=edit'),
        'color' => $room_colors[$room_id] ?? '#7f8c8d'
    ];
}
?>

<div id="booking-calendar" style="max-width:1000px;margin:20px auto;"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('booking-calendar');
    if(!calendarEl) return;

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: <?php echo json_encode($events); ?>,
        eventClick: function(info){
            info.jsEvent.preventDefault();
            if(info.event.url){
                window.open(info.event.url,'_blank');
            }
        }
    });
    calendar.render();
});
</script>
