<?php
// =====================================
// EDIT ROOM PAGE
// File: admin/rooms/edit-room.php
// =====================================

// Get room ID
$ahbn_room_id = isset($_GET['room_id']) ? intval($_GET['room_id']) : 0;
if (!$ahbn_room_id) {
    echo "<div class='error'><p>Invalid Room ID.</p></div>";
    return;
}

// Load room post
$ahbn_room = get_post($ahbn_room_id);
if (!$ahbn_room) {
    echo "<div class='error'><p>Room not found!</p></div>";
    return;
}

// ----------------------------
// Load Currency
// ----------------------------
$ahbn_currency_code = get_option('ahbn_hotel_currency', 'USD');
$ahbn_symbols = [
    'USD' => '$',
    'EUR' => '€',
    'GBP' => '£',
    'BDT' => '৳',
    'INR' => '₹',
];
$ahbn_currency_symbol = $ahbn_symbols[$ahbn_currency_code] ?? '$';

// ----------------------------
// Load Room Meta
// ----------------------------
$ahbn_price     = get_post_meta($ahbn_room_id, 'ahbn_price', true);
$ahbn_size      = get_post_meta($ahbn_room_id, 'ahbn_size', true);
$ahbn_adults    = get_post_meta($ahbn_room_id, 'ahbn_adults', true);
$ahbn_children  = get_post_meta($ahbn_room_id, 'ahbn_children', true);
$ahbn_gallery   = get_post_meta($ahbn_room_id, 'ahbn_gallery', true);
$ahbn_features  = get_post_meta($ahbn_room_id, 'ahbn_features', true);

if (!is_array($ahbn_gallery)) $ahbn_gallery = [];
if (!is_array($ahbn_features)) $ahbn_features = [];

// ----------------------------
// Save Room (POST)
// ----------------------------
if ( isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' ) {

    if (
        isset($_POST['ahbn_edit_room_nonce']) &&
        wp_verify_nonce(
            wp_unslash($_POST['ahbn_edit_room_nonce']),
            'ahbn_edit_room'
        )
    ) {

        // Sanitize inputs (WITH PREFIX ✅)
        $ahbn_room_title   = isset($_POST['room_title']) ? sanitize_text_field(wp_unslash($_POST['room_title'])) : '';
        $ahbn_room_content = isset($_POST['room_content']) ? wp_kses_post(wp_unslash($_POST['room_content'])) : '';
        $ahbn_price        = isset($_POST['price']) ? floatval($_POST['price']) : 0;
        $ahbn_size         = isset($_POST['size']) ? sanitize_text_field(wp_unslash($_POST['size'])) : '';
        $ahbn_adults       = isset($_POST['adults']) ? intval($_POST['adults']) : 0;
        $ahbn_children     = isset($_POST['children']) ? intval($_POST['children']) : 0;

        $ahbn_gallery  = ( isset($_POST['gallery']) && is_array($_POST['gallery']) )
            ? array_map('intval', wp_unslash($_POST['gallery']))
            : [];

        $ahbn_features = ( isset($_POST['features']) && is_array($_POST['features']) )
            ? array_map('sanitize_text_field', wp_unslash($_POST['features']))
            : [];

        // Update post
        wp_update_post([
            'ID'           => $ahbn_room_id,
            'post_title'   => $ahbn_room_title,
            'post_content' => $ahbn_room_content,
        ]);

        // Update meta
        update_post_meta($ahbn_room_id, 'ahbn_price', $ahbn_price);
        update_post_meta($ahbn_room_id, 'ahbn_size', $ahbn_size);
        update_post_meta($ahbn_room_id, 'ahbn_adults', $ahbn_adults);
        update_post_meta($ahbn_room_id, 'ahbn_children', $ahbn_children);
        update_post_meta($ahbn_room_id, 'ahbn_gallery', $ahbn_gallery);
        update_post_meta($ahbn_room_id, 'ahbn_features', $ahbn_features);

        echo '<div class="updated"><p>Room updated successfully!</p></div>';
    }
}
?>

<div class="wrap">
    <h1>Edit Room: <?php echo esc_html($ahbn_room->post_title); ?></h1>

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
                           value="<?php echo esc_attr($ahbn_room->post_title); ?>">
                </td>
            </tr>

            <!-- ROOM CONTENT -->
            <tr>
                <th>Room Description</th>
                <td>
                    <?php
                    wp_editor(
                        $ahbn_room->post_content,
                        'room_content',
                        ['textarea_rows' => 6]
                    );
                    ?>
                </td>
            </tr>

            <!-- PRICE -->
            <tr>
                <th>Price (<?php echo esc_html($ahbn_currency_symbol); ?>)</th>
                <td>
                    <input type="number" 
                           name="price" 
                           step="0.01"
                           value="<?php echo esc_attr($ahbn_price); ?>">
                </td>
            </tr>

            <!-- SIZE -->
            <tr>
                <th>Room Size</th>
                <td>
                    <input type="text" 
                           name="size"
                           class="regular-text"
                           value="<?php echo esc_attr($ahbn_size); ?>">
                </td>
            </tr>

            <!-- ADULTS -->
            <tr>
                <th>Adults</th>
                <td>
                    <input type="number" 
                           name="adults"
                           value="<?php echo esc_attr($ahbn_adults); ?>">
                </td>
            </tr>

            <!-- CHILDREN -->
            <tr>
                <th>Children</th>
                <td>
                    <input type="number" 
                           name="children"
                           value="<?php echo esc_attr($ahbn_children); ?>">
                </td>
            </tr>

            <!-- GALLERY -->
            <tr>
                <th>Gallery</th>
                <td>
                    <div id="ahbn-gallery-wrapper">
                        <?php foreach ($ahbn_gallery as $ahbn_img_id): ?>
                            <div class="ahbn-thumb">
                                <?php echo wp_get_attachment_image($ahbn_img_id, 'thumbnail'); ?>
                                <input type="hidden" name="gallery[]" value="<?php echo esc_attr($ahbn_img_id); ?>">
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
                        <?php foreach ($ahbn_features as $ahbn_feature): ?>
                            <p>
                                <input type="text" name="features[]" value="<?php echo esc_attr($ahbn_feature); ?>">
                                <a href="#" class="remove-feature">Remove</a>
                            </p>
                        <?php endforeach; ?>
                    </div>
                    <a href="#" id="ahbn-add-feature" class="button">Add Feature</a>
                </td>
            </tr>

        </table>

        <p>
            <input type="submit" class="button button-primary" value="Update Room">
        </p>

    </form>
</div>

<script>
document.getElementById('ahbn-add-feature').addEventListener('click', function(e){
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
