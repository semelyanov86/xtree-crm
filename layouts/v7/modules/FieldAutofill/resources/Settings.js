/* ********************************************************************************
 * The content of this file is subject to the Field Autofill ("License");
 * You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is VTExperts.com
 * Portions created by VTExperts.com. are Copyright(C) VTExperts.com.
 * All Rights Reserved.
 * ****************************************************************************** */

Vtiger_Index_Js("Settings_FieldAutofill_Settings_Js",{
    editInstance:false,
    getInstance: function(){
        if(FieldAutofill_Settings_Js.editInstance == false){
            var instance = new FieldAutofill_Settings_Js();
            FieldAutofill_Settings_Js.editInstance = instance;
            return instance;
        }
        return FieldAutofill_Settings_Js.editInstance;
    }
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
                 var errorMsg = "License Key cannot be empty";
                 license_key.validationEngine('showPrompt', errorMsg , 'error','bottomLeft',true);
                 aDeferred.reject();
                 return aDeferred.promise();
             }else{
                 app.helper.showProgress();
                 var params = {};
                 params['module'] = app.getModuleName();
                 params['action'] = 'Activate';
                 params['mode'] = 'activate';
                 params['license'] = license_key.val();

                 app.request.post({'data': params}).then(
                     function(err,data){
                         if(err === null) {
                             app.helper.hideProgress();
                             if(data.message ="Valid License") {
                                 var message=data.message;
                                 if(message !='Valid License') {
                                     jQuery('#error_message').html(message);
                                     jQuery('#error_message').show();
                                 }else{
                                     document.location.href="index.php?module=FieldAutofill&parent=Settings&view=Settings&mode=step3";
                                 }
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
             app.helper.showProgress();
             var params = {};
             params['module'] = app.getModuleName();
             params['action'] = 'Activate';
             params['mode'] = 'valid';

             app.request.post({'data': params}).then(
                 function(err,data){
                     if(err === null) {
                         app.helper.hideProgress();
                         if (data=="success") {
                             document.location.href = "index.php?module=FieldAutofill&parent=Settings&view=Settings";
                         }
                     }else{
                         app.helper.hideProgress();
                     }
                 }
             );
         });
     },
     /* For License page - End */
    registerSelectModulesEvent: function (container) {
        var thisInstance=this;
        container.find('#modulesList').on('change', function(e){
            app.helper.showProgress();
            var selectedVal=jQuery(this).val();
            var url ='index.php?module=FieldAutofill&view=MassActionAjax&mode=getFieldsOfModules';
            var actionParams = {
                "type":"POST",
                "url":url,
                "dataType":"html",
                "data" : {
                    'selected_val': selectedVal
                }
            };

            app.request.post(actionParams).then(
                function(err,data){
                    if(err === null) {
                        app.helper.hideProgress();
                        var massEditForm = container.find("#mapped_field");
                        massEditForm.html(data);
                        // app.changeSelectElementView(massEditForm);
                        vtUtils.applyFieldElementsView(massEditForm);
                        // app.showSelect2ElementView(massEditForm.find('select.select2'));
                    }else{
                        // to do
                    }
                }
            );
        });
    },

    registerAddMappingButton: function (container) {
        var thisInstance=this;
        container.on('click','#addMappingButton', function(e){
            app.helper.showProgress();
            var selectedVal=jQuery("#modulesList").val();
            var url ='index.php?module=FieldAutofill&view=MassActionAjax&mode=createNewMapping';
            var actionParams = {
                "type":"POST",
                "url":url,
                "dataType":"html",
                "data" : {
                    'selected_val': selectedVal,
                }
            };
            var mapping_template=container.find('#mapping_template').html();
            app.request.post(actionParams).then(
                function(err,data){
                    if(err === null) {
                        app.helper.hideProgress();
                        jQuery(e.currentTarget).parent().parent("tr").before('<tr data-mapping-id="'+data+'">'+mapping_template+'</tr>');
                        var trContain = container.find('tr[data-mapping-id="'+data+'"]');
                        trContain.find('select').each( function(e){
                            jQuery(this).removeClass('templateselect');
                        });
                        $('.templateselect:visible').remove();

                        vtUtils.applyFieldElementsView(trContain);
                        // app.changeSelectElementView(trContain);
                        // app.showSelect2ElementView(trContain.find('select.select2'));
                    }else{
                        // to do
                    }
                }
            );
        });
    },

    registerMappingFieldChange: function (container) {
        container.on('change','.mappingField', function(e){
            var field=jQuery(this).data("field");
            var fieldname=jQuery(this).val();
            var mappingId=jQuery(this).parent().parent("tr").data("mapping-id");
            var url ='index.php?module=FieldAutofill&view=MassActionAjax&mode=saveMappingField';
            var actionParams = {
                "type":"POST",
                "url":url,
                "dataType":"html",
                "data" : {
                    'field':field,
                    'fieldname':fieldname,
                    'mappingId':mappingId
                }
            };
            app.request.post(actionParams).then(
                function(err,data){
                    if(err === null) {
                    }else{
                    }
                }
            );
        });
    },

    registerDeleteMappingButton: function (container) {
        container.on('click','.deleteMappingButton', function(e){
            var mappingId=jQuery(this).parent().parent("tr").data("mapping-id");
            var url ='index.php?module=FieldAutofill&view=MassActionAjax&mode=delMappingField';
            var actionParams = {
                "type":"POST",
                "url":url,
                "dataType":"html",
                "data" : {
                    'mappingId':mappingId
                }
            };
            app.request.post(actionParams).then(
                function(err,data){
                    if(err === null) {
                        jQuery(e.currentTarget).parent().parent("tr").remove();
                    }else{
                        // to do
                    }
                }
            );
        });
    },
     registerConfirmCheckBox: function(container) {
         container.on('change','input[name="show_popup"]', function() {
             app.helper.showProgress();
             var selectedVal=jQuery("#modulesList").val();
             var val=0;
             if(jQuery(this).is(':checked')) {
                 val=1;
             }
             var url ='index.php?module=FieldAutofill&action=ActionAjax&mode=updateConfirmPopup';
             var actionParams = {
                 "type":"POST",
                 "url":url,
                 "dataType":"html",
                 "data" : {
                     "selected_val":selectedVal,
                     "val":val
                 }
             };

             app.request.post(actionParams).then(
                 function(err,data){
                     if(err === null) {
                         app.helper.hideProgress();
                     }else{
                         // to do
                     }
                 }
             );
         });
     },

    /**
     * Function which will register basic events which will be used in quick create as well
     *
     */
    registerEvents : function() {
        this._super();
        var container = jQuery('#FieldAutoFillSettings');
        this.registerSelectModulesEvent(container);
        this.registerAddMappingButton(container);
        this.registerMappingFieldChange(container);
        this.registerDeleteMappingButton(container);
        //this.registerConfirmCheckBox(container);
        /* For License page - Begin */
        this.registerActivateLicenseEvent();
        this.registerValidEvent();
        /* For License page - End */
    }
});




