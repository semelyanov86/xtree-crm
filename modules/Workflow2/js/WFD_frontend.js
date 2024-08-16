(function () {
/** DetailView **/

"use strict";
jQuery('.listViewActionsDiv .addButton[onclick*="runListViewWorkflow"]').each(function(index, ele) {
    var html = jQuery('strong', this).html();

    jQuery(this).html('<strong>' + html + '</strong>');
    var onclick = jQuery(this).attr('onclick');

    if(onclick.indexOf('//#') != -1) {
        var parts = onclick.split('//');
        parts[1] = parts[1].replace(';','');

        jQuery(this).css('backgroundColor', parts[1]);
    }
});
//hh
var WorkflowRecordMessages = [];
function startWorkflowById(workflow, crmid, async) {
    if(typeof async == "undefined") {
        async = true;
    }
    if(async != true) {
        async = false;
    }

    if(typeof crmid == "undefined") {
        crmid = "0";
    }
    if(typeof workflow == "undefined") {
        return false;
    }

    var html = "<div id='workflow_executer' style='width:150px;height:150px;background-image:url(modules/Workflow2/icons/modal_white.png);border:1px solid #777777; box-shadow:0 0 2px #ccc; position:absolute;top:100px;right:300px;text-align:center;'><br><br><img src='modules/Workflow2/icons/sending.gif'><br><br><strong>Executing Workflow ...</strong></div>";
    jQuery("body").append(html);
    jQuery.ajax("index.php", {
        async: async,
        cache: false,
        data:{
            "module" : "Workflow2",
            "action" : "Workflow2Ajax",
            "file"   : "ajaxExecuteWorkflow",

            "crmid" : crmid,
            "workflow" : workflow
        },
        type: 'POST',
        dataType: 'json'
    }).always(function( response ) {
        jQuery("#workflow_executer").remove();

        if(response.result == "startfields") {
            var html = "<div style='position:absolute;background-color:#fff;border:3px solid #5890c9;box-shadow:0px 0px 5px #777;border-radius:3px;top:-100px;left:0px;width:200px;padding:5px;'><form method='POST' onsubmit='submitStartfields(" + '"' + response.workflow + '","' + crmid + '","' + module + '"' + ");return false;' id='wf_startfields'>";
            html += "<img src='modules/Workflow2/icons/cross-button.png' style='position:absolute;right:-6px;top:-6px;cursor:pointer;' onclick='jQuery(\"#startfieldsContainer\").fadeOut(\"fast\");'>";
            html += "<div class='small'>These Workflow requests some values.</div>";

            jQuery.each(response.fields, function(index, value) {
                var inputField = "";
                var fieldName = '' + value.name + '';

                switch(value.type) {
                    case "TEXT":

                        inputField = '<input type="text" style="width:180px;" name="' + fieldName + '" value="' + value.default + '">';
                        break;
                    case "CHECKBOX":
                        if(value.default === null) {
                            value.default = "off";
                        }
                        inputField = '<input type="checkbox" name="' + fieldName + '" ' + (value["default"].toLowerCase()=="on"?"checked='checked'":"") + ' value="on">';
                        break;
                    case "SELECT":
                        var splitValues = value["default"].split("\n");

                        inputField = '<select style="width:183px;" name="' + fieldName + '">';
                        jQuery.each(splitValues, function(index, value) {
                            var fieldValue = value;
                            var fieldKey = value;

                            if(value.indexOf("#~#") != -1) {
                                var parts = value.split("#~#");
                                fieldValue = parts[0];
                                fieldKey = parts[1];
                            }

                            inputField += "<option value='" + fieldKey + "'>" + fieldValue + "</option>";
                        });
                        inputField += '</select>';

                        break;
                    case "DATE":
                        inputField = '<input style="width:130px;" type="text" name="' + fieldName + '" id="'+fieldName+'" value="' + value["default"] + '">';
                        inputField += '<img src="modules/Workflow2/icons/calenderButton.png" style="margin-bottom:-8px;cursor:pointer;" id="jscal_trigger_' + fieldName + '">';
                        inputField += '<script type="text/javascript">Calendar.setup ({inputField : "' + fieldName + '", ifFormat : "%Y-%m-%d", button:"jscal_trigger_' + fieldName + '",showsTime : false, singleClick : true, step : 1});</script>';

                        break;
                }

                html += "<label><div style='overflow:hidden;min-height:26px;padding:2px 0;'><div style='" + (value.type=="CHECKBOX"?"float:left;":"") + "'><strong>"+ value.label + "</strong></div><div style='text-align:right;'>" + inputField + "</div></div></label>";
            });
            html += "<input type='submit' name='submitStartField' value='Execute Workflow' class='button small edit'>";
            html += "</form></div>";

            jQuery("#startfieldsContainer").hide();
            jQuery("#startfieldsContainer").html(html);
            jQuery("#startfieldsContainer").fadeIn("fast");
        }
    });

}

function reloadWFDWidget() {
    var widgetContainer = jQuery('div.widgetContainer#' + jQuery("#module").val() + '_sideBar_Workflows');
    var key = widgetContainer.attr('id');
    FlexUtils('Workflow2').cacheSet(key, 0);

    widgetContainer.html('');

    // Vtiger_Index_Js.loadWidgets(widgetContainer);
}

window.continueWorkflow = function (execid, crmid, block_id) {
    var Execution = new WorkflowExecution();
    Execution.setContinue(execid, block_id);
    Execution.execute();

}

window.stopWorkflow = function(execid, crmid, taskid, direct) {
    if(typeof direct == 'undefined' || direct != true) {
        if(!confirm("stop Workflow?"))
            return;
    }

    jQuery.post("index.php?module=Workflow2&action=QueueStop", {
            "crmid" : crmid,
            "execID" : execid,
            "taskID" : taskid
        },
        function(response) {
            reloadWFDWidget();
        }
    );

    return false;
}

/** ListView **/
function executeWorkflow(button, module, selection) {
    var selectedIDs = "";

    if(typeof selection == "undefined") {
        selectedIDs = jQuery('#allselectedboxes').val().split(";");
        selectedIDs = selectedIDs.join(";");
    } else {
        selectedIDs = selection.join(";");
    }

    if (jQuery("#Wf2ListViewPOPUP").length == 0)
    {
        var div = document.createElement('div');
        div.setAttribute('id','Wf2ListViewPOPUP');
        div.setAttribute('style','display:none;width:350px; position:absolute;');
        div.innerHTML = 'Loading';
        document.body.appendChild(div);

        //      for IE7 compatiblity we can not use setAttribute('style', <val>) as well as setAttribute('class', <val>)
        newdiv = document.getElementById('Wf2ListViewPOPUP');
        newdiv.style.display = 'none';
        newdiv.style.width = '400px';
        newdiv.style.position = 'absolute';
    }

    jQuery('#status').show();

    currentListViewPopUpContent = "#wf2popup_wf_execute";

    jQuery.post("index.php", {
        "module" : "Workflow2",
        "action" : "Workflow2Ajax",
        "file"   : "ListViewPopup",

        "return_module" : module,
        "record_ids"    : selectedIDs
    }, function(response) {
        jQuery("#Wf2ListViewPOPUP").html(response);

        fnvshobj(button,'Wf2ListViewPOPUP');

        var EMAILListview = document.getElementById('Wf2ListViewPOPUP');
        var EMAILListviewHandle = document.getElementById('Workflow2ViewDivHandle');
        Drag.init(EMAILListviewHandle,EMAILListview);

        jQuery('#status').hide();

    });

}

var currentListViewPopUpContent = "#wf2popup_wf_execute";
function showWf2PopupContent(id) {
    jQuery(currentListViewPopUpContent + "_TAB").addClass("deactiveWf2Tab");
    jQuery(id + "_TAB").removeClass("deactiveWf2Tab");
    jQuery(currentListViewPopUpContent).hide();
    jQuery(id).show();
    currentListViewPopUpContent= id;

    if(id == "wf2popup_wf_importer") {
        jQuery("#execute_mode").val("execute");
    } else {
        jQuery("#execute_mode").val("import");
    }
}

function executeLVWorkflow() {
    if(jQuery("#execute_mode").val() == "import") {
        return true;
    }

    var record_ids = jQuery("#WFLV_record_ids").val();
    var return_module = jQuery("#WFLV_return_module").val();
    var workflow = jQuery("#exec_this_workflow").val();
    var parallel = jQuery("#exec_workflow_parallel").attr("checked")=="checked"?1:0;

    var ids = record_ids.split("#~#");

    jQuery("#executionProgress_Value").html("0 / " + ids.length);
    jQuery("#executionProgress").show();

    jQuery.ajaxSetup({async:false});
    var counter = 0;

    jQuery.each(ids, function(index, value) {
        jQuery.post("index.php?module=Workflow2&action=Workflow2Ajax&file=ajaxExecuteWorkflow", {
                "crmid" : value,
                "return_module" : return_module,
                "workflow" : workflow,
                "allow_parallel" : parallel
            }
        );
        counter = counter + 1;
        jQuery("#executionProgress_Value").html(counter + " / " + ids.length);
    });
    jQuery.ajaxSetup({async:true});

    jQuery("#executionProgress_Value").html("100%");

    if(currentListViewPopUpContent == "#wf2popup_wf_execute") {
        return false;
    }
}
var ENABLEredirectionOrReloadAfterFinish = true;
var WorkflowMetaData = {};
var WithinRecordLessWF = false;

function runListViewWorkflow(workflowId, couldStartWithoutRecord, collection_process) {
    if(typeof couldStartWithoutRecord === 'undefined' && typeof collection_process === 'undefined') {

        if(typeof WorkflowMetaData[workflowId] === 'undefined') {
            FlexAjax('Workflow2').postAction('WorkflowInfo', {workflow_id: workflowId}, 'json').then(function (workflowInfo) {
                WorkflowMetaData[workflowId] = workflowInfo;

                runListViewWorkflow(workflowId);
            });
            return false;
        } else {
            couldStartWithoutRecord = WorkflowMetaData[workflowId].withoutrecord;
            collection_process = WorkflowMetaData[workflowId].collection_process;
        }
    }

    if(WithinRecordLessWF === true)  {
        couldStartWithoutRecord = false;
    }
    if(typeof couldStartWithoutRecord === 'undefined') {
        couldStartWithoutRecord = false;
    }
    if(typeof collection_process === 'undefined') {
        collection_process = false;
    }

    var processSettings = {};
    if(typeof WorkflowDesignerProcessSettings == 'undefined' || typeof WorkflowDesignerProcessSettings[workflowId] == 'undefined') {
        processSettings = {'withoutrecord': couldStartWithoutRecord, 'collection_process' : collection_process};
    } else {
        processSettings = WorkflowDesignerProcessSettings[workflowId];
    }

    var listInstance = window.app.controller();
    var params = listInstance.getListSelectAllParams(false);

    if(params !== false) {
        var selectedIds = params.selected_ids;
    } else {
        var selectedIds = [0];
    }

    RedooUtils('Workflow2').blockUI({
        title: 'Executing ... ',
        message: '<p style="margin:20px 0px;font-size:14px;"><strong style="text-transform:uppercase;">' + FLEXLANG('Please wait', 'Workflow2') + ' ...&nbsp;&nbsp;&nbsp;&nbsp;</strong><span id="workflowDesignerDone">0</span> of <span id="workflowDesignerTotal">' + (selectedIds!='all'?selectedIds.length:'?') + '</span> done</p><div style="margin: 10px 10px 10px 10px;" id="executionProgress" class="progress"><div class="progress-bar progress-bar-success progress-bar-striped" style="width: 0;"></div></div>',
        theme: false,
        css: {
            'backgroundColor':'#2d3e49',
            'color': '#ffffff',
            'border': '1px solid #fff'
        },
        onBlock: function() {
            var counter = -1;

            if(selectedIds == 'all') {
                jQuery.ajaxSetup({async:false});
                var parameter = listInstance.getDefaultParams();
                parameter.module = 'Workflow2';
                parameter.view = undefined;
                parameter.action = 'GetSelectedIds';

                jQuery.post('index.php', parameter, function(response) {
                    selectedIds =  response.ids;
                }, 'json');

                jQuery('#workflowDesignerTotal').html(selectedIds.length);
                jQuery.ajaxSetup({async:true});
            }

            var totalIds = selectedIds.length;

            if(selectedIds.length > 1) {
                ENABLEredirectionOrReloadAfterFinish = false;
            }

            var couldStartWithoutRecord = false;

            if(selectedIds.length == 0 && processSettings.withoutrecord == false) {
                alert('Please choose a record to execute');
                RedooUtils('Workflow2').unblockUI();
                return;
            }
            /*
            if(selectedIds.length == 0) {
                couldStartWithoutRecord = true;
            }
*/
            if(processSettings.collection_process == "1") {
                var workflow = new Workflow();
                ENABLEredirectionOrReloadAfterFinish = true;
                workflow.setRequestedData({ 'recordids':selectedIds.join(',') }, 'collection_recordids');

                var crmid = selectedIds.shift();

                workflow.execute(workflowId, crmid, function(response) {
                    RedooUtils('Workflow2').unblockUI();
                    if(typeof response.redirection == "undefined") {
                        window.location.reload();
                    }

                    return true;
                });
                return;
            }
            if(processSettings.withoutrecord == "1" && selectedIds.length == 1 && selectedIds[0] == 0) {
                var workflow = new Workflow();
                ENABLEredirectionOrReloadAfterFinish = true;
                var crmid = 0;

                WithinRecordLessWF = true;
                workflow.execute(workflowId, crmid, function(response) {
                    RedooUtils('Workflow2').unblockUI();
                    if(typeof response.redirection == "undefined") {
                        window.location.reload();
                    }

                    return true;
                }, true);
                return;
            }

            function _executeCallback() {
                counter = counter + 1;
                jQuery('#workflowDesignerDone').html(counter);
                var crmid = selectedIds.shift();

                if(couldStartWithoutRecord === true) {
                    crmid = 0;
                    couldStartWithoutRecord = false;
                }

                var progress = Math.round(((totalIds - selectedIds.length) / totalIds) * 100);
                jQuery('#executionProgress .progress-bar-success').css('width', progress + '%');

                if(typeof crmid !== 'undefined') {
                    var workflow = new Workflow();
                    workflow.setBackgroundMode(true);
                    workflow.execute(workflowId, crmid, _executeCallback);
                } else {
                    RedooUtils('Workflow2').unblockUI();
                    window.location.reload();
                }
            }

            _executeCallback();
        }
    });

}
function runListViewSidebarWorkflow() {
    runListViewWorkflow(jQuery("#workflow2_workflowid").val(), jQuery("#workflow2_workflowid option:selected").data('withoutrecord') == '1');
}
function runSidebarWorkflow(crmid) {
    if(jQuery("#workflow2_workflowid").val() == "") {
        return;
    }

    var workflow = new Workflow();
    workflow.execute(jQuery("#workflow2_workflowid").val(), crmid);
}


function WorkflowWidgetLoaded() {
    jQuery('.WFdivider', '#WorkflowDesignerWidgetContainer').each(function(index, element) {
        if(jQuery(element).next().length == 0 || jQuery(element).next().hasClass('WFdivider')) {
            jQuery(element).hide();
        }
    });
}
var WFDvisibleMessages = {};


var WorkflowHandler = {
    startImport : function(moduleName) {
        RedooAjax('Workflow2').postView('ImportModal', {target_module:moduleName}).then(function(response) {
            RedooUtils('Workflow2').hideModalBox();
            RedooUtils('Workflow2').showContentOverlay(response).then(function() {
                RedooUtils('Workflow2').loadScript('modules/Workflow2/js/Importer.js').then(function() {
                    var Import = new Importer();
                    Import.init();
                });
            });
        });

        /*
        jQuery.post('index.php?module=Workflow2&view=ImportStep1', { source_module: source_module, currentUrl: window.location.href },  function(html) {
            app.showModalWindow(html, function(data) {
                jQuery('#modalSubmitButton').removeAttr('disabled');
            });
        });
        */
    }
};
window.WorkflowHandler = WorkflowHandler;

function showEntityData(crmid) {
    jQuery.post('index.php?module=Workflow2&view=EntityData', { crmid:crmid },  function(html) {
        app.showModalWindow(html, function(data) {
            jQuery('.EntityDataDelete').on('click', function(e) {
                var dataid = jQuery(e.currentTarget).data('id');

                jQuery.post('index.php', {
                    'module':'Workflow2',
                    'action':'EntityDataDelete',
                    'dataid':dataid
                }, function() {
                    showEntityData(crmid);
                });
            });
        });
    });
}
var workflowObj;
window.closeForceNotification = function(messageId) {
    jQuery.post('index.php?module=Workflow2&action=MessageClose', { messageId:messageId, force: 1 });
}
var UserQueue = {
    run: function(exec_id, block_id) {
        var Execution = new WorkflowExecution();

        Execution.setContinue(exec_id, block_id);

        Execution.execute();
    }
};

var WorkflowPermissions = {
    returnCounter:0,
    submit: function(execID, confID, hash, result) {
        if(jQuery('#row_' + confID).data('already') == '1') {
            if(!confirm('Permission already set. Set again?')) {
                return;
            }
        }

        var execution = new WorkflowExecution();
        execution.setCallback(function(response) {  });

        execution.setContinue(execID, 0);
        //execution.enableRedirection(false);
        execution.submitRequestFields('authPermission', [{name:'permission', value: result}, {name:'confid', value: confID}, {name:'hash', value: hash}], {}, jQuery('.confirmation_container'));

        var row = jQuery('#row_' + confID);
        jQuery('.btn.decision', row).removeClass('pressed').addClass('unpressed');
        jQuery('.btn.decision_' + result, row).addClass('pressed').removeClass('unpressed');

        return false;
    },
    submitAll:function(blockId, result) {
        /*if(jQuery('table.block' + blockId + ' [data-already="1"]').length > 0) {
         if(!confirm('This will overwrite every already defined value! Continue?')) {
         return;
         }
         }*/

        jQuery('table.block' + blockId + ' .permissionRow input.selectRows:checked').each(function(index, value) {
            var row = jQuery(this).closest('.permissionRow');

            var confId = jQuery(row).data('id');
            var execID = jQuery(row).data('execid');
            var hash = jQuery(row).data('hash');

            WorkflowPermissions.returnCounter++;
            var execution = new WorkflowExecution();
            execution.setCallback(function(response) { WorkflowPermissions.returnCounter--; if(WorkflowPermissions.returnCounter == 0) window.location.reload(); });

            execution.setContinue(execID, 0);
            execution.enableRedirection(false);
            execution.submitRequestFields('authPermission', [{name:'permission', value: result}, {name:'confid', value: confId}, {name:'hash', value: hash}], {}, jQuery('.confirmation_container'));
        });

        return false;
    }
};
window.WorkflowPermissions = WorkflowPermissions;

var WorkflowFrontendTypes = {
    getWorkflows:function(type, module, crmid) {
        if(typeof type === 'undefined') {
            console.error('You do not define a FrontendType. Please check!');
            return;
        }
        if(typeof module === 'undefined') {
            console.error('You do not define a Module of FrontendTypes. Please check!');
            return;
        }

        if(typeof crmid === 'undefined' && typeof WFDFrontendConfig !== 'undefined' && typeof WFDFrontendConfig[type] !== 'undefined' && typeof WFDFrontendConfig[type][module] !== 'undefined') {
            var aDeferred = jQuery.Deferred();

            var result = [];
            jQuery.each(WFDFrontendConfig[type][module], function(index, value) {
                var tmp = value.config;

                tmp.workflow_id = value.workflowid;
                tmp.module = module;
                tmp.label = value.label;
                tmp.color = value.color;
                tmp.textcolor = value.textcolor;
                result.push(tmp);
            });

            aDeferred.resolveWith({}, [result]);

            return aDeferred.promise();
        }

        return FlexAjax('Workflow2').postAction('FrontendLinks', {
            'type'      :   type,
            'target_module'    :   module,
            'target_crmid'     :   crmid
        }, 'json');

    },
    triggerWorkflow:function(type, workflowid, crmid, envVars) {
        var dfd = jQuery.Deferred();

        var execution = new WorkflowExecution();
        execution.setCallback(function(response) {
            var workflowFrontendActions = new Workflow();
            workflowFrontendActions.checkFrontendActions('init', crmid);

            dfd.resolve(response);
            return false;
        });

        execution.init(crmid);
        execution.setWorkflowById(workflowid);

        if(typeof envVars === 'undefined') {
            var envVars = {};
        }

        execution.setFrontendType(type);
        execution.setEnvironment(envVars);
        execution.execute();

        return dfd.promise();
    },
    trigggerWorkflow:function(type, workflowid, crmid, envVars) {
        return WorkflowFrontendTypes.triggerWorkflow(type, workflowid, crmid, envVars);
    }
};
window.WorkflowFrontendTypes = WorkflowFrontendTypes;
;/*
 * @copyright 2016-2018 Redoo Networks GmbH
 * @link https://redoo-networks.com/
 * This file is part of a vTigerCRM module, implemented by Redoo Networks GmbH and must not used without permission.
 */
!function(d){"use strict";var c="Workflow2",a={postAction:function(e,t,i,n){return void 0===t&&(t={}),t.module=c,t.action=e,void 0===n&&"string"==typeof i&&(n=i,i=!1),void 0!==i&&1==i&&(t.parent="Settings"),a.post("index.php",t,n)},postSettingsView:function(e,t,i){return a.postView(e,t,!0,i)},postSettingsAction:function(e,t,i){return a.postAction(e,t,!0,i)},postView:function(e,t,i,n){return void 0===t&&(t={}),t.module=c,t.view=e,void 0===n&&"string"==typeof i&&(n=i,i=!1),void 0!==i&&1==i&&(t.parent="Settings"),a.post("index.php",t,n)},post:function(e,t,r){var l=jQuery.Deferred();"object"==typeof e&&(t=e,e="index.php"),void 0===t&&(t={}),void 0===r&&void 0!==t.dataType&&(r=t.dataType);var s={url:e,data:t};return void 0!==r&&(s.dataType=r),s.dataType="text",s.type="POST",jQuery.ajax(s).always(function(t){if(void 0!==r&&"json"==r)try{t=jQuery.parseJSON(t)}catch(e){N.unblockUI(),console.error("FlexAjax Error - Should: JSON Response:"),console.log("Request: ",s),console.log(t);var i=10;jQuery(".RedooAjaxError").each(function(e,t){i+=jQuery(t).height()+30});var n="error_"+Math.floor(1e6*Math.random()),o=t.substr(0,500).replace(/</g,"&lt;").replace(/\\(.?)/g,function(e,t){switch(t){case"\\":return"\\";case"0":return"\0";case"":return"";default:return t}});500<t.length&&(o+=" .....<em>shortened</em>....... "+t.substr(-500).replace(/</g,"&lt;").replace(/\\(.?)/g,function(e,t){switch(t){case"\\":return"\\";case"0":return"\0";case"":return"";default:return t}}));var a='<div class="RedooAjaxError" style="word-wrap:break-word;position:fixed;bottom:'+i+'px;box-sizing:border-box;left:10px;padding:10px;width:25%;background-color:#ffffff;z-index:90000;border:2px solid #C9331E;background-color:#D29D96;" id="'+n+'"><i class="icon-ban-circle" style="margin-top:2px;margin-right:5px;"></i><span style="color:#C9331E;font-weight:bold;">ERROR:</span> '+e+'<br/><span style="color:#C9331E;font-weight:bold;">Response:</span>'+o+"</div>";return jQuery("body").append(a),void jQuery("#"+n).on("click",function(){jQuery(this).fadeOut("fast",function(){jQuery(this).remove()})})}if(void 0!==t.success&&0==t.success&&-1!=t.error.code.indexOf("request"))return confirm("Request Error. Reload of Page is required.")&&window.location.reload(),void N.showNotification(t.error.message,!1);l.resolve(t)}),l.promise()},get:function(e,t,i){console.error("Vtiger do not support GET Requests")},request:function(e){return a.post("index.php",e)}},l={get:function(e,t){return void 0!==r[e]?r[e]:t},set:function(e,t){r[e]=t}},n={process:function(e){d(".FlexField",e).each(function(e,t){var i=new n.handler(t);i.init(),d(t).data("flexfield",i)})},handler:function(e){switch(this.container=d(e),this.type=this.container.data("type"),this.name=this.container.data("name"),this.value=null,this.handler={init:d.proxy(function(){},this),set:d.proxy(function(e){this.value=e},this),get:d.proxy(function(){return this.value},this)},this.type){case"reference":this.handler.init=d.proxy(function(){var e="";e+='<input name="'+this.name+'" rel="RecordId" type="hidden" value="" class="sourceField">',e+='<button type="button" class="btn btn-default ChooseRecordBtn" data-module="'+this.container.data("module")+'">choose Record</button>',e+='<button type="button" class="btn btn-default ClearRecordBtn">Clear</button>',e+='<span rel="RecordLabel" data-placeholder="No Product"></span>',e+="</span>",this.container.html(e),d(".ClearRecordBtn",this.container).on("click",d.proxy(function(e){var t=jQuery(e.currentTarget);this.container.find('[rel="RecordLabel"]').html(t.parent().find('[rel="RecordLabel"]').data("placeholder")),this.container.find('[rel="RecordId"]').val("").data("label","")},this)),d(".ChooseRecordBtn",this.container).on("click",d.proxy(function(e){var t=d(e.currentTarget).data("module");"#"==t.substr(0,1)&&(t=d(t).val());var i={module:t};Vtiger_Popup_Js.getInstance().show(i,d.proxy(function(e){var t=JSON.parse(e);for(var i in t)t.hasOwnProperty(i)&&(this.container.find('[rel="RecordLabel"]').html(t[i].name),this.container.find('[rel="RecordId"]').val(i).data("label",t[i].name))},this))},this))},this),this.handler.get=d.proxy(function(){return{id:this.container.find('[rel="RecordId"]').val(),display:this.container.find('[rel="RecordId"]').data("label")}},this),this.handler.set=d.proxy(function(e){this.container.find('[rel="RecordId"]').val(e)},this)}this.init=function(){this.handler.init()},this.get=function(){return this.handler.get()},this.val=function(){return this.handler.get()},this.set=function(e){return this.handler.set(e)}}},s={getInstance:function(e){var l=function(e,t,i){var n={selfdecorate:!1};this.field_options=n,this.field_value=null,this.field_type=t,this.field_name=e,this.field_label=i,this.rendered=!1,this.containerId="cfid"+Math.round(1e5*Math.random(999999))+Math.round(1e5*Math.random(999999)),this.fieldclass="cf"+Math.round(1e5*Math.random())+Math.round(1e5*Math.random()),this.typedata=s.getType(t),this.setOptions=function(e){this.field_options=d.extend(!0,n,e)},this.getOptions=function(){return this.field_options},this.setValue=function(e){this.field_value=e,!0===this.rendered&&this.typedata.setter(this.field_value,this)},this.init=function(){"function"==typeof this.typedata.init&&this.typedata.init(this)},this.getValue=function(){return this.typedata.getter(this)},this.getFieldName=function(){return this.field_name},this.render=function(){var e='<div class="group materialstyle" id="'+this.containerId+'">';return e+=this.typedata.render(this),e+="<label>"+this.field_label+"</label>",e+="</div>",this.rendered=!0,e},this.getContainer=function(){return d("#"+this.containerId)}};return new function(e){this.parameters=e,this.fields=[],this.events=d({}),this.on=function(e,t){this.events.on(e,t)},this.addField=function(e,t,i,n,o){var a=new l(t,i,e);void 0!==o&&a.setOptions(o),void 0!==n&&a.setValue(n);var r=[];r.push(a),this.fields.push(r)},this.initCSS=function(){var e="<style type=\"text/css\">.FlexFormContainer .materialstyle{border:1px solid transparent}.FlexFormContainer .materialstyle.group{position:relative;margin-bottom:15px;margin-top:10px}.FlexFormContainer .materialstyle.prefix-icon .fa{position:absolute;top:8px;font-size:14px}.FlexFormContainer .materialstyle.prefix-icon label{left:19px}.FlexFormContainer .materialstyle.prefix-icon input{padding-left:20px}.FlexFormContainer .materialstyle input{font-size:14px;padding:10px 10px 10px 0;display:block;width:100%;border:none;height:30px;border-bottom:1px solid #757575;border-top:none!important;border-left:none!important;border-right:none!important;box-shadow:none!important}.FlexFormContainer .materialstyle input[type=file]{border-bottom:none!important}.FlexFormContainer .materialstyle select{font-size:14px;padding:5px 10px 5px 0;display:block;width:100%;border:none;height:38px;border-bottom:1px solid #757575}.FlexFormContainer .materialstyle textarea{font-size:14px;padding:3px 5px;display:block;width:100%;height:150px;border:1px solid #757575}.FlexFormContainer .materialstyle .select2-container{width:100%}.FlexFormContainer .materialstyle .select2-container .select2-choice,.materialstyle .select2-container .select2-choices,.materialstyle .select2-container .select2-choices .select2-search-field input{-webkit-box-shadow:none;box-shadow:none}.FlexFormContainer .materialstyle input:focus,.materialstyle select:focus,.materialstyle textarea:focus{outline:0}.FlexFormContainer .materialstyle label{color:#bbb;font-size:16px;font-weight:400;position:absolute;pointer-events:none;left:5px;top:5px;transition:.2s ease all;-moz-transition:.2s ease all;-webkit-transition:.2s ease all}.FlexFormContainer .materialstyle input.fixedUsed~label,.materialstyle input.used~label,.materialstyle input:focus~label,.materialstyle select.fixedUsed~label,.materialstyle select.used~label,.materialstyle select:focus~label,.materialstyle textarea.fixedUsed~label,.materialstyle textarea.used~label,.materialstyle textarea:focus~label,.materialstyle.fixedUsed>label{top:-18px;left:0;font-size:12px;color:#5264AE}.FlexFormContainer .materialstyle .bar{position:relative;display:block;width:100%}.FlexFormContainer .materialstyle .bar:after,.materialstyle .bar:before{content:'';height:2px;width:0;bottom:1px;position:absolute;background:#5264AE;transition:.2s ease all;-moz-transition:.2s ease all;-webkit-transition:.2s ease all}.FlexFormContainer .materialstyle .bar:before{left:50%}.FlexFormContainer .materialstyle .bar:after{right:50%}.FlexFormContainer .ReqValRow .materialstyle { padding:5px 0; }.FlexFormContainer .materialstyle input:focus~.bar:after,.materialstyle input:focus~.bar:before{width:50%}</style>",t="flexformmodalcss"+function(e){var t,i=0;if(0==e.length)return i;var n=e.length;for(t=0;t<n;t++)i=(i<<5)-i+e.charCodeAt(t),i|=0;return i}(e);if(0===d("style."+t).length){var i=d(e).addClass(t);d("head").append(i)}},this.renderModal=function(e,t,i,n){this.initCSS(),void 0===e&&(e=600),void 0===t&&(t=""),void 0===i&&(i="Save"),void 0===n&&(n="Cancel");var o="";o+='<div class="modal-dialog modelContainer" style="width:'+e+'px;">',o+='<div class="modal-content" style="width:100%;">',o+='<div class="modal-header"><div class="clearfix"><div class="pull-right"><button type="button" class="close" aria-label="Close" data-dismiss="modal"><span aria-hidden="true" class="fa fa-close"></span></button></div><h4 class="pull-left">'+t+"</h4></div></div>",o+='<div class="modal-body FlexFormContainer">',jQuery.each(this.fields,function(e,t){o+='<div class="ReqValRow">',jQuery.each(t,function(e,t){o+=t.render()}),o+="</div>"}),o+="</div>",o+='<div class="modal-footer "><center><button class="btn btn-success" type="submit" name="saveButton"><strong>'+i+'</strong></button><a href="#" class="cancelLink" type="reset" data-dismiss="modal">'+n+"</a></center></div>",o+="</div>",o+="</div>",N.showModalBox(o).then(d.proxy(function(e,t){jQuery.each(this.fields,function(e,t){jQuery.each(t,function(e,t){t.init()})}),d(".btn-success",e).on("click",d.proxy(function(){var i={};jQuery.each(this.fields,function(e,t){jQuery.each(t,function(e,t){i[t.getFieldName()]=t.getValue()})}),this.events.trigger("save",i)},this)),jQuery(".materialstyle input, .materialstyle select, .materialstyle textarea",e).on("blur",function(){jQuery(this).val()?jQuery(this).addClass("used"):jQuery(this).removeClass("used")}).trigger("blur"),jQuery('.materialstyle .select2-container, .materialstyle input[type="file"]',e).each(function(e,t){jQuery(t).closest(".materialstyle").addClass("fixedUsed")})},this))}}(e)},registerType:function(e,t,i,n,o,a,r){s._types[e]={init:i,render:t,validate:n,setter:o,getter:a,options:r}},getType:function(e){if(void 0===s._types[e])throw"["+c+"] FlexForm: "+e+" is undefined";return s._types[e]},_types:{text:{options:{},render:function(e){return'<input type="text" class="inputEle input-fullwidth '+e.fieldclass+'" value="'+(null!==e.field_value?e.field_value:"")+'" />'},validate:function(e,t){return!0},getter:function(e){return e.getContainer().find("."+e.fieldclass).val()},setter:function(e,t){t.getContainer().find("."+t.fieldclass).val(e)}},textarea:{options:{},render:function(e){return'<textarea class="inputEle input-fullwidth '+e.fieldclass+'">'+(null!==e.field_value?e.field_value:"")+"</textarea>"},validate:function(e,t){return!0},getter:function(e){return e.getContainer().find("."+e.fieldclass).val()},setter:function(e,t){t.getContainer().find("."+t.fieldclass).val(e)}},picklist:{options:{},render:function(i){var e=i.getOptions(),n='<select class="select2 '+i.fieldclass+'">';return d.each(e.options,function(e,t){n+='<option value="'+e+'" '+(i.field_value==e?'selected="selected"':"")+">"+t+"</option>"}),n+="</select>"},init:function(e){},validate:function(e,t){return!0},getter:function(e){return e.getContainer().find("."+e.fieldclass).select2("val")},setter:function(e,t){t.getContainer().find("."+t.fieldclass).select2("val",e)}}}},o={init:function(e,t){l.set("__translations_"+e,t)},getTranslator:function(){return function(e){return o.__(e)}},__:function(e){var t=app.getUserLanguage(),i=l.get("__translations_"+t,{});return"function"==typeof i?(o.init(t,i()),o.__(e)):void 0!==i[e]?i[e]:e}},N={layout:null,currentLVRow:null,listViewFields:!1,isVT7:function(){return void 0!==app.helper},createFileDrop:function(n){void 0===n&&(n={}),void 0===n.hovertext&&(n.hovertext="Drop File"),void 0===n.container&&(n.container="body"),void 0===n.container&&(n.container="body"),void 0===n.data&&(n.data={}),"BODY"!==d(n.container).prop("tagName")&&"static"==d(n.container).css("position")&&d(n.container).css("position","relative"),d(n.container).addClass("RegisteredFileDrop");var e=d(n.container).height();if(void 0===n.url){if(void 0===n.action)throw"URL or action is mandatory for FileDrop Component";n.url="index.php?module="+c+"&action="+n.action,void 0!==n.settings&&!0===n.settings&&(n.url+="&parent=Settings")}if(0===d("style#FlexFileDropStyles").length){var t=20,i=!0,o="40%";e<200&&(i=!(t=12),o="10%");var a='<style type="text/css" id="FlexFileDropStyles">div.FlexFileDropOverlay {\n  position: absolute;\n  top: 0;\n  left: 0;\n  height: 100%;\n  width: 100%;\n  z-index: 2000;\n  background-color: rgba(0, 0, 0, 0.7);\n  color: #ffffff;\n  font-weight: bold;\n  letter-spacing: 1px;\n  text-transform: uppercase;\n  font-size: '+t+'px;\n   overflow:hidden;   text-align: center;\n  font-family: "Arial Black", Gadget, sans-serif;\n}\ndiv.FlexFileDropOverlay * {\n  pointer-events: none;\n}\ndiv.FlexFileDropOverlay span#uploadHint {\n  position: relative;\n  top: '+o+";\n}\ndiv.FlexFileDropOverlay span#uploadHint i {\n"+(0==i?"display:none;":"")+"  font-size: 64px;\n  margin-bottom: 30px;\n}</style>";d("head").append(a)}if(0===d(".FlexFileDropOverlay",n.container).length){a='<div class="FlexFileDropOverlay" style="display:none;"><span id="uploadHint"><i class="fa fa-upload" aria-hidden="true"></i><br>'+n.hovertext+"</span></div>";d(n.container).append(a)}var r=d(".FlexFileDropOverlay",n.container);d(n.container).on("dragenter",d.proxy(function(e){e.stopPropagation(),e.preventDefault()},this)),d(n.container).on("dragover",d.proxy(function(e){e.stopPropagation(),e.preventDefault()},this)),d(n.container).on("drop",d.proxy(function(e){e.stopPropagation(),e.preventDefault()},this));var l=new N.Signal;function s(e){var i=new FormData;i.append("file",e);var t=n.url;n.data;d.each(n.data,function(e,t){i.append(e,t)});jQuery.ajax({url:t,type:"POST",contentType:!1,processData:!1,cache:!1,data:i,success:function(e){l.dispatch(e)}})}return d(n.container).append('<input type="file" style="display:none;" class="fileselector" />'),d(n.container).find(".fileselector").on("change",function(e){var t=d(e.currentTarget);0<t.prop("files").length&&s(t.prop("files")[0])}),d(n.container).find(".fileselector").on("click",function(e){e.stopPropagation()}),d(n.container).on("click",function(){d(n.container).find(".fileselector").trigger("click")}),d(n.container).on("dragenter",function(e){r.fadeIn("fast")}),r.on("dragleave",function(e){r.fadeOut("fast")}),r.on("drop",function(e){e.preventDefault(),e.stopPropagation(),r.fadeOut("fast");var t=e.originalEvent.dataTransfer.files;d.each(t,jQuery.proxy(function(e,t){s(t)},this))}),l},showRecordInOverlay:function(e,t){window.open("index.php?module="+e+"&view=Detail&record="+t)},showNotification:function(e,t,i){void 0===t&&(t=!0),void 0===i&&(i={}),N.isVT7()&&(i.message=e,!0===t?app.helper.showSuccessNotification(i):app.helper.showErrorNotification(i))},cacheSet:function(e,t){if(N.isVT7())return app.storage.set(e,t)},cacheGet:function(e,t){if(N.isVT7())return app.storage.get(e,t)},cacheClear:function(e){if(N.isVT7())return app.storage.clear(e)},cacheFlush:function(){if(N.isVT7())return app.storage.flush()},getCurrentDateFormat:function(e){if(e=e.toLowerCase(),!1!==l.get("__CurrentDateFormat_"+e,!1))return l.get("__CurrentDateFormat_"+e,!1);var i,t={};switch(e){case"php":t={yyyy:"%Y",yy:"%y",dd:"%d",mm:"%m"};break;case"moment":t={yyyy:"YYYY",yy:"YY",dd:"DD",mm:"MM"}}return N.isVT7()&&(i=app.getDateFormat()),d.each(t,function(e,t){i=i.replace(e,t)}),l.set("__CurrentDateFormat_"+e,i),i},getCurrentCustomViewId:function(){return!0===N.isVT7()?d('input[name="cvid"]').val():jQuery("#customFilter").val()},selectRecordPopup:function(e,t){var i=jQuery.Deferred(),n=Vtiger_Popup_Js.getInstance();return N.isVT7()?("string"==typeof e&&(e={module:e,view:"Popup",src_module:"Emails",src_field:"testfield"}),void 0!==t&&!0===t&&(e.multi_select=1),app.event.off("FlexUtils.SelectRecord"),app.event.one("FlexUtils.SelectRecord",function(e,t){i.resolveWith(window,[jQuery.parseJSON(t)])}),n.showPopup(e,"FlexUtils.SelectRecord",function(e){})):("string"==typeof e&&(e={module:e,view:"Popup",src_module:"Emails",src_field:"testfield"}),void 0!==t&&!0===t&&(e.multi_select=1),n.show(e,function(e){i.resolveWith(e)})),i.promise()},getCurrentLayout:function(){if(null!==N.layout)return N.layout;var e=jQuery("body").data("skinpath").match(/layouts\/([^/]+)/);return 2<=e.length?(N.layout=e[1],e[1]):N.layout="vlayout"},getQueryParams:function(e){var t=window.document.URL.toString();if(0<t.indexOf("?")){var i=t.split("?")[1].split("&"),n=new Array(i.length),o=new Array(i.length),a=0;for(a=0;a<i.length;a++){var r=i[a].split("=");n[a]=r[0],""!=r[1]?o[a]=decodeURI(r[1]):o[a]="No Value"}for(a=0;a<i.length;a++)if(n[a]==e)return o[a]}return!1},onListChange:function(){if(0==l.get("__onListChangeSignal",!1)){var i=new N.Signal;app.event.on("post.listViewFilter.click",function(e,t){i.dispatch(t)}),l.set("__onListChangeSignal",i)}return l.get("__onListChangeSignal")},onRelatedListChange:function(){if(0==l.get("__onRelatedListChangeSignal",!1)){var i=new N.Signal;app.event.on("post.relatedListLoad.click",function(e,t){i.dispatch(t)}),l.set("__onRelatedListChangeSignal",i)}return l.get("__onRelatedListChangeSignal")},UUIDCounter:1,FieldChangeEventInit:!1,onFieldChange:function(e){void 0===e&&(e="div#page"),void 0===jQuery(e).data("fielduid")&&(jQuery(e).data("fielduid","parentEle"+N.UUIDCounter),N.UUIDCounter++);var r,t=jQuery(e).data("fielduid");if(jQuery(e).addClass("RedooFieldChangeTracker"),0==l.get("__onFieldChangeSignal"+t,!1)){if(r=new N.Signal,N.isVT7())!1===N.FieldChangeEventInit&&"undefined"!=typeof Vtiger_Detail_Js&&(app.event.on(Vtiger_Detail_Js.PostAjaxSaveEvent,function(e,t,i,n){var o=t.closest(".RedooFieldChangeTracker").data("fielduid");r=l.get("__onFieldChangeSignal"+o);var a=t.data("name");-1!==a.indexOf("[]")&&void 0===i[a]&&void 0!==i[a.replace("[]","")]&&(a=a.replace("[]","")),r.dispatch({name:t.data("name"),new:i[a].value},t,i,n)}),N.FieldChangeEventInit=!0);else if("listview"!==N.getViewMode()&&"undefined"!=typeof Vtiger_Detail_Js){var i=Vtiger_Detail_Js.getInstance(),a=i.getContentHolder();a.on(i.fieldUpdatedEvent,function(e,t){var i=d(e.target),n=i.attr("name"),o=i.closest(".RedooFieldChangeTracker").data("fielduid");(r=l.get("__onFieldChangeSignal"+o)).dispatch({name:n,new:t.new},t,{},a)})}l.set("__onFieldChangeSignal"+t,r)}else r=l.get("__onFieldChangeSignal"+t);return l.get("__onFieldChangeSignal"+t)},getRecordLabels:function(e){var t=jQuery.Deferred(),i=[],n=l.get("LabelCache",{});return jQuery.each(e,function(e,t){void 0===n[t]&&i.push(t)}),0<i.length?a.postAction("RecordLabel",{ids:i,dataType:"json"}).then(function(e){jQuery.each(e.result,function(e,t){n[e]=t}),l.set("LabelCache",n),t.resolveWith({},[n])}):t.resolveWith({},[n]),t.promise()},getFieldList:function(t){var i=jQuery.Deferred();return void 0!==r.FieldLoadQueue[t]?r.FieldLoadQueue[t]:(r.FieldLoadQueue[t]=i,void 0!==r.FieldCache[t]?i.resolve(r.FieldCache[t]):a.post("index.php",{module:c,mode:"GetFieldList",action:"RedooUtils",module_name:t},"json").then(function(e){r.FieldCache[t]=e,i.resolve(e.fields)}),i.promise())},filterFieldListByFieldtype:function(e,n){var o={};return jQuery.each(e,function(e,t){var i=[];jQuery.each(t,function(e,t){t.type==n&&i.push(t)}),0<i.length&&(o[e]=i)}),o},fillFieldSelect:function(n,o,e,t){void 0===t&&(t=""),void 0===e&&void 0!==window.moduleName&&(e=window.moduleName),"string"==typeof o&&(o=[o]),N.getFieldList(e,t).then(function(e){""!=t&&(e=N.filterFieldListByFieldtype(e,t));var i="";jQuery.each(e,function(e,t){i+='<optgroup label="'+e+'">',jQuery.each(t,function(e,t){i+='<option value="'+t.name+'" '+(-1!=jQuery.inArray(t.name,o)?'selected="selected"':"")+">"+t.label+"</option>"}),i+="</optgroup>",jQuery("#"+n).html(i),jQuery("#"+n).hasClass("select2")&&jQuery("#"+n).select2("val",o),jQuery("#"+n).trigger("FieldsLoaded")})})},_getDefaultParentEle:function(){return"div#page"},getMainModule:function(e){return N.isVT7()?N._getMainModuleVT7(e):N._getMainModuleVT6(e)},_getMainModuleVT6:function(e){void 0===e&&(e=N._getDefaultParentEle());var t=N.getViewMode(e);if("detailview"==t||"summaryview"==t)return d("#module",e).val();if("editview"==t||"quickcreate"==t)return d('[name="module"]',e).val();if("listview"==t)return d("#module",e).val();if("relatedview"==t){if(0<d('[name="relatedModuleName"]',e).length)return d('[name="relatedModuleName"]',e).val();if(0<d("#module",e).length)return d("#module",e).val()}var i=N.getQueryParams("module");return!1!==i?i:""},_getMainModuleVT7:function(e){void 0===e&&(e=N._getDefaultParentEle());var t=N.getViewMode(e);if(void 0!==d(e).data("forcerecordmodule"))return d(e).data("forcerecordmodule");if("#overlayPageContent.in"!=e&&0<d("#overlayPageContent.in").length)return N._getMainModuleVT7("#overlayPageContent.in");if("undefined"!=typeof _META&&("detailview"==t||"summaryview"==t||"commentview"==t||"historyview"==t||"editview"==t||"listview"==t)&&0==d(e).hasClass("modal"))return _META.module;if("detailview"==t||"summaryview"==t)return d("#module",e).val();if("editview"==t||"quickcreate"==t)return 0<d("#module",e).length?d("#module",e).val():d('[name="module"]',e).val();if("listview"==t)return d("#module",e).val();if("relatedview"==t){if(0<d('[name="relatedModuleName"]',e).length)return d('[name="relatedModuleName"]',e).val();if(0<d("#module",e).length)return d("#module",e).val()}var i=N.getQueryParams("module");return!1!==i?i:""},getMainRecordId:function(){var e="div#page";void 0===e&&(e=N._getDefaultParentEle());N.getViewMode(e);return d("#recordId",e).val()},getRecordIds:function(e){void 0===e&&(e=N._getDefaultParentEle());var i=[],t=N.getViewMode(e);return"detailview"==t||"summaryview"==t?i.push(d("#recordId",e).val()):"quickcreate"==t||("editview"==t?i.push(d('[name="record"]').val()):"listview"==t?d(".listViewEntries").each(function(e,t){i.push(d(t).data("id"))}):"relatedview"==t&&d(".listViewEntries").each(function(e,t){i.push(d(t).data("id"))})),i},onQuickCreate:function(i){jQuery('.quickCreateModule, .addButton[data-url*="QuickCreate"]').on("click",function e(){if(0==jQuery(".quickCreateContent",".modelContainer").length)window.setTimeout(e,200);else{var t=jQuery(".modelContainer");console.log("onQuickCreate Done"),i(t.find('input[name="module"]').val(),t)}})},getViewMode:function(e){return N.isVT7()?N._getViewModeVT7(e):N._getViewModeVT6(e)},_getViewModeVT6:function(e){void 0===e&&(e=N._getDefaultParentEle());var t=d("#view",e);return r.viewMode=!1,0<t.length&&"List"==t[0].value&&(r.viewMode="listview"),0<d(".detailview-table",e).length?r.viewMode="detailview":0<d(".summaryView",e).length?r.viewMode="summaryview":0<d(".recordEditView",e).length&&(0==d(".quickCreateContent",e).length?r.viewMode="editview":r.viewMode="quickcreate"),0<d(".relatedContents",e).length&&(r.viewMode="relatedview",0<d("td[data-field-type]",e).length?r.popUp=!1:r.popUp=!0),!1===r.viewMode&&0<d("#view",e).length&&"Detail"==d("#view",e).val()&&(r.viewMode="detailview"),r.viewMode},_getViewModeVT7:function(e){return void 0===e&&(e=N._getDefaultParentEle()),r.viewMode=!1,0<d(".detailview-table",e).length?r.viewMode="detailview":0<d(".summaryView",e).length?r.viewMode="summaryview":0<d(".recordEditView",e).length?0==d(".quickCreateContent",e).length?r.viewMode="editview":r.viewMode="quickcreate":0<d(".commentsRelatedContainer",e).length?r.viewMode="commentview":0<d(".HistoryContainer",e).length?r.viewMode="historyview":0<jQuery(".relatedContainer",e).find(".relatedModuleName").length?r.viewMode="relatedview":0<jQuery(".listViewContentHeader",e).length&&"undefined"!=typeof _META&&"List"==_META.view&&(r.viewMode="listview"),!1===r.viewMode&&0<d("#view",e).length&&"Detail"==d("#view",e).val()&&(r.viewMode="detailview"),r.viewMode},getContentMaxHeight:function(){if(0!=N.isVT7())return jQuery("#page").height();switch(N.getCurrentLayout()){case"begbie":return jQuery(".mainContainer").height();default:return jQuery("#leftPanel").height()-50}},getContentMaxWidth:function(){if(0==N.isVT7())return jQuery("#rightPanel").width()},hideModalBox:function(){!0===N.isVT7()?app.helper.hideModal():app.hideModalWindow()},showModalBox:function(e,t){var i=jQuery.Deferred();!1===N.isVT7()?app.showModalWindow(e,function(e){i.resolveWith(window,e)}):(void 0===t&&(t={close:function(){}}),void 0===t.close&&(t.close=function(){}),l.set("__onModalClose",t.close),0<jQuery(".myModal .modal-dialog").length&&0<jQuery(".modal.in").length?(jQuery(".myModal .modal-dialog").replaceWith(e),i.resolveWith(window,jQuery(".modal.myModal")[0])):app.helper.showModal(e,{cb:function(e){i.resolveWith(window,e)}}).off("hidden.bs.modal").on("hidden.bs.modal",function(){t.close()}));return i.promise()},showContentOverlay:function(e,t){if(N.isVT7())return app.helper.loadPageContentOverlay(e,t);0==d("#overlayPageContent").length&&d("body").append("<div id='overlayPageContent' style=\"margin:0;\" class='fade modal content-area overlayPageContent overlay-container-60' tabindex='-1' role='dialog' aria-hidden='true'>\n        <div class=\"data\">\n        </div>\n        <div class=\"modal-dialog\">\n        </div>\n    </div>");var i=new jQuery.Deferred;t=jQuery.extend({backdrop:!0,show:!0,keyboard:!1},t);var n=d("#overlayPageContent");n.addClass("full-width");var o=!1;return n.hasClass("in")&&(o=!0),n.one("shown.bs.modal",function(){i.resolve(d("#overlayPageContent"))}),n.one("hidden.bs.modal",function(){n.find(".data").html("")}),n.find(".data").html(e),n.modal(t),o&&i.resolve(jQuery("#overlayPageContent")),i.promise()},hideContentOverlay:function(){if(!N.isVT7()){var e=new jQuery.Deferred,t=d("#overlayPageContent");return t.one("hidden.bs.modal",function(){t.find(".data").html(""),e.resolve()}),d("#overlayPageContent").modal("hide"),e.promise()}app.helper.hidePageContentOverlay()},setFieldValue:function(e,t,i){void 0!==i&&null!=i||(i=N._getDefaultParentEle());var n=N.getFieldElement(e,i,!0);if(!1!==n)switch(n.prop("tagName")){case"TEXTAREA":n.val(t);break;case"INPUT":switch(n.attr("type")){case"text":n.hasClass("dateField")?N.isVT7()?n.datepicker("update",t):""!==t?n.val(t).DatePickerSetDate(t,!0):n.val(t).DatePickerClear():n.val(t);break;case"hidden":if(n.hasClass("sourceField")){var o=Vtiger_Edit_Js.getInstance(),a=n.closest("td");""!=t.id?o.setReferenceFieldValue(a,{id:t.id,name:t.label}):d(".clearReferenceSelection",a).trigger("click")}}break;case"SELECT":n.val(t),!1===N.isVT7()?n.hasClass("chzn-select")&&n.trigger("liszt:updated"):n.hasClass("select2")&&n.trigger("change.select2")}},layoutDependValue:function(e,t){var i=N.getCurrentLayout();return void 0!==e[i]?e[i]:t},getFieldElement:function(e,t,i){if(void 0!==t&&null!=t||(t=N._getDefaultParentEle()),void 0===i&&(i=!1),"object"==typeof e)return e;var n=!1;if("detailview"==N.getViewMode(t))0<d("#"+N.getMainModule(t)+"_detailView_fieldValue_"+e,t).length||0<d("#Events_detailView_fieldValue_"+e,t).length?(n=d("#"+N.getMainModule(t)+"_detailView_fieldValue_"+e),"Calendar"==N.getMainModule(t)&&0==n.length&&(n=d("#Events_detailView_fieldValue_"+e,t))):0<d("#_detailView_fieldValue_"+e,t).length&&(n=d("#_detailView_fieldValue_"+e,t));else if("summaryview"==N.getViewMode(t)){var o;if(0==(o=N.isVT7()?jQuery('[data-name="'+e+'"]',this.parentEle):jQuery('[name="'+e+'"]',this.parentEle)).length)return!1;n=d(o[0]).closest(N.layoutDependValue({vlayout:"td",v7:".row",begbie:"div.mycdivfield",responsive:".row"},"td"))}else if("editview"==N.getViewMode(t)||"quickcreate"==N.getViewMode(t)){var a=d('[name="'+e+'"]',t);if(0==a.length)return!1;if(1==i)return a;n=d(a[0]).closest(N.layoutDependValue({vlayout:".fieldValue",v7:".fieldValue",begbie:"div.mycdivfield",responsive:".fieldValue"},".fieldValue"))}else if("listview"==N.getViewMode(t)){if(!1===N.listViewFields&&(N.listViewFields=N.getListFields(t)),null===N.currentLVRow)return!1;if(void 0===N.listViewFields[e])return!1;n=0<=N.listViewFields[e]?d(d("td.listViewEntryValue",N.currentLVRow)[N.listViewFields[e]]):d(d("td.listViewEntryValue",N.currentLVRow)[-1*Number(N.listViewFields[e]+100)])}else"relatedview"==N.getViewMode(t)&&(!1===N.listViewFields&&(N.listViewFields=N.getListFields(t)),n=0<d("td[data-field-type]",N.currentLVRow).length?d(d("td[data-field-type]",N.currentLVRow)[N.listViewFields[e]]):d(d("td.listViewEntryValue",N.currentLVRow)[N.listViewFields[e]]));return n},refreshContent:function(e,t,i,n){void 0===i&&(n=!1),void 0===i&&(i={}),void 0===t&&(t=!1),i.module=c,i.view=e,!0===t&&(i.parent="Settings");var o=jQuery.Deferred();return N.isVT7()?a.request(i).then(function(e){var t;0<jQuery(".settingsPageDiv").length?(jQuery(".settingsPageDiv").html(e),t=jQuery(".settingsPageDiv")):(jQuery(".ContentReplacement").html(e),t=jQuery(".ContentReplacement")),!0===n&&jQuery(".select2",t).select2(),o.resolve()}):a.request(i).then(function(e){jQuery(jQuery(".contentsDiv")[0]).html(e),!0===n&&jQuery(jQuery(".contentsDiv")[0]).find(".select2").select2(),o.resolve()}),o.promise()},getListFields:function(e){var t;t=N.isVT7()?jQuery(".listview-table .listViewContentHeaderValues",e):jQuery(".listViewEntriesTable .listViewHeaderValues",e);var i={};for(var n in t)if(t.hasOwnProperty(n)&&jQuery.isNumeric(n)){var o=t[n];null==jQuery(o).data("columnname")?i[jQuery(o).data("fieldname")]=n:i[jQuery(o).data("columnname")]=n}return i},loadStyles:function(e,t){"string"==typeof e&&(e=[e]);var i=jQuery.Deferred();return void 0===t&&(t=!1),d.when.apply(d,d.map(e,function(e){return t&&(e+="?_ts="+(new Date).getTime()),d.get(e,function(){d("<link>",{rel:"stylesheet",type:"text/css",href:e}).appendTo("head")})})).then(function(){i.resolve()}),i.promise()},loadScript:function(e,t){var i=jQuery.Deferred();return void 0===l.loadedScript&&(l.loadedScript={}),void 0!==l.loadedScript[e]?(i.resolve(),i):(t=jQuery.extend(t||{},{dataType:"script",cache:!0,url:e}),jQuery.ajax(t))}},r={FieldCache:{},FieldLoadQueue:{},viewMode:!1,popUp:!1};function u(e,t,i,n,o){this._listener=t,this._isOnce=i,this.context=n,this._signal=e,this._priority=o||0}function f(e,t){if("function"!=typeof e)throw Error("listener is a required param of {fn}() and should be a Function.".replace("{fn}",t))}function t(){this._bindings=[],this._prevParams=null;var e=this;this.dispatch=function(){t.prototype.dispatch.apply(e,arguments)}}"undefined"!=typeof console&&void 0!==console.log&&console.log("Initialize FlexUtils "+c+" V2.4.2"),void 0===window.FlexStore&&(window.FlexStore={}),void 0===window.RedooStore&&(window.RedooStore={}),window.RedooStore[c]=window.FlexStore[c]={Ajax:a,Utils:N,Cache:l,Form:s,Fields:n,Translate:o},void 0===window.FlexAjax&&(window.FlexAjax=function(e){return void 0!==window.FlexStore[e]?window.FlexStore[e].Ajax:void 0!==window.RedooStore[e]?window.RedooStore[e].Ajax:void console.error("FlexAjax "+e+" Scope not found")}),void 0===window.RedooAjax&&(window.RedooAjax=window.FlexAjax),void 0===window.FlexFields&&(window.FlexFields=function(e){if(void 0!==window.RedooStore[e])return window.RedooStore[e].Fields;console.error("FlexFields "+e+" Scope not found")}),void 0===window.FlexUtils&&(window.FlexUtils=function(e){return void 0!==window.FlexStore[e]?window.FlexStore[e].Utils:void 0!==window.RedooStore[e]?window.RedooStore[e].Utils:void console.error("FlexUtils "+e+" Scope not found")}),void 0===window.RedooUtils&&(window.RedooUtils=window.FlexUtils),void 0===window.FlexCache&&(window.FlexCache=function(e){return void 0!==window.RedooStore[e]?window.RedooStore[e].Cache:void 0!==window.FlexStore[e]?window.FlexStore[e].Cache:void console.error("FlexCache "+e+" Scope not found")}),void 0===window.RedooCache&&(window.RedooCache=window.FlexCache),void 0===window.FlexForm&&(window.FlexForm=function(e){if(void 0!==window.FlexStore[e])return window.FlexStore[e].Form;console.error("FlexForm "+e+" Scope not found")}),void 0===window.FlexTranslate&&(window.FlexTranslate=function(e){if(void 0!==window.FlexStore[e])return window.FlexStore[e].Translate;console.error("FlexTranslate "+e+" Scope not found")}),void 0===window.FlexEvents&&(window.FlexEvents=d({})),t.prototype={VERSION:"1.0.0",memorize:!(u.prototype={active:!0,params:null,execute:function(e){var t;return this.active&&this._listener&&(e=this.params?this.params.concat(e):e,t=this._listener.apply(this.context,e),this._isOnce&&this.detach()),t},detach:function(){return this.isBound()?this._signal.remove(this._listener,this.context):null},isBound:function(){return!!this._signal&&!!this._listener},isOnce:function(){return this._isOnce},getListener:function(){return this._listener},getSignal:function(){return this._signal},_destroy:function(){delete this._signal,delete this._listener,delete this.context},toString:function(){return"[SignalBinding isOnce:"+this._isOnce+", isBound:"+this.isBound()+", active:"+this.active+"]"}}),_shouldPropagate:!0,active:!0,_registerListener:function(e,t,i,n){var o=this._indexOfListener(e,i);if(-1!==o){if((e=this._bindings[o]).isOnce()!==t)throw Error("You cannot add"+(t?"":"Once")+"() then add"+(t?"Once":"")+"() the same listener without removing the relationship first.")}else e=new u(this,e,t,i,n),this._addBinding(e);return this.memorize&&this._prevParams&&e.execute(this._prevParams),e},_addBinding:function(e){for(var t=this._bindings.length;--t,this._bindings[t]&&e._priority<=this._bindings[t]._priority;);this._bindings.splice(t+1,0,e)},_indexOfListener:function(e,t){for(var i,n=this._bindings.length;n--;)if((i=this._bindings[n])._listener===e&&i.context===t)return n;return-1},has:function(e,t){return-1!==this._indexOfListener(e,t)},add:function(e,t,i){return f(e,"add"),this._registerListener(e,!1,t,i)},addOnce:function(e,t,i){return f(e,"addOnce"),this._registerListener(e,!0,t,i)},remove:function(e,t){f(e,"remove");var i=this._indexOfListener(e,t);return-1!==i&&(this._bindings[i]._destroy(),this._bindings.splice(i,1)),e},removeAll:function(){for(var e=this._bindings.length;e--;)this._bindings[e]._destroy();this._bindings.length=0},getNumListeners:function(){return this._bindings.length},halt:function(){this._shouldPropagate=!1},dispatch:function(e){if(this.active){var t,i=Array.prototype.slice.call(arguments),n=this._bindings.length;if(this.memorize&&(this._prevParams=i),n)for(t=this._bindings.slice(),this._shouldPropagate=!0;t[--n]&&this._shouldPropagate&&!1!==t[n].execute(i););}},forget:function(){this._prevParams=null},dispose:function(){this.removeAll(),delete this._bindings,delete this._prevParams},toString:function(){return"[Signal active:"+this.active+" numListeners:"+this.getNumListeners()+"]"}};var e=t;e.Signal=t,N.Signal=e.Signal,function(){function e(Q){function i(e,o){var t,i,n,a,r,l,s,d,c,u=e==window,f=o&&void 0!==o.message?o.message:void 0;if(!(o=Q.extend({},N.blockUI.defaults,o||{})).ignoreIfBlocked||!Q(e).data("blockUI.isBlocked")){if(o.overlayCSS=Q.extend({},N.blockUI.defaults.overlayCSS,o.overlayCSS||{}),t=Q.extend({},N.blockUI.defaults.css,o.css||{}),o.onOverlayClick&&(o.overlayCSS.cursor="pointer"),i=Q.extend({},N.blockUI.defaults.themedCSS,o.themedCSS||{}),f=void 0===f?o.message:f,u&&A&&U(window,{fadeOut:0}),f&&"string"!=typeof f&&(f.parentNode||f.jquery)){var h=f.jquery?f[0]:f,p={};Q(e).data("blockUI.history",p),p.el=h,p.parent=h.parentNode,p.display=h.style.display,p.position=h.style.position,p.parent&&p.parent.removeChild(h)}Q(e).data("blockUI.onUnblock",o.onUnblock);var v,g,y,m,w=o.baseZ;v=O||o.forceIframe?Q('<iframe class="blockUI" style="z-index:'+w+++';display:none;border:none;margin:0;padding:0;position:absolute;width:100%;height:100%;top:0;left:0" src="'+o.iframeSrc+'"></iframe>'):Q('<div class="blockUI" style="display:none"></div>'),g=o.theme?Q('<div class="blockUI blockOverlay ui-widget-overlay" style="z-index:'+w+++';display:none"></div>'):Q('<div class="blockUI blockOverlay" style="z-index:'+w+++';display:none;border:none;margin:0;padding:0;width:100%;height:100%;top:0;left:0"></div>'),o.theme&&u?(m='<div class="blockUI '+o.blockMsgClass+' blockPage ui-dialog ui-widget ui-corner-all" style="z-index:'+(w+10)+';display:none;position:fixed">',o.title&&(m+='<div class="ui-widget-header ui-dialog-titlebar ui-corner-all blockTitle">'+(o.title||"&nbsp;")+"</div>"),m+='<div class="ui-widget-content ui-dialog-content"></div>',m+="</div>"):o.theme?(m='<div class="blockUI '+o.blockMsgClass+' blockElement ui-dialog ui-widget ui-corner-all" style="z-index:'+(w+10)+';display:none;position:absolute">',o.title&&(m+='<div class="ui-widget-header ui-dialog-titlebar ui-corner-all blockTitle">'+(o.title||"&nbsp;")+"</div>"),m+='<div class="ui-widget-content ui-dialog-content"></div>',m+="</div>"):m=u?'<div class="blockUI '+o.blockMsgClass+' blockPage" style="z-index:'+(w+10)+';display:none;position:fixed"></div>':'<div class="blockUI '+o.blockMsgClass+' blockElement" style="z-index:'+(w+10)+';display:none;position:absolute"></div>',y=Q(m),f&&(o.theme?(y.css(i),y.addClass("ui-widget-content")):y.css(t)),o.theme||g.css(o.overlayCSS),g.css("position",u?"fixed":"absolute"),(O||o.forceIframe)&&v.css("opacity",0);var b=[v,g,y],x=Q(u?"body":e);Q.each(b,function(){this.appendTo(x)}),o.theme&&o.draggable&&Q.fn.draggable&&y.draggable({handle:".ui-dialog-titlebar",cancel:"li"});var _=L&&(!Q.support.boxModel||0<Q("object,embed",u?null:e).length);if(P||_){if(u&&o.allowBodyStretch&&Q.support.boxModel&&Q("html,body").css("height","100%"),(P||!Q.support.boxModel)&&!u)var F=R(e,"borderTopWidth"),C=R(e,"borderLeftWidth"),k=F?"(0 - "+F+")":0,V=C?"(0 - "+C+")":0;Q.each(b,function(e,t){var i=t[0].style;if(i.position="absolute",e<2)u?i.setExpression("height","Math.max(document.body.scrollHeight, document.body.offsetHeight) - (jQuery.support.boxModel?0:"+o.quirksmodeOffsetHack+') + "px"'):i.setExpression("height",'this.parentNode.offsetHeight + "px"'),u?i.setExpression("width",'jQuery.support.boxModel && document.documentElement.clientWidth || document.body.clientWidth + "px"'):i.setExpression("width",'this.parentNode.offsetWidth + "px"'),V&&i.setExpression("left",V),k&&i.setExpression("top",k);else if(o.centerY)u&&i.setExpression("top",'(document.documentElement.clientHeight || document.body.clientHeight) / 2 - (this.offsetHeight / 2) + (blah = document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop) + "px"'),i.marginTop=0;else if(!o.centerY&&u){var n="((document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop) + "+(o.css&&o.css.top?parseInt(o.css.top,10):0)+') + "px"';i.setExpression("top",n)}})}if(f&&(o.theme?y.find(".ui-widget-content").append(f):y.append(f),(f.jquery||f.nodeType)&&Q(f).show()),(O||o.forceIframe)&&o.showOverlay&&v.show(),o.fadeIn){var S=o.onBlock?o.onBlock:D,j=o.showOverlay&&!f?S:D,M=f?S:D;o.showOverlay&&g._fadeIn(o.fadeIn,j),f&&y._fadeIn(o.fadeIn,M)}else o.showOverlay&&g.show(),f&&y.show(),o.onBlock&&o.onBlock.bind(y)();if(T(1,e,o),u?(A=y[0],B=Q(o.focusableElements,A),o.focusInput&&setTimeout(E,20)):(n=y[0],a=o.centerX,r=o.centerY,l=n.parentNode,s=n.style,d=(l.offsetWidth-n.offsetWidth)/2-R(l,"borderLeftWidth"),c=(l.offsetHeight-n.offsetHeight)/2-R(l,"borderTopWidth"),a&&(s.left=0<d?d+"px":"0"),r&&(s.top=0<c?c+"px":"0")),o.timeout){var I=setTimeout(function(){u?Q.unblockUI(o):Q(e).unblock(o)},o.timeout);Q(e).data("blockUI.timeout",I)}}}function U(e,t){var i,n,o=e==window,a=Q(e),r=a.data("blockUI.history"),l=a.data("blockUI.timeout");l&&(clearTimeout(l),a.removeData("blockUI.timeout")),t=Q.extend({},N.blockUI.defaults,t||{}),T(0,e,t),null===t.onUnblock&&(t.onUnblock=a.data("blockUI.onUnblock"),a.removeData("blockUI.onUnblock")),n=o?Q("body").children().filter(".blockUI").add("body > .blockUI"):a.find(">.blockUI"),t.cursorReset&&(1<n.length&&(n[1].style.cursor=t.cursorReset),2<n.length&&(n[2].style.cursor=t.cursorReset)),o&&(A=B=null),t.fadeOut?(i=n.length,n.stop().fadeOut(t.fadeOut,function(){0==--i&&s(n,r,t,e)})):s(n,r,t,e)}function s(e,t,i,n){var o=Q(n);if(!o.data("blockUI.isBlocked")){e.each(function(){this.parentNode&&this.parentNode.removeChild(this)}),t&&t.el&&(t.el.style.display=t.display,t.el.style.position=t.position,t.el.style.cursor="default",t.parent&&t.parent.appendChild(t.el),o.removeData("blockUI.history")),o.data("blockUI.static")&&o.css("position","static"),"function"==typeof i.onUnblock&&i.onUnblock(n,i);var a=Q(document.body),r=a.width(),l=a[0].style.width;a.width(r-1).width(r),a[0].style.width=l}}function T(e,t,i){var n=t==window,o=Q(t);if((e||(!n||A)&&(n||o.data("blockUI.isBlocked")))&&(o.data("blockUI.isBlocked",e),n&&i.bindEvents&&(!e||i.showOverlay))){var a="mousedown mouseup keydown keypress keyup touchstart touchend touchmove";e?Q(document).bind(a,i,r):Q(document).unbind(a,r)}}function r(e){if("keydown"===e.type&&e.keyCode&&9==e.keyCode&&A&&e.data.constrainTabKey){var t=B,i=!e.shiftKey&&e.target===t[t.length-1],n=e.shiftKey&&e.target===t[0];if(i||n)return setTimeout(function(){E(n)},10),!1}var o=e.data,a=Q(e.target);return a.hasClass("blockOverlay")&&o.onOverlayClick&&o.onOverlayClick(e),0<a.parents("div."+o.blockMsgClass).length||0===a.parents().children().filter("div.blockUI").length}function E(e){if(B){var t=B[!0===e?B.length-1:0];t&&t.focus()}}function R(e,t){return parseInt(Q.css(e,t),10)||0}Q.fn._fadeIn=Q.fn.fadeIn;var D=Q.noop||function(){},O=/MSIE/.test(navigator.userAgent),P=/MSIE 6.0/.test(navigator.userAgent)&&!/MSIE 8.0/.test(navigator.userAgent);document.documentMode;var L=Q.isFunction(document.createElement("div").style.setExpression);N.blockUI=function(e){i(window,e)},N.unblockUI=function(e){U(window,e)},Q.growlUI=function(e,t,i,n){var o=Q('<div class="growlUI"></div>');e&&o.append("<h1>"+e+"</h1>"),t&&o.append("<h2>"+t+"</h2>"),void 0===i&&(i=3e3);var a=function(e){e=e||{},Q.blockUI({message:o,fadeIn:void 0!==e.fadeIn?e.fadeIn:700,fadeOut:void 0!==e.fadeOut?e.fadeOut:1e3,timeout:void 0!==e.timeout?e.timeout:i,centerY:!1,showOverlay:!1,onUnblock:n,css:N.blockUI.defaults.growlCSS})};a(),o.css("opacity"),o.mouseover(function(){a({fadeIn:0,timeout:3e4});var e=Q(".blockMsg");e.stop(),e.fadeTo(300,1)}).mouseout(function(){Q(".blockMsg").fadeOut(1e3)})},Q.fn.block=function(e){if(this[0]===window)return Q.blockUI(e),this;var t=Q.extend({},N.blockUI.defaults,e||{});return this.each(function(){var e=Q(this);t.ignoreIfBlocked&&e.data("blockUI.isBlocked")||e.unblock({fadeOut:0})}),this.each(function(){"static"==Q.css(this,"position")&&(this.style.position="relative",Q(this).data("blockUI.static",!0)),this.style.zoom=1,i(this,e)})},Q.fn.unblock=function(e){return this[0]===window?(Q.unblockUI(e),this):this.each(function(){U(this,e)})},N.blockUI.version=2.7,N.blockUI.defaults={message:"<h1>Please wait...</h1>",title:null,draggable:!0,theme:!1,css:{padding:0,margin:0,width:"30%",top:"40%",left:"35%",textAlign:"center",color:"#000",border:"3px solid #aaa",backgroundColor:"#fff",cursor:"wait"},themedCSS:{width:"30%",top:"40%",left:"35%"},overlayCSS:{backgroundColor:"#000",opacity:.6,cursor:"wait"},cursorReset:"default",growlCSS:{width:"350px",top:"10px",left:"",right:"10px",border:"none",padding:"5px",opacity:.6,cursor:"default",color:"#fff",backgroundColor:"#000","-webkit-border-radius":"10px","-moz-border-radius":"10px","border-radius":"10px"},iframeSrc:/^https/i.test(window.location.href||"")?"javascript:false":"about:blank",forceIframe:!1,baseZ:2e3,centerX:!0,centerY:!0,allowBodyStretch:!0,bindEvents:!0,constrainTabKey:!0,fadeIn:200,fadeOut:400,timeout:0,showOverlay:!0,focusInput:!0,focusableElements:":input:enabled:visible",onBlock:null,onUnblock:null,onOverlayClick:null,quirksmodeOffsetHack:4,blockMsgClass:"blockMsg",ignoreIfBlocked:!1};var A=null,B=[]}"function"==typeof define&&define.amd&&define.amd.jQuery?define(["jquery"],e):e(jQuery)}()}(jQuery);
;jQuery(function() {
    var workflowFrontendActions = new Workflow();
    workflowFrontendActions.checkFrontendActions('init');

    var listenCommentWidget = false;

    if(typeof Vtiger_Detail_Js != 'undefined' && jQuery('#recordId').length > 0) {
        var thisInstance = Vtiger_Detail_Js.getInstance();
        var detailContentsHolder = thisInstance.getContentHolder();

        RedooUtils('Workflow2').onFieldChange('div#page').add(function() {
            workflowFrontendActions.checkFrontendActions('edit');
        });
/*
        detailContentsHolder.on(thisInstance.fieldUpdatedEvent, function(e, params){
            var fieldName = jQuery(e.target).attr("name");
            workflowFrontendActions.checkFrontendActions('edit');
        });
*/
        detailContentsHolder.on(thisInstance.widgetPostLoad, function(e, p) {
            if(listenCommentWidget == false) return;

            if(jQuery('.commentContainer', e.target).length > 0) {
                listenCommentWidget = false;
                workflowFrontendActions.checkFrontendActions('edit');
            }
        });

        detailContentsHolder.on('click','.detailViewSaveComment', function(e){
            listenCommentWidget = true;
        });
    }
    /*
    // TODO: UPDATE for VT7
    if(typeof Vtiger_Header_Js != 'undefined') {
        var thisInstance = Vtiger_Header_Js.getInstance();
        thisInstance.registerQuickCreateCallBack(function(e, b, c) {

            var workflowFrontendActions = new Workflow();
            workflowFrontendActions.checkFrontendActions('both', e.data.result['_recordId']);
        });
    }
    */


    jQuery(window).on('workflow.detail.sidebar.ready', function() {
        jQuery('#WorkflowDesignerErrorLoaded').hide();
        var workflow = new Workflow();
        workflowObj = workflow;

    }).on('workflow.list.sidebar.ready', function() {
        jQuery('#WorkflowDesignerErrorLoaded').hide();
        var workflow = new Workflow();
        workflowObj = workflow;

    });
    /* jQuery(window).on('workflow.list.sidebar.ready', function() {
     console.log('List-Sidebar ready');
     }); */

    if(typeof Vtiger_Edit_Js != 'undefined') {
        jQuery('body').on(Vtiger_Edit_Js.referenceSelectionEvent, function (e, b, c, d) {
            var workflowObj = new Workflow();
            workflowObj.setBackgroundMode(true);

            workflowObj.addExtraEnvironment('source_module', jQuery('[name="module"]').val())
            workflowObj.addExtraEnvironment('source_record', jQuery('[name="record"]').val());

            workflowObj.execute('WF_REFERENCE', b.record, function() {
                workflowObj.checkFrontendActions('edit');
            });
        });
    }

    var viewMode = Workflow2Frontend.getViewMode(jQuery('div#page'));

//    if (viewMode == 'detailview' || viewMode == 'summaryview') {
        Workflow2Frontend.TopbuttonHandler(jQuery('div#page'));
//    }

    if (jQuery('#module', jQuery('div#page')).length > 0) {
        if (jQuery('#module', jQuery('div#page')).val() == 'Campaigns' && typeof Campaigns_Detail_Js != 'undefined') {

            // Enable ViewCheck
            if (
                typeof WFDFrontendConfig !== 'undefined' &&
                typeof WFDFrontendConfig['relatedbtn'] !== 'undefined'
            ) {
                var viewMode = Workflow2Frontend.getViewMode(jQuery('div#page'));

                if (viewMode == 'relatedview') {
                    Workflow2Frontend.RelatedListHandler(jQuery('div#page'));
                }

                jQuery(document).on('postajaxready', function (e) {
                    var viewMode = Workflow2Frontend.getViewMode(jQuery('div#page'));

                    if (viewMode == 'relatedview') {
                        Workflow2Frontend.RelatedListHandler(jQuery('div#page'));
                    }
                });

                if (jQuery('#module', jQuery('div#page')).length > 0) {
                    if (jQuery('#module', jQuery('div#page')).val() == 'Campaigns' && typeof Campaigns_Detail_Js != 'undefined') {
                        thisInstance = Campaigns_Detail_Js.getInstance();
                        var detailContentsHolder = thisInstance.getContentHolder();
                        var detailContainer = detailContentsHolder.closest('div.detailViewInfo');
                        jQuery('.related', detailContainer).on('click', 'li', function (e, urlAttributes) {
                            window.setTimeout(function () {
                                var viewMode = Workflow2Frontend.getViewMode(jQuery('div#page'));

                                if (viewMode == 'relatedview') {
                                    Workflow2Frontend.RelatedListHandler(jQuery('div#page'));
                                }
                            }, 1000);
                        });
                    }
                }
            }
        }
    }

    RedooUtils('Workflow2').onRelatedListChange().add(function() {
        var objWorkflow = new Workflow();
        objWorkflow.showInlineButtons();
    });

    jQuery('.quickCreateModule, .addButton[data-url*="QuickCreate"]').on('click', function __check() {
        if(jQuery('.quickCreateContent','.modelContainer').length == 0) {
            window.setTimeout(__check, 200);
        } else  {
            jQuery('#globalmodal form[name="QuickCreate"] .btn[type="submit"]').on('click', function() {
                WorkflowExecution
            });
        }
    });

});;jQuery('#WorkflowDesignerErrorLoaded').hide();
var WFUserIsAdmin;
window.Workflow = function () {
    this.crmid = 0;
    this._allowParallel = 0;
    this._workflowid = null;
    this._workflowTrigger = null;

    this._currentExecId = null;

    this.ExecutionCallback = null;

    this._requestValues = {};
    this._requestValuesKey = null;
    this._backgroundMode = false;
    this._extraEnvironment = {};
    this._ListViewMode = false;

    /**
     *
     * @param workflow WorkflowID or Trigger
     * @param crmid Record to use
     */
    this.execute = function(workflow, crmid, callback, ignoreViewMode) {
        if(typeof ignoreViewMode === 'undefined') ignoreViewMode = false;

        this.crmid = crmid;

        if(FlexUtils('Workflow2').getViewMode() == 'listview' && crmid == 0 && ignoreViewMode === false) {
            runListViewWorkflow(workflow);
        } else {
            if (jQuery.isNumeric(workflow)) {
                this._executeById(workflow, callback);
            } else {
                this._executeByTrigger(workflow, callback);
            }
        }
    };

    this.setListView = function(value) {
        this._ListViewMode = (value == true);
    };

    this.checkFrontendActions = function(step, crmid) {
        WorkflowRecordMessages = [];
        if(typeof crmid == 'undefined') {
            var crmid = 0;

            var recordId;
            if (Workflow2Frontend.getViewMode(jQuery('div#page')) == 'detailview' || Workflow2Frontend.getViewMode(jQuery('div#page')) == 'summaryview') {
                recordId = $('#recordId', jQuery('div#page')).val();
            } else if (Workflow2Frontend.getViewMode(jQuery('div#page')) == 'quickcreate') {
                recordId = 0;
            } else if (Workflow2Frontend.getViewMode(jQuery('div#page')) == 'editview') {
                recordId = jQuery('[name="record"]').val();
            } else if (Workflow2Frontend.getViewMode(jQuery('div#page')) == 'listview') {
                recordId = 0;
            } else if (Workflow2Frontend.getViewMode(jQuery('div#page')) == 'relatedview') {
                recordId = 0;
            } else if (Workflow2Frontend.getViewMode(jQuery('div#page')) == 'composemail') {
                var ids = jQuery('[name="selected_ids"]').val();
                recordId = jQuery.parseJSON(ids)[0];
            } else {
                recordId = 0;
            }
        } else {
            recordId = Number(crmid);
        }

        if(recordId == 0 || typeof recordId == 'undefined') {
            if( $('#recordId').length > 0) {
                var recordId =  $('#recordId').val();
            }
        }

        /*if(typeof recordId == "undefined" || recordId == 0) {
            return;
        }*/

        if(typeof _META != 'undefined') {
            var moduleName = _META.module;
        } else {
            var moduleName = RedooUtils('Workflow2').getMainModule('div#page');
        }

        RedooAjax('Workflow2').postAction('CheckFrontendActions', {'crmid':recordId, 'step':step, src_module: moduleName}, 'json').then(jQuery.proxy(function(response) {
            if(response.length == 0) return;

            if(typeof response.buttons != 'undefined' && response.buttons.length > 0) {
                this.generateInlineButtons(response.buttons);
            }

            if(typeof response.detailviewtop != 'undefined' && response.detailviewtop.length > 0) {
                this.generateDetailViewTopButtons(response.detailviewtop);
            }

            if(typeof response['btn-list'] == 'object') {
                this.generateBtnTrigger(response['btn-list']);
            }

            if(jQuery('.wfdGeneralButton').length == 0) {
                if(typeof _META != 'undefined') {
                    var moduleName = _META.module;
                } else {
                    var moduleName = RedooUtils('Workflow2').getMainModule(parentEle);
                }
                var recordId = jQuery('[name="record_id"]').val();

                if(response.show_general_button != false) {
                    if(jQuery('.detailview-header-block').length > 0) {
                        var TopButton = '<button class="btn btn-default wfdGeneralButton" data-view="detailview" data-module="' + moduleName + '" data-crmid="' + recordId + '" style="margin-right:5px;font-weight:bold;"><i class="fa fa-location-arrow"></i> ' + response.labels.start_process + '</button>';
                        jQuery('.detailViewButtoncontainer .btn-toolbar .btn-group:first-of-type').prepend('' + TopButton + '');
                    } else if(jQuery('#appnav .nav ').length > 0){
                        var TopButton = '<button class="btn btn-default wfdGeneralButton module-buttons" data-view="detailview" data-module="' + moduleName + '" data-crmid="' + recordId + '" style="font-weight:bold;"><i class="fa fa-location-arrow"></i> ' + response.labels.start_process + '</button>';
                        jQuery('#appnav .nav ').prepend('<li style="padding-right:20px;">' + TopButton + '</li>');
                    } else {
                        //resposnvie
                        var TopButton = '<button style="margin-right:20px;" class="btn btn-default wfdGeneralButton module-buttons" data-view="detailview" data-module="' + moduleName + '" data-crmid="' + recordId + '" style="font-weight:bold;"><i class="fa fa-location-arrow"></i> ' + response.labels.start_process + '</button>';
                        jQuery('div#appnav').find('div.btn-group').prepend(TopButton);
                    }

                    /*if (jQuery('.WFDetailViewGroupTop').length > 0) {
                        jQuery('.WFDetailViewGroupTop').prepend(TopButton);
                    } else {
                        jQuery('.detailViewButtoncontainer .btn-toolbar ').prepend('<div class="btn-group WFDetailViewGroupTop">' + TopButton + '</div>');
                    }*/

                    jQuery('.wfdGeneralButton').on('click', function (e) {
                        var module = jQuery(e.currentTarget).data('module');
                        var crmid = jQuery(e.currentTarget).data('crmid');
                        var view = jQuery(e.currentTarget).data('view');

                        Workflow2Frontend.showWorkflowPopup(module, crmid, view);
                    });
                }
            }


            WFUserIsAdmin = response.is_admin == true ? true : false;
            jQuery.each(response.actions, function(index, value) {
                switch(value.type) {
                    case 'redirect':
                        if(value.configuration.url == '_internal_reload') {
                            window.location.reload();
                            return false;
                        }
                        if(value.configuration.target == "same") {
                            window.location.href = value.configuration.url;
                            return false;
                        } else {
                            window.open(value.configuration.url);
                            return;
                        }

                        break;
                    case 'AdjustGlobalTax':
                        console.log(value);
                        break;
                    case 'confirmation':
                        if(jQuery('.confirmation_container').length == 0) {
                            var html = '<div class="confirmation_container row block" style="background-color:#ffffff;padding-top:10px;margin-top:10px;"></div>';
                            jQuery('div.details').before(html);
                        }

                        var config = value.configuration;
                        var bgColor = config.backgroundcolor;
                        //if(bgColor == '') {
                            bgColor = '#ffffff';
                        //}
                        /*
                        if(config.border != '') {
                            var borderCSS = 'border:2px solid ' + config.border + ';border-top:0;';
                        } else {
                            var borderCSS = '';
                        }*/
                        var borderCSS = '';
                        var html = '<div class="row" style="line-height:24px;' + borderCSS +'background-color:' + bgColor + ';">';
                        html += '<div style="font-weight:bold;margin-bottom:10px;line-height:24px;" class="col-lg-6">' + config.infomessage + ' <div style="font-size:10px;color:#5e5e55;line-height:10px;">' + config.text_eingestellt +': ' + config.first_name + ' ' + config.last_name + ' / ' + config.timestamp +  '</div></div>';
                        html += '<div style="font-weight:bold;margin-bottom:10px;line-height:34px;" class="col-lg-6">';
                        if(config.buttons.btn_accept != '') {
                            html += '<a onclick="return WorkflowPermissions.submit(\'' + config.execid +'##' + config.blockid + '\', \'' + config.conf_id + '\', \''+ config.hash1 + ' \', \'ok\');" class="btn btn-success decision decision_ok" style="margin-right:5px;min-width:100px;" href="index.php?module=Workflow2&view=List&aid=' + config.conf_id + '&a=ok&h=' + config.hash1 + '">' + config.buttons.btn_accept + '</a>';
                        }
                        if(value.configuration.buttons.btn_rework != '') {
                            html += '<a onclick="return WorkflowPermissions.submit(\'' + config.execid +'##' + config.blockid + '\', \'' + config.conf_id + '\', \''+ config.hash2 + ' \', \'rework\');" class="btn btn-warning decision decision_rework" style="margin-right:5px;min-width:100px;"  href="index.php?module=Workflow2&view=List&aid=' + config.conf_id + '&a=rework&h=' + config.hash1 + '">' + config.buttons.btn_rework + '</a>';
                        }
                        if(value.configuration.buttons.btn_decline != '') {
                            html += '<a onclick="return WorkflowPermissions.submit(\'' + config.execid +'##' + config.blockid + '\', \'' + config.conf_id + '\', \''+ config.hash3 + ' \', \'decline\');" class="btn btn-danger decision decision_decline" style="margin-right:5px;min-width:100px;"  href="index.php?module=Workflow2&view=List&aid=' + config.conf_id + '&a=decline&h=' + config.hash1 + '">' + config.buttons.btn_decline + '</a>';
                        }


                        jQuery('.confirmation_container').append(html);
                        jQuery('.confirmation_container').slideDown();
                        break;
                    case 'requestValues':
                        continueWorkflow(value.configuration.execid, value.configuration.crmid, value.configuration.blockid);
                        return false;
                        break;
                    case 'message':
                        WorkflowRecordMessages.push(value.configuration);
                        break;
                }
            });

            this.parseMessages();
        }, this));
    };

    this.generateBtnTrigger = function(buttons) {
        jQuery('.wfdButtonHeaderbutton').remove();

        if(typeof buttons.headerbtn != 'undefined') {
            var html = '';
            jQuery.each(buttons.headerbtn, $.proxy(function(index, value) {
                var rand = Math.floor(Math.random() * 9999999) + 1000000;
                if(value.color != '') {
                    var cssStyle = 'color:' + value.textcolor + ';background-color: ' + value.color + ';background-image:none;';
                } else {
                    var cssStyle = ''
                }

                html += '<li><button type="button" data-id="' + value.workflow_id + '" class="btn btn-default module-buttons wfdButtonHeaderbutton" style="' + cssStyle + '">' + value.label + '</button></li>';
            }, this));

            jQuery('#appnav .nav ').prepend('' + html + '');

            jQuery('.wfdButtonHeaderbutton').on('click', function(e) {
                var target = jQuery(e.currentTarget);
                var workflowObj = new Workflow();

                if(FlexUtils('Workflow2').getViewMode() == 'listview') {
                    workflowObj.execute(target.data('id'), 0);
                } else {
                    workflowObj.execute(target.data('id'), RedooUtils('Workflow2').getRecordIds()[0]);
                }
            });
        }
    };

    this.generateDetailViewTopButtons = function(buttons) {
        jQuery('.WFDetailViewGroupTop').remove();
        var html = '';
            jQuery.each(buttons, function(index, value) {
                var rand = Math.floor(Math.random() * 9999999) + 1000000;
                if(value.color != '') {
                    var cssStyle = 'color:' + value.textcolor + ';background-color: ' + value.color + ';background-image:none;';
                } else {
                    var cssStyle = ''
                }

                html += '<button data-id="' + value.workflow_id + '" class="btn btn-default wfdButtonTopbutton" style="' + cssStyle + '">' + value.label + '</button>';
            });
            jQuery('.detailViewButtoncontainer .btn-toolbar ').prepend('<div class="btn-group WFDetailViewGroupTop">' + html + '</div>');

            jQuery('.wfdButtonTopbutton').on('click', function() {
                var workflow = new Workflow();
                workflow.execute(jQuery(this).data('id'), jQuery('#recordId').val());
            });
    };

    this.generateInlineButtons = function(buttons) {
        var final = {};

        jQuery.each(buttons, jQuery.proxy(function (index, button) {
            jQuery.each(button.config.field, jQuery.proxy(function (fieldIndex, fieldName) {
                if (typeof final[fieldName] == 'undefined') {
                    final[fieldName] = {
                        config: button.config,
                        buttons: []
                    };
                }

                final[fieldName]['buttons'].push(button);
            }, this));
            //
        }, this));

        RedooCache('Workflow2').set('currentInlineButtons', final);
        this.showInlineButtons();
    };

    this.showInlineButtons = function() {
        jQuery('.WFInlineButton').remove();
        jQuery('.WFDInlineDropdown').remove();

        jQuery.each(RedooCache('Workflow2').get('currentInlineButtons', []), jQuery.proxy(function(fieldName, fields) {
            var field = RedooUtils('Workflow2').getFieldElement(fieldName);

            if(field != false) {
                var dropdownHTML = '';
                var buttonHTML = '';

                    jQuery.each(fields['buttons'], jQuery.proxy(function (index, button) {
                        if(typeof button.config.dropdown == 'undefined' || button.config.dropdown == '0') {
                            // Buttons shouldn't arranged as DropDown

                            var existingButtons = jQuery('.WFInlineButton[data-wfid="' + button.workflow_id + '"][data-frontendid="' + button.frontend_id + '"][data-fieldname="' + fieldName + '"]');
                            if (existingButtons.length > 0) {
                                jQuery(existingButtons).show().removeClass('tmpbtn');
                            } else {
                                buttonHTML = '<button type="button" data-wfid="' + button.workflow_id + '" data-frontendid="' + button.frontend_id + '" data-fieldname="' + fieldName + '" class="WFInlineButton btn pull-right" style="height:20px;line-height:16px;font-size:10px; padding:1px 10px; background-color:' + button.color + ';color:' + button.textcolor + ';margin-left:5px;">' + button.label + '</button>';
                            }
                        } else {

                            // Buttons shouldn't arranged as DropDown
                            //jQuery.each(fields['buttons'], jQuery.proxy(function (index, button) {
                            dropdownHTML += '<li style=" background-color:' + button.color + ';color:' + button.textcolor + ';" class="dropdown-submenu"><a data-wfid="' + button.workflow_id + '" data-frontendid="' + button.frontend_id + '" data-fieldname="' + fieldName + '" href="#" style="color:' + button.textcolor + ';">' + button.label + '</a></li>';
                            //}, this));
                        }
                    }, this));

                jQuery('.WFDInlineDropdown', field).remove();

                if(RedooUtils('Workflow2').getViewMode() == 'detailview') {
                    if(dropdownHTML != '') {
                        var finalHTML = '<div class="btn-group pull-right WFDInlineDropdown" style="margin-left:5px;"><a class="btn dropdown-toggle" data-toggle="dropdown" href="#"  style="height:20px;color:#666666;border:1px solid #666666;line-height:16px;font-size:10px; padding:1px 5px;"><span class="caret"></span></a><ul class="dropdown-menu">' + dropdownHTML + '</ul></div>';
                        field.append(finalHTML);
                    }

                    if(buttonHTML != '') {
                        field.append(buttonHTML);
                    }
                } else if(RedooUtils('Workflow2').getViewMode() == 'summaryview') {
                    if(dropdownHTML != '') {
                        var finalHTML = '<div class="btn-group pull-right WFDInlineDropdown" style="margin-left:5px;"><a class="btn dropdown-toggle" data-toggle="dropdown" href="#"  style="font-size:10px; padding:1px 5px;"><span class="caret"></span></a><ul class="dropdown-menu">' + dropdownHTML + '</ul></div>';
                        field.append(finalHTML);
                    }
                    console.log(buttonHTML, field);
                    if(buttonHTML != '') {
                        field.append(buttonHTML);
                    }
                }

            }
        }, this));

        jQuery('.WFInlineButton.tmpbtn').hide();

        jQuery('.WFInlineButton, .WFDInlineDropdown li a').off('click').on('click', function(e) {
            e.stopPropagation();

            var wfId = jQuery(e.currentTarget).data('wfid');

            var workflow = new Workflow();
            workflow.execute(wfId, RedooUtils('Workflow2').getRecordIds()[0], function() {});
        });

        jQuery("div.WFDInlineDropdown").on('click', function(e) {
            e.stopPropagation();

            jQuery(".dropdown-toggle", e.currentTarget).dropdown('toggle');
        });
    };

    this.setBackgroundMode = function(value) {
        this._backgroundMode = value;
    };
    this.setRequestedData = function(values, relatedKey) {
        this._requestValues = values;
        this._requestValuesKey = relatedKey;
    };

    this.allowParallel = function(value) {
        this._allowParallel = value?1:0;
    };

    this.addExtraEnvironment = function(key, value) {
        this._extraEnvironment[key] = value;
    };

    this._executeByTrigger = function(triggerName, ExecutionCallback) {
        var Execution = new WorkflowExecution();
        Execution.init(this.crmid);
        Execution.setRequestedData(this._requestValues, this._requestValuesKey);

        if(this._allowParallel == 1) {
            Execution.allowParallel();
        }
        Execution.enableRedirection(ENABLEredirectionOrReloadAfterFinish);


        if(typeof ExecutionCallback != 'undefined') {
            this._workflowTrigger = triggerName;
        }

        if(typeof ExecutionCallback != 'undefined') {
            Execution.setCallback(ExecutionCallback);
        }

        jQuery.each(this._extraEnvironment, function(index, value) {
            Execution.addEnvironment(index, value);
        });

        Execution.setBackgroundMode(this._backgroundMode);
        Execution.setWorkflowByTrigger(triggerName);
        Execution.execute();
    };

    this._executeById = function(workflow_id, ExecutionCallback) {
        var Execution = new WorkflowExecution();
        Execution.init(this.crmid);
        Execution.setRequestedData(this._requestValues, this._requestValuesKey);

        if(this._allowParallel == 1) {
            Execution.allowParallel();
        }
        Execution.enableRedirection(ENABLEredirectionOrReloadAfterFinish);


        if(typeof ExecutionCallback != 'undefined') {
            this._workflowid = workflow_id;
        }

        if(typeof ExecutionCallback != 'undefined') {
            Execution.setCallback(ExecutionCallback);
        }

        jQuery.each(this._extraEnvironment, function(index, value) {
            Execution.addEnvironment(index, value);
        });

        Execution.setListViewMode(this._ListViewMode);
        Execution.setBackgroundMode(this._backgroundMode);
        Execution.setWorkflowById(workflow_id);
        Execution.execute();

    }; /** ExecuteById **/

    this._submitStartfields = function(fields, urlStr) {
        app.hideModalWindow();
        RedooUtils('Workflow2').blockUI({
            'message' : 'Workflow is executing',
            // disable if you want key and mouse events to be enable for content that is blocked (fix for select2 search box)
            bindEvents: false,

            //Fix for overlay opacity issue in FF/Linux
            applyPlatformOpacityRules : false
        });

        jQuery.post("index.php", {
                "module" : "Workflow2",
                "action" : "Execute",
                "file"   : "ajaxExecuteWorkflow",

                "crmid" : this.crmid,
                "workflow" : this._workflowid,
                allow_parallel: this._allowParallel,
                "startfields": fields
            },
            jQuery.proxy(function(response) {
                RedooUtils('Workflow2').unblockUI();

                try {
                    response = jQuery.parseJSON(response);
                } catch (e) {
                    console.log(response);
                    return;
                }

                if(response["result"] == "ok") {
                    if(ENABLEredirectionOrReloadAfterFinish) {
                        window.location.reload();
                    }
                } else {
                    console.log(response);
                }
            }, this)
        );
    }

    this.closeForceNotification = function(messageId) {
        jQuery.post('index.php?module=Workflow2&action=MessageClose', { messageId:messageId, force: 1 });
    }

    this.parseMessages = function() {
        if(typeof WorkflowRecordMessages != 'object' || WorkflowRecordMessages.length == 0) {
            return;
        }
        RedooUtils('Workflow2').loadScript('modules/Workflow2/views/resources/js/noty/jquery.noty.packaged.min.js').then(jQuery.proxy(function()
        {
            jQuery.each(WorkflowRecordMessages, function(index, value) {

                if(typeof WFDvisibleMessages['workflowMessage' + value['id']] != 'undefined' && WFDvisibleMessages['workflowMessage' + value['id']] == true) {
                    return;
                }

                var type = 'alert';
                switch(value.type) {
                    case 'success':
                        type = 'success';
                        break;
                    case 'info':
                        type = 'alert';
                        break;
                    case 'error':
                        type = 'error';
                        break;
                }
                value.message = '<strong>' + value.subject + "</strong><br/>" + value.message;

                if(value.show_until != '') {
                    value.message += '<br/><span style="font-size:10px;font-style: italic;">' +value.show_until + '</span>';
                }
                if(WFUserIsAdmin == true) {
                    value.message += '&nbsp;&nbsp;<a href="#" style="font-size:10px;font-style: italic;" onclick="closeForceNotification(' + value.id + ');">(Remove Message)</a>';
                }

                WFDvisibleMessages['workflowMessage' + value['id']] = true;
                if(value.position != -1) {
                    noty({
                        text: value.message,
                        id: 'workflowMessage' + value['id'],
                        type: value.type,
                        timeout: false,
                        'layout': value.position,
                        'messageId': value.id,
                        callback: {
                            "afterClose": function () {
                                WFDvisibleMessages['workflowMessage' + this.options.messageId] = false;
                                jQuery.post('index.php?module=Workflow2&action=MessageClose', {messageId: this.options.messageId});
                            }
                        }
                    });
                }
            });
        }), this);
    }

    this.loadCachedScript = function( url, options ) {

        // Allow user to set any option except for dataType, cache, and url
        options = jQuery.extend( options || {}, {
            dataType: "script",
            cache: true,
            url: url
        });

        // Use $.ajax() since it is more flexible than $.getScript
        // Return the jqXHR object so we can chain callbacks
        return jQuery.ajax( options );
    };
}
;var WorkflowRunning = false;

var Workflow2Frontend = {
    viewMode:false,
    runCampaignRealationWF:function(workflow_id) {
        runListViewWorkflow(workflow_id);
    },
    showWorkflowPopup:function(MainModule, RecordIds, MainView) {

        //var MainView = RedooUtils('Workflow2').getViewMode(parentEle);
//        var RecordIds = RedooUtils('Workflow2').getRecordIds(parentEle);

        RedooAjax('Workflow2').postView('WorkflowPopup', {
            'target_module': MainModule,
            'target_view': MainView,
            'target_record': RecordIds
        }).then(function(response) {
            RedooUtils('Workflow2').showModalBox(response).then(function(data) {
                jQuery('[type="submit"]', data).on('click', function(e) {
                    if(jQuery('#workflow2_workflowid').val() == '' || jQuery('#workflow2_workflowid').val() == null) {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }

                    RedooUtils('Workflow2').hideModalBox();

                    var crmid = jQuery('.WorkflowPopupCRMID', data).val();
                    var workflow = new Workflow();
                    workflow.execute(jQuery('#workflow2_workflowid').val() , crmid);
                });
            });
        });
    },
    TopbuttonHandler:function(parentEle) {
        var MainModule = RedooUtils('Workflow2').getMainModule(parentEle);
        var CurrentViewMode = FlexUtils('Workflow2').getViewMode();

        if (
            typeof WFDFrontendConfig !== 'undefined' &&
            typeof WFDFrontendConfig['morebtn'] !== 'undefined' &&
            typeof WFDFrontendConfig['morebtn'][MainModule] !== 'undefined'
        ) {
            if(jQuery('.detailViewButtoncontainer ul.dropdown-menu').hasClass('WFDAddHandler') === false) {
                var html = '';
                jQuery.each(WFDFrontendConfig['morebtn'][MainModule], function(index, value) {
                    var rand = Math.floor(Math.random() * 9999999) + 1000000;
                    html += '<li data-id="' + value.workflowid + '" class="wfdButtonMoreBtn" style="' + (value.color != '' ? 'color:' + value.textcolor + ';background-color: ' + value.color + ';':'') + '"><a href="#" style="' + (value.color != '' ? 'color:' + value.textcolor + ';':'') + '">' + value.label + '</a></li>';
                });
                jQuery('.detailViewButtoncontainer ul.dropdown-menu').addClass('WFDAddHandler');
                jQuery('.detailViewButtoncontainer ul.dropdown-menu').append(html);

                jQuery('.wfdButtonMoreBtn a').on('click', function(e) {
                    $(e.currentTarget).closest('li').trigger('click');
                    e.preventDefault();
                    return false;
                });
                jQuery('.wfdButtonMoreBtn').on('click', function() {
                    var workflow = new Workflow();
                    workflow.execute(jQuery(this).data('id'), jQuery('#recordId').val());
                });
            }
        }

        if (
            CurrentViewMode == 'listview' &&
            typeof WFDFrontendConfig !== 'undefined' &&
            typeof WFDFrontendConfig['listviewbtn'] !== 'undefined' &&
            typeof WFDFrontendConfig['listviewbtn'][MainModule] !== 'undefined'
        ) {

            if(jQuery('.detailViewButtoncontainer').hasClass('WFDAddHandler') === false) {
                var html = '';
                jQuery.each(WFDFrontendConfig['listviewbtn'][MainModule], function(index, value) {
                    var rand = Math.floor(Math.random() * 9999999) + 1000000;

                    if(value.color != '') {
                        var cssStyle = 'color:' + value.textcolor + ';background-color: ' + value.color + ';background-image:none;';
                    } else {
                        var cssStyle = ''
                    }

                    html += '<button type="button" data-id="' + value.workflowid + '" class="btn btn-default wfdButtonTopbutton" style="' + cssStyle + '">' + value.label + '</button>';
                });

                jQuery('.detailViewButtoncontainer').addClass('WFDAddHandler');
                jQuery('.listViewActionsContainer').append(html);

                jQuery('.wfdButtonTopbutton').on('click', function(e) {
                    e.preventDefault();
                    var workflow = new Workflow();
                    workflow.execute(jQuery(this).data('id'), 0);
                });
            }
        }
    },
    RelatedListHandler:function(parentEle) {

        if(typeof WFDFrontendConfig !== 'undefined' && typeof WFDFrontendConfig['relatedbtn'] !== 'undefined') {
            var MainModule = Workflow2Frontend.getMainModule(parentEle);
            if(typeof WFDFrontendConfig['relatedbtn'][MainModule] !== 'undefined') {
                var btnHtml = '';
                for(var index in WFDFrontendConfig['relatedbtn'][MainModule] ) {
                    if (WFDFrontendConfig['relatedbtn'][MainModule].hasOwnProperty(index) && jQuery.isNumeric(index)) {
                        var value = WFDFrontendConfig['relatedbtn'][MainModule][index];

                        btnHtml += '<button type="button" class="btn CampaignRelationBtn" onclick="Workflow2Frontend.runCampaignRealationWF(' + value['workflowid'] +');" style="background-color:' + value['color'] +';">' + value['label'] +'</button>';
                    }
                }

                var parent = jQuery(jQuery('div.relatedHeader .btn')[0]).closest('.btn-group').parent();
                parent.append('<div class="btn-group">' + btnHtml + '</div>');
            }
        }


    },
    getMainModule:function (parentEle) {
        var viewMode = Workflow2Frontend.getViewMode(parentEle);

        if (viewMode == 'detailview' || viewMode == 'summaryview') {
            return jQuery('#module', parentEle).val();
        } else if (viewMode == 'editview' || viewMode == 'quickcreate') {
            return jQuery('[name="module"]', parentEle).val();
        } else if (viewMode == 'listview') {
            return jQuery('#module', parentEle).val();
        } else if (viewMode == 'relatedview') {
            if (jQuery('[name="relatedModuleName"]', parentEle).length > 0) {
                return jQuery('[name="relatedModuleName"]', parentEle).val();
            }
            if (jQuery('#module', parentEle).length > 0) {
                return jQuery('#module', parentEle).val();
            }
        }
        return '';
    },
    getViewMode: function(parentEle, obj) {
        var viewEle = jQuery("#view", parentEle);

        if(viewEle.length > 0 && viewEle[0].value == "List") {
            Workflow2Frontend.viewMode = "listview";
        }

        if(jQuery(".detailview-table", parentEle).length > 0) {
            Workflow2Frontend.viewMode = "detailview";
        } else if(jQuery(".summary-table", parentEle).length > 0) {
            Workflow2Frontend.viewMode = "summaryview";
        } else if(jQuery(".recordEditView", parentEle).length > 0) {
            if(jQuery('.quickCreateContent', parentEle).length == 0) {
                Workflow2Frontend.viewMode = "editview";
            } else {
                Workflow2Frontend.viewMode = "quickcreate";
            }
        }

        if(jQuery('.relatedContents', parentEle).length > 0) {
            Workflow2Frontend.viewMode = "relatedview";

            if(jQuery('td[data-field-type]', parentEle).length > 0) {
                Workflow2Frontend.popUp = false;
            } else {
                Workflow2Frontend.popUp = true;
            }
        }

        if(Workflow2Frontend.viewMode === false) {
            if(jQuery('#view', parentEle).length > 0) {
                if(jQuery('#view', parentEle).val() == 'Detail') {
                    Workflow2Frontend.viewMode = 'detailview';
                }
            }
        }

        return Workflow2Frontend.viewMode;
    }
};
;window.WorkflowExecution = function() {
    this._crmid = null;
    this._execId = null;

    this._workflowId = null;
    this._workflowTrigger = null;

    this._execId = null;
    this._blockID = null;

    this._requestValues = {};
    this._requestValuesKey = null;

    this._callback = null;
    this._allowParallel = false;
    this._allowRedirection = true;
    this._backgroundMode = false;
    this._extraEnvironment = {};
    this._ListViewMode = false;
    this._FrontendType = undefined;

    this.setFrontendType = function(type) {
        this._FrontendType = type;
    };

    this.setEnvironment = function(envVars) {
        this._extraEnvironment = envVars;
    };

    this.addEnvironment = function(key, value) {
        this._extraEnvironment[key] = value;
    };

    this.setRequestedData = function(values, relatedKey) {
        this._requestValues = values;
        this._requestValuesKey = relatedKey;
    };

    this.allowParallel = function() {
        this._allowParallel = true;
    };
    this.init = function(crmid) {
        this._crmid = crmid;
    };

    this.setWorkflowByTrigger = function(triggerName) {
        this._workflowTrigger = triggerName;
        this._workflowId = undefined;
    };

    this.setWorkflowById = function(workflow_id) {
        this._workflowId = workflow_id;
        this._workflowTrigger = undefined;
    };

    this.setBackgroundMode = function(value) {
        this._backgroundMode = value;
    };
    this.setCallback = function(callback) {
        this._callback = callback;
    };
    this.setListViewMode = function(listView) {
        this._ListViewMode = listView == true;
    };

    this.enableRedirection = function(value) {
        this._allowRedirection = value ? true : false;
    };

    this._handleDownloads = function(response) {
        var html = '<p>' + response.download_text + '</p>';
        html += '<ul style="list-style:none;">';
        $.each(response.downloads, function(index, data) {
            html += '<li style="margin-bottom:5px;"><a href="' + data.url + '" target="_blank"><i class="fa fa-download" style="margin-right:10px;" aria-hidden="true"></i> <strong>' + data.filename + '</strong></a></li>';
        });
        html += '</ul>';

        bootbox.dialog({
            message:html,
            closeButton:true,
            buttons: {
                ok: {
                    label: 'Ok',
                    className: 'btn-success'
                }
            }
        });
    };

    this._handleRedirection = function(response) {
        if(this._allowRedirection === true) {
            if(response["redirection_target"] == "same") {
                window.location.href = response["redirection"];
                return true;
            } else {
                window.open(response["redirection"]);
                return true;
            }
        }
        return false;
    };

    this.setContinue = function(execID, blockID) {
        this._execId = execID;
        this._blockID = blockID;
    };

    this.executeWithForm = function(form) {
        if(typeof jQuery(form).ajaxSubmit == 'undefined') {
            console.error('jquery.forms plugin requuired!');
            return;
        }

        WorkflowRunning = true;
        RedooUtils('Workflow2').blockUI({ message: '<h4 style="padding:5px 0;"><img src="modules/Workflow2/icons/sending.gif" style="margin-bottom:20px;" /><br/>Please wait ...</h4>' });

        jQuery(form).ajaxSubmit({
            'url' : "index.php",
            'type': 'post',
            data: {
                "module" : "Workflow2",
                "action" : "ExecuteNew",

                'crmid' : this._crmid,

                'workflowID' : this._workflowId === null ? undefined : this._workflowId,
                'allowParallel': this._allowParallel ? 1 : 0,

                'continueExecId': this._execId === null ? undefined : this._execId,
                'continueBlockId': this._blockID === null ? undefined : this._blockID,
                'requestValues': this._requestValues === null ? undefined : this._requestValues,
                'requestValuesKey': this._requestValuesKey === null ? undefined : this._requestValuesKey,
                'extraEnvironment': this._extraEnvironment,
                'listviewmode': this._ListViewMode ? 1 : 0
            },
            success:jQuery.proxy(this.executionResponse, this),
            error:jQuery.proxy(this.executionResponse, this)
        });

    };

    this.frontendWorkflows = function(workflowIDs, record) {
        var dfd = jQuery.Deferred();

        RedooAjax('Workflow2').post('index.php', {
            'module': 'Workflow2',
            'action': 'FrontendWorkflowExec',
            'workflow_ids': workflowIDs,
            'record': record,
            'dataType': 'json'
        }).then($.proxy(function(data) {
            //this.executionResponse(data);

            dfd.resolve( data );
        }, this));

        return dfd.promise();
    };

    this.execute = function() {
        if(this._backgroundMode === false) {
            RedooUtils('Workflow2').blockUI({message: '<h4 style="padding:5px 0;"><img src="modules/Workflow2/icons/sending.gif" style="margin-bottom:20px;"/><br/>Please wait ...</h4>'});
        }

        WorkflowRunning = true;
        jQuery.post("index.php", {
                "module" : "Workflow2",
                "action" : "ExecuteNew",
                //XDEBUG_PROFILE:1,

                'frontendtype': this._FrontendType,
                'crmid' : this._crmid,

                'workflowID' : this._workflowId === null ? undefined : this._workflowId,
                'triggerName' : this._workflowTrigger === null ? undefined : this._workflowTrigger,

                'allowParallel': this._allowParallel ? 1 : 0,

                'continueExecId': this._execId === null ? undefined : this._execId,
                'continueBlockId': this._blockID === null ? undefined : this._blockID,
                'requestValues': this._requestValues === null ? undefined : this._requestValues,
                'requestValuesKey': this._requestValuesKey === null ? undefined : this._requestValuesKey,
                'extraEnvironment': this._extraEnvironment,
                'listviewmode': this._ListViewMode ? 1 : 0
            }
        ).always(jQuery.proxy(this.executionResponse, this));
    };

    this.executionResponse = function(responseTMP) {
        if(typeof responseTMP == 'object' && typeof responseTMP.responseText != 'undefined') {
            responseTMP = responseTMP.responseText;
        }

        if(responseTMP.indexOf('Invalid request') !== -1) {
            alert('You did not do any action in VtigerCRM since a long time. The page needs to be reloaded, before you could use the Workflow Designer.');
            window.location.reload();
            return;
        }

        if(this._backgroundMode === false) {
            RedooUtils('Workflow2').unblockUI();
        }

        WorkflowRunning = false;

        var response;
        try {
            response = jQuery.parseJSON(responseTMP);
        } catch(exp) {
            console.log(exp);
            console.log(responseTMP);
            return;
        }

        if(response !== null && response["result"] == "ready") {
            if(typeof this._callback == 'function') {
                var retVal = this._callback.call(this, response);

                if(typeof retVal != 'undefined' && retVal === false) {
                    return;
                }
            }

            if(typeof response["redirection"] != "undefined" && typeof response["downloads"] != "undefined") {
                this._handleDownloads(response);
                this._handleRedirection(response);
                return;
            } else if(typeof response["redirection"] != "undefined") {
                this._handleRedirection(response);
                return;
            } else if(typeof response["downloads"] != "undefined") {
                this._handleDownloads(response);
                return;
            }

            if(this._allowRedirection === true && this._backgroundMode === false && typeof response["prevent_reload"] === 'undefined') {
                window.location.reload();
            }
        } else if(response !== null && response["result"] == "asktocontinue") {
            jQuery('body').append('<style type="text/css">.bootbox.modal {z-index: 9999 !important;}</style>');
            bootbox.confirm({
                message: response['question'],
                buttons: {
                    confirm: {
                        label: response['LBL_YES'],
                        className: 'btn-success'
                    },
                    cancel: {
                        label: response['LBL_NO'],
                        className: 'btn-danger'
                    }
                },
                callback: function (result) {
                    if(result === true) {
                        FlexUtils('Workflow2').hideModalBox();
                        var Execution = new WorkflowExecution();
                        Execution.setContinue(response['execid'], response['blockid']);
                        Execution.execute();
                    }
                }
            });
        } else if(response !== null && response["result"] == "requestForm") {
            this._requestValuesKey = response['fields_key'];
            this._execId = response['execId'];

            if(typeof RequestValuesForm2 == 'undefined') {
                jQuery.getScript('modules/Workflow2/views/resources/js/RequestValuesForm2.js', jQuery.proxy(function() {

                    var requestForm = new RequestValuesForm2(response['fields_key'], response);
                    requestForm.setCallback(jQuery.proxy(this.submitRequestFields, this));
                    requestForm.show(response.html, response.script);

                    //response, this._requestValuesKey, response['request_message'], , response['stoppable'], response['pausable'], response['options']);
                }, this));
            } else {
                var requestForm = new RequestValuesForm2(response['fields_key'], response);
                requestForm.setCallback(jQuery.proxy(this.submitRequestFields, this));
                requestForm.show(response.html, response.script);
            }
        } else if(response !== null && response["result"] == "reqvalues") {
            this._requestValuesKey = response['fields_key'];
            this._execId = response['execId'];

            if(typeof RequestValuesForm == 'undefined') {
                jQuery.getScript('modules/Workflow2/views/resources/js/RequestValuesForm.js', jQuery.proxy(function() {
                    var requestForm = new RequestValuesForm();
                    requestForm.show(response, this._requestValuesKey, response['request_message'], jQuery.proxy(this.submitRequestFields, this), response['stoppable'], response['pausable'], response['options']);
                }, this));
            } else {
                var requestForm = new RequestValuesForm();
                requestForm.show(response, this._requestValuesKey, response['request_message'], jQuery.proxy(this.submitRequestFields, this), response['stoppable'], response['pausable'], response['options']);
            }
        } else if(response !== null && response["result"] == "error") {
            console.log('Errorcode: ' + response.errorcode);
            app.showModalWindow('<div style="padding:10px 50px;text-align:center;">' + response.message + '</div>');
        } else {
            console.log(response);
        }
    };

    this.submitRequestFields = function(key, values, value2, form) {

        this._requestValues = {};
        this._requestValuesKey = key;

        var html = '';
        jQuery.each(values, jQuery.proxy(function(index, value) {
            if(value.name.substr(-2) != '[]') {
                this._requestValues[value.name] = value.value;
            } else {
                var varName = value.name.substr(0, value.name.length - 2);
                if(typeof this._requestValues[varName] === 'undefined') {
                    this._requestValues[varName] = [];
                }

                this._requestValues[varName].push(value.value);
            }
        }, this));

        if(jQuery('[type="file"]', form).length > 0) {
            var html = '<form action="#" method="POST" onsubmit="return false;">';
            jQuery('input, select, button', form).attr('disabled', 'disabled');
            jQuery('[type="file"]', form).removeAttr('disabled').each(jQuery.proxy(function(index, ele) {
                var name = jQuery(ele).attr('name');
                jQuery(ele).attr('name', 'fileUpload[' + name + ']');

                this._requestValues[name] = jQuery(ele).data('filestoreid');
            }, this));
            html += '</form>';
            this.executeWithForm(form);
            return;
        }

        this.execute();
    }
};
//# sourceMappingURL=data:application/json;charset=utf8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbImZyb250ZW5kLmpzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiI7QUFBQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EiLCJmaWxlIjoiZnJvbnRlbmQuanMiLCJzb3VyY2VzQ29udGVudCI6W119
}())
/** HANDLER START **/
var WFDFrontendConfig = [];
var WFDLanguage = {"These Workflow requests some values":"These Workflow requests some values","Execute Workflow":"Execute Workflow","enter values later":"enter values later","stop Workflow":"stop Workflow","Executing Workflow ...":"Executing Workflow ..."};
(function($) { 
/* Start Script */var FrontendWorkflowData = {Group403fa8adde89604e422f840057004a95fef20dce: function(record) { var joinCondition = ""; var checkResult = false;
return true; },
Exec1: function(record) {  return FrontendWorkflowData.Group403fa8adde89604e422f840057004a95fef20dce(record); },
Group732a159fa8f08d6fd199ebb8168c71133e20fc6a: function(record) { var joinCondition = ""; var checkResult = false;
return true; },
Exec3: function(record) { if(window.WorkflowFrontendInitialize == true) return; return FrontendWorkflowData.Group732a159fa8f08d6fd199ebb8168c71133e20fc6a(record); },
Config: {"Accounts":{"fields":{"industry":[{"function":"Exec1","workflow_id":"3"}]}},"Quotes":{"fields":{"bill_country":[{"function":"Exec3","workflow_id":"26"}]}}}};var WorkflowFrontendActions = {};WorkflowFrontendActions["message"] = function(config, callback) {
                        RedooUtils('Workflow2').loadScript('modules/Workflow2/views/resources/js/noty/jquery.noty.packaged.min.js').then(function() {
            var type = 'alert';
            switch(config.type) {
                case 'success':
                    type = 'success';
                    break;
                case 'info':
                    type = 'alert';
                    break;
                case 'error':
                    type = 'error';
                    break;
            }
            config.message = '<strong>' + config.subject + "</strong><br/>" + config.message;

            if(config.position != -1) {
                noty({
                    'text'  : config.message,
                    'type'  : config.type,
                    'timeout': config.timeout == 0 ? null : config.timeout,
                    'layout': config.position
                });
            }
            });
        
            };WorkflowFrontendActions["Confirmation"] = function(config, callback) {
                Vtiger_Helper_Js.showConfirmationBox({'message' : config.message}).then(
    function(e) {
        var data = {};
        data[config.key] = 'yes';
        callback(data);
    },
    function(error, err){
        var data = {};
        data[config.key] = 'no';
        callback(data);
    });

            };WorkflowFrontendActions["AdjustGlobalTax"] = function(config, callback) {
                        var taxRow = jQuery("#group_tax_row");
        console.log(config.tax);
        for(var taxName in config.tax) {
            taxRow.find('[name="' + taxName + '"]').val(config.tax[taxName]);
            console.log(taxRow.find('[name="' + taxName + '"]'));
        }

        var inventoryEditor = Inventory_Edit_Js.getInstance();
        inventoryEditor.calculateGroupTax();
        inventoryEditor.calculateGrandTotal();

        
            };WorkflowFrontendActions["inputvalue"] = function(config, callback) {
                        fieldEle = jQuery(config.inputele);
        value = config.value;
        fieldEvents = fieldEle.data('events');
        if(fieldEle.hasClass('autoComplete')) {
            return;
        }
        if(fieldEle.hasClass('dateField')) {
            fieldEle.val(value).DatePickerSetDate(value, true);
        }
        if(fieldEle.hasClass('chzn-select')) {
            fieldEle.val(value).trigger('liszt:updated');
        }

        if(fieldEle.hasClass('select2')) {
            fieldEle.val(value).trigger('change');
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

        fieldEle.trigger('keyup');
        fieldEle.trigger('focusout');

        
            };WorkflowFrontendActions["disableSubmit"] = function(config, callback) {
                jQuery('.btn[type="submit"]').attr("disabled", "disabled");
            };WorkflowFrontendActions["enableSubmit"] = function(config, callback) {
                jQuery('.btn[type="submit"]').removeAttr("disabled");
            };WorkflowFrontendActions["focusField"] = function(config, callback) {
                var fieldEle = RedooUtils('Workflow2').getFieldElement(config.field, this.parentEle, true).focus();
if(typeof config.flash != 'undefined' && config.flash == '1') {
    fieldEle.effect( 'highlight', { color: config.flashcolor }, 500 );
}
        
            };WorkflowFrontendActions["removeTooltip"] = function(config, callback) {
                            var tooltips = RedooCache('Workflow2').get('WFToolTips', {});
            if(typeof tooltips[config.tooltipid] != 'undefined') {
                jQuery.each(tooltips[config.tooltipid], function(index, ele) {
                    jQuery(ele).tooltipster('destroy');
                });
                tooltips[config.tooltipid] = [];
            }
        
            };WorkflowFrontendActions["picklistfilter"] = function(config, callback) {
                jQuery('[name="' + config.field + '"] option:not([value=""])', this.parentEle).prop('disabled', 'disabled');
var selectObj = jQuery('[name="' + config.field + '"]', this.parentEle);
var currentValue = selectObj.val();
var resetValue = true;
jQuery.each(config.values, function(index, value) {
    jQuery('[value="' + value + '"]', selectObj).prop('disabled', false)
    if(value === currentValue) {
        resetValue = false;
    }
});
if(resetValue === true) {
    selectObj.val('');
}
jQuery('option.shouldHide', selectObj).hide().removeClass('shouldHide').trigger('liszt:updated');

            };WorkflowFrontendActions["picklistfilterRemove"] = function(config, callback) {
                jQuery('[name="' + config.field + '"] option', this.parentEle).show();


            };WorkflowFrontendActions["showTooltip"] = function(config, callback) {
                        if(!jQuery("body").hasClass("ColorizerTooltipsterCSSLoaded") && !jQuery("body").hasClass("WorkflowTooltipsterCSSLoaded")) {
            RedooUtils('Workflow2').loadStyles('https://cdn.jsdelivr.net/jquery.tooltipster/4.2.5/css/tooltipster.bundle.min.css');
            jQuery("body").addClass("WorkflowTooltipsterCSSLoaded");
        }

        RedooUtils('Workflow2').loadStyles('https://cdn.jsdelivr.net/jquery.tooltipster/4.2.5/css/tooltipster.bundle.min.css').then(function() {
        RedooUtils('Workflow2').loadScript('https://cdn.jsdelivr.net/jquery.tooltipster/4.2.5/js/tooltipster.bundle.min.js').then(function() {
            var currentHash = Math.ceil(Math.random() * 10000);
            jQuery("head").append("<style type='text/css'>.tooltipster-sidetip.tooltipster-" + config.theme + ".tt" + currentHash + " .tooltipster-box { background-color: " + config.backgroundcolor + ";  } .tooltipster-sidetip.tooltipster-" + config.theme + ".tt" + currentHash + " .tooltipster-content { color: " + config.textcolor + " }  .tooltipster-sidetip.tooltipster-" + config.theme + ".tt" + currentHash + " .tooltipster-arrow-background {border-" + config.position + "-color: " + config.backgroundcolor + " !important; }  </style>");

            jQuery.each(config.field, function(index, ele) {
                if(config.target == 'input') {
                    var fieldEle = RedooUtils('Workflow2').getFieldElement(ele, this.parentEle, true);
                } else {
                    var fieldEle = RedooUtils('Workflow2').getFieldElement(ele);

                    if(config.target == 'label') {
                        fieldEle = fieldEle.prev();
                    }
                }

                if(jQuery(fieldEle).hasClass('tooltipstered')) {
                 //   jQuery(fieldEle).tooltipster('destroy');
                }

                var instance = jQuery(fieldEle).tooltipster({
                    content:  config.content,
                    contentAsHTML: config.html_enabled == '1',
                    theme: ["tooltipster-" + config.theme, "tt" + currentHash],
                    side: config.position,
                    interactive: config.interactive == "1",
                    trigger: 'custom',
                    distance: 2,
                    multiple: true,
                    timer: config.timeout == '' ? 0 : config.timeout * 1000

                }).tooltipster('open');

                if(config.tooltipid != '') {
                    var tooltips = RedooCache('Workflow2').get('WFToolTips', {});
                    if(typeof tooltips[config.tooltipid] == 'undefined') {
                        tooltips[config.tooltipid] = [];
                    }
                    tooltips[config.tooltipid].push(instance);
                    RedooCache('Workflow2').set('WFToolTips', tooltips);
                }
            });
        });
        });
    
            };
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
 /* Finish Script */ 

})(jQuery);/* Render take 0s */

/** MODULELANGUAGESTRINGS START **/
if(typeof FLEXMODLANGUAGE == "undefined") var FLEXMODLANGUAGE = {};
if(typeof FLEXLANG == "undefined") var FLEXLANG = function(key, module) { var lang = app.getUserLanguage(); if(typeof FLEXMODLANGUAGE[module] != "undefined" && typeof FLEXMODLANGUAGE[module][lang] != "undefined" &&  typeof FLEXMODLANGUAGE[module][lang][key] != "undefined") { return FLEXMODLANGUAGE[module][lang][key]; } return key; };
FLEXMODLANGUAGE["Workflow2"] = {"en_us":{"LBL_GET_KNOWN_ENVVARS":"Recognized environment variables","LBL_DUPLICATE_BLOCK":"duplicate Block","LBL_DELETE_BLOCK":"remove Block","LBL_CHANGE_BLOCKCOLOR":"change color","LBL_REMOVE_BLOCKCOLOR":"remove color","HEAD_USAGE_OF_THIS_CONNECTION":"Usage of this path","LBL_DATE":"Date","TXT_CHOOSE_VALID_FIELD":"choose a field","LBL_MANAGE_SIDEBARTOOGLE":"Workflow Designer process your input","LBL_CREATE_TYPE":"create new block manually","LBL_SAVED_SUCCESSFULLY":"Successfully saved","page":"Page","select all of this type":"Select this type","LBL_PASTE_BLOCK":"paste blocks","LBL_COPY_BLOCK":"copy blocks","Reset value":"Reset value","Empty field":"Empty field","Reference Field":"Reference Field","Available fields":"Available fields","Quantity":"Quantity","Unit Price":"Unit Price","Product Description":"Description","Product":"Product","Export Blocks by Text":"Export Blocks by Text","Import Blocks by Text":"Import Blocks by Text","Expression-Errors found":"Expression-Errors found","Name of new Folder?":"Name of new Folder?","Filter Workflows":"Filter Workflows","Please wait":"Please wait","WF_DELETE_CONFIRM":"Please confirm to delete the Workflow?\r\n\r\nRunning processes will be stopped!"}};
/** MODULELANGUAGESTRINGS END **/