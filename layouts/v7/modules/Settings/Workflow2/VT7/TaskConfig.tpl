<div class="app-menu app-nav"></div>
<link rel="stylesheet" href="{$smarty.const.PATH_CONTEXTMENU}/src/jquery.contextMenu.css?v={$CURRENT_VERSION}" type="text/css" media="all" />
<link rel="stylesheet" href="modules/Workflow2/views/resources/js/rcswitcher/rcswitcher.min.css?v={$CURRENT_VERSION}" type="text/css" media="all" />

<script type="text/javascript" src="{$smarty.const.PATH_CONTEXTMENU}/src/jquery.contextMenu.js"></script>
<script type="text/javascript" src="{$smarty.const.UNDERSCOREJS}"></script>

<link href='//fonts.googleapis.com/css?family=Source+Code+Pro' rel='stylesheet' type='text/css'>
<script type="text/javascript">
    function ignoreerror()
    {
        return true
    }
    window.onerror=ignoreerror();
</script>
<div id='overlayPageContent' class='fade modal overlayPageContent content-area overlay-container-300' tabindex='-1' role='dialog' aria-hidden='true'>
    <div class="data">
    </div>
    <div class="modal-dialog">
    </div>
</div>

<div class="TaskConfigContainer">
    <div id="pageOverlay" onclick="closePageOverlay();" style='cursor:url("modules/Workflow2/icons/cross-button.png"), auto;position:fixed;z-index:20000;top:0;left:0;display:none;height:100%;width:100%;background-image:url("modules/Workflow2/icons/modal.png");'><div id='pageOverlayContent' style='position:fixed;cursor:default;top:100px;margin:auto;left:50%;padding:10px;background-color:#ffffff;'>&nbsp;</div></div>
		<form method="POST" action="#" onsubmit="return checkForm();" id="mainTaskForm" name="mainTaskForm" accept-charset="UTF-8" enctype="multipart/form-data">
            <input type="hidden" name="task[_envHelper]" value="" id="_envHelper" />
            {$csrf}

			<input type="hidden" id="save_workflow_id" name="editID" value="{$DATA.id}">

            <div id="fixedHeader">
			<table class="tableHeading" id="ConfigHeadline" width="100%" border="0" cellspacing="0" cellpadding="5">
				<tr>
					<td class="big" nowrap="nowrap">
						<strong>{$MOD.LBL_ZUSAMMENFASSUNG}{if !empty($modifiedBy)} <em>({vtranslate('last modified by', 'Settings:Workflow2')} {$modifiedBy} {$modified} )</em>{/if}</strong>
					</td>
					<td class="small buttonbar" align="right" style="padding:5px 5px 10px 10px;text-align:right;">
                        {if $helpUrl neq ""}
                        <div class="btn-group" style="margin-right:50px;">
                            <input type="button" onclick="window.open('{$helpUrl}');" name="help_page" class="btn btn-info" value="{vtranslate("LBL_DOCUMENTATION", "Settings:Workflow2")}" id="help_page">
                        </div>
                        {/if}
                        {if $DATA.type neq "start"}
                        <div class="btn-group" style="margin-right:10px;">
                            <input type="button" id="edittask_duplicate_button" onclick="duplicateBlock({$block_id});" class="btn btn-default" value="{vtranslate("LBL_DUPLICATE_BLOCK", "Settings:Workflow2")}">
                            <input type="button" id="edittask_remove_button" onclick="removeBlock({$block_id});" class="btn btn-default" value="{vtranslate("LBL_DELETE_BLOCK", "Settings:Workflow2")}">
                        </div>
                        {/if}
                        <div class="btn-group">
                            <input type="submit" name="save" class="btn btn-success" value="{vtranslate("LBL_SAVE", "Settings:Workflow2")}" id="save">
                            <input type="button" id="edittask_cancel_button" class="btn btn-default" value="{vtranslate("LBL_CLOSE", "Settings:Workflow2")}">
                        </div>
					</td>
				</tr>
			</table>
            </div>
        {foreach item=message from=$hint}
            {if $message neq ""}
                <div style='background-color:#fed22f;padding:5px;text-align:center;'>{$message}</div>
            {/if}
        {/foreach}
		<table border="0" cellpadding="5" cellspacing="0" width="100%" class="newTable">
			<tr>
                {if $DATA.type neq "start"}
				<td class="dvtCellLabel" style="width:15%;text-align:right;padding-right:20px;"nowrap="nowrap"><img src='modules/Workflow2/icons/save-indicator.gif' style="display:none;margin-left:5px;margin-bottom:-5px;" id="text_save_indicator"> <b><font color="red">*</font> {vtranslate("LBL_AUFGABENBEZEICHNUNG", "Settings:Workflow2")}</b></td>
				<td class="dvtCellInfo" align="left" ><input type="text" class="detailedViewTextBox textfield taskTitle" name="taskSettings[text]" onblur="saveTaskText(this.value);" style="width:100%;" value="{if !empty($DATA.text)}{$DATA.text|htmlentities}{else}Block {$block_id}{/if}" id="task_text"></td>
                {/if}
                <td class="dvtCellLabel" style="width:15%;text-align:right;padding-right:20px;" align=right nowrap="nowrap"><b> {helpurl url="workflowdesigner:tasks" height=28} {vtranslate("LBL_STATUS", "Settings:Workflow2")}</b></td>
                <td class="dvtCellInfo" align="left">
                    <select name="active" class="form-control select2" id="taskSelectActive" style="width:100px;">
                        <option value="true">{vtranslate("LBL_ACTIVE", "Settings:Workflow2")}</option>
                        <option value="false" {if $DATA.active eq "0"}selected='selected'{/if}>{vtranslate("LBL_INACTIVE", "Settings:Workflow2")}</option>
                    </select>
                </td>

			</tr>
		</table>

	<script src="modules/Workflow2/resources/jquery.timepicker.js" type="text/javascript" charset="utf-8"></script>

	<script src="modules/Workflow2/resources/functional.js" type="text/javascript" charset="utf-8"></script>
	<script src="modules/Workflow2/resources/VTUtils.js" type="text/javascript" charset="utf-8"></script>
	<script src="modules/Workflow2/resources/json2.js" type="text/javascript" charset="utf-8"></script>
	<script src="modules/Workflow2/resources/fieldvalidator.js" type="text/javascript" charset="utf-8"></script>
	<script src="modules/Workflow2/resources/edittaskscript.js?v={$smarty.const.WORKFLOW2_VERSION}" type="text/javascript" charset="utf-8"></script>
    <script src="modules/Workflow2/resources/vtigerwebservices.js" type="text/javascript" charset="utf-8"></script>
    <script src="libraries/jquery/jquery_windowmsg.js?&v=6.0.0" type="text/javascript" charset="utf-8"></script>
    <script type="text/javascript" src="modules/Workflow2/views/resources/js/complexecondition.js?v={$CURRENT_VERSION}"></script>
    <script type="text/javascript" src="modules/Workflow2/views/resources/js/jquery.form.min.js?v={$CURRENT_VERSION}"></script>
    <script type="text/javascript" src="modules/Workflow2/views/resources/js/rcswitcher/rcswitcher-4.0.min.js?v={$CURRENT_VERSION}"></script>

	<script type="text/javascript" charset="utf-8">

		var returnUrl = '/index.php?module=Workflow2&action=Workflow2Ajax&file=configBlock&id={$DATA.id}';
		var validator;
		edittaskscript(jQuery);
        function handleError(fn){
        	return function(status, result){
        		if(status){
        			fn(result);
        		} else {
        		    // alert('Failure:'+result);
                    wsFields = {};
        		}
        	};
        }
        function initChosen() {ldelim}
            jQuery(".chzn-select").select2();
            {*jQuery("#taskSelectActive").chosen({ldelim}disable_search_threshold: 3{rdelim});*}
            jQuery(".chzn-select-nosearch").select2();
        {rdelim}

        function saveTaskText(text) {ldelim}
            jQuery("#text_save_indicator").show();
            jQuery.post("index.php?module=Workflow2&parent=Settings&action=TaskSaveTitle", {ldelim} ajaxaction:'setTaskText', block_id:'{$block_id}', text:text{rdelim}, function() {ldelim}
                jQuery("#text_save_indicator").hide();
            {rdelim});
        {rdelim}

        jQuery(function() {ldelim}
            initChosen();


    jQuery(document).keydown(function(e) {ldelim}
               if ( (e.which == '115' || e.which == '83' ) && (e.ctrlKey || e.metaKey) )
               {ldelim}
                   e.preventDefault();
                   jQuery("#mainTaskForm").trigger("submit");
                   return false;
               {rdelim}
               else
               {ldelim}
                   return true;
               {rdelim}
           });
        {rdelim});

	</script>
	<script type="text/javascript">
        var WFD_VERSION = '{$CURRENT_VERSION}';
        var moduleName = '{$workflow_module_name}';
	</script>
    <script type="text/javascript">
        var MOD = {$MOD|@json_encode};
    </script>
    <script type="text/javascript">
        var oldTask = {$task|@json_encode};
    </script>
    <script type="text/javascript">
        /** For various field **/
        var dateFormat = '{$current_user.date_format}';
        var workflowID = {$workflowID};
        var workflowModuleName = moduleName;
    </script>
    <script type="text/javascript">
        {$additionalInlineJS}
    </script>
    {if $envSettings|@count gt 0}
    <div id="contEnvironmental" style="display:none;">
        <table class="tableHeading" border="0"  width="100%" cellspacing="0" cellpadding="5">
            <tr>
                <td class="big" nowrap="nowrap">
                    <strong>{vtranslate('LBL_ENVIRONMENTAL_VARS_HEAD', 'Settings:Workflow2')}</strong>
                </td>
            </tr>
        </table>
        <p style="font-style:italic;margin: 10px;">
          {$MOD.LBL_ENVIRONMENTAL_DESCRIPTION}
        </p>
        <table border="0" cellpadding="5" cellspacing="0" width="100%" class="small newTable">
            {foreach key=key item=value from=$envSettings}
            <tr>
                <td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{$value}</td>
                <td class='dvtCellInfo'>-> $env[<input type="text" name="task[env][{$key}]" value="{$task.env.$key}" class="form_input" style='margin:0 10px 0 5px;width: 250px;'>]</td>
            </tr>
            {/foreach}
        </table>
    </div>
    {/if}
            <div id="globalOptions" style="display:none;">
                <table class="tableHeading" border="0"  width="100%" cellspacing="0" cellpadding="5">
                    <tr>
                        <td class="big" nowrap="nowrap">
                            <strong>{vtranslate('General Task Options', 'Settings:Workflow2')} <a href="https://support.redoo-networks.com/knowledgebase/global-task-options/" target="_blank"><i class="icon-question-sign"></i></a></strong>

                        </td>
                    </tr>
                </table>
                <table border="0" cellpadding="5" cellspacing="0" width="100%" class="small newTable">
                    <tr>
                        <td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('Use/Output currencies formated, instead of full precision','Settings:Workflow2')}&nbsp;&nbsp;<a href="https://support.redoo-networks.com/knowledgebase/global-task-options/#formatcurrencies" target="_blank"><i class="icon-question-sign"></i></a></td>
                        <td class='dvtCellInfo'><input type="checkbox" name="task[__int][currenciesformat]" {if $task['__int']['currenciesformat'] eq '1'}checked="checked"{/if} class="rcSwitch doInit" value="1" style='margin:0 10px 0 5px;width: 250px;'></td>
                    </tr>
                </table>
            </div>

            <h5 style="border-bottom:1px solid #ccc;padding:5px 0;">
                {if $envSettings|@count gt 0}<a style="font-size:12px;float:right;" href="#" onclick="jQuery('#contEnvironmental').slideToggle('fast');return false;">{$MOD.LBL_ENVIRONMENTAL_VARS_HEAD}</a>{/if}
                <a style="font-size:12px;float:right;margin-right:50px;" href="#" onclick="jQuery('#globalOptions').slideToggle('fast');return false;">{vtranslate('General Task Options', 'Settings:Workflow2')}</a>

                {vtranslate('LBL_AUSGABENBEZEICHNUNG','Settings:Workflow2')}
            </h5>
            {if $SHOW_FRONTEND_NOTICE eq 'yes'}
                <div style='background-color:#09e200;padding:5px;text-align:left;margin-bottom:5px;'><i class="icon-ok-sign" style="margin-top:2px;"></i>&nbsp;&nbsp;&nbsp;{vtranslate('Edit View show direct result', 'Settings:Workflow2')}</div>
            {/if}
            {if $SHOW_FRONTEND_NOTICE eq 'no'}
                <div style='background-color:#fed22f;padding:5px;text-align:left;margin-bottom:5px;'><i class="icon-remove-sign" style="margin-top:2px;"></i>&nbsp;&nbsp;&nbsp;<strong>{vtranslate('Task not show result in EditView', 'Settings:Workflow2')}</strong></div>
            {/if}


            {$CONTENT}
		</form>
</div>