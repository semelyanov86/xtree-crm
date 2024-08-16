/**
 * Created by Stefan on 06.04.2016.
 */
var SWFormulaAssistant = null;
(function($){
    SWFormulaAssistant = function() {
        this.saveUrl = 'index.php?module=Workflow2&action=FormulaSave&parent=Settings';
        this.loadUrl = 'index.php?module=Workflow2&action=FormulaLoad&parent=Settings';
        this.parentEle = null;
        this.dfd = null;
        this.ShowSaveOnly = true;

        this.formulaId = 0;

        this.start = function(value) {
            this.dfd = jQuery.Deferred();

            this.show();

            if(typeof value != 'undefined' && value != '') {
                window.setTimeout($.proxy(function() {
                    var regex = /\$\[FORMULA,([0-9]+)\]/;
                    var match = regex.exec(value);

                    if(match && typeof match[1] != 'undefined') {
                        $.post(this.loadUrl, {formulaId:match[1]}, function(response) {
                            $('[name="formulaId"]', this.parentEle).val(response.id);
                            $('[name="formulaName"]', this.parentEle).val(response.name);
                            $('[name="formula"]', this.parentEle).val(response.formula).trigger('blur');


                            if(typeof response.variables != 'undefined') {
                                $.each(response.variables, $.proxy(function(index, value) {
                                    $('.FA_VarField[data-varname="' + index + '"]').val(value);
                                }, this));
                            }

                        }, 'json');
                    }
                }, this), 500);
            }


            return this.dfd.promise();
        };

        this.save = function() {
            var dfd = jQuery.Deferred();
            if($('[name="formula"]', this.parentEle).val() == '') {
                return false;
            }

            var data = {
                'formulaId'   : $('[name="formulaId"]', this.parentEle).val(),
                'formula'     : $('[name="formula"]', this.parentEle).val(),
                'formulaName' : $('[name="formulaName"]', this.parentEle).val(),
                'variables'   : {}
            };
            $('.FA_VarField', this.parentEle).each(function(index, ele) {
                data['variables'][$(ele).data('varname')] = $(ele).val();
            });

            $.post(this.saveUrl, data, $.proxy(function(response) {
                $('[name="formulaId"]', this.parentEle).val(response['id']);
                dfd.resolve();
            }, this), 'json');

            return dfd.promise();
        };

        this.hideSaveOnly = function() {
            this.ShowSaveOnly = false;
        };

        this.show = function() {
            var html = '<div class="modal-dialog modelContainer" style="width:600px;"><div class="FormulaAssistantContainer" style="width:600px;"><div class="FormulaAssistantInnerContainer">';
            html += '<input type="hidden" name="formulaId" value="' + this.formulaId + '" />';
            html += '<h3>' + app.vtranslate('Formula Assistant') + '</h3>';
            html += '<span style="display:inline-block;width:100px;">Formula:</span> <input type="text" name="formula" class="FA_Formula" value="" style="width:300px;font-size:16px;padding:7px 4px;" /><hr/>';
            html += '<strong>' + app.vtranslate('Variables you use:') + '</strong>';
            html += '<div class="FA_Variables"></div></div>';
            html += '<div class="FA_Footer"><input type="text" style="width:150px;" maxlength="46" name="formulaName" placeholder="' + app.vtranslate('Formula Name') + '" value="" /><input type="button" style="margin-left:5px;" name="save" class="btn btn-primary pull-right FormulaAssistantSaveAndInsert" value="Save & Insert Formula" /><input type="button" name="save" class="FormulaAssistantCloseBtn btn btn-warning pull-right" value="close" />' + (this.ShowSaveOnly?'<input type="button" name="save" class="btn FABtn_Save pull-right" value="Save Formula" />':'') + '</div>';
            html += '</div></div>';

            RedooUtils('Workflow2').showModalBox(html).then($.proxy(function() {
                this.parentEle = $('.FormulaAssistantContainer');

                $('.FormulaAssistantCloseBtn', this.parentEle).on('click', function() {
                    app.hideModalWindow();
                });
                $('.FormulaAssistantSaveAndInsert', this.parentEle).on('click', $.proxy(function(e) {
                    var returnVal = this.save();

                    if(returnVal !== false) {
                        returnVal.then($.proxy(function() {
                            if($('[name="formulaId"]', this.parentEle).val() != '' && $('[name="formulaId"]', this.parentEle).val() != '0') {

                                this.dfd.resolveWith({}, ['$[FORMULA,' + $('[name="formulaId"]', this.parentEle).val() + ']']);
                                app.hideModalWindow();
                            }
                        }, this));
                    }
                }, this));

                $('.FABtn_Save', this.parentEle).on('click', $.proxy(function() {
                    this.save();
                }, this));

                $('.FA_Formula', this.parentEle).on('blur', $.proxy(function() {
                    var value = $('.FA_Formula', this.parentEle).val().toUpperCase();
                    // Convert the form to all uppercase Variable Names
                    $('.FA_Formula', this.parentEle).val(value);

                    var checkRegex = /[a-zA-Z0-9]+/g;

                    var matches;
                    var variables = {};
                    do {
                        matches = checkRegex.exec(value);
                        if(matches && !jQuery.isNumeric(matches[0])) {
                            variables[matches[0]] = true;
                        }
                    } while(matches);

                    var html = '';
                    $.each(variables, $.proxy(function(index, value) {
                        if($('.FA_VarField[data-varname="' + index + '"]').length > 0) {
                            var currentValue = $('.FA_VarField[data-varname="' + index + '"]').val();
                        } else {
                            var currentValue = '';
                        }
                        var fieldName = 'var_' + index;

                        var options = {
                            'class'       : 'FA_VarField',
                            'width'       : '450px',
                            'dataAttr'    : {
                                'varname'   : index
                            }
                        };
                        html += '<div class="FA_VarContainer"><label for="' + fieldName + '">' + index + '</label><div class="FA_VarValueContainer">' + createTemplateTextfield(fieldName, fieldName, currentValue, options) + '</div></div>';
                    }, this));

                    $('.FA_Variables').html(html);

                }, this));
            }, this));

        }
    }
})(jQuery);
