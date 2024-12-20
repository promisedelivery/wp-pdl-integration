jQuery(document).ready(function ($) {
    const tableSelector = 'table.wp-list-table.orders';

    // Ensure the column header is added only once.
    if (!$('.promise-delivery-column-header').length) {
        // Add "Promise Delivery" column header.
        $(tableSelector + ' thead tr').each(function () {
            $(this).find('th.order_status').after('<th class="promise-delivery-column-header">Promise Delivery</th>');
        });
    }

    // Ensure the column body is added only once.
    $(tableSelector + ' tbody tr').each(function () {
        if (!$(this).find('.promise-delivery-column').length) {
            const orderId = $(this).attr('id').replace('post-', ''); // Extract order ID.
            $(this).find('td.order_status').after(`
                <td class="promise-delivery-column">
                    <button class="button pdl-fetch-meta" data-order-id="${orderId}">Fetch Data</button>
                </td>
            `);
        }
    });

    // Handle button clicks to fetch order meta.
    $(document).on('click', '.pdl-fetch-meta', function () {
        const button = $(this);
        const orderId = button.data('order-id');

        // Fetch order meta via AJAX.
        $.ajax({
            url: pdl_ajax_data.ajax_url,
            method: 'POST',
            data: {
                action: 'pdl_get_order_meta',
                order_id: orderId,
            },
            beforeSend: function () {
                button.text('Loading...').prop('disabled', true);
            },
            success: function (response) {
                if (response.success) {
                    button.replaceWith(`<span>${response.data.promise_delivery}</span>`);
                } else {
                    button.text('Error').prop('disabled', false);
                    alert(response.data.message || 'An error occurred.');
                }
            },
        });
    });
});
