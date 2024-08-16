jQuery.loadScript = function (url, arg1, arg2) {
  var cache = false, callback = null;
  //arg1 and arg2 can be interchangable
  if ($.isFunction(arg1)){
    callback = arg1;
    cache = arg2 || cache;
  } else {
    cache = arg1 || cache;
    callback = arg2 || callback;
  }

  var load = true;
  //check all existing script tags in the page for the url
  jQuery('script[type="text/javascript"]')
    .each(function () {
      return load = (url != $(this).attr('src'));
    });
  if (load){
    //didn't find it in the page, so load it
    jQuery.ajax({
      type: 'GET',
      url: url,
      success: callback,
      dataType: 'script',
      cache: cache
    });
  } else {
    //already loaded so just call the callback
    if (jQuery.isFunction(callback)) {
      callback.call(this);
    };
  };
};

jQuery.loadScript("modules/Workflow2/views/resources/js/jquery.form.min.js");

var RequestValuesForm = function () {
    "use strict";

    this.callback           = null;
    this.fieldsKey          = null;

    this.getKey = function() {
        return this.fieldsKey;
    };

    this.show = function (windowContent, fieldsKey, message, callback, stoppable, pausable, options) {
        if(typeof WFDLanguage === 'undefined') {
            var WFDLanguage = {
                'These Workflow requests some values': 'This Workflow requests some values',
                'Execute Workflow': 'Execute Workflow',
                'Executing Workflow ...': 'Executing Workflow ...',
                'stop Workflow': 'stop Workflow'
            };
        }

        if(typeof options == 'undefined') {
            var options = {
                'successText' : WFDLanguage['Execute Workflow'],
                'stopOnClose' : false
            };
        }

        if(typeof pausable == 'undefined') {
            pausable = true;
        }

        this.callback = callback;
        this.fieldsKey = fieldsKey;
        this.windowContent = windowContent;

        var html = '<div class="modal-dialog modelContainer requestValuesContainer" style="width:550px;">';

        html += '<link rel="stylesheet" href="layouts/v7/modules/Workflow2/resources/css/Modal.css?' + new Date().getTime() + '" type="text/css" media="screen" />';
        html += '<div class="modal-header"><div class="clearfix"><div class="pull-right " ><button type="button" class="close" aria-label="Close" data-dismiss="modal"><span aria-hidden="true" class="fa fa-close"></span></button></div><h4 class="pull-left">' + WFDLanguage['These Workflow requests some values'] + '</h4></div></div>';



        html += "<form method='POST' onsubmit='return false;' id='wf_startfields' enctype='multipart/form-data'>";
        html += "<div id='workflow_startfield_executer' style='display:none;width:100%;height:100%;top:0px;left:0px;background-image:url(modules/Workflow2/icons/modal_white.png);border:1px solid #777777;  position:absolute;text-align:center;'><img src='modules/Workflow2/icons/sending.gif'><br><br><strong>" + WFDLanguage['Executing Workflow ...'] + "</strong></div>";

        html += '<div class="modal-content">';
            html += "<div class='wfLimitHeightContainer requestValueForm'>";
                html += "<p style='margin-left:15px;'><strong>" + message + "</strong></p>";

                html += windowContent['html'];

            html += "</div>"; // .wfLimitHeightContainer

            html += "<div class='clearfix'></div>";

            html += '<div class="modal-footer "><center>';

            html += "<button type='submit' name='submitStartField' class='btn btn-success pull-right'><i class='icon-ok' style='color: #ffffff;'></i> " + options.successText + "</button>";

            if(typeof stoppable != 'undefined' && stoppable == true) {
                var execId = this.windowContent.execId.split('##');execId = execId[0];
                html += "<button type='button' name='submitStartField' class='pull-left btn btn-danger' style='float:left;' onclick='stopWorkflow(\"" + execId + "\",\"" + this.windowContent.crmId + "\",\"" + this.windowContent.blockId + "\", true);app.hideModalWindow();'><i class='icon-remove'></i> " + WFDLanguage['stop Workflow'] + "</button>";
            }

            html += '</center></div></form></div></div>';

        RedooUtils('Workflow2').showModalBox(html).then(jQuery.proxy(function(data) {
            jQuery('.requestValuesContainer').on('click mousedown keydown keypress', function(e) {
                e.stopPropagation();
            });

            jQuery('.MakeSelect2', data).select2();
            createReferenceFields('.requestValueForm');
            if(pausable == false) {
                jQuery(".blockUI").off("click");
            }

            if(this.windowContent['script'] != '') {
                jQuery.globalEval( this.windowContent['script'] )
            }

            var quickCreateForm = jQuery('form#wf_startfields');
            jQuery('.wfLimitHeightContainer', quickCreateForm).css('maxHeight', (jQuery( window ).height() - 250) + 'px');

            jQuery('#wf_startfields').on('submit', jQuery.proxy(function() {
                if(typeof this.callback == 'function') {
                    RedooUtils('Workflow2').hideModalBox();
                    this.callback.call(this, this.getKey(), jQuery("#wf_startfields").serializeArray(), jQuery("#wf_startfields").serialize(), jQuery("#wf_startfields"));
                }
                return false;
            }, this));

        }, this));

    };
};

