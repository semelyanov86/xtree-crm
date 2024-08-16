/* ********************************************************************************
 * The content of this file is subject to the Table Block ("License");
 * You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is VTExperts.com
 * Portions created by VTExperts.com. are Copyright(C) VTExperts.com.
 * All Rights Reserved.
 * ****************************************************************************** */

jQuery.Class("AdvancedCustomFields_Settings_Js",{

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
                errorMsg = "License Key cannot be empty";
                license_key.validationEngine('showPrompt', errorMsg , 'error','bottomLeft',true);
                aDeferred.reject();
                return aDeferred.promise();
            }else{
                var progressIndicatorElement = jQuery.progressIndicator({
                    'position' : 'html',
                    'blockInfo' : {
                        'enabled' : true
                    }
                });
                var params = {};
                params['module'] = app.getModuleName();
                params['action'] = 'Activate';
                params['mode'] = 'activate';
                params['license'] = license_key.val();

                AppConnector.request(params).then(
                    function(data) {
                        progressIndicatorElement.progressIndicator({'mode' : 'hide'});
                        if(data.success) {
                            var message=data.result.message;
                            if(message !='Valid License') {
                                jQuery('#error_message').html(message);
                                jQuery('#error_message').show();
                            }else{
                                document.location.href="index.php?module=AdvancedCustomFields&parent=Settings&view=Settings&mode=step3";
                            }
                        }
                    },
                    function(error) {
                        progressIndicatorElement.progressIndicator({'mode' : 'hide'});
                    }
                );
            }
        });
    },

    registerValidEvent: function () {
        jQuery(".installationContents").find('[name="btnFinish"]').click(function() {
            var progressIndicatorElement = jQuery.progressIndicator({
                'position' : 'html',
                'blockInfo' : {
                    'enabled' : true
                }
            });
            var params = {};
            params['module'] = app.getModuleName();
            params['action'] = 'Activate';
            params['mode'] = 'valid';

            AppConnector.request(params).then(
                function (data) {
                    progressIndicatorElement.progressIndicator({'mode': 'hide'});
                    if (data.success) {
                        document.location.href = "index.php?module=AdvancedCustomFields&parent=Settings&view=Settings";
                    }
                },
                function (error) {
                    progressIndicatorElement.progressIndicator({'mode': 'hide'});
                }
            );
        });
    },
    /* For License page - End */
    
    //updatedBlockSequence : {},
    registerAddButtonEvent: function () {
        var thisInstance=this;
        jQuery('.contentsDiv').on("click",'.addButton', function(e) {
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
         var progressIndicatorElement = jQuery.progressIndicator();
         var actionParams = {
             "type":"POST",
             "url":url,
             "dataType":"html",
             "data" : {}
         };
         AppConnector.request(actionParams).then(
             function(data) {
                 progressIndicatorElement.progressIndicator({'mode' : 'hide'});
                 if(data) {
                     var callBackFunction = function(data) {
                         var form = jQuery('#tableblocks_form');
                         var params = app.validationEngineOptions;
                         params.onValidationComplete = function(form, valid){
                             if(valid) {
                                 thisInstance.saveAdvancedCustomFieldsDetails(form);
                                 return valid;
                             }
                         };
                         form.validationEngine(params);

                         form.submit(function(e) {
                             e.preventDefault();
                         })
                     };
                     app.showModalWindow(data, function(data){
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
                     }, {'width':'600px'})
                 }
             }
         );
     },

     /**
      * This function will save the block detail
      */
     saveAdvancedCustomFieldsDetails : function(form) {         
         var thisInstance = this;
         
         //Validate AdvancedCustomFields
         var validate_params = {};
         validate_params['module'] = app.getModuleName();
         validate_params['action'] = 'ActionAjax';
         validate_params['mode'] = 'validateAdvancedCustomFields';
         validate_params['selected_module'] = jQuery('#select_module').val();
         validate_params['name'] = jQuery('#name').val();
         AppConnector.request(validate_params).then(
             function(data) {  
                if(data["result"].valid)
                {
                     //save valid AdvancedCustomFields
                        var progressIndicatorElement = jQuery.progressIndicator({
                           'position' : 'html',
                           'blockInfo' : {
                               'enabled' : true
                           }
                       });


                       var data_form = form.serializeFormData();
                       data_form['module'] = app.getModuleName();
                       data_form['action'] = 'SaveAjax';
                       data_form['mode'] = 'saveAdvancedCustomFieldsDetails';

                       AppConnector.request(data_form).then(                 
                           function(data) {                 
                               if(data['success']) {
                                   progressIndicatorElement.progressIndicator({'mode' : 'hide'});
                                   app.hideModalWindow();
                                   var params = {};
                                   var message = app.vtranslate('AdvancedCustomFields saved');
                                   params = {
                                            text: message,
                                            type: 'success'
                                    };
                                   Settings_Vtiger_Index_Js.showMessage(params);
                                   thisInstance.loadListAdvancedCustomFields();

                               }
                               else{
                                   progressIndicatorElement.progressIndicator({'mode' : 'hide'});
                                   app.hideModalWindow();
                                   var params = {};
                                   var message = app.vtranslate('Errors');
                                   params = {
                                       text: message,
                                       type: 'error'
                                   };
                                   Settings_Vtiger_Index_Js.showMessage(params);
                                   thisInstance.loadListAdvancedCustomFields();
                               }
                           },
                           function(error) {
                               progressIndicatorElement.progressIndicator({'mode' : 'hide'});
                               var message = app.vtranslate(error);
                               params = {
                                   text: message,
                                   type: 'error'
                               };
                               Settings_Vtiger_Index_Js.showMessage(params);
                           }
                       );
                }
                else{
                        var error_mss = "";
                        if(data["result"].error ===1) error_mss = app.vtranslate('Advanced Custom Fields is invalid');
                        else error_mss = app.vtranslate('Field name existed');
			var AdvancedCustomFieldsErrorParam = {};
                        AdvancedCustomFieldsErrorParam = {
				text: error_mss,
				type: 'error'
			};
                        Settings_Vtiger_Index_Js.showMessage(AdvancedCustomFieldsErrorParam);
                        jQuery('#name').focus();
                    return false;
                } 
             }
         );
     },
     loadListAdvancedCustomFields: function() {
         var progressIndicatorElement = jQuery.progressIndicator({
             'position' : 'html',
             'blockInfo' : {
                 'enabled' : true
             }
         });
         var params = {};
         params['module'] = 'AdvancedCustomFields';
         params['view'] = 'MassActionAjax';
         params['mode'] = 'reloadListAdvancedCustomFields';
         params['source_module'] = jQuery('#tableBlockModules').val();

         AppConnector.request(params).then(
             function(data) {
                 progressIndicatorElement.progressIndicator({'mode' : 'hide'});
                 var contents = jQuery('.listViewEntriesDiv');
                 contents.html(data);
                 //thisInstance.registerSortableEvent();
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
             var progressIndicatorElement = jQuery.progressIndicator();
             var select_module=jQuery(this).val();
             var actionParams = {
                 "type":"POST",
                 "url": "index.php?module=AdvancedCustomFields&view=EditAjax&mode=getBlocks",
                 "dataType":"html",
                 "data" : {
                     "select_module" : select_module
                 }
             };
             AppConnector.request(actionParams).then(
                 function(data) {
                     progressIndicatorElement.progressIndicator({'mode' : 'hide'});
                     if(data) {
                         container.find('#div_blocks').html(data);
                         // TODO Make it better with jQuery.on
                         app.changeSelectElementView(container); 
                         app.showSelect2ElementView(container.find('select.select2'));                        
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
             Vtiger_Helper_Js.showConfirmationBox({'message' : message}).then(
                 function(e) {
                     var blockId = jQuery(element).data('id');
                     var params = {};
                     params['module'] = 'AdvancedCustomFields';
                     params['action'] = 'ActionAjax';
                     params['mode'] = 'deleteAdvancedCustomFields';
                     params['record'] = blockId;
                     AppConnector.request(params).then(
                         function(data) {
                             thisInstance.loadListAdvancedCustomFields();
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