<?php
// =====================================
// EDIT ROOM PAGE
// File: admin/rooms/edit-room.php
// =====================================

// Get room ID
$room_id = isset($_GET['room_id']) ? intval($_GET['room_id']) : 0;
if (!$room_id) {
    echo "<div class='error'><p>Invalid Room ID.</p></div>";
    return;
}

// Load room post
$room = get_post($room_id);
if (!$room) {
    echo "<div class='error'><p>Room not found!</p></div>";
    return;
}

// ----------------------------
// Load Currency
// ----------------------------
$currency_code = get_option('ahbn_hotel_currency', 'USD');
$symbols = [
    'USD' => '$',
    'EUR' => '€',
    'GBP' => '£',
    'BDT' => '৳',
    'INR' => '₹',
];
$currency_symbol = $symbols[$currency_code] ?? '$';

// ----------------------------
// Load Room Meta
// ----------------------------
$price = get_post_meta($room_id, 'ahbn_price', true);
$size = get_post_meta($room_id, 'ahbn_size', true);
$adults = get_post_meta($room_id, 'ahbn_adults', true);
$children = get_post_meta($room_id, 'ahbn_children', true);
$gallery = get_post_meta($room_id, 'ahbn_gallery', true);
$features = get_post_meta($room_id, 'ahbn_features', true);

if (!is_array($gallery)) $gallery = [];
if (!is_array($features)) $features = [];

// ----------------------------
// Save Room (POST)
// ----------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ahbn_edit_room_nonce'])) {

    if (wp_verify_nonce($_POST['ahbn_edit_room_nonce'], 'ahbn_edit_room')) {

        // Update title + content
        wp_update_post([
            'ID' => $room_id,
            'post_title' => sanitize_text_field($_POST['room_title']),
            'post_content' => wp_kses_post($_POST['room_content']),
        ]);

        // Update meta
        update_post_meta($room_id, 'ahbn_price', floatval($_POST['price']));
        update_post_meta($room_id, 'ahbn_size', sanitize_text_field($_POST['size']));
        update_post_meta($room_id, 'ahbn_adults', intval($_POST['adults']));
        update_post_meta($room_id, 'ahbn_children', intval($_POST['children']));

        // Gallery
        $gallery = isset($_POST['gallery']) ? array_map('intval', $_POST['gallery']) : [];
        update_post_meta($room_id, 'ahbn_gallery', $gallery);

        // Features
        $features = isset($_POST['features']) ? array_map('sanitize_text_field', $_POST['features']) : [];
        update_post_meta($room_id, 'ahbn_features', $features);

        echo "<div class='updated'><p>Room updated successfully!</p></div>";
    }
}

?>

<div class="wrap">
    <h1>Edit Room: <?php echo esc_html($room->post_title); ?></h1>

    <form method="post">
        <?php wp_nonce_field('ahbn_edit_room', 'ahbn_edit_room_nonce'); ?>

        <table class="form-table">

            <!-- ROOM TITLE -->
            <tr>
                <th>Room Title</th>
                <td>
                    <input type="text" 
                           name="room_title" 
                           class="regular-text" 
                           value="<?php echo esc_attr($room->post_title); ?>">
                </td>
            </tr>

            <!-- ROOM CONTENT -->
            <tr>
                <th>Room Description</th>
                <td>
                    <?php
                    wp_editor(
                        $room->post_content,
                        'room_content',
                        ['textarea_rows' => 6]
                    );
                    ?>
                </td>
            </tr>

            <!-- PRICE -->
            <tr>
                <th>Price (<?php echo esc_html($currency_symbol); ?>)</th>
                <td>
                    <input type="number" 
                           name="price" 
                           step="0.01"
                           value="<?php echo esc_attr($price); ?>">
                </td>
            </tr>

            <!-- SIZE -->
            <tr>
                <th>Room Size</th>
                <td>
                    <input type="text" 
                           name="size"
                           class="regular-text"
                           value="<?php echo esc_attr($size); ?>">
                </td>
            </tr>

            <!-- ADULTS -->
            <tr>
                <th>Adults</th>
                <td>
                    <input type="number" 
                           name="adults"
                           value="<?php echo esc_attr($adults); ?>">
                </td>
            </tr>

            <!-- CHILDREN -->
            <tr>
                <th>Children</th>
                <td>
                    <input type="number" 
                           name="children"
                           value="<?php echo esc_attr($children); ?>">
                </td>
            </tr>

            <!-- GALLERY -->
            <tr>
                <th>Gallery</th>
                <td>
                    <div id="ahbn-gallery-wrapper">
                        <?php foreach ($gallery as $img_id): ?>
                            <div class="ahbn-thumb">
                                <?php echo wp_get_attachment_image($img_id, 'thumbnail'); ?>
                                <input type="hidden" name="gallery[]" value="<?php echo esc_attr($img_id); ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <a href="#" class="button" id="ahbn-add-gallery">Add Images</a>
                </td>
            </tr>

            <!-- FEATURES -->
            <tr>
                <th>Features</th>
                <td>
                    <div id="ahbn-features-wrapper">
                        <?php foreach ($features as $feature): ?>
                            <p>
                                <input type="text" name="features[]" value="<?php echo esc_attr($feature); ?>">
                                <a href="#" class="remove-feature">Remove</a>
                            </p>
                        <?php endforeach; ?>
                    </div>

                    <a href="#" id="add-feature" class="button">Add Feature</a>
                </td>
            </tr>

        </table>

        <p>
            <input type="submit" class="button button-primary" value="Update Room">
        </p>

    </form>
</div>

<!-- SIMPLE JS -->
<script>
document.getElementById('add-feature').addEventListener('click', function(e){
    e.preventDefault();
    let wrap = document.getElementById('ahbn-features-wrapper');
    wrap.insertAdjacentHTML('beforeend',
        '<p><input type="text" name="features[]" value=""> <a href="#" class="remove-feature">Remove</a></p>'
    );
});

document.addEventListener('click', function(e){
    if(e.target.classList.contains('remove-feature')){
        e.preventDefault();
        e.target.parentNode.remove();
    }
});
</script>
