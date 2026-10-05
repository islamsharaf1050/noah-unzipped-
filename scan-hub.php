<?php
if (!defined('ABSPATH')) {
    exit;
}

/* ==========================================
   ADMIN MENU: single "NOAH QR Action Hub" top-level menu.
   The noah_item post type (registered with show_in_menu = 'noah-qr-hub')
   nests "NOAH Inventory Items" and "Add New" under this same menu.
   ========================================== */
add_action('admin_menu', function () {
    add_menu_page(
        __('NOAH QR Action Hub', 'noah-qr-hub'),
        __('QR Action Hub', 'noah-qr-hub'),
        'manage_woocommerce', // staff with WooCommerce permissions only
        'noah-qr-hub',
        'noah_qr_hub_render_scan_page',
        'dashicons-camera-alt',
        56
    );
}, 9); // priority 9: must register before the CPT attaches its submenu at the default priority

/* ==========================================
   SCAN HUB PAGE
   ========================================== */
function noah_qr_hub_render_scan_page() {
    ?>
    <div class="wrap noah-qr-hub-container">
        <h1>NOAH QR / Barcode Scanner &amp; Action Hub</h1>
        <?php wp_nonce_field('noah_qr_hub_action', 'noah_qr_nonce'); ?>

        <div class="noah-scanner-box">
            <label for="noah-barcode-input"><strong>Scan QR/Barcode or enter item code</strong></label>
            <input type="text" id="noah-barcode-input" class="large-text" placeholder="Waiting for scan..." autofocus>
            <button id="noah-scan-btn" class="button button-primary button-hero">Search</button>
        </div>

        <div id="noah-product-card" class="noah-product-card" style="display:none;">
            <div class="noah-card-header">
                <img id="noah-prod-img" src="" alt="Item">
                <div class="noah-prod-info">
                    <h2 id="noah-prod-name">Item name</h2>
                    <p class="noah-sku">Code: <span id="noah-prod-sku"></span></p>
                    <div class="noah-badges">
                        <span id="noah-ownership-badge" class="badge badge-ownership">Ownership: </span>
                        <span id="noah-status-badge" class="badge">Status</span>
                    </div>
                    <p class="noah-payout-line" id="noah-payout-line"></p>
                </div>
            </div>

            <div class="noah-actions-grid">
                <button class="noah-action-btn btn-reserve" data-action="reserved">Reserve Item</button>
                <button class="noah-action-btn btn-sell" data-action="sold">Sell In-Store</button>
                <button class="noah-action-btn btn-pickup" data-action="picked_up">Mark as Picked Up</button>
                <button class="noah-action-btn btn-available" data-action="available">Mark Available</button>
            </div>

            <div id="noah-action-feedback" class="noah-feedback"></div>
        </div>
    </div>
    <?php
}

/* ==========================================
   ENQUEUE ASSETS — only on our own admin page
   ========================================== */
add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'toplevel_page_noah-qr-hub') {
        return;
    }

    wp_enqueue_style(
        'noah-qr-hub-admin',
        NOAH_QR_HUB_URL . 'css/scan-hub-admin.css',
        array(),
        NOAH_QR_HUB_VERSION
    );

    wp_enqueue_script(
        'noah-qr-hub-scan',
        NOAH_QR_HUB_URL . 'js/scan-hub.js',
        array('jquery'),
        NOAH_QR_HUB_VERSION,
        true
    );
});
