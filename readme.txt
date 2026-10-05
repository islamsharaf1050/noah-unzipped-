=== NOAH QR Action Hub ===
Contributors: NOAH Dev Team
Tags: inventory, qr, barcode, woocommerce, consignment
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 2.0.0
Requires Plugins: woocommerce

Unified physical inventory, QR/barcode scanning, ownership & consignment tracking, and the "Meine Verkäufe" seller account tab for NOAH Edelmetalle.

== Description ==

This plugin merges two previously separate prototypes into one system:

1. A scan-and-act staff tool (QR Action Hub) for looking up a physical item by barcode/QR code and updating its status in one tap.
2. A full inventory data model (NOAH Inventory Items) that tracks ownership (NOAH-owned vs. customer consignment), a linked customer account, and offer/payout status — required for the "Sell With NOAH" consignment workflow.

See DOCUMENTATION.md (included alongside this plugin) for a full feature-by-feature usage guide.

== Changelog ==

= 2.0.0 =
* Merged the original scan-UI prototype (noah-qr-hub v1.0) with the full inventory/ownership/payout data model.
* Added the "Meine Verkäufe bei NOAH" customer account tab.
* Added brand-consistent styling (navy / gold / cream) to both the staff Scan Hub and the customer-facing My Account pages.
* Legacy product barcode field kept as a fallback lookup for backward compatibility; new scans auto-create a proper Inventory Item.

= 1.0 =
* Original prototype: barcode field on WooCommerce products, standalone scan UI, no ownership/consignment tracking.
