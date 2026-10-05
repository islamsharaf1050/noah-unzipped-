jQuery(function ($) {
    var nonce = $('#noah_qr_nonce').val();
    var currentId = null;

    function lookup(code) {
        if (!code) {
            return;
        }
        $.post(ajaxurl, {
            action: 'noah_lookup_item',
            code: code,
            nonce: nonce
        }, function (res) {
            if (res.success) {
                var d = res.data;
                currentId = d.id;
                $('#noah-prod-name').text(d.name);
                $('#noah-prod-sku').text(d.code);
                $('#noah-prod-img').attr('src', d.image);
                $('#noah-ownership-badge').text('Ownership: ' + d.ownership);
                $('#noah-status-badge')
                    .text(d.status_label)
                    .removeClass()
                    .addClass('badge status-' + d.status);
                $('#noah-payout-line').text(d.payout_line || '');
                $('#noah-product-card').fadeIn();
                $('#noah-barcode-input').val('');
                $('#noah-action-feedback').html('');
            } else {
                alert(res.data.message || 'Not found');
            }
        });
    }

    $('#noah-barcode-input').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            lookup($(this).val());
        }
    });

    $('#noah-scan-btn').on('click', function () {
        lookup($('#noah-barcode-input').val());
    });

    $('.noah-action-btn').on('click', function () {
        var status = $(this).data('action');
        if (!currentId) {
            return;
        }
        if (!confirm('Set status to ' + status + '?')) {
            return;
        }
        $.post(ajaxurl, {
            action: 'noah_update_item_status',
            id: currentId,
            status: status,
            nonce: nonce
        }, function (res) {
            if (res.success) {
                $('#noah-action-feedback').html(
                    '<p style="color:#2f8a4e;font-weight:bold;">Updated: ' + res.data.status_label + '</p>'
                );
                $('#noah-status-badge')
                    .text(res.data.status_label)
                    .removeClass()
                    .addClass('badge status-' + status);
            } else {
                alert(res.data.message || 'Error');
            }
        });
    });
});
