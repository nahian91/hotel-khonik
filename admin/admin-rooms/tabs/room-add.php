<?php
$post_type = 'ahbn_room';

/* -----------------------------
   GLOBAL SETTINGS
------------------------------ */
$room_types      = get_option('ahbn_room_types', ['Standard','Deluxe','Suite']);
$amenities_list  = get_option('ahbn_room_amenities', ['Wi-Fi','AC','Breakfast','TV','Mini Bar','Parking']);
$extras_list     = get_option('ahbn_room_extras', ['Airport Pickup','Breakfast Included']);
$currency_symbol = get_option('ahbn_room_currency', '$');
$room_numbers    = get_option('ahbn_room_numbers', ['101','102','103']);

/* -----------------------------
   EDIT MODE
------------------------------ */
$edit_room = isset($_GET['edit_room']) ? intval($_GET['edit_room']) : 0;

/* -----------------------------
   HANDLE FORM SUBMISSION
------------------------------ */
if (isset($_POST['ahbn_save_room'])) {

    $room_id = intval($_POST['room_id']);
    $data = [
        'post_title'   => sanitize_text_field($_POST['room_name']),
        'post_content' => wp_kses_post($_POST['room_desc']),
        'post_type'    => $post_type,
        'post_status'  => 'publish',
    ];

    $room_numbers_selected = isset($_POST['room_number']) && is_array($_POST['room_number'])
        ? array_map('sanitize_text_field', $_POST['room_number'])
        : [];

    $featured_image_id = isset($_POST['featured_image_id']) ? intval($_POST['featured_image_id']) : 0;
    $gallery_ids = isset($_POST['gallery_image_ids']) && is_array($_POST['gallery_image_ids'])
        ? array_map('intval', $_POST['gallery_image_ids'])
        : [];

    $room_ids_to_save = [];

    // If editing a single room
    if ($room_id > 0 && count($room_numbers_selected) === 1) {
        wp_update_post($data + ['ID' => $room_id]);
        update_post_meta($room_id, 'ahbn_room_number', $room_numbers_selected);
        $room_ids_to_save[] = $room_id;
    } else {
        // Create a new room for each selected number
        foreach ($room_numbers_selected as $rnum) {
            $new_room_id = wp_insert_post($data);
            if ($new_room_id) {
                update_post_meta($new_room_id, 'ahbn_room_number', [$rnum]);
                $room_ids_to_save[] = $new_room_id;
            }
        }
    }

    // Save content, featured, and gallery for all created rooms
    foreach ($room_ids_to_save as $r_id) {
        update_post_meta($r_id, 'ahbn_price', floatval($_POST['room_price']));
        update_post_meta($r_id, 'ahbn_room_type', sanitize_text_field($_POST['room_type']));
        update_post_meta($r_id, 'ahbn_availability', sanitize_text_field($_POST['availability']));
        update_post_meta($r_id, 'ahbn_featured', isset($_POST['featured']) ? 1 : 0);
        update_post_meta($r_id, 'ahbn_amenities', isset($_POST['amenities']) ? $_POST['amenities'] : []);
        update_post_meta($r_id, 'ahbn_extras', isset($_POST['extras']) ? $_POST['extras'] : []);

        // Set Featured Image
        if ($featured_image_id) set_post_thumbnail($r_id, $featured_image_id);

        // Set Gallery Images
        if ($gallery_ids) update_post_meta($r_id, 'ahbn_room_gallery', $gallery_ids);
    }

    echo '<div class="updated notice"><p>Room(s) saved successfully!</p></div>';
}

/* -----------------------------
   LOAD DATA FOR EDIT MODE
------------------------------ */
$room_name = $room_desc = $room_price = $availability = $room_type = '';
$amenities_selected = $extras_selected = $gallery_ids = [];
$featured = 0;
$room_number = [];
$featured_image_id = 0;

if ($edit_room > 0) {
    $r = get_post($edit_room);
    $room_name = $r->post_title;
    $room_desc = $r->post_content;
    $room_price = get_post_meta($edit_room, 'ahbn_price', true);
    $room_type = get_post_meta($edit_room, 'ahbn_room_type', true);
    $availability = get_post_meta($edit_room, 'ahbn_availability', true);
    $room_number = get_post_meta($edit_room, 'ahbn_room_number', true);
    if (!is_array($room_number)) $room_number = [$room_number];
    $amenities_selected = get_post_meta($edit_room, 'ahbn_amenities', true);
    $extras_selected = get_post_meta($edit_room, 'ahbn_extras', true);
    if (!is_array($extras_selected)) $extras_selected = [];
    $gallery_ids = get_post_meta($edit_room, 'ahbn_room_gallery', true);
    if (!is_array($gallery_ids)) $gallery_ids = [];
    $featured = get_post_meta($edit_room, 'ahbn_featured', true);
    $featured_image_id = get_post_thumbnail_id($edit_room);
} else {
    $room_type = $room_types[0];
    $availability = 'Available';
    $room_number = [];
    $extras_selected = [];
}
?>

