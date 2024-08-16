/**
 * Created by Stefan on 29.09.2016.
 */
jQuery(function() {
    jQuery('.InventorySelectorSelector').on('change', function(e) {
        var value = jQuery(this).val();

        if(value === null) {
            jQuery('.ConfigInventoryLoaderRow').hide();
        } else {
            jQuery('.ConfigInventoryLoaderRow').show();
        }

        jQuery('.InventarLoaderProvider').hide();

        jQuery.each(value, function(index, value) {
            jQuery('.InventarLoaderProvider#config_' + value).show();
        });

    });
});