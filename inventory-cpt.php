<?php
if (!defined('ABSPATH')) {
    exit;
}

/* ==========================================
   1. CUSTOM POST TYPE: noah_item
   Source of truth for every physical piece — the client spec requires
   tracking ownership (NOAH-owned vs. customer consignment), a linked
   customer account, and offer/payout status, none of which a bare
   WooCommerce product can hold.
   ========================================== */
function noah_qr_hub_register_post_type() {
    register_post_type('noah_item', array(
        'label' => 'NOAH Inventory Items',
        'labels' => array(
            'name' => 'NOAH Inventory Items',
            'singular_name' => 'Inventory Item',
            'add_new_item' => 'Add New Inventory Item',
        ),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => 'noah-qr-hub',
        'supports' => array('title'),
        'capability_type' => 'post',
        'map_meta_cap' => true,
    ));
}
add_action('init', 'noah_qr_hub_register_post_type');

/* ==========================================
   2. AUTO-GENERATE ITEM CODE + QR IMAGE ON FIRST SAVE
   ========================================== */
function noah_qr_hub_generate_code_and_qr($post_id, $post, $update) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (wp_is_post_revision($post_id)) {
        return;
    }
    if (get_post_meta($post_id, '_noah_item_code', true)) {
        return; // already generated, never overwrite
    }
    $code = 'NOAH-' . strtoupper(substr(md5($post_id . time()), 0, 8));
    update_post_meta($post_id, '_noah_item_code', $code);
    update_post_meta($post_id, '_noah_item_status', 'available');
    update_post_meta(
        $post_id,
        '_noah_item_qr_url',
        'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($code)
    );
}
add_action('save_post_noah_item', 'noah_qr_hub_generate_code_and_qr', 10, 3);

/* ==========================================
   3. ITEM DETAILS META BOX
   ========================================== */
function noah_qr_hub_add_meta_boxes() {
    add_meta_box('noah_item_details', 'Item Details', 'noah_qr_hub_render_item_metabox', 'noah_item', 'normal', 'high');
}
add_action('add_meta_boxes', 'noah_qr_hub_add_meta_boxes');

function noah_qr_hub_render_item_metabox($post) {
    $product_id    = get_post_meta($post->ID, '_noah_linked_product', true);
    $ownership     = get_post_meta($post->ID, '_noah_ownership', true) ?: 'noah';
    $owner_name    = get_post_meta($post->ID, '_noah_owner_name', true);
    $owner_user_id = get_post_meta($post->ID, '_noah_owner_user_id', true);
    $status        = get_post_meta($post->ID, '_noah_item_status', true) ?: 'available';
    $code          = get_post_meta($post->ID, '_noah_item_code', true);
    $qr            = get_post_meta($post->ID, '_noah_item_qr_url', true);
    $date_received = get_post_meta($post->ID, '_noah_date_received', true);
    $offer_amount  = get_post_meta($post->ID, '_noah_offer_amount', true);
    $payout_amount = get_post_meta($post->ID, '_noah_payout_amount', true);
    $payout_status = get_post_meta($post->ID, '_noah_payout_status', true) ?: 'pending';
    wp_nonce_field('noah_item_save', 'noah_item_nonce');
    ?>
    <p><strong>Item Code:</strong> <?php echo esc_html($code ?: 'Will be generated on save'); ?></p>
    <?php if ($qr) : ?>
        <p>
            <img src="<?php echo esc_url($qr); ?>" width="150" height="150" alt="QR Code" /><br />
            Print this and attach it to the physical item.
        </p>
    <?php endif; ?>
    <p>
        <label>Linked WooCommerce Product ID<br />
            <input type="number" name="noah_linked_product" value="<?php echo esc_attr($product_id); ?>" />
        </label>
    </p>
    <p>
        <label>Ownership<br />
            <select name="noah_ownership">
                <option value="noah" <?php selected($ownership, 'noah'); ?>>NOAH-owned</option>
                <option value="consignment" <?php selected($ownership, 'consignment'); ?>>Customer / Consignment</option>
            </select>
        </label>
    </p>
    <p>
        <label>Original Owner / Customer (display name)<br />
            <input type="text" name="noah_owner_name" value="<?php echo esc_attr($owner_name); ?>" style="width:100%;max-width:400px;" />
        </label>
    </p>
    <p>
        <label>Linked NOAH Account (so the seller can see this item in "Meine Verkäufe")<br />
            <?php
            wp_dropdown_users(array(
                'name' => 'noah_owner_user_id',
                'selected' => $owner_user_id,
                'show_option_none' => '— No linked account —',
                'option_none_value' => '0',
            ));
            ?>
        </label>
    </p>
    <p>
        <label>Date Received<br />
            <input type="date" name="noah_date_received" value="<?php echo esc_attr($date_received); ?>" />
        </label>
    </p>
    <p>
        <label>Status<br />
            <select name="noah_item_status">
                <option value="available" <?php selected($status, 'available'); ?>>Available</option>
                <option value="reserved" <?php selected($status, 'reserved'); ?>>Reserved</option>
                <option value="sold" <?php selected($status, 'sold'); ?>>Sold</option>
                <option value="picked_up" <?php selected($status, 'picked_up'); ?>>Picked up</option>
            </select>
        </label>
    </p>
    <hr />
    <p><em>Seller offer &amp; payout (visible to the linked customer in their account):</em></p>
    <p>
        <label>Agreed / Offer Amount (€)<br />
            <input type="number" step="0.01" name="noah_offer_amount" value="<?php echo esc_attr($offer_amount); ?>" />
        </label>
    </p>
    <p>
        <label>Payout Amount (€)<br />
            <input type="number" step="0.01" name="noah_payout_amount" value="<?php echo esc_attr($payout_amount); ?>" />
        </label>
    </p>
    <p>
        <label>Payout Status<br />
            <select name="noah_payout_status">
                <option value="pending" <?php selected($payout_status, 'pending'); ?>>Pending</option>
                <option value="approved" <?php selected($payout_status, 'approved'); ?>>Approved</option>
                <option value="paid" <?php selected($payout_status, 'paid'); ?>>Paid</option>
            </select>
        </label>
    </p>
    <?php
}

