/* ********************************************************************************
 * The content of this file is subject to the Table Block ("License");
 * You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is VTExperts.com
 * Portions created by VTExperts.com. are Copyright(C) VTExperts.com.
 * All Rights Reserved.
 * ****************************************************************************** */

Vtiger.Class("AdvancedCustomFields_Settings_Js",{

},{
    /* For License page - Begin */
    init : function() {
        this.initiate();
    },
    /*
     * Function to initiate the step 1 instance
     */
    initiate : function(){
        var step=jQuery(".installationContents").find('.step').val();
        this.initiateStep(step);
    },
    /*
     * Function to initiate all the operations for a step
     * @params step value
     */
    initiateStep : function(stepVal) {
        var step = 'step'+stepVal;
        this.activateHeader(step);
    },

    activateHeader : function(step) {
        var headersContainer = jQuery('.crumbs ');
        headersContainer.find('.active').removeClass('active');
        jQuery('#'+step,headersContainer).addClass('active');
    },

    registerActivateLicenseEvent : function() {
        var aDeferred = jQuery.Deferred();
        jQuery(".installationContents").find('[name="btnActivate"]').click(function() {
            var license_key=jQuery('#license_key');
            if(license_key.val()=='') {
                app.helper.showAlertBox({message:"License Key cannot be empty"});
                aDeferred.reject();
                return aDeferred.promise();
            }else{
                app.helper.showProgress('');
                var params = {};
                params['module'] = app.getModuleName();
                params['action'] = 'Activate';
                params['mode'] = 'activate';
                params['license'] = license_key.val();
                app.request.post({'data':params}).then(
                    function(err,data){
                        if(err === null) {
                            app.helper.hideProgress();
                            var message=data.message;
                            if(message !='Valid License') {
                                jQuery('#error_message').html(message);
                                jQuery('#error_message').show();
                            }else{
                                document.location.href="index.php?module=AdvancedCustomFields&parent=Settings&view=Settings&mode=step3";
                            }
                        }else{
                            app.helper.hideProgress();
                        }
                    }
                );
            }
        });
    },

    registerValidEvent: function () {
        jQuery(".installationContents").find('[name="btnFinish"]').click(function() {
            app.helper.showProgress('');
            var params = {};
            params['module'] = app.getModuleName();
            params['action'] = 'Activate';
            params['mode'] = 'valid';
            app.request.post({'data':params}).then(
                function(err,data){
                    if(err === null) {
                        app.helper.hideProgress();
                        document.location.href = "index.php?module=AdvancedCustomFields&parent=Settings&view=Settings";
                    }else{
                        app.helper.hideProgress();
                    }
                }
            );
        });
    },
    /* For License page - End */
    
    //updatedBlockSequence : {},
    registerAddButtonEvent: function () {
        var thisInstance=this;
        jQuery('.settingsPageDiv').on("click",'.addButton', function(e) {
            var source_module = jQuery('#tableBlockModules').val();
            if(source_module !='' && source_module !='All') {
                var url=jQuery(e.currentTarget).data('url') + '&source_module='+source_module;
            }else {
                var url=jQuery(e.currentTarget).data('url');
            }
            thisInstance.showEditView(url,true);
        });
    },
    registerEditButtonEvent: function() {
        var thisInstance=this;
        jQuery(document).on("click",".editBlockDetails", function(e) {
            var url = jQuery(this).data('url');
            thisInstance.showEditView(url,false);
        });
    },
    /*
     * function to show editView for Add/Edit block
     * @params: url - add/edit url
     */
    showEditView : function(url,is_create_new) {
        var thisInstance = this;
        app.helper.showProgress('');
        var actionParams = {
            "url":url
        };
        app.request.post(actionParams).then(
            function(err,data){
                if(err === null) {
                    app.helper.hideProgress();
                    var callBackFunction = function(data) {
                        var frm = jQuery('#tableblocks_form');
                        var params = app.validationEngineOptions;
                        params.submitHandler = function (frm) {
                            thisInstance.saveAdvancedCustomFieldsDetails(frm);
                        };
                        frm.vtValidate(params);

                        frm.submit(function(e) {
                            e.preventDefault();
                        })
                    };
                    app.helper.showModal(data, {'width': '400px', 'cb' : function (data){
                        if(typeof callBackFunction == 'function'){
                            callBackFunction(data);
                        }
                        thisInstance.registerPopupEvents();
                        if(!is_create_new){
                            var module_selected =  jQuery("#s2id_select_module").find('span').first().html();
                            jQuery("#s2id_select_module").html(module_selected);
                            jQuery("#s2id_select_module").css('margin-top','5px');
                            var field_type =  jQuery("#s2id_select_type").find('span').first().html();
                            jQuery("#s2id_select_type").html(field_type);
                            jQuery("#s2id_select_type").css('margin-top','5px');
                            jQuery("#name").prop('disabled', true);
                        }
                    }});
                }else{
                    app.helper.hideProgress();
                }
            }
        );
    },

    /**
     * This function will save the block detail
     */
    saveAdvancedCustomFieldsDetails : function(frm) {         
        var thisInstance = this;
         
        //Validate AdvancedCustomFields
        var params = {};
        params['module'] = app.getModuleName();
        params['action'] = 'ActionAjax';
        params['mode'] = 'validateAdvancedCustomFields';
        params['selected_module'] = jQuery('#select_module').val();
        params['name'] = jQuery('#name').val();
        app.request.post({'data':params}).then(
            function(err,data){
                if(err === null) {
                    if(data.valid)
                    {
                        //save valid AdvancedCustomFields
                        app.helper.showProgress('');

                        var params = $(frm).serializeFormData();
                        params['module'] = app.getModuleName();
                        params['action'] = 'SaveAjax';
                        params['mode'] = 'saveAdvancedCustomFieldsDetails';
                        app.request.post({'data':params}).then(
                            function(err,data){
                                if(err === null) {
                                    app.helper.hideProgress();
                                    if(data[0] == 'success') {
                                        app.helper.hideProgress();
                                        app.hideModalWindow();
                                        var params = {};
                                        var message = app.vtranslate('AdvancedCustomFields saved');
                                         var params = {
                                             message: message,
                                         };
                                         app.helper.showSuccessNotification(params);
                                        thisInstance.loadListAdvancedCustomFields();
                                    
                                    }
                                    else{
                                        app.helper.hideProgress();
                                        app.hideModalWindow();
                                        var params = {};
                                        var message = app.vtranslate('Errors');
                                         var params = {
                                             message: message,
                                         };
                                         app.helper.showErrorNotification(params);
                                        thisInstance.loadListAdvancedCustomFields();
                                    }
                                }else{
                                    app.helper.hideProgress();
                                    var message = app.vtranslate(error);
                                    var params = {
                                        message: message,
                                    };
                                    app.helper.showErrorNotification(params);
                                }
                            }
                        );
                    }
                    else{
                        var error_mss = "";
                        if(data.error ===1) error_mss = app.vtranslate('Advanced Custom Fields is invalid');
                        else error_mss = app.vtranslate('Field name existed');
                        var params = {
                            message: error_mss,
                        };
                        app.helper.showErrorNotification(params);
                        jQuery('#name').focus();
                        return false;
                    } 
                }
            }
        );
    },
    loadListAdvancedCustomFields: function() {
        app.helper.showProgress('');
        var params = {};
        params['module'] = 'AdvancedCustomFields';
        params['view'] = 'MassActionAjax';
        params['mode'] = 'reloadListAdvancedCustomFields';
        params['source_module'] = jQuery('#tableBlockModules').val();
        app.request.post({'data':params}).then(
            function(err,data){
                if(err === null) {
                    app.helper.hideProgress();
                     var contents = jQuery('.listViewEntriesDiv');
                     contents.html(data);
                }else{
                    app.helper.hideProgress();
                }
            }
        );
     },
     /**
      * Function which will handle the registrations for the elements
      */
     registerPopupEvents: function() {
         var container=jQuery('#massEditContainer');
         this.registerPopupSelectModuleEvent(container);
     },

    registerPopupSelectModuleEvent : function(container) {
        var thisInstance = this;
        container.on("change",'[name="select_module"]', function(e) {
            app.helper.showProgress('');
            var select_module=jQuery(this).val();
            var params = {
                "type":"POST",
                "url": "index.php?module=AdvancedCustomFields&view=EditAjax&mode=getBlocks&select_module="+select_module,
                "dataType":"html"
            };
            app.request.post(params).then(
                function(err,data){
                    if(err === null) {
                        app.helper.hideProgress();
                        container.find('#div_blocks').html(data);
                        // TODO Make it better with jQuery.on
                        app.changeSelectElementView(container); 
                        app.showSelect2ElementView(container.find('select.select2'));
                    }else{
                        app.helper.hideProgress();
                    }
                }
            );       
        })
    },
    registerDeleteAdvancedCustomFieldsEvent: function () {
        var thisInstance = this;
        var contents = jQuery('.listViewEntriesDiv');
        contents.on('click','.deleteBlock', function(e) {
            var element=jQuery(e.currentTarget);
            var message = app.vtranslate('JS_LBL_ARE_YOU_SURE_YOU_WANT_TO_DELETE');
            app.helper.showConfirmationBox({'message' : message}).then(
                function(e) {
                   var blockId = jQuery(element).data('id');
                   var params = {};
                   params['module'] = 'AdvancedCustomFields';
                   params['action'] = 'ActionAjax';
                   params['mode'] = 'deleteAdvancedCustomFields';
                   params['record'] = blockId;
                   app.request.post({'data':params}).then(
                       function(err,data){
                           if(err === null) {
                               thisInstance.loadListAdvancedCustomFields();
                           }
                       }
                   );
                },
                function(error, err){
                }
            );
        });
    },
    registerEvents : function() {
        this.registerAddButtonEvent();
        this.registerEditButtonEvent();
        this.registerDeleteAdvancedCustomFieldsEvent();
        /* For License page - Begin */
        this.registerActivateLicenseEvent();
        this.registerValidEvent();
        /* For License page - End */
    }
});

jQuery(document).ready(function(){
    var instance = new AdvancedCustomFields_Settings_Js();
    instance.registerEvents();
    
    // Fix issue not display menu
    Vtiger_Index_Js.getInstance().registerEvents();
});