<!-- -----------------------------
     ADD ROOM FORM
------------------------------ -->
<form method="post" enctype="multipart/form-data">
<input type="hidden" name="room_id" value="<?php echo esc_attr($edit_room); ?>">

<div class="ahbn-add-room-form" style="display:flex; gap:20px; flex-wrap:wrap;">
    <!-- LEFT COLUMN -->
    <div class="left-column" style="flex:1; min-width:300px;">
        <table class="form-table">
            <tr>
                <th>Room Name</th>
                <td><input type="text" name="room_name" value="<?php echo esc_attr($room_name); ?>" required style="width:100%"></td>
            </tr>

            <tr>
                <th>Room Number</th>
                <td>
                    <?php foreach ($room_numbers as $num): ?>
                        <label style="margin-right:10px;">
                            <input type="checkbox" name="room_number[]" value="<?php echo esc_attr($num); ?>"
                            <?php echo in_array($num, $room_number) ? 'checked' : ''; ?>>
                            <?php echo esc_html($num); ?>
                        </label>
                    <?php endforeach; ?>
                </td>
            </tr>

            <tr>
                <th>Description</th>
                <td>
                    <?php
                    wp_editor(
                        $room_desc,
                        'room_desc',
                        ['textarea_name'=>'room_desc','media_buttons'=>true,'textarea_rows'=>10,'teeny'=>false]
                    );
                    ?>
                </td>
            </tr>

            <tr>
                <th>Price</th>
                <td><input type="number" step="0.01" name="room_price" value="<?php echo esc_attr($room_price); ?>" style="width:100%"></td>
            </tr>

            <tr>
                <th>Room Type</th>
                <td>
                    <select name="room_type" style="width:100%">
                        <?php foreach ($room_types as $type): ?>
                            <option value="<?php echo esc_attr($type); ?>" <?php selected($room_type, $type); ?>>
                                <?php echo esc_html($type); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>

            <tr>
                <th>Availability</th>
                <td>
                    <select name="availability" style="width:100%">
                        <option value="Available" <?php selected($availability,'Available'); ?>>Available</option>
                        <option value="Booked" <?php selected($availability,'Booked'); ?>>Booked</option>
                        <option value="Maintenance" <?php selected($availability,'Maintenance'); ?>>Maintenance</option>
                    </select>
                </td>
            </tr>
        </table>
    </div>

    <!-- RIGHT COLUMN -->
    <div class="right-column" style="flex:1; min-width:300px;">
        <table class="form-table">
            <tr>
                <th>Amenities</th>
                <td>
                    <?php foreach ($amenities_list as $am): ?>
                        <label>
                            <input type="checkbox" name="amenities[]" value="<?php echo esc_attr($am); ?>"
                            <?php echo (is_array($amenities_selected) && in_array($am, $amenities_selected)) ? 'checked' : ''; ?>>
                            <?php echo esc_html($am); ?>
                        </label><br>
                    <?php endforeach; ?>
                </td>
            </tr>

            <tr>
                <th>Extras</th>
                <td>
                    <?php foreach ($extras_list as $ex): ?>
                        <label>
                            <input type="checkbox" name="extras[]" value="<?php echo esc_attr($ex); ?>"
                            <?php echo (is_array($extras_selected) && in_array($ex, $extras_selected)) ? 'checked' : ''; ?>>
                            <?php echo esc_html($ex); ?>
                        </label><br>
                    <?php endforeach; ?>
                </td>
            </tr>

            <tr>
                <th>Featured Image</th>
                <td>
                    <div class="featured-image-wrapper">
                        <?php if($featured_image_id): ?>
                            <?php echo wp_get_attachment_image($featured_image_id, [150,150]); ?>
                        <?php endif; ?>
                        <input type="hidden" name="featured_image_id" id="featured_image_id" value="<?php echo esc_attr($featured_image_id); ?>">
                        <button type="button" class="button select-featured">Select Image</button>
                        <button type="button" class="button remove-featured">Remove</button>
                    </div>
                </td>
            </tr>

            <tr>
                <th>Gallery Images</th>
                <td>
                    <ul id="gallery-list" class="gallery-list">
                        <?php if($gallery_ids): foreach($gallery_ids as $g_id): ?>
                            <li data-id="<?php echo esc_attr($g_id); ?>" class="gallery-item">
                                <?php echo wp_get_attachment_image($g_id, [100,100]); ?>
                                <button type="button" class="remove-gallery button">Remove</button>
                            </li>
                        <?php endforeach; endif; ?>
                    </ul>
                    <input type="hidden" name="gallery_image_ids" id="gallery_image_ids" value="<?php echo implode(',', $gallery_ids); ?>">
                    <button type="button" class="button add-gallery">Add Gallery Images</button>
                </td>
            </tr>

            <tr>
                <th>Featured Switch</th>
                <td>
                    <label class="switch">
                        <input type="checkbox" name="featured" value="1" <?php checked($featured,1); ?>>
                        <span class="slider round"></span>
                    </label>
                </td>
            </tr>
        </table>
    </div>
