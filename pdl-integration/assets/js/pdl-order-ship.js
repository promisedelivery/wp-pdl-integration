jQuery(document).ready(function ($) {
    $('.pdl-ship-button').on('click', function () {
        var button = $(this);
        var orderId = button.data('order-id');

        if (!confirm('Are you sure you want to ship this order?')) {
            return;
        }

        button.prop('disabled', true).text('Processing...');

        $.ajax({
            url: pdl_ajax_data.ajax_url,
            method: 'POST',
            data: {
                action: 'pdl_ship_order',
                nonce: pdl_ajax_data.nonce,
                order_id: orderId,
            },
            success: function (response) {
                if (response.success) {
                    button.closest('td').html('Shipped: ' + response.data.invoice_id);
                } else {
                    alert(response.data.message || 'Error occurred.');
                    button.prop('disabled', false).text('Click to Ship');
                }
            },
            error: function () {
                alert('An error occurred. Please try again.');
                button.prop('disabled', false).text('Click to Ship');
            },
        });
    });
});
