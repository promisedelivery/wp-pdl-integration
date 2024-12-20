jQuery(document).ready(function ($) {
    $('.pdl-ship-order').on('click', function (e) {
        e.preventDefault(); // Prevent default button behavior
        var orderId = $(this).data('order-id');

        $.ajax({
            url: pdl_ajax_data.ajax_url,
            method: 'POST',
            data: {
                action: 'pdl_create_parcel',
                nonce: pdl_ajax_data.nonce,
                order_id: orderId,
            },
            success: function (response) {
                if (response.success) {
                    alert('Parcel created successfully!');
                    location.reload();
                } else {
                    alert(response.data.message || 'An error occurred.');
                }
            },
            error: function (xhr, status, error) {
                alert('AJAX error: ' + error);
            },
        });
    });
});
