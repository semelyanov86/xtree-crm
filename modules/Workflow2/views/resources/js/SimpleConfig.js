jQuery(function() {

    jQuery('.SwitchSimpleConfigSwitch').on('click', function() {
        var ele = jQuery(this);

        var targetid = ele.data('targetid');
        var name = ele.data('name');

        var parentEle = jQuery('td.SCMode.'+targetid);

        var currentMode = parentEle.find('.SCModeSelector').val();
        var currentValue = parentEle.find('.SimpleConfigContainer[data-type="' + currentMode +'"] [name="' + name + '"]').val();

        if(currentMode == 'default') {
            parentEle.removeClass('SCMode_default').addClass('SCMode_custom');
            parentEle.find('.SCModeSelector').val('custom');

            var defaultEle = parentEle.find('.SimpleConfigDefaultContainer [name="' + name + '"]');
            parentEle.find('.SimpleConfigDefaultContainer [name="' + name + '"]').val(currentValue).prop('disabled', true);
            parentEle.find('.SimpleConfigCustomContainer [name="' + name + '"]').val(currentValue).prop('disabled', false);
            /*
            var value = jQuery('[name="' + name + '"]').val();
            createTemplateTextfield(name, targetid, value, {});
             */
        } else {
            parentEle.addClass('SCMode_default').removeClass('SCMode_custom');
            parentEle.find('.SCModeSelector').val('default');

            parentEle.find('.SimpleConfigDefaultContainer [name="' + name + '"]').val(currentValue).prop('disabled', false);
            parentEle.find('.SimpleConfigCustomContainer [name="' + name + '"]').val(currentValue).prop('disabled', true);

        }
    });

    jQuery('.SimpleConfigRepeatField').on('click', function(e) {
        var ele = jQuery(e.currentTarget);

        var parentFieldHtml = ele.closest('.SCFieldIntern').html();
        ele.closest('.SCFieldIntern').after('<div class="SCFieldIntern">' + parentFieldHtml + '</div>');
        //var td = ele.closest('td');

    });

    jQuery('.SCMode_custom').each(function(index, ele) {
        var name = jQuery(ele).data('name');
        jQuery(ele).find('.SimpleConfigDefaultContainer [name="' + name + '"]').prop('disabled', true);
    });
});