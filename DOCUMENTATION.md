# NOAH QR Action Hub — Feature Guide

Version 2.0.0

This plugin gives NOAH staff one place to track every physical item — whether it's NOAH-owned stock or a customer's consignment piece — and gives customers who are selling through NOAH a way to see their item's status and payout online.

---

## 1. What's inside

| Feature | Where to find it |
|---|---|
| NOAH Inventory Items (the core data) | wp-admin → **QR Action Hub** → NOAH Inventory Items |
| Scan/lookup screen for staff | wp-admin → **QR Action Hub** (top-level menu) |
| Legacy barcode field | Product edit screen → Inventory tab |
| "Meine Verkäufe bei NOAH" customer tab | Frontend → My Account (customer-facing) |
| Brand styling | Applies automatically to My Account, login, and register pages |

---

## 2. NOAH Inventory Items — the core record for every physical piece

**Why this exists:** a plain WooCommerce product can't record who owns a piece, whether it's being sold on consignment, or what payout is owed. Every physical item — NOAH-owned or a customer's — gets its own Inventory Item record.

**How to use it:**
1. Go to **QR Action Hub → Add New Inventory Item**.
2. Give it a title (e.g. "Gold Ring 585, 3.2g").
3. Click **Publish**. The plugin automatically generates:
   - A unique **Item Code** (e.g. `NOAH-2AF47252`)
   - A **printable QR code** — print this and physically attach it to the item.
4. Fill in the **Item Details** box:
   - **Linked WooCommerce Product ID** — connects this physical item to its online listing, so status changes here update the shop automatically.
   - **Ownership** — "NOAH-owned" or "Customer / Consignment".
   - **Original Owner / Customer** — free-text name, for internal reference.
   - **Linked NOAH Account** — pick the customer's actual WordPress account here. This is what makes the item show up in *their* "Meine Verkäufe" tab. If you skip this, the customer won't be able to see the item online (staff can still manage it either way).
   - **Date Received**, **Status** (Available / Reserved / Sold / Picked up).
   - **Offer Amount** and **Payout Amount / Status** — only relevant for consignment items; this is exactly what the customer sees in their account.
5. Click **Update** to save changes at any time.

**Important:** internal fields (purchase price, margin, staff notes) are intentionally *not* part of this plugin's customer-facing output — only Offer Amount, Payout Amount, and Payout Status ever reach the customer.

---

## 3. QR Action Hub — the staff scan screen

This is the fast, touch-friendly screen staff use at checkout or in the back office.

**How to use it:**
1. Go to **QR Action Hub** (top-level menu, camera icon).
2. Click into the input box, then either:
   - Scan the item's printed QR code with a handheld scanner (it types the code automatically and submits), or
   - Type the item code manually and press Enter / click **Search**.
3. The item's card appears: photo (pulled from the linked product), name, code, ownership, current status, and — if it's a consignment item — the offer/payout line.
4. Tap one of the four action buttons:
   - **Reserve Item** — holds it, removes it from the online shop.
   - **Sell In-Store** — marks it sold, removes it from the online shop.
   - **Mark as Picked Up** — for consignment items the customer has collected (e.g. after a completed sale).
   - **Mark Available** — puts it back on the online shop.
5. Each action asks for confirmation before it runs, and updates instantly — no page reload needed.

**Backward compatibility:** if you scan a code that isn't a known Inventory Item, the plugin checks the old "Legacy Barcode" field on WooCommerce products. If it finds a match, it automatically creates a proper Inventory Item for that product on the spot — so old stock keeps working without any manual migration.

---

## 4. Legacy Barcode field (product edit screen)

Found under each product's **Inventory** tab, this is the original barcode field from the first prototype. It's kept only as a fallback lookup for stock that predates this plugin's Inventory Items. **New stock should always get a proper Inventory Item instead** — it's the only way to track ownership, payouts, and get a printable QR code.

---

## 5. "Meine Verkäufe bei NOAH" — the customer account tab

Automatically added to every logged-in customer's **My Account** menu, right after "Orders".

**What the customer sees:** a table of every Inventory Item linked to their account, showing:
- Item name
- Status (Verfügbar / Reserviert / Verkauft / Abgeholt)
- Angebot (offer amount)
- Auszahlung (payout amount)
- Auszahlungsstatus (Ausstehend / Genehmigt / Ausbezahlt)

**What they don't see:** anything internal — purchase price, margin, staff notes. This matches the requirement that NOAH controls exactly which information a seller can view.

**Nothing to configure** — this tab appears automatically once an item is linked to the customer's account via the "Linked NOAH Account" field described in section 2.

---

## 6. Brand styling

The plugin automatically applies NOAH's brand colors (navy `#1B2430`, gold `#C9A467`, cream `#F2E9DC`) to:
- Login form
- Registration form
- All My Account tabs, including Orders, Addresses, Account details, and Meine Verkäufe

No setup needed — this is CSS that loads automatically on account-related pages only, so it never affects the rest of the site.

---

## 7. Data & database notes

- No custom database tables are created. Everything is stored using WordPress's standard post + post-meta system under the `noah_item` post type, plus one meta field (`_noah_internal_barcode`) on WooCommerce products for the legacy fallback.
- This means normal WordPress backups, exports, and staging/production migrations all cover this plugin's data automatically — nothing extra to back up separately.
- Deactivating the plugin does **not** delete any data. Uninstalling (deleting) the plugin also leaves existing data in place, so it's safe to deactivate for troubleshooting without losing inventory records.

---

## 8. What this plugin does *not* cover

Per the original Phase 1 scope, the following are separate, larger pieces of work and are **not** part of this plugin:
- Live gold/silver/platinum/palladium pricing feed and the public buy-in price list.
- POS integration (physical sales still require a staff member to update the item's status here manually — there is no automatic link to an in-store register).
- Homepage/storefront design (handled separately via Elementor page templates).

---

## 9. Installation

1. In wp-admin, go to **Plugins → Add New → Upload Plugin**.
2. Choose the `noah-qr-hub.zip` file and click **Install Now**.
3. If the original "NOAH QR Action Hub" v1.0 plugin is still installed, deactivate and delete it first to avoid a naming conflict (this v2.0 package replaces it entirely).
4. Click **Activate**.
5. No further setup is required — the Inventory Items menu, Scan Hub, account tab, and styling are all active immediately.
