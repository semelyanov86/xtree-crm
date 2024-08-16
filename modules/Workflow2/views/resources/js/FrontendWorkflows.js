/**
 * Created by Stefan on 14.11.2016.
 */
jQuery(function() {
    var parentEleSrc = 'div#page';
    var currentWorkflowFrontendParentEle = parentEleSrc;

    window.WorkflowFrontendInitialize = true;

    var FrontendWorkflowExecution = function(parentEle) {
        this.record = {};

        this._workflowIds = [];
        this._execId = undefined;
        this._blockID = undefined;

        this._requestValues = {};
        this._requestValuesKey = null;
        this._extraEnvironment = {};
        this._manager = null;

        this.setManagerObject = function(manager) {
            this._manager = manager;
        };

        this.setRecordData = function(recordData) {
            this.record = recordData;
        };

        this.setWorkflowIds = function(workflowIDs) {
            this._workflowIds = workflowIDs;
        };

        this.parseFrontendWorkflowResult = function(newRecord) {
            if(typeof newRecord.env != 'undefined') {
                this._manager.setLastEnvironment(newRecord.env);
            }

            /** Set Field Values from Result **/
            jQuery.each(newRecord.record, $.proxy(function(field, value) {
                var allFieldEleParent = RedooUtils('Workflow2').getFieldElement(field, this.parentEle);
                
                if(field == 'region_id' || field == 'currency_id') {
                    allFieldEleParent = $('select.select2[name="' + field + '"]').parent();
                    if(value.indexOf('x') != -1) {
                        var parts = value.split('x');
                        value = parts[1];
                    }
                }

                if(allFieldEleParent.length > 0) {
                    allFieldEle = $(allFieldEleParent.find('[name="' + field + '"]'));

                    allFieldEle.each(function(index, fieldEle) {
                        fieldEle = $(fieldEle);
                        if(fieldEle.hasClass('autoComplete')) {
                            return;
                        }

                        if(fieldEle.hasClass('dateField')) {
                            fieldEle.datepicker('update', value);
                        }
                        if(fieldEle.hasClass('chzn-select')) {
                            fieldEle.val(value).trigger('liszt:updated');
                        }

                        // Bugfix 2021-03-01
                        if(fieldEle.hasClass('select2') && fieldEle.select2('val') != value) {
                            fieldEle.select2('val', value).trigger('change');
                        }

                        if(fieldEle.attr('type') == 'checkbox') {
                            fieldEle.prop('checked', value == '1');
                        }
                        if(fieldEle.hasClass('sourceField')) {
                            var obj = Vtiger_Edit_Js.getInstance();
                            obj.setReferenceFieldValue(allFieldEleParent, {
                                id: value,
                                name: newRecord.record[field + '_display']
                            });
                        }
                        if(fieldEle.attr('type') == 'checkbox') {
                            fieldEle.prop('checked', value == '1');
                        }
                        if(fieldEle.attr('type') == 'text' || fieldEle.prop("tagName") == 'TEXTAREA') {
                            fieldEle.val(value);
                        }
                    });


                }

            }, this));
            /** Set Field Values from Result FINISH **/

            /** Execute Simple Actions **/
            jQuery.each(newRecord.actions, $.proxy(function(index, action) {
                var callback = $.proxy(function(extraEnvironment) {
                    this._execId = action.execid;
                    this._blockID = action.blockid;

                    jQuery.extend(true, this._extraEnvironment, extraEnvironment);
                    //console.log(this._extraEnvironment);
                    this.execute();
                }, this);

                if(typeof WorkflowFrontendActions[action.type] == 'function') {
                    $.proxy(WorkflowFrontendActions[action.type], this)(action.config, callback);
                }
            }, this));
            /** Execute Simple Actions FINISH **/

            /** Check UserQueue **/
            if(typeof newRecord.userqueue != 'undefined' && newRecord.userqueue.length > 0) {
                jQuery.each(newRecord.userqueue, $.proxy(function(index, response) {
                    switch(response.result) {
                        case 'reqvalues':
                            this._requestValuesKey = response['fields_key'];
                            this._execId = response['execId'];
                            this._blockID = response['blockId'];

                            if(typeof RequestValuesForm == 'undefined') {
                                jQuery.getScript('modules/Workflow2/views/resources/js/RequestValuesForm.js', jQuery.proxy(function() {
                                    var requestForm = new RequestValuesForm();
                                    requestForm.show(response, response['fields_key'], response['request_message'], $.proxy(this.SubmitRequestFields, this), response['stoppable'], response['pausable']);
                                }, this));
                            } else {
                                var requestForm = new RequestValuesForm();
                                requestForm.show(response, response['fields_key'], response['request_message'], $.proxy(this.SubmitRequestFields, this), response['stoppable'], response['pausable']);
                            }

                            break;
                    }
                }, this));
            }
            /** Check UserQueue FINISH **/
        };

        this.SubmitRequestFields = function(key, values, value2, form) {
            this._requestValues = {};
            this._requestValuesKey = key;

            var requestValues = {};
            var html = '';
            jQuery.each(values, function(index, value) {
                requestValues[value.name] = value.value;
            });
            this._requestValues = requestValues;

            if(jQuery('[type="file"]', form).length > 0) {
                var html = '<form action="#" method="POST" onsubmit="return false;">';
                jQuery('input, select, button', form).attr('disabled', 'disabled');
                jQuery('[type="file"]', form).removeAttr('disabled').each(function(index, ele) {
                    var name = jQuery(ele).attr('name');
                    jQuery(ele).attr('name', 'fileUpload[' + name + ']');

                    requestValues[name] = jQuery(ele).data('filestoreid');
                });
                html += '</form>';
                form = html;

                if(typeof jQuery(form).ajaxSubmit == 'undefined') {
                    console.error('jquery.forms plugin requuired!');
                    return;
                }

                WorkflowRunning = true;
                RedooUtils('Workflow2').blockUI({ message: '<h3 style="padding:5px 0;"><img src="modules/Workflow2/icons/sending.gif" /><br/>Please wait ...</h3>' });

                jQuery(form).ajaxSubmit({
                    'url' : "index.php",
                    'type': 'post',
                    data: {
                        "module" : "Workflow2",
                        "action" : "FrontendWorkflowExec",

                        'crmid' : this._crmid,

                        'workflowID' : this._workflowId === null ? undefined : this._workflowId,
                        'allowParallel': this._allowParallel ? 1 : 0,

                        'continueExecId': this._execId === null ? undefined : this._execId,
                        'continueBlockId': this._blockID === null ? undefined : this._blockID,
                        'requestValues': this._requestValues === null ? undefined : this._requestValues,
                        'requestValuesKey': this._requestValuesKey === null ? undefined : this._requestValuesKey,
                        'extraEnvironment': this._extraEnvironment
                    },
                    success:jQuery.proxy(this.executionResponse, this),
                    error:jQuery.proxy(this.executionResponse, this)
                });

                this.executeWithForm(form);
                return;
            }

            this.execute();
        };

        this.execute = function() {
            var dfd = jQuery.Deferred();

            var environment = {};
            jQuery.extend(true, environment, this._manager.getLastEnvironment(), this._extraEnvironment);

            RedooAjax('Workflow2').post('index.php', {
                'module': 'Workflow2',
                'action': 'FrontendWorkflowExec',
                'workflow_ids': this._workflowIds,
                'record': this.record,

                'continueExecId': this._execId === null ? undefined : this._execId,
                'continueBlockId': this._blockID === null ? undefined : this._blockID,
                'requestValues': this._requestValues === null ? undefined : this._requestValues,
                'requestValuesKey': this._requestValuesKey === null ? undefined : this._requestValuesKey,
                'extraEnvironment': environment,
                'dataType': 'json'
            }).then($.proxy(function(data) {
                this.parseFrontendWorkflowResult( data );

                dfd.resolve( data );
            }, this));

            return dfd.promise();
        };

        /** Initialize **/
    };

    var FrontendWorkflowManager = function(parentEle) {
        this.parentEle = parentEle;
        this.FieldValueCache = {};
        this.record = {};
        this._lastEnvironment = {};
        this.mainModule = RedooUtils('Workflow2').getMainModule(this.parentEle);

        this.setLastEnvironment = function(envVars) {
            this._lastEnvironment = envVars;
        };
        this.getLastEnvironment = function() {
            return this._lastEnvironment;
        };

        this.checkFrontendWorkflows = function(e) {
            if(typeof Inventory_Edit_Js != 'undefined') {
                var inventoryInstance = Inventory_Edit_Js.getInstance();

                inventoryInstance.updateLineItemElementByOrder();
                var lineItemTable = inventoryInstance.getLineItemContentsContainer();
                /*jQuery('.discountSave',lineItemTable).trigger('click');*/
                inventoryInstance.lineItemToTalResultCalculations();
                inventoryInstance.saveProductCount();
                inventoryInstance.saveSubTotalValue();
                inventoryInstance.saveTotalValue();
                inventoryInstance.savePreTaxTotalValue();
            }

            var recordRAW = jQuery('#EditView', this.parentEle).serializeArray();

            var record = {};
            jQuery.each(recordRAW, $.proxy(function(index, value) {
                record[value.name] = value.value;
            }, this));

            if(typeof record.record != 'undefined') {
                record.crmid = record.record;
                record.id = record.record;

            }

            var workflowIds = [];
            jQuery.each(this.trigger, $.proxy(function(index, value) {
                if(FrontendWorkflowData[value['function']](record) === true) {
                    workflowIds.push(value.workflow_id);
                }
            }, this));

            if(workflowIds.length > 0) {
                jQuery('[monitorchanges="1"]', this.parentEle).each($.proxy(function(index, ele) {
                    var name = jQuery(ele).attr('name');
                    this.manager.FieldValueCache[name] = record[name];
                }, this));

                var FrontendWorkflowExec = new FrontendWorkflowExecution(parentEleSrc);
                FrontendWorkflowExec.setManagerObject(this.manager);
                FrontendWorkflowExec.setRecordData(record);
                FrontendWorkflowExec.setWorkflowIds(workflowIds);
                FrontendWorkflowExec.execute();
            }
        };

        /** Initialize **/
        if (typeof FrontendWorkflowData == 'undefined') return;
        var ViewMode = RedooUtils('Workflow2').getViewMode(this.parentEle);
        if (ViewMode != 'editview') return;

        if (typeof FrontendWorkflowData.Config[this.mainModule] == 'undefined') return;

        jQuery.each(FrontendWorkflowData.Config[this.mainModule].fields, $.proxy(function (field, trigger) {
            if(field == 'crmid') {
                return;
            }

            var fieldParentEle = RedooUtils('Workflow2').getFieldElement(field, this.parentEle);

            if(fieldParentEle.length > 0) {
                var fieldEle = fieldParentEle.find('[name="' + field + '"]');

                if($('.clearReferenceSelection', fieldParentEle).length > 0) {
                    $('.clearReferenceSelection', fieldParentEle).on(Vtiger_Edit_Js.referenceDeSelectionEvent, $.proxy(this.checkFrontendWorkflows, { trigger:trigger, manager:this }));
                }

                $(fieldEle).on(Vtiger_Edit_Js.referenceSelectionEvent, $.proxy(this.checkFrontendWorkflows, { trigger:trigger, manager:this }));
                $(fieldEle).on('change', $.proxy(this.checkFrontendWorkflows, { trigger:trigger, manager:this }));

                $.proxy(this.checkFrontendWorkflows, { trigger:trigger, manager:this })();
            }
        }, this));



    };

    jQuery(function() {
        window.setTimeout(function() {
            window.WorkflowFrontendInitialize = false;
        }, 1000);
    });

    var FrontendWorkflow = new FrontendWorkflowManager(parentEleSrc);

    window.currentFrontendWorkflowManager = FrontendWorkflow;
});
