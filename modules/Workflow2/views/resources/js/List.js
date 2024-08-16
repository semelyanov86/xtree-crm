
jQuery(function() {
    jQuery('.selectAllCheckboxes').on('click', function(e) {
        var table = jQuery(this).closest('table');

        jQuery('.selectRows', table).prop('checked', jQuery(this).prop('checked'));
    });
});