<?php
// ==========================================
// Shortcode: [ahbn_thanks_page]
// Displays a Thank You page message after booking
// ==========================================
add_shortcode('ahbn_thanks_page', function($atts) {
    $atts = shortcode_atts([
        'title'       => __('Thank You for Your Booking!', 'awesome-hotel-booking'),
        'message'     => __('Your booking has been successfully completed. We look forward to welcoming you!', 'awesome-hotel-booking'),
        'button_text' => __('Back to Home', 'awesome-hotel-booking'),
        'button_url'  => home_url(),
    ], $atts, 'ahbn_thanks_page');

    ob_start(); ?>
    <div class="ahbn-thanks-page" style="text-align:center; padding:40px;">
        <h1><?php echo esc_html($atts['title']); ?></h1>
        <p><?php echo esc_html($atts['message']); ?></p>
        <a href="<?php echo esc_url($atts['button_url']); ?>" class="button button-primary"><?php echo esc_html($atts['button_text']); ?></a>
    </div>
    <?php
    return ob_get_clean();
});
