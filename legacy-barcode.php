<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * The original plugin prototype stored a barcode directly on the
 * WooCommerce product (_noah_internal_barcode). We keep that field so
 * any stock already tagged that way keeps working, but it's now a
 * fallback: the scan lookup checks noah_item records first, and only
 * falls back to this field if nothing matches — at which point it
 * auto-creates a proper noah_item so the product enters the unified
 * inventory system going forward. New items should be added directly
 * as NOAH Inventory Items instead.
 */
add_action('woocommerce_product_options_sku', function () {
    echo '<div class="options_group">';
    woocommerce_wp_text_input(array(
        'id' => '_noah_internal_barcode',
        'label' => __('NOAH Legacy Barcode (optional)', 'noah-qr-hub'),
        'placeholder' => 'Only needed for old stock without an Inventory Item yet',
        'desc_tip' => 'true',
        'description' => __('Fallback lookup only. New items should be tracked via NOAH Inventory Items instead, which supports ownership, QR codes, and payouts.', 'noah-qr-hub'),
    ));
    echo '</div>';
});

add_action('woocommerce_process_product_meta', function ($post_id) {
    $barcode = $_POST['_noah_internal_barcode'] ?? '';
    if (!empty($barcode)) {
        update_post_meta($post_id, '_noah_internal_barcode', sanitize_text_field($barcode));
    }
});
