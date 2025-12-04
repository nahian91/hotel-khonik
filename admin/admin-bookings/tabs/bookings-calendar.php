<?php
// Ensure scripts/styles only load in this tab
add_action('admin_footer', function() {
    ?>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.7/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.7/main.min.js"></script>
    <?php
});

// Fetch bookings and rooms
$ahbn_bookings = get_posts(['post_type'=>'ahbn_booking','numberposts'=>-1,'post_status'=>'publish']);
$ahbn_rooms    = get_posts(['post_type'=>'ahbn_room','numberposts'=>-1]);

$ahbn_room_colors = [];
$ahbn_color_palette = ['#1abc9c','#3498db','#9b59b6','#e67e22','#e74c3c','#f1c40f','#2ecc71','#34495e','#16a085','#c0392b'];
foreach($ahbn_rooms as $ahbn_i => $ahbn_room){
    $ahbn_room_colors[$ahbn_room->ID] = $ahbn_color_palette[$ahbn_i % count($ahbn_color_palette)];
}

$ahbn_events = [];
foreach($ahbn_bookings as $ahbn_b){
    $ahbn_room_id   = get_post_meta($ahbn_b->ID,'ahbn_room_id',true);
    $ahbn_room_name = get_post($ahbn_room_id) ? get_post($ahbn_room_id)->post_title : 'Unknown Room';
    $ahbn_events[] = [
        'title' => $ahbn_b->post_title . ' - ' . $ahbn_room_name,
        'start' => get_post_meta($ahbn_b->ID,'ahbn_check_in',true),
        'end'   => get_post_meta($ahbn_b->ID,'ahbn_check_out',true),
        'url'   => admin_url('post.php?post='.$ahbn_b->ID.'&action=edit'),
        'color' => $ahbn_room_colors[$ahbn_room_id] ?? '#7f8c8d'
    ];
}
?>

<div id="ahbn_booking_calendar" style="max-width:1000px;margin:20px auto;"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('ahbn_booking_calendar');
    if(!calendarEl) return;

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: <?php echo json_encode($ahbn_events); ?>,
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
