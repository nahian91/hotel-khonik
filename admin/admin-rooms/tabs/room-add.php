<?php
$post_type = 'ahbn_room';

/* -----------------------------
   GLOBAL SETTINGS
------------------------------ */
$ahbn_room_types      = get_option('ahbn_room_types', ['Standard','Deluxe','Suite']);
$ahbn_amenities_list  = get_option('ahbn_room_amenities', ['Wi-Fi','AC','Breakfast','TV','Mini Bar','Parking']);
$ahbn_extras_list     = get_option('ahbn_room_extras', ['Airport Pickup','Breakfast Included']);
$ahbn_currency_symbol = get_option('ahbn_room_currency', '$');
$ahbn_room_numbers    = get_option('ahbn_room_numbers', ['101','102','103']);

/* -----------------------------
   EDIT MODE
------------------------------ */
$ahbn_edit_room = isset($_GET['edit_room']) ? intval($_GET['edit_room']) : 0;

/* -----------------------------
   HANDLE FORM SUBMISSION
------------------------------ */
if (
    isset($_POST['ahbn_save_room']) &&
    isset($_POST['_wpnonce']) &&
    wp_verify_nonce(wp_unslash($_POST['_wpnonce']), 'ahbn_save_room_verify')
) {

    $ahbn_room_id = isset($_POST['room_id']) ? intval($_POST['room_id']) : 0;

    $ahbn_room_name  = isset($_POST['room_name']) ? sanitize_text_field(wp_unslash($_POST['room_name'])) : '';
    $ahbn_room_desc  = isset($_POST['room_desc']) ? wp_kses_post(wp_unslash($_POST['room_desc'])) : '';
    $ahbn_room_price = isset($_POST['room_price']) ? floatval($_POST['room_price']) : 0;
    $ahbn_room_type  = isset($_POST['room_type']) ? sanitize_text_field(wp_unslash($_POST['room_type'])) : '';
    $ahbn_available  = isset($_POST['availability']) ? sanitize_text_field(wp_unslash($_POST['availability'])) : '';
    $ahbn_featured   = isset($_POST['featured']) ? 1 : 0;

    $ahbn_room_numbers = ( isset($_POST['room_number']) && is_array($_POST['room_number']) )
        ? array_map('sanitize_text_field', wp_unslash($_POST['room_number']))
        : [];

    $ahbn_amenities = ( isset($_POST['amenities']) && is_array($_POST['amenities']) )
        ? array_map('sanitize_text_field', wp_unslash($_POST['amenities']))
        : [];

    $ahbn_extras = ( isset($_POST['extras']) && is_array($_POST['extras']) )
        ? array_map('sanitize_text_field', wp_unslash($_POST['extras']))
        : [];

    $ahbn_featured_image_id = isset($_POST['featured_image_id']) ? intval($_POST['featured_image_id']) : 0;

    $ahbn_gallery_ids = isset($_POST['gallery_image_ids'])
        ? array_map('intval', explode(',', wp_unslash($_POST['gallery_image_ids'])))
        : [];

    // Prepare post data
    $ahbn_post_data = [
        'post_title'   => $ahbn_room_name,
        'post_content' => $ahbn_room_desc,
        'post_type'    => $post_type,
        'post_status'  => 'publish',
    ];

    if ( $ahbn_room_id > 0 ) {
        $ahbn_post_data['ID'] = $ahbn_room_id;
        wp_update_post($ahbn_post_data);
    } else {
        $ahbn_room_id = wp_insert_post($ahbn_post_data);
    }

    // Update meta
    update_post_meta($ahbn_room_id, 'ahbn_price', $ahbn_room_price);
    update_post_meta($ahbn_room_id, 'ahbn_room_type', $ahbn_room_type);
    update_post_meta($ahbn_room_id, 'ahbn_availability', $ahbn_available);
    update_post_meta($ahbn_room_id, 'ahbn_featured', $ahbn_featured);
    update_post_meta($ahbn_room_id, 'ahbn_room_number', $ahbn_room_numbers);
    update_post_meta($ahbn_room_id, 'ahbn_amenities', $ahbn_amenities);
    update_post_meta($ahbn_room_id, 'ahbn_extras', $ahbn_extras);

    if ( $ahbn_featured_image_id ) {
        set_post_thumbnail($ahbn_room_id, $ahbn_featured_image_id);
    }

    if ( ! empty($ahbn_gallery_ids) ) {
        update_post_meta($ahbn_room_id, 'ahbn_room_gallery', $ahbn_gallery_ids);
    }

    echo '<div class="updated notice"><p>Room saved successfully!</p></div>';

} elseif ( isset($_POST['ahbn_save_room']) ) {

    echo '<div class="error notice"><p>Security check failed. Room not saved.</p></div>';
}

