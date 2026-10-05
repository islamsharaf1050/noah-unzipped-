<?php
/**
 * Plugin Name: NOAH QR Action Hub
 * Description: Unified physical inventory, QR/barcode scanning, ownership & consignment tracking, and the "Meine Verkäufe" seller account tab for NOAH Edelmetalle. Merges the original scan-UI prototype with the full inventory/ownership/payout data model required by the Phase 1 client specification.
 * Version: 2.0.0
 * Author: NOAH Dev Team
 * Text Domain: noah-qr-hub
 * Requires Plugins: woocommerce
 */

if (!defined('ABSPATH')) {
    exit;
}

define('NOAH_QR_HUB_VERSION', '2.0.0');
define('NOAH_QR_HUB_PATH', plugin_dir_path(__FILE__));
define('NOAH_QR_HUB_URL', plugin_dir_url(__FILE__));

// Brand palette — single source of truth so every screen this plugin renders
// stays in sync with the NOAH brand guide (cream / navy / gold).
define('NOAH_COLOR_NAVY', '#1B2430');
define('NOAH_COLOR_GOLD', '#C9A467');
define('NOAH_COLOR_CREAM', '#F2E9DC');
define('NOAH_COLOR_TEXT', '#20242C');

require_once NOAH_QR_HUB_PATH . 'includes/inventory-cpt.php';
require_once NOAH_QR_HUB_PATH . 'includes/legacy-barcode.php';
require_once NOAH_QR_HUB_PATH . 'includes/scan-hub.php';
require_once NOAH_QR_HUB_PATH . 'includes/ajax-handlers.php';
require_once NOAH_QR_HUB_PATH . 'includes/account-tab.php';

/**
 * Activation: flush rewrite rules once so the "Meine Verkäufe" endpoint
 * and the noah_item post type's rewrite rules register immediately,
 * without waiting for a manual permalink re-save.
 */
register_activation_hook(__FILE__, function () {
    // Ensure the CPT and endpoint are registered before flushing.
    noah_qr_hub_register_post_type();
    noah_qr_hub_register_account_endpoint();
    flush_rewrite_rules();
});

register_deactivation_hook(__FILE__, function () {
    flush_rewrite_rules();
});
