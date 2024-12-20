jQuery(document).ready(function ($) {
    // Initialize Select2 for billing and shipping area fields.
    $('#billing_area, #shipping_area').select2({
        placeholder: 'Select an area...',
        allowClear: true,
        width: '100%',
    });

    // Fetch billing areas dynamically based on the selected billing state.
    $('#_billing_state').on('change', function () {
        var state_id = $(this).val();

        $.ajax({
            url: pdl_order_edit_ajax_data.ajax_url,
            method: 'POST',
            data: {
                action: 'pdl_get_admin_order_edit_areas',
                state_id: state_id,
            },
            success: function (response) {
                var areaDropdown = $('#billing_area');
                areaDropdown.empty();

                areaDropdown.append('<option value="">' + 'Select a billing area...' + '</option>');

                if (response.success && response.data) {
                    $.each(response.data, function (key, value) {
                        areaDropdown.append('<option value="' + key + '">' + value + '</option>');
                    });
                }

                areaDropdown.trigger('change');
            },
        });
    });

    // Fetch shipping areas dynamically based on the selected shipping state.
    $('#_shipping_state').on('change', function () {
        var state_id = $(this).val();

        $.ajax({
            url: pdl_order_edit_ajax_data.ajax_url,
            method: 'POST',
            data: {
                action: 'pdl_get_admin_order_edit_areas',
                state_id: state_id,
            },
            success: function (response) {
                var areaDropdown = $('#shipping_area');
                areaDropdown.empty();

                areaDropdown.append('<option value="">' + 'Select a shipping area...' + '</option>');

                if (response.success && response.data) {
                    $.each(response.data, function (key, value) {
                        areaDropdown.append('<option value="' + key + '">' + value + '</option>');
                    });
                }

                areaDropdown.trigger('change');
            },
        });
    });
});
