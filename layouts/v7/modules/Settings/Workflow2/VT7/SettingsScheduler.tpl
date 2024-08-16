<div class="container-fluid WorkflowSchedulerContainer" id="moduleManagerContents">
    <div class="editViewHeader">
        <h4>
            <a href="index.php?module=Workflow2&view=Index&parent=Settings">{vtranslate('Workflow Designer', 'Workflow2')}</a> &raquo;
            {vtranslate('LBL_SETTINGS_SCHEDULER', 'Workflow2')}
        </h4>
    </div>
    <hr/>

    <div class="listViewActionsDiv">
        <div class="alert alert-info">
            {vtranslate('To give you the most power, scheduled Workflows are configured like a cronjob in Unix based systems. You found a generator and information here: %s', 'Settings:Workflow2')|sprintf:'<a href="http://www.openjs.com/scripts/jslibrary/demos/crontab.php" target="_blank"><strong>http://www.openjs.com/scripts/jslibrary/demos/crontab.php</strong></a>'}
        </div>
        {if $SHOW_CRON_NOTICE eq true}
            <div class="alert alert-danger">
                <strong>{vtranslate('Unless you configure Cronjobs of VtigerCRM this feature will NOT work.', 'Settings:Workflow2')}</strong>&nbsp;&nbsp;&nbsp;<a class="btn btn-primary" href="https://wiki.vtiger.com/index.php/Cron" target="_blank">{vtranslate('open','Settings:Workflow2')|ucfirst} {vtranslate('Information','Settings:Workflow2')}</strong></a>
            </div>
        {/if}

        {if count($schedules) eq 0}
    No Schedules configured
    {else}
        <table cellspacing="0" class='table'>
            <tr class="listViewHeaders">
                <th></th>
                <th>{vtranslate('Workflow','Settings:Workflow2')}</th>
                <th style="width:140px;"></th>
                <th style="width:100px;">{vtranslate('Load Preset','Settings:Workflow2')}</th>
                <th style="width:100px;">{vtranslate('LBL_MINUTES','Settings:Workflow2')}</th>
                <th style="width:100px;">{vtranslate('LBL_HOURS','Settings:Workflow2')}</th>
                <th style="width:100px;">{vtranslate('Day of Month','Settings:Workflow2')}</th>
                <th style="width:100px;">{vtranslate('Month','Settings:Workflow2')}</th>
                <th style="width:100px;">{vtranslate('Day of Week','Settings:Workflow2')}</th>
                <th style="width:100px;">{vtranslate('Year','Settings:Workflow2')}</th>
                <th style="width:100px;">{vtranslate('Timezone','Settings:Workflow2')}</th>
                <th></th>
            </tr>

        {foreach from=$schedules key=modulename item=crons}
            <tr>
                <th colspan="12">{vtranslate($modulename, $modulename)}</th>
            </tr>
            {foreach from=$crons item=cron}
                <tr class="cronRow cronRow_{$cron.id} FirstSchedulerRow {if $cron.active eq '1'}active{else}inactive{/if} {if empty($cron.workflow_id)}noworkflow{/if}" data-id="{$cron.id}">
                    <td style="width: 70px;vertical-align: middle;">
                        <select data-sid='{$cron.id}' data-field='active' style="width: 70px;margin: 0;">
                            <option value='0' {if $cron.active eq '0'}selected="selected"{/if}>{vtranslate('LBL_INACTIVE','Settings:Workflow2')}</option>
                            <option value='1' {if $cron.active eq '1'}selected="selected"{/if}>{vtranslate('LBL_ACTIVE','Settings:Workflow2')}</option>
                        </select>
                    </td>
                    <td style="width: 230px;vertical-align: middle;">
                        <select data-sid='{$cron.id}' data-field='workflow_id' class="select2" style="width: 230px;" id="workflowSelection_{$cron.id}">
                            <option value='0' {if $workflow_id eq '0'}selected="selected"{/if}>{vtranslate('LBL_NO_WORKFLOW','Settings:Workflow2')}</option>
                            {foreach from=$workflows item=module key=module_name}
                                <optgroup label='{$module_name}'>
                                {foreach from=$module item=workflow key=workflow_id}
                                    <option data-module="{$workflow.module_name}" value='{$workflow_id}' {if $workflow_id eq $cron.workflow_id}selected="selected"{/if}>{$workflow.title}</option>
                                {/foreach}
                                </optgroup>
                            {/foreach}
                        </select>
                    </td>
                    <td>
                        <input type="checkbox" data-sid='{$cron.id}' data-field='enable_records' name="choose_records" value="1" {if $cron.enable_records eq 1}checked="checked"{/if} />
                        <input type="hidden" class="configField" name="records_{$cron.id}" id="records_{$cron.id}" data-sid='{$cron.id}' data-field='condition' value="{$cron.condition}" />
                        <input type="button" class="btn btn-default RecordChooseBtn" data-sid='{$cron.id}' onclick="" value="{vtranslate('select Records', 'Settings:Workflow2')}" />
                    </td>
                    <td style="vertical-align: middle;">
                        <select class="CronPreset select2" style="width:150px;">
                            <option value=""></option>
                            <option value="0;6;*;*;*;*">{vtranslate('Daily', 'Settings:Workflow2')}</option>
                            <option value="0;6;*;*;Mon-Fri;*">{vtranslate('Workdays once', 'Settings:Workflow2')}</option>
                            <option value="0;*;*;*;Mon-Fri;*">{vtranslate('Workdays hourly', 'Settings:Workflow2')}</option>
                            <option value="0;6;*;*;Mon;*">{vtranslate('Every Monday', 'Settings:Workflow2')}</option>
                            <option value="0;6;1;*;*;*">{vtranslate('Monthly first day', 'Settings:Workflow2')}</option>
                        </select>
                    </td>
                    <td style="vertical-align: middle;"><input type="text" class="defaultTextfield" style='width: 100%;margin: 0;' data-sid='{$cron.id}' data-field='minute' value='{$cron.minute}'></td>
                    <td style="vertical-align: middle;"><input type="text" class="defaultTextfield" style='width: 100%;margin: 0;' data-sid='{$cron.id}' data-field='hour' value='{$cron.hour}'></td>
                    <td style="vertical-align: middle;"><input type="text" class="defaultTextfield" style='width: 100%;margin: 0;' data-sid='{$cron.id}' data-field='dom' value='{$cron.dom}'></td>
                    <td style="vertical-align: middle;"><input type="text" class="defaultTextfield" style='width: 100%;margin: 0;' data-sid='{$cron.id}' data-field='month' value='{$cron.month}'></td>
                    <td style="vertical-align: middle;"><input type="text" class="defaultTextfield" style='width: 100%;margin: 0;' data-sid='{$cron.id}' data-field='dow' value='{$cron.dow}'></td>
                    <td style="vertical-align: middle;"><input type="text" class="defaultTextfield" style='width: 100%;margin: 0;' data-sid='{$cron.id}' data-field='year' value='{$cron.year}'></td>
                    <td style="vertical-align: middle;">
                        <select class="select2" style="width:160px"data-field='timezone' data-sid='{$cron.id}'>
                            <option value="UTC">UTC</option>
                            <option value="default" {if $cron.timezone eq 'default'}selected="selected"{/if}>CRM Default Timezone</option>
                        </select>
                    </td>
                    <td style="width: 60px;vertical-align: middle;"><a href="#" onclick='Scheduler.delScheduler({$cron.id});return false;'>{vtranslate('delete','Settings:Workflow2')}</a></td>
                </tr>
                <tr class="cronRow cronRow_{$cron.id} SecondSchedulerRow {if $cron.active eq '1'}active{else}inactive{/if} {if empty($cron.workflow_id)}noworkflow{/if}">
                    <td colspan="3" style="border-top:none !important;"></td>
                    <td colspan="9" style="border-top:none !important;">{vtranslate('Next execution:', 'Settings:Workflow2')} <span class="NextExecutionTimer">{$cron.next_execution} {$cron.timezone_display}</span></td>
                </tr>
            {/foreach}
        {/foreach}
        </table>
        {/if}
        <strong>{vtranslate('To enter specific hours, you need to use UTC timezone!', 'Settings:Workflow2')} ({vtranslate('Current time in UTC', 'Settings:Workflow2')}: {$currentUTCTime})</strong>
        <br/>
        <br/>
        <button class='btn btn-primary' type="button" onclick='Scheduler.newScheduler();'><strong>{vtranslate('new Entry','Settings:Workflow2')}</strong></button>
    </div>
</div>
<script type="text/javascript">
    var WFDTexts = {
        'Choose Records' : '{vtranslate('Choose Records', 'Settings:Workflow2')}',
    };
</script>
<script type="text/javascript" src="modules/Workflow2/views/resources/js/complexecondition.js?v={$CURRENT_VERSION}"></script>
<style>
    .main-container .module-nav {
        z-index:10903;
    }
</style>