</div>

<p><input type="submit" name="ahbn_save_room" class="button button-primary" value="Save Room"></p>
</form>

<!-- -----------------------------
     STYLES & SCRIPTS
------------------------------ -->
<style>
/* Simple Switch */
.switch { position: relative; display: inline-block; width: 50px; height: 24px; }
.switch input { display:none; }
.slider { position: absolute; cursor: pointer; top:0; left:0; right:0; bottom:0; background-color:#ccc; transition:0.4s; border-radius:24px; }
.slider:before { position:absolute; content:""; height:18px; width:18px; left:3px; bottom:3px; background-color:white; transition:0.4s; border-radius:50%; }
input:checked + .slider { background-color:#2196F3; }
input:checked + .slider:before { transform: translateX(26px); }
.gallery-list { list-style:none; padding:0; margin:0; display:flex; flex-wrap:wrap; gap:5px; }
.gallery-item { position:relative; }
.gallery-item img { display:block; }
.gallery-item .remove-gallery { position:absolute; top:0; right:0; }
</style>

<script>
jQuery(document).ready(function($){
    // Featured Image
    var featured_frame;
    $('.select-featured').on('click', function(e){
        e.preventDefault();
        if(featured_frame) featured_frame.open();
        featured_frame = wp.media({
            title: 'Select Featured Image',
            button: { text: 'Set Featured' },
            multiple: false
        });
        featured_frame.on('select', function(){
            var attachment = featured_frame.state().get('selection').first().toJSON();
            $('#featured_image_id').val(attachment.id);
            $('.featured-image-wrapper').prepend('<img src="'+attachment.url+'" style="max-width:150px;">');
        });
        featured_frame.open();
    });
    $('.remove-featured').on('click', function(){
        $('#featured_image_id').val('');
        $('.featured-image-wrapper img').remove();
    });

    // Gallery Images
    var gallery_frame;
    $('.add-gallery').on('click', function(e){
        e.preventDefault();
        gallery_frame = wp.media({
            title: 'Select Gallery Images',
            button: { text: 'Add to gallery' },
            multiple: true
        });
        gallery_frame.on('select', function(){
            var selection = gallery_frame.state().get('selection');
            var ids = $('#gallery_image_ids').val() ? $('#gallery_image_ids').val().split(',') : [];
            selection.each(function(attachment){
                var a = attachment.toJSON();
                if(!ids.includes(a.id.toString())) {
                    ids.push(a.id);
                    $('#gallery-list').append('<li data-id="'+a.id+'" class="gallery-item"><img src="'+a.url+'" style="max-width:100px;"><button type="button" class="remove-gallery button">Remove</button></li>');
                }
            });
            $('#gallery_image_ids').val(ids.join(','));
        });
        gallery_frame.open();
    });

    $(document).on('click', '.remove-gallery', function(){
        var li = $(this).closest('li');
        var id = li.data('id').toString();
        var ids = $('#gallery_image_ids').val().split(',').filter(i => i != id);
        $('#gallery_image_ids').val(ids.join(','));
        li.remove();
    });

    // Make gallery sortable
    $('#gallery-list').sortable({
        update: function(event, ui){
            var ids = [];
            $('#gallery-list li').each(function(){ ids.push($(this).data('id')); });
            $('#gallery_image_ids').val(ids.join(','));
        }
    });
});
</script>
