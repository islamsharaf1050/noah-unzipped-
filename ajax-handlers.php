<?php
if (!defined('ABSPATH')) {
    exit;
}

/* ==========================================
   LOOKUP: checks noah_item first, falls back to the legacy product
   barcode field. A legacy match auto-creates a proper noah_item so
   that product enters the unified inventory system from then on.
   ========================================== */
add_action('wp_ajax_noah_lookup_item', function () {
    check_ajax_referer('noah_qr_hub_action', 'nonce');

    if (!current_user_can('manage_woocommerce')) {
        wp_send_json_error(array('message' => 'Unauthorized'));
    }

    $code = sanitize_text_field($_POST['code'] ?? '');
    if (!$code) {
        wp_send_json_error(array('message' => 'Enter a code'));
    }

    $items = get_posts(array(
        'post_type' => 'noah_item',
        'meta_key' => '_noah_item_code',
        'meta_value' => $code,
        'posts_per_page' => 1,
    ));

    if (empty($items)) {
        $products = get_posts(array(
            'post_type' => 'product',
            'meta_key' => '_noah_internal_barcode',
            'meta_value' => $code,
            'posts_per_page' => 1,
        ));

        if (empty($products)) {
            wp_send_json_error(array('message' => 'No item found for code ' . esc_html($code)));
        }

        $product = $products[0];
        $new_id = wp_insert_post(array(
            'post_type' => 'noah_item',
            'post_title' => $product->post_title,
            'post_status' => 'publish',
        ));
        update_post_meta($new_id, '_noah_linked_product', $product->ID);
        update_post_meta($new_id, '_noah_item_code', $code);
        update_post_meta($new_id, '_noah_item_status', 'available');
        update_post_meta($new_id, '_noah_ownership', 'noah');
        update_post_meta(
            $new_id,
            '_noah_item_qr_url',
            'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($code)
        );
        $items = get_posts(array('post_type' => 'noah_item', 'include' => array($new_id)));
    }

    $item = $items[0];
    $status = get_post_meta($item->ID, '_noah_item_status', true) ?: 'available';
    $ownership = get_post_meta($item->ID, '_noah_ownership', true) === 'consignment'
        ? 'Customer / Consignment'
        : 'NOAH-owned';

    $product_id = get_post_meta($item->ID, '_noah_linked_product', true);
    $image = '';
    if ($product_id && function_exists('wc_get_product')) {
        $p = wc_get_product($product_id);
        if ($p) {
            $image = wp_get_attachment_image_url($p->get_image_id(), 'thumbnail');
        }
    }
    if (!$image && function_exists('wc_placeholder_img_src')) {
        $image = wc_placeholder_img_src();
    }

    $offer = get_post_meta($item->ID, '_noah_offer_amount', true);
    $payout_status = get_post_meta($item->ID, '_noah_payout_status', true);
    $payout_line = $offer !== ''
        ? ('Offer: €' . number_format((float) $offer, 2) . ($payout_status ? ' — Payout: ' . ucfirst($payout_status) : ''))
        : '';

    wp_send_json_success(array(
        'id' => $item->ID,
        'name' => get_the_title($item),
        'code' => get_post_meta($item->ID, '_noah_item_code', true),
        'image' => $image,
        'ownership' => $ownership,
        'status' => $status,
        'status_label' => noah_qr_hub_status_label($status),
        'payout_line' => $payout_line,
    ));
});

/* ==========================================
   UPDATE STATUS from the scan screen (Reserve / Sold / Picked up / Available)
   ========================================== */
add_action('wp_ajax_noah_update_item_status', function () {
    check_ajax_referer('noah_qr_hub_action', 'nonce');

    if (!current_user_can('manage_woocommerce')) {
        wp_send_json_error(array('message' => 'Unauthorized'));
    }

    $id = intval($_POST['id'] ?? 0);
    $status = sanitize_text_field($_POST['status'] ?? '');
    $allowed = array('available', 'reserved', 'sold', 'picked_up');

    if (!$id || !in_array($status, $allowed, true)) {
        wp_send_json_error(array('message' => 'Invalid request'));
    }

    update_post_meta($id, '_noah_item_status', $status);
    noah_qr_hub_sync_product_stock($id, $status);

    wp_send_json_success(array('status_label' => noah_qr_hub_status_label($status)));
});