/* -----------------------------
   LOAD DATA FOR EDIT MODE
------------------------------ */
$ahbn_room_name = $ahbn_room_desc = $ahbn_room_price = $ahbn_availability = $ahbn_room_type = '';
$ahbn_amenities_selected = $ahbn_extras_selected = $ahbn_gallery_ids = [];
$ahbn_featured = 0;
$ahbn_room_number = [];
$ahbn_featured_image_id = 0;

if ($ahbn_edit_room > 0) {
    $ahbn_r = get_post($ahbn_edit_room);
    $ahbn_room_name = $ahbn_r->post_title;
    $ahbn_room_desc = $ahbn_r->post_content;
    $ahbn_room_price = get_post_meta($ahbn_edit_room, 'ahbn_price', true);
    $ahbn_room_type = get_post_meta($ahbn_edit_room, 'ahbn_room_type', true);
    $ahbn_availability = get_post_meta($ahbn_edit_room, 'ahbn_availability', true);
    $ahbn_room_number = get_post_meta($ahbn_edit_room, 'ahbn_room_number', true);
    if (!is_array($ahbn_room_number)) $ahbn_room_number = [$ahbn_room_number];
    $ahbn_amenities_selected = get_post_meta($ahbn_edit_room, 'ahbn_amenities', true);
    $ahbn_extras_selected = get_post_meta($ahbn_edit_room, 'ahbn_extras', true);
    if (!is_array($ahbn_extras_selected)) $ahbn_extras_selected = [];
    $ahbn_gallery_ids = get_post_meta($ahbn_edit_room, 'ahbn_room_gallery', true);
    if (!is_array($ahbn_gallery_ids)) $ahbn_gallery_ids = [];
    $ahbn_featured = get_post_meta($ahbn_edit_room, 'ahbn_featured', true);
    $ahbn_featured_image_id = get_post_thumbnail_id($ahbn_edit_room);
} else {
    $ahbn_room_type = $ahbn_room_types[0];
    $ahbn_availability = 'Available';
}

/* -----------------------------
     ADD ROOM FORM
------------------------------ */
?>

<form method="post" enctype="multipart/form-data">
<?php wp_nonce_field('ahbn_save_room_verify'); ?>
<input type="hidden" name="room_id" value="<?php echo esc_attr($ahbn_edit_room); ?>">

