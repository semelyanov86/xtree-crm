/*
 * @copyright 2016-2017 Redoo Networks GmbH
 * @link https://redoo-networks.com/
 * This file is part of a vTigerCRM module, implemented by Redoo Networks GmbH and must not used without permission.
 */

(function($) {
    window.ValueList = function(field, container, moduleName) {
        var parentEle = $(container);
        var moduleName = moduleName;
        var ConfigField = field;
        var Fields = null;
        var FieldsHTML = "";
        var LANG = null;
        var rowCounter = 0;

        var RowHTML = $('#PlaceHolder_' + field).html();

        this.init = function(oldConfiguration) {
            $('#AddRowBtn_' + ConfigField).on('click', $.proxy(function() {
                var row = this.addValueRow();
            }, this));

            $.each(oldConfiguration, $.proxy(function(index, data) {
                var row = this.addValueRow();

                $('.Headline', row).val(data.label);
                $('.Mode', row).val(data.mode).trigger('change');

                $('input.ValueField, textarea.ValueField', row).val(data.value);
                $('select.ValueField', row).select2('val', data.value);

            }, this));
        };

        this.addValueRow = function() {
            var html = RowHTML;
            html = html.replace(/##SETID##/g, rowCounter);

            rowCounter++;

            var rowContainer = $('<div class="ValueListRow">' + html + '</div>')
            parentEle.append(rowContainer);

            $('.RemoveRow', rowContainer).on('click', function(e) {
                $(e.currentTarget).closest('.ValueListRow').remove();
            });

            $('.Mode', rowContainer).on('change', $.proxy(function(e) {
                var value = $(e.currentTarget).val();
                var rowContainer = $(e.currentTarget).closest('.ValueListRow');
                var prefix = $('.ValueContainer', rowContainer).data('prefix');

                var fieldName = prefix + '[value]';
                var fieldId = prefix.replace(/\[/, '_').replace(/\]/, '') + '_value';

                var currentValue = '';
                var placeholder = $('.ValueContainer', rowContainer).data('placeholder');

                switch(value) {
                    case 'field':
                        $('.ValueContainer', rowContainer).html(FieldsHTML.replace(/##FIELDNAME##/, fieldName).replace(/##FIELDID##/, fieldId));
                        break;
                    case 'value':
                        $('.ValueContainer', rowContainer).html(createTemplateTextfield(fieldName, fieldId, currentValue, {module: moduleName, refFields: true, class:'ValueField', placeholder:placeholder}));
                        break;
                    case 'function':
                        $('.ValueContainer', rowContainer).html(createTemplateTextarea(fieldName, fieldId, currentValue, {module: moduleName, refFields: true, mode:'expression', class:'ValueField'}));
                        break;
                    case 'column':
                        var html = '<input type="text" class="form-control" name="' + fieldName + '" id="' + fieldId + '" placeholder="' + LANG['define array key to choose'] + '" value="' + currentValue + '" />';
                        $('.ValueContainer', rowContainer).html(html);
                        break;
                }

                rowContainer.find('.MakeSelect2').each(function(index, ele) {
                    $(ele).removeClass('MakeSelect2').select2();
                });
            }, this)).trigger('change');

            rowContainer.find('.MakeSelect2').each(function(index, ele) {
                $(ele).removeClass('MakeSelect2').select2();
            });

            return rowContainer;
        };

        this.setFields = function(fields) {
            Fields = fields;

            FieldsHTML = '<select style="vertical-align:top;" class="MakeSelect2 form-control ValueField" name=\'##FIELDNAME##\' id=\'##FIELDID##\'>';
            FieldsHTML += '<option value="">' + LANG['LBL_CHOOSE'] + '</option>';

            $.each(Fields, function(blockLabel, fieldList) {
                FieldsHTML += '<optgroup label="' + blockLabel + '">';

                $.each(fieldList, function(index, field) {
                    FieldsHTML += '<option value="$' + field.name + '">' + field.label + '</option>';
                });

                FieldsHTML += '</optgroup>';
            });
            FieldsHTML += '</select>';
        };

        this.setLanguage = function(language) {
            LANG = language;
        }

    };
})(jQuery);