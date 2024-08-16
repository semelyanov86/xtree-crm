{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/
-->*}

<div class="container-fluid" id="moduleManagerContents">
    <div class="editViewHeader">
        <h4>
            <a
                href="index.php?module=Workflow2&view=Index&parent=Settings">{vtranslate('Workflow Designer', 'Workflow2')}</a>
        </h4>
    </div>
    <hr>
    <div class="listViewActionsDiv">
        {if $SHOW_PHP72_WARNING eq true}
            <div class="alert alert-danger">
                {vtranslate('Workflow Designer raise system requirements to at least version 7.0.0, because of features and performance.<br/><strong>You still use a PHP version lower then 7.0.0.</strong>', 'Settings:Workflow2')}<br />
                <br />
                <a class="btn btn-primary" href="https://drive.redoo-networks.com/f/5377800bcf3d4b2bab8d/?dl=1">Download
                    7.03.05</a>
            </div>
        {/if}
        {if $SHOW_EVENT_NOTICE eq true}
            <div class="alert alert-danger">
                <strong>{vtranslate('The 2 Workflow Designer Eventhandlers could not be found in your system. Please check your database and run the "check DB" function.', 'Settings:Workflow2')}</strong>
            </div>
        {/if}
        {if $SHOW_CRON_NOTICE eq true}
            <div class="alert alert-danger">
                <strong>{vtranslate('Please check your VtigerCRM cron setup! No cron was executed in last 24 hours. Otherwise you will get problems with some functions of Workflow Designer.', 'Settings:Workflow2')}</strong>&nbsp;&nbsp;&nbsp;<a
                    class="btn  btn-primary" href="https://wiki.vtiger.com/index.php/Cron"
                    target="_blank">{vtranslate('open','Settings:Workflow2')|ucfirst}
                    {vtranslate('Information','Settings:Workflow2')}</strong></a>
            </div>
        {/if}
        {if empty($ERROR_HANDLER_VALUE)}
            <div class="alert alert-danger">
                {vtranslate('Please configure Log Management to receive errors!', 'Settings:Workflow2')}&nbsp;&nbsp;&nbsp;<a
                    class="btn btn-primary"
                    href="index.php?module=Workflow2&view=SettingsLogging&parent=Settings">{vtranslate('open','Settings:Workflow2')|ucfirst}
                    {vtranslate('LBL_SETTINGS_LOGGING','Settings:Workflow2')}</strong></a>
            </div>
        {/if}

        <div class="row" style="">
            <span class='btn-group col-lg-4'>
                <button type="button" class="btn addButton btn-default module-buttons addWorkflowButton">
                    <div class="fa fa-plus" aria-hidden="true"></div>
                    &nbsp;&nbsp;<strong>{vtranslate("Create new workflow","Settings:Workflow2")}</strong>
                </button>
                <button type="button" class="btn addButton btn-default module-buttons" onclick="importWorkflow();">
                    <div class="fa fa-plus" aria-hidden="true"></div>
                    &nbsp;&nbsp;<strong>{vtranslate("import Workflow","Settings:Workflow2")}</strong>
                </button>
            </span>

            <span class="btn-toolbar col-lg-5" style="display:flex;">

                <select class="" style="width:80%;" id="overviewModule">
                    <option value="0">{vtranslate('all modules', 'Settings:Colorizer')}</option>
                    {foreach from=$entityModules item=module key=tabid}
                        <option value="{$tabid}" {if ($targetModule eq $tabid)}selected='selected' {/if}>{$module.1}
                            ({if !empty($moduleWfCount[$tabid])}{$moduleWfCount[$tabid][0] + $moduleWfCount[$tabid][1]} /
                                {$moduleWfCount[$tabid][1]} {vtranslate('LBL_ACTIVE', 'Settings:Workflow2')}
                            {else}0
                            {/if})
                        </option>
                    {/foreach}
                </select>
                {*<a href="index.php?module=Workflow2&view=Index&parent=Settings&viewmode=folder" style="line-height:30px;margin-left:20px;width:20%;">{vtranslate('Folderview', 'Settings:Workflow2')}</a>*}
                <i class="icon-search SearchField" style="margin-top:8px;margin-left:20px;"></i>
            </span>

            <span class='btn-toolbar col-lg-3'>
                {if $is_admin eq true}
                    <button class="btn btn-success pull-right UpdateCheckModule" data-module="Workflow2"><i
                            class=" icon-white icon-asterisk"></i>
                        <strong>{vtranslate("LBL_UPDATE_MODULE","Settings:Workflow2")}</strong></button>
                {/if}
            </span>
        </div>

        {foreach from=$workflows item=workflowArray key=moduleName}
            <div class="workflowModuleHeader" data-target="{$workflowArray[0]['module_name']}">
                {*<span class="pull-right"><a style='color:white;' id="toggleSidebarButton_{$workflowArray[0]['module_name']}" onclick="toggleSidebar('{$workflowArray[0]['module_name']}','toggleSidebarButton_{$workflowArray[0]['module_name']}');return false;" href='#'>{if $workflowArray[0]['sidebar_active'] eq false}{vtranslate('LBL_ACTIVATE_SIDEBAR', 'Settings:Workflow2')}{else}{vtranslate('LBL_DEACTIVATE_SIDEBAR', 'Settings:Workflow2')}{/if}</a></span>*}
                {if count($workflows) > 1}
                    <div style="float:left;font-weight:bold;text-transform:uppercase;">
                        <img src="modules/Workflow2/icons/toggle_minus.png" class="toggleImageCollapse toggleImage"
                            style="{if $visibility[$workflowArray[0]['module_name']] eq false}display:none;{/if}" />
                        <img src="modules/Workflow2/icons/toggle_plus.png" class="toggleImageExpand toggleImage"
                            style="{if $visibility[$workflowArray[0]['module_name']] eq true}display:none;{/if}" />
                        &nbsp;<b>&nbsp;{$moduleName}</b> ({count($workflowArray)})
                    </div>
                {else}
                    <div style="float:left;font-weight:bold;text-transform:uppercase;">
                        &nbsp;<b>&nbsp;{$moduleName}</b> ({count($workflowArray)})
                    </div>
                {/if}
            </div>

            <table width="100%" class="WorkflowModuleTable" cellspacing="0" cellpadding="4"
                style="{if $visibility[$workflowArray[0]['module_name']] eq false && count($workflows) > 1}display:none;{/if}border-collapse:collapse;"
                id="workflowList{$workflowArray[0]['module_name']}"
                data-visible="{if $visibility[$workflowArray[0]['module_name']] eq true}1{else}0{/if}">
                {foreach from=$workflowArray item=workflow}
                    <tr class='workflowOverview' data-search="{$workflow.title|@strtolower}" data-id="{$workflow.id}"
                        style="background-color:{if $workflow["active"]=="0"}#ffffff{else}#F0FFEB{/if};">
                        <td width="10" style="padding:0;margin:0;font-size:1px;background-color:transparent !important;">&nbsp;
                        </td>
                        <td class="dvtCellInfo" style="width:30px;text-align:center;">
                            {if $workflow["active"]=="0"}
                                <i class="fa fa-play" aria-hidden="true" style="font-size:15px;color:#1b7e5a;cursor:pointer;"
                                    alt="{vtranslate("Activate","Settings:Workflow2")}"
                                    title="{vtranslate("Activate","Settings:Workflow2")}"
                                    onclick="window.location.href='index.php?module=Workflow2&view=Index&parent=Settings&workflow={$workflow.id}&act=activate';"></i>
                                {*<img src='modules/Workflow2/icons/play.png' style="cursor:pointer;" alt="{vtranslate("Activate","Settings:Workflow2")}" title="{vtranslate("Activate","Settings:Workflow2")}" onclick="window.location.href='index.php?module=Workflow2&view=Index&parent=Settings&workflow={$workflow.id}&act=activate';" >*}
                                <!--<input type="button" class="button green"value="" />-->
                            {else}
                                <i class="fa fa-stop" aria-hidden="true" style="font-size:15px;color:#de2550;cursor:pointer;"
                                    alt="{vtranslate("Deactivate","Settings:Workflow2")}"
                                    title="{vtranslate("Deactivate","Settings:Workflow2")}"
                                    onclick="window.location.href='index.php?module=Workflow2&view=Index&parent=Settings&workflow={$workflow.id}&act=deactivate';"></i>

                                {*<img src='modules/Workflow2/icons/stop.png' style="cursor:pointer;" alt="{vtranslate("Deactivate","Settings:Workflow2")}" title="{vtranslate("Deactivate","Settings:Workflow2")}" onclick="window.location.href='index.php?module=Workflow2&view=Index&parent=Settings&workflow={$workflow.id}&act=deactivate';">*}
                                <!--<input type="button" class="button red" value="<?php echo getTranslatedString("Deactivate", "Workflow2") ?>" />-->
                            {/if}
                        </td>
                        <td class="dvtCellInfo" style="width:70px;"
                            title="{vtranslate('last modified by', 'Settings:Workflow2')} {$workflow.user_name} ({$workflow.modify})">
                            ID {$workflow.id}</td>
                        <td class="dvtCellInfo"
                            style="font-size:12px;cursor:pointer;{if $workflow.active=="1"}font-weight:bold;{/if}"
                            onclick="window.location.href='index.php?module=Workflow2&view=Config&parent=Settings&workflow={$workflow.id}'">
                            <span style="cursor: pointer;">{$workflow.title}</span>{if $workflow.errornum > 0}<span
                                    style="margin-left:30px;color:red;font-weight:bold;cursor:pointer;"
                                    onclick="window.open('index.php?module=Workflow2&view=ErrorLog&parent=Settings&workflow_id={$workflow.id}', '', 'width=700,height=800');">{$workflow.errornum}
                                {vtranslate('errors during last 7 days', 'Settings:Workflow2')}</span>{/if}
                            <span style='float:right;color:#aaa;font-style:normal;'>{$workflow.startCondition}</span></span>
                        </td>
                        <td class="dvtCellInfo {if $workflow.active=="1"}activeWorkflow{else}inactiveWorkflow{/if}">
                            {if $workflow.active=="0"}{vtranslate("LBL_INACTIVE","Settings:Workflow2")}{else}{vtranslate("LBL_ACTIVE","Settings:Workflow2")}{/if}
                        </td>
                        <td class="dvtCellInfo" style="background-color:#fff;width:40%;min-width:600px;text-align:center;">
                            <div class="buttonbar inline" style="float: left;margin-right:20px;">
                                <input type="button" class="btn btn-primary"
                                    onclick="window.location.href='index.php?module=Workflow2&view=Config&parent=Settings&workflow={$workflow.id}';"
                                    value="{vtranslate("Edit","Settings:Workflow2")}" />
                            </div>
                            <div class="btn-group inline" style="float: left;">
                                <input type="button" class="btn btn-default"
                                    onclick="window.location.href='index.php?module=Workflow2&view=Statistic&parent=Settings&workflow={$workflow.id}';"
                                    value="{vtranslate("Statistics","Settings:Workflow2")}" />
                                <input type="button" class="btn btn-default"
                                    onclick="window.location.href='index.php?module=Workflow2&view=Authmanager&parent=Settings&workflow={$workflow.id}';"
                                    value="{vtranslate("BTN_AUTH_MANAGEMENT","Settings:Workflow2")}" />
                                <input type="button" data-id="{$workflow.id}" class="btn btn-default yellow exportWFBtn"
                                    value="{vtranslate("Export","Settings:Workflow2")}" />
                                <input type="button" data-id="{$workflow.id}" class="btn btn-default yellow viewBPMNBtn"
                                    value="{vtranslate("BPMN","Settings:Workflow2")}" />
                            </div>
                            <div class="WFIconBar">
                                <i class="fa fa-files-o fa-2x" title="{vtranslate('LBL_DUPLICATE')}" aria-hidden="true"
                                    onclick="window.location.href='index.php?module=Workflow2&view=Index&parent=Settings&workflow={$workflow.id}&act=duplicate';"></i>
                                <i class="fa fa-trash-o fa-2x" title="{vtranslate('Delete')}" aria-hidden="true"
                                    onclick="if(confirm(app.vtranslate('WF_DELETE_CONFIRM'))) window.location.href='index.php?module=Workflow2&view=Index&parent=Settings&workflow={$workflow.id}&act=delete';"></i>
                                <i class="fa fa-eye fa-2x WFChangeVisibility" aria-hidden="true" data-value="0"
                                    title="{vtranslate('Workflow is visible for Users')}"
                                    style="color:green;  {if $workflow.invisible eq '1'}display:none;{/if}"></i>
                                <i class="fa fa-eye-slash fa-2x WFChangeVisibility" data-value="1" aria-hidden="true"
                                    title="{vtranslate('Workflow is invisible for Users')}"
                                    style="color:red;  {if $workflow.invisible eq '0'}display:none;{/if}"></i>
                            </div>
                        </td>
                        <td width="10" style="padding:0;margin:0;font-size:1px;background-color:transparent !important;">&nbsp;
                        </td>
                    </tr>
                {/foreach}
            </table>
        {/foreach}
        <div style="margin-top:5px;text-align:center;">
            <?php echo getTranslatedString("This Workflow administration needs IE9+, Google Chrome, Firefox or Safari!", "Workflow2"); ?><br><strong>
                <?php echo getTranslatedString("Do not open a Workflow with IE < 9!"); ?>
            </strong>
        </div>


        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"
            type="text/css" />
        <link href="modules/Workflow2/views/resources/js/notifications/main.css" rel="stylesheet" type="text/css"
            media="screen" />
        <script src="modules/Workflow2/views/resources/js/notifications/js/notification-min.js"></script>


        <script type="text/javascript">
            var exportPromptText = "{vtranslate("You could set a password to protect the export file.","Settings:Workflow2")}";
</script>