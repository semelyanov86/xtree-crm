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
{strip}
    <div class="container-fluid" id="moduleManagerContents">
        <div class="widget_header row-fluid">
            <div class="span6">
                <h3>{vtranslate('Workflow Designer', 'Workflow2')} &raquo; {vtranslate('Folderview', 'Settings:Workflow2')}
                </h3>
            </div>
        </div>
        <hr>

        <div class="listViewContentDiv" id="listViewContents">
            {if $SHOW_EVENT_NOTICE eq true}
                <div class="alert alert-error">
                    <strong>{vtranslate('The 2 Workflow Designer Eventhandlers could not be found in your system. Please check your database and run the "check DB" function.', 'Settings:Workflow2')}</strong>
                </div>
            {/if}
            {if $SHOW_CRON_NOTICE eq true}
                <div class="alert alert-error">
                    <strong>{vtranslate('Please check your VtigerCRM cron setup! No cron was executed in last 24 hours. Otherwise you will get problems with some functions of Workflow Designer.', 'Settings:Workflow2')}</strong>&nbsp;&nbsp;&nbsp;<a class="btn" href="https://wiki.vtiger.com/index.php/Cron" target="_blank">{vtranslate('open','Settings:Workflow2')|ucfirst}
                        {vtranslate('Information','Settings:Workflow2')}</strong></a>
                </div>
            {/if}
            {if empty($ERROR_HANDLER_VALUE)}
                <div class="alert alert-error">
                    {vtranslate('Please configure Log Management to receive errors!', 'Settings:Workflow2')}&nbsp;&nbsp;&nbsp;<a class="btn" href="index.php?module=Workflow2&view=SettingsLogging&parent=Settings">{vtranslate('open','Settings:Workflow2')|ucfirst}
                        {vtranslate('LBL_SETTINGS_LOGGING','Settings:Workflow2')}</strong></a>
                </div>
            {/if}

            <div class="row-fluid" style="margin-left:0;">
                <span class='btn-toolbar span4'>
                    <button type="button" class="btn addButton addWorkflowButton"><i class="icon-plus"></i>
                        <strong>{vtranslate("Create new workflow","Settings:Workflow2")}</strong></button>
                    <button type="button" class="btn addButton" onclick="importWorkflow();"><i class="icon-file"></i>
                        <strong>{vtranslate("import Workflow","Settings:Workflow2")}</strong></button>
                </span>

                <span class="btn-toolbar span5" style="display:flex;">
                    <select style="width:80%;" id="overviewFolder">
                        <option value="0">{vtranslate('all folders', 'Settings:Colorizer')}</option>
                        {foreach from=$availFolder item=folder}
                            <option value="{$folder|@urlencode}" {if ($targetFolder eq $folder)}selected='selected' {/if}>
                                {$folder}</option>
                        {/foreach}
                    </select>
                    <a href="index.php?module=Workflow2&view=Index&parent=Settings&viewmode=module" style="line-height:30px;margin-left:20px;width:20%;">{vtranslate('Moduleview', 'Settings:Workflow2')}</a>
                    <i class="icon-search SearchField" style="margin-top:8px;"></i>
                </span>

                <span class='btn-toolbar span3'>
                    {if $is_admin eq true}
                        <button class="btn btn-success pull-right" onclick="window.location.href='index.php?module=Workflow2&view=Upgrade&parent=Settings';"><i class=" icon-white icon-asterisk"></i>
                            <strong>{vtranslate("LBL_UPDATE_MODULE","Settings:Workflow2")}</strong></button>
                    {/if}
                </span>
            </div>

            {foreach from=$workflows item=moduleArray key=folderName}
                <div data-folder="{$folderName}" class="WorkflowFolder {if $visibility[$folderName] eq '1'}FolderOpened{else}FolderClosed{/if}" style="{if !empty($folderSettings[$folderName])}background-color:{$folderSettings[$folderName]['color']};{/if}">
                    <div class="WorkflowFolderTitle" style="text-transform:uppercase;">
                        <div class="WorkflowFolderImageSelector"></div>
                        <span class="WorkflowFolderModules">{$workflowModules[$folderName]}</span>
                        <i class="icon-folder-close"></i>
                        <i class="icon-folder-open"></i>
                        &nbsp;<span class="WorkflowFolderTitleContainer"><b>&nbsp;{$folderName}</b></span>
                        ({$folderCounts[$folderName]}) <i class="icon-edit editFolderTitle"></i>
                    </div>
                    <div class="FolderContent">
                        {foreach from=$moduleArray item=workflowArray key=moduleName}
                            <h5>{$moduleName}</h5>

                            <table class="WorkflowList" width="100%" cellspacing="0" cellpadding="4">
                                <tbody>
                                    {foreach from=$workflowArray item=workflow}
                                        <tr class='workflowOverview' data-search="{$workflow.title|@strtolower}" data-id="{$workflow.id}" data-name="{$workflow.title}" style="background-color:{if $workflow["active"]=="0"}#ffffff{else}#F0FFEB{/if};">
                                            <td width="10" style="padding:0;margin:0;font-size:1px;background-color:transparent !important;">&nbsp;
                                            </td>
                                            <td class="dvtCellInfo buttonbar" style="width:30px;text-align:center;">
                                                {if $workflow["active"]=="0"}
                                                    <img src='modules/Workflow2/icons/play.png' style="cursor:pointer;" alt="{vtranslate("Activate","Settings:Workflow2")}" title="{vtranslate("Activate","Settings:Workflow2")}" onclick="window.location.href='index.php?module=Workflow2&view=Index&parent=Settings&workflow={$workflow.id}&act=activate';">
                                                    <!--<input type="button" class="button green"value="" />-->
                                                {else}
                                                    <img src='modules/Workflow2/icons/stop.png' style="cursor:pointer;" alt="{vtranslate("Deactivate","Settings:Workflow2")}" title="{vtranslate("Deactivate","Settings:Workflow2")}" onclick="window.location.href='index.php?module=Workflow2&view=Index&parent=Settings&workflow={$workflow.id}&act=deactivate';">
                                                    <!--<input type="button" class="button red" value="<?php echo getTranslatedString("Deactivate", "Workflow2") ?>" />-->
                                                {/if}
                                            </td>
                                            <td class="dvtCellInfo" style="width:70px;" title="{vtranslate('last modified by', 'Settings:Workflow2')} {$workflow.user_name} ({$workflow.modify})">
                                                ID {$workflow.id}</td>
                                            <td class="dvtCellInfo" style="{if $workflow.active=="1"}font-weight:bold;{/if}"><span style="cursor: pointer;" onclick="window.location.href='index.php?module=Workflow2&view=Config&parent=Settings&workflow={$workflow.id}'">{$workflow.title}</span>{if $workflow.errornum > 0}<span style="margin-left:30px;color:red;font-weight:bold;cursor:pointer;" onclick="window.open('index.php?module=Workflow2&view=ErrorLog&parent=Settings&workflow_id={$workflow.id}', '', 'width=700,height=800');">{$workflow.errornum}
                                                {vtranslate('errors during last 7 days', 'Settings:Workflow2')}</span>{/if}
                                            <span style='float:right;color:#aaa;font-style:normal;'>{$workflow.startCondition}</span></span>
                                        </td>
                                        <td class="dvtCellInfo {if $workflow.active=="1"}activeWorkflow{else}inactiveWorkflow{/if}">
                                            {if $workflow.active=="0"}{vtranslate("LBL_INACTIVE","Settings:Workflow2")}{else}{vtranslate("LBL_ACTIVE","Settings:Workflow2")}{/if}
                                        </td>
                                        <td class="dvtCellInfo" style="background-color:#fff;width:500px;text-align:center;">
                                            <div class="buttonbar inline" style="float: left;margin-right:20px;">
                                                <input type="button" class="btn green" onclick="window.location.href='index.php?module=Workflow2&view=Config&parent=Settings&workflow={$workflow.id}';" value="{vtranslate("Edit","Settings:Workflow2")}" />
                                            </div>
                                            <div class="btn-group inline" style="float: left;">
                                                <input type="button" class="btn yellow" onclick="window.location.href='index.php?module=Workflow2&view=Statistic&parent=Settings&workflow={$workflow.id}';" value="{vtranslate("Statistics","Settings:Workflow2")}" />
                                                <input type="button" class="btn yellow" onclick="window.location.href='index.php?module=Workflow2&view=Authmanager&parent=Settings&workflow={$workflow.id}';" value="{vtranslate("BTN_AUTH_MANAGEMENT","Settings:Workflow2")}" />
                                                <input type="button" data-id="{$workflow.id}" class="btn yellow exportWFBtn" value="{vtranslate("Export","Settings:Workflow2")}" />
                                            </div>
                                            <div class="WFIconBar">
                                                <i class="fa fa-files-o fa-2x" title="{vtranslate('LBL_DUPLICATE')}" aria-hidden="true" onclick="window.location.href='index.php?module=Workflow2&view=Index&parent=Settings&workflow={$workflow.id}&act=duplicate';"></i>
                                                <i class="fa fa-trash-o fa-2x" title="{vtranslate('Delete')}" aria-hidden="true" onclick="if(confirm(app.vtranslate('WF_DELETE_CONFIRM'))) window.location.href='index.php?module=Workflow2&view=Index&parent=Settings&workflow={$workflow.id}&act=delete';"></i>
                                                <i class="fa fa-eye fa-2x WFChangeVisibility" aria-hidden="true" data-value="0" title="{vtranslate('Workflow is visible for Users')}" style="color:green;  {if $workflow.invisible eq '1'}display:none;{/if}"></i>
                                                <i class="fa fa-eye-slash fa-2x WFChangeVisibility" data-value="1" aria-hidden="true" title="{vtranslate('Workflow is invisible for Users')}" style="color:red;  {if $workflow.invisible eq '0'}display:none;{/if}"></i>
                                            </div>
                                        </td>
                                        <td width="10" style="padding:0;margin:0;font-size:1px;background-color:#fff;"><i title="{vtranslate('Move to new folder', 'Settings:Workflow2')}" class="icon-share MoveToNewFolder"></i></td>
                                    </tr>
                                {/foreach}
                            </tbody>
                        </table>

                    {/foreach}
                </div>
            </div>
        {/foreach}

        <div style="margin-top:5px;text-align:center;">
            <?php echo getTranslatedString("This Workflow administration needs IE9+, Google Chrome, Firefox or Safari!", "Workflow2"); ?><br><strong>
                <?php echo getTranslatedString("Do not open a Workflow with IE < 9!"); ?>
            </strong>
        </div>

        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <link href="modules/Workflow2/views/resources/js/notifications/main.css" rel="stylesheet" type="text/css" media="screen" />

        <script src="modules/Workflow2/views/resources/js/jscolor/jscolor.js" type="text/javascript"></script>
        <script src="modules/Workflow2/views/resources/js/notifications/js/notification-min.js"></script>
        <script type="text/javascript">
            var exportPromptText = "{vtranslate("You could set a password to protect the export file.","Settings:Workflow2")}";
        </script>
{/strip}