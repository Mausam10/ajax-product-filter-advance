jQuery(document).ready(function ($) {
    $('#apf-filter').on('submit', function (e) {
        e.preventDefault();

        const category = $('select[name="product_category"]').val();

        $.ajax({
            url: apf_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'apf_filter',
                category: category,
            },
            beforeSend: function () {
                $('#apf-results').html('<p>Loading...</p>');
            },
            success: function (response) {
                $('#apf-results').html(response);
            },
            error: function () {
                $('#apf-results').html('<p>Something went wrong. Please try again.</p>');
            },
        });
    });
});


jQuery(document).ready(function ($) {
    // AJAX Product Filter Form Submission
    $('#apf-filter').on('submit', function (e) {
        e.preventDefault();

        const category = $('select[name="product_category"]').val();

        $.ajax({
            url: apf_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'apf_filter',
                category: category,
            },
            beforeSend: function () {
                $('#apf-results').html('<p>Loading...</p>');
            },
            success: function (response) {
                $('#apf-results').html(response);
            },
            error: function () {
                $('#apf-results').html('<p>Something went wrong. Please try again.</p>');
            },
        });
    });

    // Preset Selection and Update Feedback
    $('.apf-preset-select').on('change', function () {
        const presetId = $(this).val();

        if (!presetId) {
            $('#apf-results').html('<p>Please select a valid preset.</p>');
            return;
        }

        $.ajax({
            url: apf_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'apf_load_preset',
                preset_id: presetId,
            },
            beforeSend: function () {
                $('#apf-status')
                    .removeClass('success error')
                    .addClass('info')
                    .text('Loading preset...')
                    .fadeIn();
            },
            success: function (response) {
                $('#apf-status')
                    .removeClass('info error')
                    .addClass('success')
                    .text('Preset applied successfully!')
                    .fadeOut(3000);

                $('#apf-results').html(response);
            },
            error: function () {
                $('#apf-status')
                    .removeClass('info success')
                    .addClass('error')
                    .text('Failed to apply preset. Please try again.')
                    .fadeOut(3000);
            },
        });
    });

    // Admin Preset Save and Delete Feedback
    $('#apf-save-preset, .apf-delete-preset').on('click', function () {
        $('#apf-status')
            .removeClass('success error')
            .addClass('info')
            .text('Processing...')
            .fadeIn();

        setTimeout(function () {
            $('#apf-status').fadeOut();
        }, 3000);
    });
});
