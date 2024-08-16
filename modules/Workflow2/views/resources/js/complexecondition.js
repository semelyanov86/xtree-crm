/**
 * Complexe Condition Component
 *
 * Version 2.3
 *
 * Changelog
 * 2.3 - Rework for Standalone task
 *
 * Handling
 * Require RedooUtils with same Scope
 * Require set of ConditionScopeModule to current Module
 * Require jquery.form.min.js inside views/resources of ConditionScopeModule
 * Condition Popup needs "ComplexeCondition" action in ConditionScopeModule/action
 * Condition Popup needs "ComplexeCondition" view in ConditionScopeModule/views
 * Condition Popup needs "ConditionPopup.tpl" Template in ConditionScopeModule
 *
 * Usage
 */

Array.prototype.last = function () {
    return this[this.length - 1];
};
if (typeof jQuery.assocArraySize == 'undefined') {
    jQuery.assocArraySize = function (obj) {
        // http://stackoverflow.com/a/6700/11236
        var size = 0, key;
        for (key in obj) {
            if (obj.hasOwnProperty(key)) size++;
        }
        return size;
    };
}

(function($) {
    var ConditionScopeModule = 'Workflow2';

    window.ComplexeCondition = function(parentEle, fieldname) {
        this.initialized = false;
        this.conditionOperators = {};
        this.operationOptions = {
            "owner": [],
            "picklist": [],
            "multipicklist": [],
            "boolean": [],
            "number": [],
            "date": [],
            "text": [],
            "crmid": []
        };
        this.hasChanged = false;
        this.moduleFields = {};
        this.availCurrency = [];
        this.available_users = [];
        this.parentEle = jQuery(parentEle);

        this.condition = {};
        this.fieldData = {};
        this.enableTemplateFields = true;
        this.haveToLoadRefModules = 0;
        this.tmpJoinStack = [];
        this.groupData = {};

        this.environmentVariables = [];
        this.currentRecordId = '';
        this.currentRecordName = '';
        this.currentRecordIndex = -1;
        this.DisabledConditionMode = false;

        if(typeof fieldname !== 'undefined') {
            this.field = fieldname;
        } else {
            this.field = 'condition';
        }

        this.columnsRewrites = {
            "assigned_user_id": "smownerid"
        };

        this.prevType = false;
        this.prevGroupRecordName = false;
        this.currentParentGroupId = '';

        this.incrementalStage = 0;
        this.backgroundColors = ['f9f9f9', 'e7f4fe', 'd9d9d9', 'e7f4fe', 'b9b9b9', 'd9d9d9', 'e9e9e9', 'f9f9f9'];

        this.initValues = [];
        this.currentInitConfig = {};

        /**
         Should the join selects created visibly
         */
        this.createJoinsVisible = false;
        this.preSelectJoin = 'and';

        this.mainCheckModule = null; // Which module sould be checked
        this.mainSourceModule = null; // From which module the variables should taken
        this.imagePath = '';

        this.setEnabledTemplateFields = function(status) {
            this.enableTemplateFields = (status == true);
        };
        this.disableConditionMode = function() {
            this.DisabledConditionMode = true;
        };

        this.setImagePath = function (path) {
            this.imagePath = path;
        };
        this.setMainCheckModule = function (moduleName) {
            this.mainCheckModule = moduleName;
        };
        this.setMainSourceModule = function (moduleName) {
            this.mainSourceModule = moduleName;
        };
        this.enableHasChange = function (value) {
            this.hasChanged = value;
        };

        this.setAvailableCurrencies = function (currency) {
            this.availCurrency = currency;
        };

        this.setAvailableUser = function (user) {
            this.available_users = user;
        };

        this._InitSelect2 = function (tmpIndex) {
            var elements = jQuery('.MakeSelect2', this.parentEle);
            elements.removeClass('MakeSelect2');
            elements.each(function(index, ele) {
                jQuery(ele).select2({
                    closeOnSelect: jQuery(ele).data('closeonselect') == '0' ? false : true
                });
            });

        };
        this.init = function () {
            var d = document.documentElement.style
            if (('flexWrap' in d) || ('WebkitFlexWrap' in d) || ('msFlexWrap' in d)) {
                jQuery(this.parentEle).addClass('supportFlexbox')
            }

            if (typeof MOD == 'undefined') {

            }

            this.loadFields(jQuery.proxy(this._AfterLoadFields, this));
        };
        this._AfterLoadFields = function () {
            this.parentEle.hide();

            this.currentParentGroupId = 'root';
            this.initValues = [];

            var html = '<div class="ConditionControlPanel"><strong>Options:&nbsp;&nbsp;</strong>';
            html += '<label><input type="checkbox" class="ConditionJoinCollapse" checked="checked" /> Collapse logical operators</label>';
            html += '</div>';

            this.parentEle.html(html);

            var data = this.addGroup(this.condition);
            this.parentEle.append(data.html);

            jQuery('.ConditionJoinCollapse', this.parentEle).on('change', jQuery.proxy(function (e) {
                if (jQuery(e.target).prop('checked') == true) {
                    jQuery(this.parentEle).addClass('ConditionCollapseJoins').removeClass('ConditionExpandJoins');
                } else {
                    jQuery(this.parentEle).removeClass('ConditionCollapseJoins').addClass('ConditionExpandJoins');
                }
            }, this)).trigger('change');

            this.initGroupEvents(this.parentEle);
            this.initConditionEvents(this.parentEle);

            //return;
            jQuery.each(this.initValues, jQuery.proxy(function (index, value) {
                this.currentInitConfig = {};

                var condition = value.condition;
                var conditionRecord = jQuery('.ConditionalRecord[data-recordindex="' + value.recordIndex + '"][data-recordid="' + value.recordId + '"][data-recordname="' + value.recordName + '"]', this.parentEle);

                if (condition.rawvalue !== null) {
                    if (typeof condition.rawvalue == 'string') {
                        condition.rawvalue = {'value': condition.rawvalue};
                    }
                    this.currentInitConfig = condition.rawvalue;
                } else {
                    this.currentInitConfig = {};
                }
                if (condition.operation.indexOf('/') == -1) {
                    if (condition.operation == 'bigger') {
                        condition.operation = 'after';
                    }
                    if (condition.operation == 'lower') {
                        condition.operation = 'before';
                    }

                    condition.operation = 'core/' + condition.operation;
                }

                jQuery('.ConditionNot', conditionRecord).val(condition.not);
                jQuery('.ConditionMode', conditionRecord).val(condition.mode);

                jQuery('.ConditionField', conditionRecord).val(condition.field).trigger('change');
                jQuery('.ConditionOperation', conditionRecord).val(condition.operation).trigger('change');

                this.currentInitConfig = {};
            }, this));

            this.currentInitConfig = {};

            jQuery('.ConditionalGroup', this.parentEle).each(function (index, value) {
                if(jQuery('> .CondContainer > .ConditionalJoin select', value).length > 0) {
                    jQuery('> .btn-toolbar .ConditionalMasterJoin select', value).val(jQuery(jQuery('> .CondContainer > .ConditionalJoin select', value)[0]).val());
                } else {
                    jQuery('> .btn-toolbar .ConditionalMasterJoin select', value).val('and');
                }
                
            });

            this.parentEle.fadeIn('fast', jQuery.proxy(function () {
                this._InitSelect2();
            }, this));

            this.initialized = true;

            if(this.enableTemplateFields) {
                InitAutocompleteText();
            }
        };

        this.BtnClickRemoveGroup = function (e) {
            var target = jQuery(e.target);
            var conditionRecordEle = jQuery(target).closest('.ConditionalGroup');

            var recordIndex = conditionRecordEle.data('recordindex');
            jQuery('.ConditionalJoin#conditional_join_' + recordIndex).remove();

            conditionRecordEle.slideUp('fast', function () {
                jQuery(this).remove();
            })
        };

        this.ChangeMasterJoin = function (e) {
            var target = jQuery(e.target);
            var conditionRecordEle = jQuery(target).closest('.ConditionalGroup');

            jQuery('> .CondContainer > .ConditionalJoin select', conditionRecordEle).val(target.val());
        };
        this.ChangeJoin = function (e) {
            var target = jQuery(e.target);
            var conditionRecordEle = jQuery(target).closest('.ConditionalGroup');

            conditionRecordEle.children('.CondContainer').children('.ConditionalJoin').find('select').val(target.val());
            jQuery('> .btn-toolbar .ConditionalMasterJoin select', conditionRecordEle).val(target.val());
        };

        this.addConditionBtnClick = function (e) {
            var target = jQuery(e.target);
            var conditionRecordEle = jQuery(target).closest('.ConditionalGroup');

            var recordId = conditionRecordEle.data('recordid');
            var recordIndex = conditionRecordEle.data('recordindex');
            var recordName = conditionRecordEle.data('recordname');

            var backupValues = this._backupRecordValues();

            this.currentRecordId = recordId;
            this.currentRecordName = recordName;

            var tmpHtml = this.addRow();

            this._restoreRecordValues(backupValues);

            this.groupData[recordId].content = true;

            if (conditionRecordEle.children('.CondContainer').children('.ConditionalJoin').length > 0) {
                var predefinedJoinOperator = jQuery(conditionRecordEle.children('.CondContainer').children('.ConditionalJoin')[0]).find('select')[0].value;

                jQuery(conditionRecordEle.children('.CondContainer').children('.ConditionalJoin').show().find('select')).val(predefinedJoinOperator);
            }

            conditionRecordEle.children('.CondContainer').append(tmpHtml.html);


            //jQuery('.NewConditionalRecord').css('height', '0px');
            jQuery('.NewConditionalRecord').css('display', 'flex');
            //jQuery('.NewConditionalRecord').slideDown('fast');
            jQuery('.NewConditionalRecord').removeClass('.NewConditionalRecord');

            this.initConditionEvents(jQuery('.ConditionalRecord[data-recordid="' + tmpHtml.recordId + '"][data-recordindex="' + tmpHtml.recordIndex + '"]', conditionRecordEle));

            jQuery('.ConditionalJoin', conditionRecordEle.children('.CondContainer')).off('change').on('change', jQuery.proxy(this.ChangeJoin, this));

            this._InitSelect2();
        };

        this.initGroupEvents = function (ele) {
            jQuery('.addGroupBtn', ele).on('click', jQuery.proxy(this.BtnClickAddGroup, this))
            jQuery('.addConditionBtn', ele).on('click', jQuery.proxy(this.addConditionBtnClick, this));

            jQuery('.ConditionalMasterJoin', ele).on('change', jQuery.proxy(this.ChangeMasterJoin, this));
            jQuery('.BtnGroupRemove', ele).on('click', jQuery.proxy(this.BtnClickRemoveGroup, this));

            jQuery('.ConditionalJoin').off('change').on('change', jQuery.proxy(this.ChangeJoin, this));
        };

        this.initConditionEvents = function (ele) {
            jQuery('.ConditionField', ele).on('change', jQuery.proxy(this.updateSelectedField, this));
            jQuery('.ConditionOperation', ele).on('change', jQuery.proxy(this.updateOperationField, this));
            jQuery('.ConditionMode', ele).on('change', jQuery.proxy(this.updateOperationField, this));

            jQuery('.ConditionRemove', ele).on('click', jQuery.proxy(this.removeCondition, this));
            jQuery('.ConditionalJoin').off('change').on('change', jQuery.proxy(this.ChangeJoin, this));

            jQuery('.ConditionalRecord', ele).on('mouseover', jQuery.proxy(function (e) {
                // Hover the backgorund Color
                var target = e.target;
                if (!jQuery(target).hasClass('ConditionalRecord')) {
                    target = jQuery(target).closest('.ConditionalRecord');
                }

                jQuery('.ConditionalRecordHover', this.parentEle).removeClass('ConditionalRecordHover');
                jQuery(target).addClass('ConditionalRecordHover');
            }, this));

            jQuery('.ConditionalGroup', ele).on('mouseover', jQuery.proxy(function (e) {
                var target = e.target;
                if (!jQuery(target).hasClass('ConditionalGroup')) {
                    target = jQuery(target).closest('.ConditionalGroup');
                }

                jQuery('.ConditionalGroupHover', this.parentEle).removeClass('ConditionalGroupHover');
                jQuery(target).addClass('ConditionalGroupHover');
            }, this));

            jQuery('.ConditionField', ele).trigger('change')
        };

        this.removeCondition = function (e) {
            var target = jQuery(e.target);
            var conditionRecordEle = jQuery(target).closest('.ConditionalRecord');
            var recordIndex = conditionRecordEle.data('recordindex');
            jQuery('.ConditionalJoin#conditional_join_' + recordIndex).remove();

            conditionRecordEle.slideUp('fast', function () {
                jQuery(this).remove();
            })
        };

        this._backupRecordValues = function () {
            var ValueBackup = {};

            ValueBackup['currentRecordId'] = this.currentRecordId;
            //ValueBackup['currentRecordIndex'] = this.currentRecordIndex;
            ValueBackup['currentRecordName'] = this.currentRecordName;

            return ValueBackup;
        };
        this._restoreRecordValues = function (ValueBackup) {
            this.currentRecordId = ValueBackup['currentRecordId'];
            //this.currentRecordIndex = ValueBackup['currentRecordIndex'];
            this.currentRecordName = ValueBackup['currentRecordName'];
        };

        this.BtnClickAddGroup = function (e) {
            var target = jQuery(e.target);
            var conditionRecordEle = jQuery(target).closest('.ConditionalGroup');

            var recordId = conditionRecordEle.data('recordid');
            var recordIndex = conditionRecordEle.data('recordindex');
            var recordName = conditionRecordEle.data('recordname');

            var backupValues = this._backupRecordValues();

            this.currentRecordId = recordIndex + "_g" + this.currentRecordIndex;
            this.currentRecordName = recordName + "[g" + this.currentRecordIndex + "]";

            this.prevGroupRecordName = "g" + this.currentRecordIndex;
            this.currentParentGroupId = 'g' + recordIndex;

            this.incrementalStage = this.groupData[recordId].incrementalStage + 1;

            var tmpHtml = this.addGroup();

            this._restoreRecordValues(backupValues);

            this.groupData[recordId].content = true;

            if (conditionRecordEle.children('.CondContainer').children('.ConditionalJoin').length > 0) {
                var predefinedJoinOperator = jQuery(conditionRecordEle.children('.CondContainer').children('.ConditionalJoin')[0]).find('select')[0].value;

                jQuery(conditionRecordEle.children('.CondContainer').children('.ConditionalJoin').show().find('select')).val(predefinedJoinOperator);
            }

            conditionRecordEle.children('.CondContainer').append(tmpHtml.html);

            jQuery('.NewConditionalGroup').slideDown('fast').removeClass('.NewConditionalGroup');

            this.initGroupEvents(jQuery('.ConditionalGroup[data-recordid="' + tmpHtml.recordId + '"][data-recordindex="' + tmpHtml.recordIndex + '"]', conditionRecordEle));
            this._InitSelect2();

        };

        this.updateOperationField = function (e) {
            var target = jQuery(e.target);
            var conditionRecordEle = jQuery(target).closest('.ConditionalRecord');

            var selOperator = jQuery('.ConditionOperation', conditionRecordEle).val();
            var selField = this.getSelectedField(conditionRecordEle);
            var selMode = jQuery('.ConditionMode', conditionRecordEle).val();

            var recordId = conditionRecordEle.data('recordid');
            var recordIndex = conditionRecordEle.data('recordindex');
            var recordName = conditionRecordEle.data('recordname');

            if (selOperator.indexOf('/') == -1 && selOperator != '') {
                if (selOperator == 'bigger') {
                    selOperator = 'after';
                }
                if (selOperator == 'lower') {
                    selOperator = 'before';
                }

                operator = 'core/' + selOperator;
            }

            if (typeof this.fieldData[selField] != 'undefined') {
                fieldTypeName = this.fieldData[selField].type["name"];
            } else {
                fieldTypeName = "text";
            }

            var BaseFieldId = "records_" + recordId + "_" + recordIndex + "_rawvalue";
            var BaseFieldName = "task[" + this.field + "]" + recordName + "[" + recordIndex + "][rawvalue]";

            if (this.initialized === false) {
                var currentValue = this.currentInitConfig;
            } else {
                var currentValue = {};
            }

            jQuery('.ConditionValue', conditionRecordEle).each(function (index, value) {
                var ConfigIndex = jQuery(value).data('name');
                if (ConfigIndex === undefined || typeof currentValue[ConfigIndex] != 'undefined') return;

                currentValue[ConfigIndex] = jQuery(value).val();
            });

            var html = '';

            if (selMode == 'function') {
                if (typeof currentValue == 'object') {
                    if (typeof currentValue.value == 'undefined' || currentValue.value.indexOf('return') == -1) {
                        currentValueString = '$value = array();' + "\n";
                        jQuery.each(currentValue, function (index, value) {
                            currentValueString += "$value['" + index + "'] = '" + value + "';\n";
                        });
                        currentValueString += 'return $value;';
                        currentValue = currentValueString;
                    } else {
                        currentValue = currentValue.value;
                    }
                }
                var lines = (currentValue.match(/\n/g) || []).length;
                if (lines < 4) {
                    lines = 4;
                }
                if (lines > 15) {
                    lines = 15;
                }
                html += "<span class='customFunctionSpan'><textarea class='customFunction' style='width:100%;height:" + ((lines + 1) * 19) + "px;' name='" + BaseFieldName + "' id='" + BaseFieldId + "'>" + currentValue + "</textarea></span>";
                html += "<label id='" + recordId + "_iconspan' style='display:inline-block;'><img src='modules/" + ConditionScopeModule + "/icons/templatefieldPHP.png' style='margin-bottom:-2px;cursor:pointer;' onclick=\"insertTemplateField('" + BaseFieldId + "', '[source]->[module]->[destination]', false)\"></label>";
            } else if (selMode == 'value') {
                jQuery.each(this.conditionOperators[selOperator].config, jQuery.proxy(function (ConfigIndex, Config) {
                    var fieldName = BaseFieldName + '[' + ConfigIndex + ']';
                    var fieldId = BaseFieldId + '_' + ConfigIndex;

                    if (typeof currentValue[ConfigIndex] == 'undefined') currentValue[ConfigIndex] = '';
                    if (typeof Config.label != 'undefined') html += '<label class="condLabel">' + Config.label + '</label>';

                    html += '<span class="' + (typeof Config.length != 'undefined' ? Config.length : '') + '" style="' + (typeof Config.width != 'undefined'?'width:' + Config.width:'') + '">';

                    switch (Config.type) {
                        // 'Default' means default file dependent input type
                        case 'default':
                            switch (fieldTypeName) {
                                case "multipicklist":
                                case "picklist":
                                case "owner":
                                    var ExtraAttributes = '';
                                    var MultipleMode = false;
                                    if(typeof Config.multiple != 'undefined' && Config.multiple == true) {
                                        ExtraAttributes = 'multiple="multiple" data-closeonselect="0"';
                                        fieldName += '[]';
                                        MultipleMode = true;
                                    }

                                    html += "<select class='ConditionValue MakeSelect2' data-name='" + ConfigIndex + "' name='" + fieldName + "' id='" + fieldId + "' " + ExtraAttributes + ">";
                                    html += "<option value=''>&nbsp;&nbsp;&nbsp;&nbsp;&raquo;&nbsp;&nbsp;" + MOD.LBL_EMPTY_VALUE + "</option>";

                                    html += "<optgroup label='" + MOD.LBL_VALUES + "'>";

                                    jQuery.each(this.fieldData[selField].type.picklistValues, function (index, value) {
                                        if(MultipleMode === false) {
                                            var selected = currentValue[ConfigIndex] == index ? "selected='selected'" : "";
                                        } else {
                                            var selected = $.inArray(index, currentValue[ConfigIndex]) !== -1 ? "selected='selected'" : "";
                                        }
                                        html += "<option value='" + index + "' " + selected + ">" + value + "</option>";
                                    });

                                    html += "</optgroup>";

                                    html += "</select>";
                                    break;
                                case "boolean":
                                    //jQuery('.ConditionMode', conditionRecordEle).attr("disabled", "disabled");
                                    html += '<span id="' + fieldId + '"></span>';
                                    break;
                                case "date":
                                case "datetime":
                                    html += createTemplateDatefield(
                                        fieldName,
                                        fieldId,
                                        currentValue[ConfigIndex],
                                        {
                                            "showTime": fieldTypeName == "datetime",
                                            "format": '%Y-%m-%d',
                                            dataAttr: {
                                                'name': ConfigIndex
                                            },
                                            'class': 'ConditionValue'
                                        }
                                    );

                                    break;
                                case "reference":
                                    var referTo = this.fieldData[selField].type.refersTo[0];
                                    if (referTo == "Currency" && typeof this.availCurrency !== "undefined") {
                                        html += "<select class='condition_value select' name='" + fieldName + "' id='" + fieldId + "'>";

                                        for (var a = 0; a < this.availCurrency.length; a++) {
                                            html += "<option " + (currentValue == this.availCurrency[a].curid ? "selected='selected'" : "") + " value='" + this.availCurrency[a].curid + "'>" + this.availCurrency[a].currencylabel + "</option>";
                                        }

                                        html += "</select>";
                                    } else {
                                        html += createTemplateTextfield(
                                            fieldName,
                                            fieldId,
                                            currentValue[ConfigIndex],
                                            {
                                                refFields: true,
                                                module: this.mainSourceModule,
                                                dataAttr: {
                                                    'name': ConfigIndex
                                                },
                                                'class': 'ConditionValue',
                                                'variables': this.enableTemplateFields
                                            }
                                        );
                                    }
                                    break;
                                default:
                                    if (typeof Config['default'] != 'undefined' && currentValue[ConfigIndex] == '' && this.initialized == true) {
                                        currentValue[ConfigIndex] = Config['default'];
                                    }

                                    html += createTemplateTextfield(
                                        fieldName,
                                        fieldId,
                                        currentValue[ConfigIndex],
                                        {
                                            refFields: true,
                                            module: this.mainSourceModule,
                                            dataAttr: {
                                                'name': ConfigIndex
                                            },
                                            'class': 'ConditionValue',
                                            'variables': this.enableTemplateFields
                                        }
                                    );

                                    break;

                            }
                            // End 'default' field type
                            break;
                        case "multipicklist":
                        case 'picklist':
                            html += "<select class='MakeSelect2 ConditionValue' " + (Config.type == 'multipicklist' ? 'multiple="multiple"':'') + " data-name='" + ConfigIndex + "' name='" + fieldName + "" + (Config.type == 'multipicklist' ? '[]':'') + "' id='" + fieldId + "'>";

                            jQuery.each(Config.options, function (index, value) {
                                html += "<option " + (currentValue[ConfigIndex] == index || (typeof currentValue[ConfigIndex] == 'object' && currentValue[ConfigIndex].indexOf(index) != -1)? "selected='selected'" : "") + " value='" + index + "'>" + value + "</option>";
                            });

                            html += "</select>";
                            break;
                        case 'productid':
                            html += createProductChooser(
                                fieldName,
                                fieldId,
                                currentValue[ConfigIndex],
                                {
                                    'class': 'ConditionValue'
                                }
                            );

                            break;
                        case 'text':
                        case 'textfield':
                        default:
                            html += createTemplateTextfield(
                                fieldName,
                                fieldId,
                                currentValue[ConfigIndex],
                                {
                                    refFields: true,
                                    module: this.mainSourceModule,
                                    dataAttr: {
                                        'name': ConfigIndex
                                    },
                                    'class': 'ConditionValue',
                                    'variables': this.enableTemplateFields
                                }
                            );

                            break;
                    }

                    /*if(typeof Config.description != 'undefined') {
                     html += '<br/><span class="ConditionConfigValueHint">' + Config.description + '</span>';
                     }*/

                    html += '</span>';
                }, this));

                jQuery(conditionRecordEle).attr('data-columns', jQuery.assocArraySize(this.conditionOperators[selOperator].config));
            }

            jQuery('.ConditionContainer', conditionRecordEle).html(html);
            this._InitSelect2();
            jQuery('body').trigger('InitComponents');
        };

        this.updateSelectedField = function (e) {

            var target = jQuery(e.target);
            var conditionRecordEle = jQuery(target).closest('.ConditionalRecord');
            var recordId = conditionRecordEle.data('recordid');
            var recordIndex = conditionRecordEle.data('recordindex');
            var recordName = conditionRecordEle.data('recordname');

            this.reloadOperations(conditionRecordEle);
        };

        this.getSelectedField = function (ele) {
            return jQuery('select.ConditionField', ele).val();
        };

        this.reloadOperations = function (ele) {
            var selOperation = jQuery('select.ConditionOperation', ele).val();
            var selField = this.getSelectedField(ele);

            if (this.fieldData[selField] !== undefined) {
                fieldTypeName = this.fieldData[selField].type["name"];
            } else {
                fieldTypeName = "text";
            }

            switch (fieldTypeName) {
                case "double":
                case "integer":
                case "currency":
                    recordOperationOptions = this.operationOptions["number"];
                    break;
                case "date":
                case "datetime":
                    recordOperationOptions = this.operationOptions["date"];
                    break;
                case "multipicklist":
                case "crmid":
                case "picklist":
                case "owner":
                case "boolean":
                    recordOperationOptions = this.operationOptions[fieldTypeName];
                    break;
                default:
                    recordOperationOptions = this.operationOptions["text"];
                    break;
            }

            var operationHtml;
            for (var i = 0; i < recordOperationOptions.length; i++) {
                if (typeof this.conditionOperators[recordOperationOptions[i]] != 'undefined') {
                    title = this.conditionOperators[recordOperationOptions[i]].label;
                } else {
                    title = recordOperationOptions[i];
                }
                if (typeof MOD[title] != 'undefined') {
                    title = MOD[title];
                }

                operationHtml += "<option value='" + recordOperationOptions[i] + "' " + (selOperation != undefined && selOperation == recordOperationOptions[i] ? 'selected="selected"' : '') + ">" + title + "</option>";
            }

            jQuery('.ConditionOperation', ele).html(operationHtml).trigger('change');
        };

        this.loadFields = function (callback) {
            this.fieldData = {};

            jQuery.each(this.moduleFields, jQuery.proxy(function (index, value) {
                if(!value) return;
                
                jQuery.each(value, jQuery.proxy(function (fieldIndex, fieldValue) {
                    if (typeof this.columnsRewrites[fieldValue.name] != 'undefined') {
                        fieldValue.name = this.columnsRewrites[fieldValue.name];
                    }
                    if (typeof fieldValue.type == 'string') {
                        fieldValue.type = {'name': fieldValue.type};
                    }
                    if (fieldValue.name == 'crmid' || fieldValue.name.indexOf(' crmid)') !== -1) {
                        fieldValue.type = {'name': 'crmid'};
                    }

                    this.fieldData[fieldValue.name] = fieldValue;

                }, this));

            }, this));

            if(typeof this.fieldData["smownerid"] != 'undefined') {
                this.fieldData["smownerid"]["type"]["picklistValues"] = {};

                this.fieldData["smownerid"]["type"]["name"] = "owner";
                this.fieldData["smownerid"]["type"]["picklistValues"] = {};

                this.fieldData["smownerid"]["type"]["picklistValues"]['$current_user_id'] = '$currentUser';
            }

            jQuery.each(this.environmentVariables, jQuery.proxy(function (index, value) {
                this.environmentVariables[index] = value = value.replace(/&quot;/g, '"');
                this.fieldData[value] = {
                    field: value,
                    name: value,
                    label: value,
                    uitype: 1,
                    typeofdata: "V~O",
                    type: {'name': 'string'}
                };
            }, this));

            if(typeof this.fieldData["smownerid"] != 'undefined') {
                if (this.available_users !== null) {
                    var picklistValues = {};
                    jQuery.each(this.available_users["user"], function (value, label) {
                        picklistValues[value] = label;
                    });

                    jQuery.each(this.available_users["group"], function (value, label) {
                        picklistValues[value] = "Group: " + label;
                    });


                    this.fieldData["smownerid"]["type"]["picklistValues"] = picklistValues;
                }
            }

            jQuery.each(this.fieldData, jQuery.proxy(function(index, field) {
                if(field.type.name == 'owner' && this.available_users !== null) {
                    var picklistValues = {};
                    jQuery.each(this.available_users["user"], function (value, label) {
                        picklistValues[value] = label;
                    });

                    jQuery.each(this.available_users["group"], function (value, label) {
                        picklistValues[value] = "Group: " + label;
                    });


                    this.fieldData[index]["type"]["picklistValues"] = picklistValues;
                }
            }, this));


            this.fieldData["DEFAULTFIELD"] = {
                type: {
                    name: 'text'
                }
            };

            callback();
        };
        this.setCondition = function (value) {
            this.condition = value;
        };

        this.setConditionOperators = function (operators) {
            this.conditionOperators = operators;


            jQuery.each(operators, jQuery.proxy(function (OperatorIndex, OperatorConfig) {
                /*if(OperatorIndex == 'core/has_changed' && this.hasChanged != true) {
                 return;
                 }*/

                if (OperatorConfig.fieldtypes == 'all') {
                    jQuery.each(this.operationOptions, jQuery.proxy(function (index, value) {
                        this.operationOptions[index].push(OperatorIndex);
                    }, this));
                } else {
                    jQuery.each(OperatorConfig.fieldtypes, jQuery.proxy(function (index, value) {
                        if (typeof this.operationOptions[value] != 'undefined') {
                            this.operationOptions[value].push(OperatorIndex);
                        }
                    }, this));
                }

            }, this));
            this.operationOptions["crmid"] = jQuery.merge(jQuery.extend([], this.operationOptions["number"]), jQuery.extend([], this.operationOptions["crmid"]));
            this.operationOptions['crmid'] = jQuery.grep(this.operationOptions['crmid'], jQuery.proxy(function (itm, i) {
                return i == this.operationOptions['crmid'].indexOf(itm);
            }, this));

            this.operationOptions["owner"] = jQuery.merge(jQuery.extend([], this.operationOptions["picklist"]), jQuery.extend([], this.operationOptions["owner"]));
            this.operationOptions['owner'] = jQuery.grep(this.operationOptions['owner'], jQuery.proxy(function (itm, i) {
                return i == this.operationOptions['owner'].indexOf(itm);
            }, this));
        };

        this.setModuleFields = function (fields) {
            var blocks = {};

            jQuery.each(fields, function(index, value) {
                if(value && value !== null) {
                    blocks[index] = value;
                }
            });

            this.moduleFields = blocks;
        };
        this.setEnvironmentVariables = function (value) {
            this.environmentVariables = value;
        };

        this.addGroup = function (existingGroupValue, parentEle) {
            var html = "";

            this.groupData[this.currentRecordId] = {'content': false, 'incrementalStage': this.incrementalStage};

            var currentGroupIndex = this.currentRecordIndex;
            var GroupCreateJoinsVisible = this.createJoinsVisible;
            var oldParentGroupId = this.currentParentGroupId;

            html += "<div class='ConditionalGroup " + (this.initialized === true ? 'NewConditionalGroup' : '') + "' data-parentgroup='" + this.currentParentGroupId + "' style='background-color:#" + this.backgroundColors[this.incrementalStage] + "' data-recordindex='" + this.currentRecordIndex + "' data-recordid='" + this.currentRecordId + "' data-recordname='" + this.currentRecordName + "'><div class='CondContainer' >";

            var lastGroupJoinName = this.prevGroupRecordName;

            if (this.prevGroupRecordName !== false) {
                this.currentParentGroupId = this.prevGroupRecordName;
            }

            this.currentRecordIndex++;

            if (typeof existingGroupValue != 'undefined') {
                html += this._ParseGroupItems(existingGroupValue);
            }

            this.createJoinsVisible = GroupCreateJoinsVisible;

            html += "</div>";
            html += "<div class='btn-toolbar' style='margin: 5px 0 0 0;'>";
            html += "<div class='btn-group conditionFooterToolbar'>";
            html += "<button type='button' class='btn btn-info addGroupBtn'><i class='icon-folder-open icon-white'></i>&nbsp;&nbsp;" + MOD["LBL_ADD_GROUP"] + "</button>";
            html += "<button type='button' class='btn btn-primary  addConditionBtn'><i class='icon-plus-sign icon-white'></i>&nbsp;&nbsp;" + MOD["LBL_ADD_CONDITION"] + "</button>";

            html += this.addJoinSelect(currentGroupIndex, 'master');
            html += "</div>";

            if (this.incrementalStage != 0) {
                html += "<div class='btn-group pull-right'><button type='button' class='btn btn-danger BtnGroupRemove'><i class='icon-remove icon-white'></i>&nbsp;&nbsp;" + MOD["LBL_REMOVE_GROUP"] + "</button></div>";
            }
            html += "</div>";
            html += "</div>";

            html += this.addJoinSelect(currentGroupIndex, lastGroupJoinName);

            var returnValue = {
                'html': html,
                'recordIndex': currentGroupIndex,
                'recordId': this.currentRecordId,
                'recordName': this.currentRecordName
            };

            this.currentParentGroupId = oldParentGroupId;

            return returnValue;
        };
        this.addJoinSelect = function (currentRecordIndex, joinName) {
            if (joinName === false) return "";

            var selectedJoin = this.tmpJoinStack.last();

            var html = "<div class='ConditionalJoin ConditionalSubJoin " + (joinName == 'master' ? 'ConditionalMasterJoin' : '') + "' style='display:" + (this.createJoinsVisible ? "inline" : "none") + ";' id='conditional_join_" + currentRecordIndex + "'>";

            if (joinName == 'master') {
                html += "<select class='joinSelector'>";
            } else {
                html += "<select class='joinSelector' name='join[" + joinName + "]'>";
            }

            html += "<option value='and' " + (selectedJoin == "and" ? "selected='selected'" : "") + ">" + MOD["LBL_AND"] + "</option>";
            html += "<option value='or' " + (selectedJoin == "or" ? "selected='selected'" : "") + ">" + MOD["LBL_OR"] + "</option>";

            html += '</select>';
            html += '</div>';

            return html;
        };

        this._ParseGroupItems = function (conditionItems) {
            var html = "";
            var counter = 0;
            var doneJoin = false;

            for (var i in conditionItems) {
                var condition = conditionItems[i];
                this.preSelectJoin = condition.join;
                if (doneJoin === false) {
                    this.tmpJoinStack.push(condition.join);
                    doneJoin = true;
                }

                // if there is one more item, show join item
                this.createJoinsVisible = counter < jQuery.assocArraySize(conditionItems) - 1;

                if (counter == jQuery.assocArraySize(conditionItems)) {
                    break;
                }
                counter++;

                if (condition.type == "group") {
                    var oldRecordId = parentId = this.currentRecordId;
                    var oldRecordName = parentName = this.currentRecordName;

                    this.currentRecordId += "_g" + this.currentRecordIndex;
                    this.currentRecordName += "[g" + this.currentRecordIndex + "]";
                    this.incrementalStage += 1;

                    this.prevType = "group";
                    this.prevGroupRecordName = "g" + this.currentRecordIndex;

                    var groupData = this.addGroup(condition.childs);
                    html += groupData.html;

                    this.incrementalStage -= 1;
                    this.currentRecordId = oldRecordId;
                    this.currentRecordName = oldRecordName;
                    this.groupData[this.currentRecordId].content = true;
                } else {
                    this.prevType = "record";
                    this.prevGroupRecordName = this.currentRecordIndex;

                    if (typeof condition != "undefined" && condition.field.match(/\((\S+): \((\S+)\)\) (\S+)/)) {
                        var parts = condition.field.match(/\((\S+): \((\S+)\)\) (\S+)/);

                        condition.field = "(" + parts[1] + ": (" + parts[2] + ") " + parts[3] + ")";
                    }
                    if (typeof condition != "undefined" && this.fieldData[condition.field] === undefined) {
                        continue;
                    }
                    if (condition !== undefined) {
                        oldFormat = condition.field.match(/\((.*) ?: \((.*)\)\) (.*)/);
                        if (oldFormat !== null) {
                            condition.field = "(" + oldFormat[1] + ": (" + oldFormat[2] + ") " + oldFormat[3] + ")"
                        }
                    }

                    var rowData = this.addRow();
                    html += rowData.html;
                    this.initValues.push({
                        'recordIndex': rowData.recordIndex,
                        'recordId': rowData.recordId,
                        'recordName': rowData.recordName,
                        'condition': condition
                    });

                    this.groupData[this.currentRecordId].content = true;
                }

            }
            this.tmpJoinStack.pop();

            this.preSelectJoin = "and";
            this.createJoinsVisible = false;

            return html;
        };

        this.addRow = function () {
            var tmpHtml = "";

            tmpHtml += "<div class='ConditionalRecord " + (this.initialized === true ? 'NewConditionalRecord' : '') + "' id='record_" + this.currentRecordIndex + "' data-recordindex='" + this.currentRecordIndex + "' data-recordid='" + this.currentRecordId + "' data-recordname='" + this.currentRecordName + "'>";
            tmpHtml += "<img class='ConditionRemove' src='" + this.imagePath + "/cross-button.png' alt='delete'>";


            var fieldName = "task[" + this.field + "]" + this.currentRecordName + "[" + this.currentRecordIndex + "]";
            var fieldId = "records_" + this.currentRecordId + "_" + this.currentRecordIndex;

            tmpHtml += "<select class='ConditionField MakeSelect2' name='" + fieldName + "[field]' id='" + fieldId + "_field'>" + this.getFieldOptions() + "</select>";

            tmpHtml += "<select class='ConditionNot' name='" + fieldName + "[not]' id='" + fieldId + "_not'><option value='0'>-</option><option value='1'>" + MOD["LBL_NOT"] + "</option></select>";

            tmpHtml += "<select class='ConditionOperation' name='" + fieldName + "[operation]' id='" + fieldId + "_operation'><option value=''>_</option></select>";

            tmpHtml += '<select class="ConditionMode" ' + (this.DisabledConditionMode == true ? 'style="display:none;"': '') + ' name="' + fieldName + '[mode]" id="' + fieldId + '_mode"><option value="value">' + MOD.LBL_STATIC_VALUE + '</option><option value="function">' + MOD.LBL_FUNCTION_VALUE + '</option></select>';

            tmpHtml += "<div class='ConditionContainer' id='conditionContainer_" + this.currentRecordId + "_" + this.currentRecordIndex + "'></div>";

            tmpHtml += "</div>";

            tmpHtml += this.addJoinSelect(this.currentRecordIndex, this.currentRecordIndex);

            var returnValue = {
                'html': tmpHtml,
                'recordName': this.currentRecordName,
                'recordIndex': this.currentRecordIndex,
                'recordId': this.currentRecordId
            };

            this.currentRecordIndex++;

            return returnValue;
        };

        this.getOperationHTMLCache = null;
        this.getOperationOptions = function () {

        };

        this.fieldOptionsHTMLCache = null;
        this.getFieldOptions = function () {

            if (this.fieldOptionsHTMLCache === null) {
                var fieldOptions = '';

                jQuery.each(this.moduleFields, jQuery.proxy(function (key, value) {
                    fieldOptions += "<optgroup label='" + key + "'>";
                    for (var i = 0; i < this.moduleFields[key].length; i++) {
                        if (this.columnsRewrites[this.moduleFields[key][i].name] !== undefined) {
                            this.moduleFields[key][i].name = this.columnsRewrites[this.moduleFields[key][i].name];
                        }

                        fieldOptions += "<option value='" + this.moduleFields[key][i].name + "'>" + this.moduleFields[key][i].label + "</option>";
                    }
                    fieldOptions += "</optgroup>";
                }, this));

                if (typeof this.environmentVariables == 'object' && this.environmentVariables.length > 0) {
                    fieldOptions += "<optgroup label='Environment Variables'>";

                    jQuery.each(this.environmentVariables, function (key, value) {
                        fieldOptions += "<option value='" + value + "'>" + value + "</option>";
                    });

                    fieldOptions += "</optgroup>"
                }
                this.fieldOptionsHTMLCache = fieldOptions;
            }

            return this.fieldOptionsHTMLCache;
        }

    };

    var ConditionPopup =  {
        currentConfiguration: {},
        currentModule: '',
        open: function (inputEle, moduleEle, title, options) {
            RedooUtils(ConditionScopeModule).loadStyles("modules/" + ConditionScopeModule + "/views/resources/Conditions.css");
            var callbackFkt = function () {
                if (typeof options == 'undefined') {
                    options = {};
                }

                if (jQuery(inputEle).length == 0) {
                    console.log('no inputEle for Condition Popup');
                }

                ConditionPopup.inputEle = jQuery(inputEle);

                if (ConditionPopup.inputEle.val() != '') {
                    ConditionPopup.currentConfiguration = ConditionPopup.inputEle.val();
                } else {
                    ConditionPopup.currentConfiguration = '';

                    if (typeof moduleEle == 'string' && jQuery(moduleEle).length == 0) {
                        ConditionPopup.currentModule = moduleEle;
                    } else {
                        ConditionPopup.currentModule = jQuery(moduleEle).val();
                    }
                }

                if (typeof moduleName == 'undefined') {
                    moduleName = '';
                }

                RedooAjax(ConditionScopeModule).post('index.php', {
                    module: ConditionScopeModule,
                    'title': title,
                    'view': 'ComplexeCondition',
                    'mode': 'ConditionPopup',
                    fromModule: moduleName,
                    toModule: ConditionPopup.currentModule,
                    configuration: ConditionPopup.currentConfiguration,
                    calculator: options['calculator'] ? true : false,
                }).then(function (response) {
                    RedooUtils(ConditionScopeModule).showModalBox(response).then(function () {
                        jQuery('.modelContainer').css('maxWidth', (jQuery(window).width() - 300) + 'px');

                        jQuery('#PopupConditionForm').ajaxForm({
                            dataType: 'json',
                            success: function (response) {
                                ConditionPopup.inputEle.val(response.condition);

                                if (typeof options['textele'] != 'undefined') {
                                    if (response.html == '' && typeof options['defaultText'] != 'undefined') {
                                        response.html = options['defaultText'];
                                    }
                                    ConditionPopup.inputEle.data('conditionhtml', response.html)
                                    $(options['textele']).html(response.html);
                                }

                                ConditionPopup.inputEle.trigger('change');

                                RedooUtils(ConditionScopeModule).hideModalBox();
                            }
                        });

                        jQuery('.calculateRecords').on('click', function() {
                            if (typeof moduleEle == 'string' && jQuery(moduleEle).length == 0) {
                                ConditionPopup.currentModule = moduleEle;
                            } else {
                                ConditionPopup.currentModule = jQuery(moduleEle).val();
                            }

                            jQuery('#PopupConditionForm').ajaxSubmit({
                                dataType: 'json',
                                success: function (response) {
                                    var data = {
                                        fromModule: '',
                                        toModule: ConditionPopup.currentModule,
                                        task: response.condition
                                    }

                                    FlexAjax('Workflow2').postSettingsAction('ConditionPopupCalculator', data).then(function(response) {
                                        jQuery('#recordMatchCounter span').text(response).show();
                                        jQuery('#recordMatchCounter').show();
                                    });

                                }
                            });
                        });
                    });

                });
            };

            if(typeof jQuery(window).ajaxForm === 'undefined') {
                RedooUtils(ConditionScopeModule).loadScript("modules/" + ConditionScopeModule + "/views/resources/jquery.form.min.js").then(callbackFkt);
            } else {
                callbackFkt();
            }
        },
        loadFields: function () {

        }
    };
    window.ConditionPopup = ConditionPopup;
})(jQuery);
