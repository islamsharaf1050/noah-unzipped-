<?php
if (!defined('ABSPATH')) {
    exit;
}

/* ==========================================
   "Meine Verkäufe bei NOAH" — the seller-facing side of the consignment
   workflow. Shows a logged-in customer only what the client spec says
   they should see: item status, offer, and payout status. Internal
   fields (purchase price, margin, staff notes) never reach this screen.
   ========================================== */

add_filter('woocommerce_account_menu_items', function ($items) {
    $new_items = array();
    foreach ($items as $key => $label) {
        $new_items[$key] = $label;
        if ($key === 'orders') {
            $new_items['meine-verkaeufe'] = 'Meine Verkäufe bei NOAH';
        }
    }
    if (!isset($new_items['meine-verkaeufe'])) {
        $new_items['meine-verkaeufe'] = 'Meine Verkäufe bei NOAH';
    }
    return $new_items;
});

function noah_qr_hub_register_account_endpoint() {
    add_rewrite_endpoint('meine-verkaeufe', EP_ROOT | EP_PAGES);
}
add_action('init', 'noah_qr_hub_register_account_endpoint');

add_action('woocommerce_account_meine-verkaeufe_endpoint', function () {
    $user_id = get_current_user_id();

    $items = get_posts(array(
        'post_type' => 'noah_item',
        'meta_key' => '_noah_owner_user_id',
        'meta_value' => $user_id,
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
    ));

    echo '<h3>Meine Verkäufe bei NOAH</h3>';

    if (empty($items)) {
        echo '<p>Sie haben aktuell keine Artikel bei NOAH in Bearbeitung.</p>';
        return;
    }

    $payout_labels = array(
        'pending' => 'Ausstehend',
        'approved' => 'Genehmigt',
        'paid' => 'Ausbezahlt',
    );
    $status_labels_de = array(
        'available' => 'Verfügbar',
        'reserved' => 'Reserviert',
        'sold' => 'Verkauft',
        'picked_up' => 'Abgeholt',
    );

    echo '<table class="woocommerce-orders-table shop_table shop_table_responsive"><thead><tr>';
    echo '<th>Artikel</th><th>Status</th><th>Angebot</th><th>Auszahlung</th><th>Auszahlungsstatus</th>';
    echo '</tr></thead><tbody>';

    foreach ($items as $item) {
        $status = get_post_meta($item->ID, '_noah_item_status', true) ?: 'available';
        $offer_amount = get_post_meta($item->ID, '_noah_offer_amount', true);
        $payout_amount = get_post_meta($item->ID, '_noah_payout_amount', true);
        $payout_status = get_post_meta($item->ID, '_noah_payout_status', true) ?: 'pending';

        echo '<tr>';
        echo '<td>' . esc_html(get_the_title($item)) . '</td>';
        echo '<td>' . esc_html($status_labels_de[$status] ?? $status) . '</td>';
        echo '<td>' . ($offer_amount !== '' ? esc_html(number_format((float) $offer_amount, 2)) . ' €' : '—') . '</td>';
        echo '<td>' . ($payout_amount !== '' ? esc_html(number_format((float) $payout_amount, 2)) . ' €' : '—') . '</td>';
        echo '<td>' . esc_html($payout_labels[$payout_status] ?? $payout_status) . '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
    echo '<p style="margin-top:16px;font-size:13px;color:#666;">Interne Details (Einkaufspreis, Marge, Notizen) sind ausschließlich für NOAH-Mitarbeiter sichtbar.</p>';
});

/* ==========================================
   Enqueue the account styling only on My Account pages
   ========================================== */
add_action('wp_enqueue_scripts', function () {
    if (function_exists('is_account_page') && is_account_page()) {
        wp_enqueue_style(
            'noah-qr-hub-account',
            NOAH_QR_HUB_URL . 'css/account-styling.css',
            array(),
            NOAH_QR_HUB_VERSION
        );
    }
});
