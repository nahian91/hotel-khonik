<?php 

// ----------------------------
// Save Room Settings
// ----------------------------
if(isset($_POST['ahbn_save_room_settings'])){
    check_admin_referer('ahbn_room_settings_nonce');

    $room_types = isset($_POST['room_types']) ? array_map('sanitize_text_field', $_POST['room_types']) : [];
    $room_numbers = isset($_POST['room_numbers']) ? array_map('sanitize_text_field', $_POST['room_numbers']) : [];
    $amenities  = isset($_POST['amenities']) ? array_map('sanitize_text_field', $_POST['amenities']) : [];
    $amenities_icons = isset($_POST['amenities_icon']) ? array_map('sanitize_text_field', $_POST['amenities_icon']) : [];
    $extras     = isset($_POST['extras']) ? array_map('sanitize_text_field', $_POST['extras']) : [];
    $extras_prices = isset($_POST['extras_price']) ? array_map('floatval', $_POST['extras_price']) : [];

    update_option('ahbn_room_types', $room_types);
    update_option('ahbn_room_numbers', $room_numbers);
    update_option('ahbn_room_amenities', $amenities);
    update_option('ahbn_room_amenities_icons', $amenities_icons);
    update_option('ahbn_room_extras', $extras);
    update_option('ahbn_room_extras_prices', $extras_prices);

    echo '<div class="updated notice"><p>Settings saved successfully!</p></div>';
}

// Load saved options
$room_types = get_option('ahbn_room_types', ['Standard','Deluxe','Suite']);
$room_numbers = get_option('ahbn_room_numbers', ['101','102','103']);
$amenities  = get_option('ahbn_room_amenities', ['Wi-Fi','AC','Breakfast','TV','Mini Bar','Parking']);
$amenities_icons = get_option('ahbn_room_amenities_icons', ['dashicons-admin-network','dashicons-admin-home','dashicons-megaphone','dashicons-video-alt2','dashicons-awards','dashicons-car']);
$extras     = get_option('ahbn_room_extras', ['Airport Pickup','Breakfast Included']);
$extras_prices = get_option('ahbn_room_extras_prices', [0,0]);

$hotel_icons = [
    'dashicons-admin-home'       => 'Room / Home',
    'dashicons-admin-network'    => 'Wi-Fi',
    'dashicons-car'              => 'Parking',
    'dashicons-megaphone'        => 'Breakfast / Service',
    'dashicons-awards'           => 'Pool / Spa',
    'dashicons-video-alt2'       => 'TV',
    'dashicons-admin-users'      => 'Capacity / Guests',
    'dashicons-star-filled'      => 'Featured'
];
?>

<form method="post" class="ahbn-settings-form">
    <?php wp_nonce_field('ahbn_room_settings_nonce'); ?>
    <div class="ahbn-settings-columns">

        <!-- LEFT COLUMN -->
        <div class="ahbn-settings-column">
            <h3>Room Types</h3>
            <div id="room_types_wrapper" class="ahbn-repeater-wrapper">
                <?php foreach($room_types as $type): ?>
                <div class="ahbn-repeater-row">
                    <input type="text" name="room_types[]" value="<?php echo esc_attr($type); ?>" placeholder="Room Type" required>
                    <button type="button" class="button ahbn-remove-btn">Remove</button>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="button ahbn-add-btn" data-target="room_types">Add Room Type</button>

            <h3>Room Numbers</h3>
            <div id="room_numbers_wrapper" class="ahbn-repeater-wrapper">
                <?php foreach($room_numbers as $number): ?>
                <div class="ahbn-repeater-row">
                    <input type="text" name="room_numbers[]" value="<?php echo esc_attr($number); ?>" placeholder="Room No" required>
                    <button type="button" class="button ahbn-remove-btn">Remove</button>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="button ahbn-add-btn" data-target="room_numbers">Add Room Number</button>
        </div>

        <!-- RIGHT COLUMN -->
        <div class="ahbn-settings-column">
            <h3>Amenities</h3>
            <div id="amenities_wrapper" class="ahbn-repeater-wrapper">
                <?php foreach($amenities as $i => $amenity): ?>
                <div class="ahbn-repeater-row">
                    <input type="text" name="amenities[]" value="<?php echo esc_attr($amenity); ?>" placeholder="Amenity Name" required>
                    <select name="amenities_icon[]" class="amenity-icon-select">
                        <?php foreach($hotel_icons as $class => $label): ?>
                            <option value="<?php echo esc_attr($class); ?>" <?php selected($amenities_icons[$i] ?? '', $class); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="icon-preview dashicons <?php echo esc_attr($amenities_icons[$i] ?? ''); ?>"></span>
                    <button type="button" class="button ahbn-remove-btn">Remove</button>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="button ahbn-add-btn" data-target="amenities">Add Amenity</button>

            <h3>Extras / Add-Ons</h3>
            <div id="extras_wrapper" class="ahbn-repeater-wrapper">
                <?php foreach($extras as $i => $ex): ?>
                <div class="ahbn-repeater-row">
                    <input type="text" name="extras[]" value="<?php echo esc_attr($ex); ?>" placeholder="Feature Name" required>
                    <input type="number" name="extras_price[]" value="<?php echo esc_attr($extras_prices[$i] ?? 0); ?>" placeholder="Price" step="0.01">
                    <button type="button" class="button ahbn-remove-btn">Remove</button>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="button ahbn-add-btn" data-target="extras">Add Extra</button>
        </div>

    </div>

    <p><input type="submit" name="ahbn_save_room_settings" class="button button-primary" value="Save Settings"></p>
</form>