function createReferenceFields(parentEle) {
    jQuery('.insertReferencefield', parentEle).each(function(index, value) {
        var ele = jQuery(value);
        if(ele.data('parsed') == '1') {
            return;
        }

        ele.data('parsed', '1');

        var fieldName = ele.data('name');
        var moduleName = ele.data('module');
        var parentField = ele.data('parentfield');
        if(typeof parentField == 'undefined') {
            parentField = '';
        }

        if(typeof fieldId == 'undefined') {
            fieldId = fieldName.replace(/[^a-zA-Z0-9]/g, '');
        }

        var valueID = ele.data('crmid');
        var valueLabel = ele.data('label');
        if(typeof valueID == 'undefined') {
            valueID = '';
            valueLabel = '';
        }

        //value = value.replace(/\<\!--\?/g, '<?').replace(/\?--\>/g, '?>');

        var html = '';

        html += '<table border=0 cellpadding=0 cellspacing=0><td data-parentfield="' + parentField + '"><input type="hidden" disabled="disabled" value="' + moduleName + '" name="popupReferenceModule" /><input name="' + fieldName + '"  data-module="' + moduleName + '" type="hidden" value="' + valueID + '" class="sourceField" data-displayvalue="' + valueLabel + '" />';
        html += '<div class="row-fluid input-prepend input-append"><span class="add-on clearReferenceSelection cursorPointer"><i class="icon-remove-sign SWremoveReferenc" title="Clear"></i></span>';
        html += '<input id="' + fieldName + '_display" name="' + fieldName + '_display" type="text" class="marginLeftZero autoComplete" ' + (valueID != ''?'readonly="true"':'') + ' value="' + valueLabel + '" placeholder="Search" />';
        html += '<span class="add-on relatedPopup cursorPointer"><i class="icon-search relatedPopup" title="Select" ></i></span></td></table>';

        ele.html(html);

        jQuery('.autoComplete', ele).attr('readonly', 'readonly');

        jQuery('.clearReferenceSelection', ele).on('click', function(e){
            var element = jQuery(e.currentTarget);
            var parentTdElement = element.closest('td');
            var fieldNameElement = parentTdElement.find('.sourceField');
            var fieldName = fieldNameElement.attr('name');
            fieldNameElement.val('');
            parentTdElement.find('#'+fieldName+'_display').removeAttr('readonly').val('');
            element.trigger(Vtiger_Edit_Js.referenceDeSelectionEvent);
            e.preventDefault();
        });

        jQuery('.relatedPopup', ele).on('click', function(e){
            var thisInstance = this;
            var parentElem = jQuery(e.target).closest('td');

            var editInstance = Vtiger_Edit_Js.getInstance();
            var params = editInstance.getPopUpParams(parentElem);

            if(jQuery(parentElem).data('parentfield') != '') {
                params['related_parent_id'] = jQuery('input[name="' + jQuery(parentElem).data('parentfield') + '"]').val();
                params['related_parent_module'] = jQuery('input[name="' + jQuery(parentElem).data('parentfield') + '"]').data('module');
            }
            var isMultiple = false;
            if(params.multi_select) {
                isMultiple = true;
            }

            var sourceFieldElement = jQuery('input[class="sourceField"]',parentElem);

            var prePopupOpenEvent = jQuery.Event(Vtiger_Edit_Js.preReferencePopUpOpenEvent);
            sourceFieldElement.trigger(prePopupOpenEvent);

            if(prePopupOpenEvent.isDefaultPrevented()) {
                return ;
            }

            var popupInstance =Vtiger_Popup_Js.getInstance();
            popupInstance.show(params,function(data){
                var responseData = JSON.parse(data);
                var dataList = new Array();
                for(var id in responseData){
                    var data = {
                        'name' : responseData[id].name,
                        'id' : id
                    }
                    dataList.push(data);
                    if(!isMultiple) {
                        editInstance.setReferenceFieldValue(parentElem, data);
                    }
                }

                if(isMultiple) {
                    sourceFieldElement.trigger(Vtiger_Edit_Js.refrenceMultiSelectionEvent,{'data':dataList});
                }
                sourceFieldElement.trigger(Vtiger_Edit_Js.postReferenceSelectionEvent,{'data':responseData});
            });
        });

    });
}