<div class="ahbn-add-room-form" style="display:flex; gap:20px; flex-wrap:wrap;">
    <!-- LEFT COLUMN -->
    <div class="left-column" style="flex:1; min-width:300px;">
        <table class="form-table">
            <tr>
                <th>Room Name</th>
                <td><input type="text" name="room_name" value="<?php echo esc_attr($ahbn_room_name); ?>" required style="width:100%"></td>
            </tr>
            <tr>
                <th>Room Number</th>
                <td>
                    <?php foreach ($ahbn_room_numbers as $ahbn_num): ?>
                        <label style="margin-right:10px;">
                            <input type="checkbox" name="room_number[]" value="<?php echo esc_attr($ahbn_num); ?>"
                            <?php echo in_array($ahbn_num, $ahbn_room_number) ? 'checked' : ''; ?>>
                            <?php echo esc_html($ahbn_num); ?>
                        </label>
                    <?php endforeach; ?>
                </td>
            </tr>
            <tr>
                <th>Description</th>
                <td>
                    <?php
                    wp_editor(
                        $ahbn_room_desc,
                        'room_desc',
                        ['textarea_name'=>'room_desc','media_buttons'=>true,'textarea_rows'=>10,'teeny'=>false]
                    );
                    ?>
                </td>
            </tr>
            <tr>
                <th>Price</th>
                <td><input type="number" step="0.01" name="room_price" value="<?php echo esc_attr($ahbn_room_price); ?>" style="width:100%"></td>
            </tr>
            <tr>
                <th>Room Type</th>
                <td>
                    <select name="room_type" style="width:100%">
                        <?php foreach ($ahbn_room_types as $ahbn_type): ?>
                            <option value="<?php echo esc_attr($ahbn_type); ?>" <?php selected($ahbn_room_type, $ahbn_type); ?>>
                                <?php echo esc_html($ahbn_type); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th>Availability</th>
                <td>
                    <select name="availability" style="width:100%">
                        <option value="Available" <?php selected($ahbn_availability,'Available'); ?>>Available</option>
                        <option value="Booked" <?php selected($ahbn_availability,'Booked'); ?>>Booked</option>
                        <option value="Maintenance" <?php selected($ahbn_availability,'Maintenance'); ?>>Maintenance</option>
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
                    <?php foreach ($ahbn_amenities_list as $ahbn_am): ?>
                        <label>
                            <input type="checkbox" name="amenities[]" value="<?php echo esc_attr($ahbn_am); ?>"
                            <?php echo (is_array($ahbn_amenities_selected) && in_array($ahbn_am, $ahbn_amenities_selected)) ? 'checked' : ''; ?>>
                            <?php echo esc_html($ahbn_am); ?>
                        </label><br>
                    <?php endforeach; ?>
                </td>
            </tr>

            <tr>
                <th>Extras</th>
                <td>
                    <?php foreach ($ahbn_extras_list as $ahbn_ex): ?>
                        <label>
                            <input type="checkbox" name="extras[]" value="<?php echo esc_attr($ahbn_ex); ?>"
                            <?php echo (is_array($ahbn_extras_selected) && in_array($ahbn_ex, $ahbn_extras_selected)) ? 'checked' : ''; ?>>
                            <?php echo esc_html($ahbn_ex); ?>
                        </label><br>
                    <?php endforeach; ?>
                </td>
            </tr>

            <tr>
                <th>Featured Image</th>
                <td>
                    <div class="featured-image-wrapper">
                        <?php if($ahbn_featured_image_id): ?>
                            <?php echo wp_get_attachment_image($ahbn_featured_image_id, [150,150]); ?>
                        <?php endif; ?>
                        <input type="hidden" name="featured_image_id" id="featured_image_id" value="<?php echo esc_attr($ahbn_featured_image_id); ?>">
                        <button type="button" class="button select-featured">Select Image</button>
                        <button type="button" class="button remove-featured">Remove</button>
                    </div>
                </td>
            </tr>

            <tr>
                <th>Gallery Images</th>
                <td>
                    <ul id="gallery-list" class="gallery-list">
                        <?php if($ahbn_gallery_ids): foreach($ahbn_gallery_ids as $ahbn_g_id): ?>
                            <li data-id="<?php echo esc_attr($ahbn_g_id); ?>" class="gallery-item">
                                <?php echo wp_get_attachment_image($ahbn_g_id, [100,100]); ?>
                                <button type="button" class="remove-gallery button">Remove</button>
                            </li>
                        <?php endforeach; endif; ?>
                    </ul>
                    <input type="hidden" name="gallery_image_ids" id="gallery_image_ids" value="<?php echo esc_attr(implode(',', $ahbn_gallery_ids)); ?>">
                    <button type="button" class="button add-gallery">Add Gallery Images</button>
                </td>
            </tr>

            <tr>
                <th>Featured Switch</th>
                <td>
                    <label class="switch">
                        <input type="checkbox" name="featured" value="1" <?php checked($ahbn_featured,1); ?>>
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
    var ahbn_featured_frame;
    $('.select-featured').on('click', function(e){
        e.preventDefault();
        if(ahbn_featured_frame) ahbn_featured_frame.open();
        ahbn_featured_frame = wp.media({
            title: 'Select Featured Image',
            button: { text: 'Set Featured' },
            multiple: false
        });
        ahbn_featured_frame.on('select', function(){
            var attachment = ahbn_featured_frame.state().get('selection').first().toJSON();
            $('#featured_image_id').val(attachment.id);
            $('.featured-image-wrapper').prepend('<img src="'+attachment.url+'" style="max-width:150px;">');
        });
        ahbn_featured_frame.open();
    });
    $('.remove-featured').on('click', function(){
        $('#featured_image_id').val('');
        $('.featured-image-wrapper img').remove();
    });

    var ahbn_gallery_frame;
    $('.add-gallery').on('click', function(e){
        e.preventDefault();
        ahbn_gallery_frame = wp.media({
            title: 'Select Gallery Images',
            button: { text: 'Add to gallery' },
            multiple: true
        });
        ahbn_gallery_frame.on('select', function(){
            var selection = ahbn_gallery_frame.state().get('selection');
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
        ahbn_gallery_frame.open();
    });

    $(document).on('click', '.remove-gallery', function(){
        var li = $(this).closest('li');
        var id = li.data('id').toString();
        var ids = $('#gallery_image_ids').val().split(',').filter(i => i != id);
        $('#gallery_image_ids').val(ids.join(','));
        li.remove();
    });

    $('#gallery-list').sortable({
        update: function(event, ui){
            var ids = [];
            $('#gallery-list li').each(function(){ ids.push($(this).data('id')); });
            $('#gallery_image_ids').val(ids.join(','));
        }
    });
});
</script>
