jQuery(document).ready(function ($) {
    // Initialize Select2 for both Area dropdowns.
    $('#billing_area, #shipping_area').select2({
        placeholder: 'Select an area...',
        allowClear: true,
        width: '100%',
    });

    // Trigger when the State (District) field changes for billing.
    $('#billing_state').on('change', function () {
        var state_id = $(this).val();

        // Perform AJAX request to fetch areas based on state ID.
        $.ajax({
            url: pdl_checkout_ajax_data.ajax_url, // AJAX URL provided via wp_localize_script.
            method: 'POST',
            data: {
                action: 'pdl_get_checkout_woocommerce_areas',
                state_id: state_id,
            },
            success: function (response) {
                var areaDropdown = $('#billing_area');
                areaDropdown.empty(); // Clear existing options.

                // Add a default option.
                areaDropdown.append('<option value="">' + 'Select an area...' + '</option>');

                // Populate dropdown with areas from the response.
                if (response.success && response.data) {
                    $.each(response.data, function (key, value) {
                        areaDropdown.append('<option value="' + key + '">' + value + '</option>');
                    });
                }

                areaDropdown.trigger('change').select2({
                    placeholder: 'Select an area...',
                    allowClear: true,
                    width: '100%',
                });
            },
        });
    });

    // Trigger when the State (District) field changes for shipping.
    $('#shipping_state').on('change', function () {
        var state_id = $(this).val();

        // Perform AJAX request to fetch areas based on state ID.
        $.ajax({
            url: pdl_checkout_ajax_data.ajax_url,
            method: 'POST',
            data: {
                action: 'pdl_get_checkout_woocommerce_areas',
                state_id: state_id,
            },
            success: function (response) {
                var areaDropdown = $('#shipping_area');
                areaDropdown.empty(); // Clear existing options.

                areaDropdown.append('<option value="">' + 'Select an area...' + '</option>');

                if (response.success && response.data) {
                    $.each(response.data, function (key, value) {
                        areaDropdown.append('<option value="' + key + '">' + value + '</option>');
                    });
                }

                areaDropdown.trigger('change').select2({
                    placeholder: 'Select an area...',
                    allowClear: true,
                    width: '100%',
                });
            },
        });
    });

    // Handle billing and shipping areas dynamically.
    $('#ship-to-different-address-checkbox').on('change', function () {
        var isChecked = $(this).is(':checked');

        if (isChecked) {
            $('#shipping_area').closest('.form-row').show();
        } else {
            $('#shipping_area').closest('.form-row').hide();
            var billingArea = $('#billing_area').val();
            $('#shipping_area').val(billingArea).trigger('change');
        }
    }).trigger('change');

    // Validate Area fields before placing the order.
    $('form.checkout').on('submit', function (e) {
        var billingArea = $('#billing_area').val();
        var shippingArea = $('#shipping_area').val();
        var isDifferentShipping = $('#ship-to-different-address-checkbox').is(':checked');

        if (!billingArea || (isDifferentShipping && !shippingArea)) {
            e.preventDefault();

            alert(pdl_checkout_ajax_data.area_required_message);

            if (!billingArea) {
                $('#billing_area').next('.select2').find('.select2-selection').css({
                    border: '1px solid red',
                }).focus();
            }

            if (isDifferentShipping && !shippingArea) {
                $('#shipping_area').next('.select2').find('.select2-selection').css({
                    border: '1px solid red',
                }).focus();
            }
        }
    });
});
