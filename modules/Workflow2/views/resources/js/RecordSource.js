(function($) {
    function activateSource(id) {
        if(id == '') return;
        $('.SourceContainer:visible').slideUp('fast');
        $('.SourceContainer[data-source="' + id + '"]').slideDown('fast');

        if($('.SourceContainer[data-source="' + id + '"]').data('initiated') == null)
        {
            $('.SourceContainer[data-source="' + id + '"]').data('initiated', 1);
            Sources[id]('.SourceContainer[data-source="' + id + '"]');
        }
    }

    $('.RecordSourceChooser').on('change', function(e) {
        var target = $(e.currentTarget);
        activateSource(target.val());
    });

    $(function() {
        if(typeof CurrentSource != 'undefined') {
            activateSource(CurrentSource);
        }
    });
})(jQuery);

function addMiniFormModuleSelect() {
    if(jQuery('form#mainTaskForm [name="__vtrftk"]').length > 0) {
        jQuery('body').append('<form method="POST" name="hidden_search_form" action="#"><input type="hidden" name="__vtrftk" value="' + jQuery('form#mainTaskForm [name="__vtrftk"]').val() + '"><input type="hidden" name="task[moduleselect][search_module]" id="search_module_hidden" value=""></form>');
    } else {
        jQuery('body').append('<form method="POST" name="hidden_search_form" action="#"><input type="hidden" name="task[moduleselect][search_module]" id="search_module_hidden" value=""></form>');
    }

}
