var FileActions = {
    init: function(field) {
        jQuery('#checkAction_' + field).on('change', $.proxy(function() {
            var currentValue = jQuery('#checkAction_'  + this.field).val();

            jQuery('#FileAction' + this.field + 'Chooser .FileActionsContainer [name]').attr('disabled', 'disabled');
            jQuery('#FileAction' + this.field + 'Chooser .FileActionsContainer').hide();

            jQuery('#FileAction' + this.field + 'Chooser .FileActionContainer_' + currentValue + ' [name]').removeAttr('disabled');
            jQuery('#FileAction' + this.field + 'Chooser .FileActionContainer_' + currentValue).show();
        }, {field:field}));

        window.setTimeout(function() {
            jQuery('#checkAction_' + field).trigger('change');
        }, 200);

    }
};