/* ==========================================
   4. SAVE META BOX FIELDS + SYNC LINKED PRODUCT STOCK
   ========================================== */
function noah_qr_hub_save_item_meta($post_id) {
    if (!isset($_POST['noah_item_nonce']) || !wp_verify_nonce($_POST['noah_item_nonce'], 'noah_item_save')) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['noah_linked_product'])) {
        update_post_meta($post_id, '_noah_linked_product', intval($_POST['noah_linked_product']));
    }
    if (isset($_POST['noah_ownership'])) {
        update_post_meta($post_id, '_noah_ownership', sanitize_text_field($_POST['noah_ownership']));
    }
    if (isset($_POST['noah_owner_name'])) {
        update_post_meta($post_id, '_noah_owner_name', sanitize_text_field($_POST['noah_owner_name']));
    }
    if (isset($_POST['noah_owner_user_id'])) {
        update_post_meta($post_id, '_noah_owner_user_id', intval($_POST['noah_owner_user_id']));
    }
    if (isset($_POST['noah_date_received'])) {
        update_post_meta($post_id, '_noah_date_received', sanitize_text_field($_POST['noah_date_received']));
    }
    if (isset($_POST['noah_offer_amount'])) {
        update_post_meta($post_id, '_noah_offer_amount', sanitize_text_field($_POST['noah_offer_amount']));
    }
    if (isset($_POST['noah_payout_amount'])) {
        update_post_meta($post_id, '_noah_payout_amount', sanitize_text_field($_POST['noah_payout_amount']));
    }
    if (isset($_POST['noah_payout_status'])) {
        update_post_meta($post_id, '_noah_payout_status', sanitize_text_field($_POST['noah_payout_status']));
    }
    if (isset($_POST['noah_item_status'])) {
        $new_status = sanitize_text_field($_POST['noah_item_status']);
        update_post_meta($post_id, '_noah_item_status', $new_status);
        noah_qr_hub_sync_product_stock($post_id, $new_status);
    }
}
add_action('save_post_noah_item', 'noah_qr_hub_save_item_meta', 20);

/**
 * Keeps the online shop and physical inventory in sync: an item marked
 * sold or reserved in-store disappears from the online shop immediately.
 * (Per the client spec, there is no POS integration — this sync only
 * runs when staff update the item's status through this plugin.)
 */
function noah_qr_hub_sync_product_stock($item_post_id, $status) {
    $product_id = get_post_meta($item_post_id, '_noah_linked_product', true);
    if (!$product_id || !function_exists('wc_get_product')) {
        return;
    }
    $product = wc_get_product($product_id);
    if (!$product) {
        return;
    }
    $product->set_stock_status(in_array($status, array('sold', 'reserved'), true) ? 'outofstock' : 'instock');
    $product->save();
}

function noah_qr_hub_status_label($status) {
    $labels = array(
        'available' => 'Available',
        'reserved' => 'Reserved',
        'sold' => 'Sold',
        'picked_up' => 'Picked up',
    );
    return $labels[$status] ?? $status;
}
