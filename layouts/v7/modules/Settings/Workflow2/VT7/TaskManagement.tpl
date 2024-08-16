<div class="container-fluid" id="moduleManagerContents">
    <div class="editViewHeader">
        <h4>
            <a href="index.php?module=Workflow2&view=Index&parent=Settings">{vtranslate('Workflow Designer', 'Workflow2')}</a> &raquo;
            {vtranslate('Task Management', 'Workflow2')}
        </h4>
    </div>
    <hr/>

    <div class="listViewActionsDiv">
        {foreach from=$messages item=msg}
            {if $msg.0 eq 'error'}
                <div class="alert alert-error">
                    <strong>{$msg.1}</strong>
                    {$msg.2}
                </div>
            {elseif $msg.0 eq 'info'}
                <div class="alert">
                    <strong>{$msg.1}</strong>
                    {$msg.2}
                </div>
            {else}
                <div class="alert-info">
                    <strong>{$msg.1}</strong>
                    {$msg.2}
                </div>
            {/if}
        {/foreach}

    <button type="button" class="pull-left btn btn-success" onclick="updateRepositories();">{vtranslate('LBL_TASK_REPO_UPDATE', 'Settings:Workflow2')}</button>

    <button type="button" class="pull-right btn btn-primary" onclick="window.location.href='index.php?module=Workflow2&view=TaskRepoManager&parent=Settings';">{vtranslate('LBL_TASK_REPO_MANAGEMENT', 'Settings:Workflow2')}</button>
    <button type="button" class="pull-right btn btn-warning" onclick="importTaskfile();">{vtranslate('LBL_TASK_IMPORT_FILE', 'Settings:Workflow2')}</button>
    <br/>
    <br/>

<table style="margin: 10px 0 0 0px;" class="table table-striped table-bordered table-hover table-condensed">
    <tr>
        <td width="230">Task</td>
        <td width="50">installed</td>
        <td width="50">latest</td>
        <td width="150">last check</td>
        <td>Update available</td>
    </tr>
    {foreach from=$blocks item=tasks key=repo_title}
        <tr class="info" height="25">
            <td colspan='4' style="font-weight: bold;background-color:#e0e0e0;line-height:28px;">&nbsp;&nbsp;{$repo_title}</td>
            <td style="background-color:#e0e0e0;">
                {if $tasks.no_update neq true}
                    <button class='btn btn-info' data-install="{$task.id}" onclick='installAll({$tasks.repo_id});'>Install all new</button>
                    <button class='btn btn-info' data-install="{$task.id}" onclick='installAllUpdates({$tasks.repo_id});'>Install all updates</button>
                {/if}
            </td>
        </tr>
        {foreach from=$tasks.core item=task}
            <tr height="20">
                <td><strong>Core:</strong> {$task.text}</td>
                <td>{$task.version}</td>
                <td>{$task.latest_version} {if $task.status neq 'stable'}({$task.status}){/if}</td>
                <td>{$task.last_update}</td>
                <td>
                    {if $task.prevent === false && $task.latest_version > $task.version}
                        {if $task.version == ''}
                            <button class='btn btn-info' data-install="{$task.id}" onclick='installUpdate({$task.id});'>Install</button>
                            <strong>new Task</strong>
                        {else}
                            <button class='btn btn-info' data-update="{$task.id}" onclick='installUpdate({$task.id});'>Upgrade</button>
                            <strong>Update available</strong>
                        {/if}
                    {else}
                        {if $task.prevent === false}
                            <a href="#" alt="Redownload" title="Redownload" onclick="installUpdate({$task.id}, false, 1);">no ({vtranslate('Redownload', 'Settings:Workflow2')})</a>
                        {else}<span style="font-size: 10px;font-weight: bold;">
                            {$task.prevent}
                        {/if}
                    {/if}
                </td>
            </tr>
        {/foreach}
        {foreach from=$tasks.task item=task}
            <tr height="20">
                <td>{$task.text}</td>
                <td>{$task.version}</td>
                <td>{$task.latest_version} {if $task.status neq 'stable'}({$task.status}){/if}</td>
                <td>{$task.last_update}</td>
                <td>
                    {if $task.prevent === false && $task.latest_version > $task.version}
                        {if $task.version == ''}
                            <button class='btn btn-info' data-install="{$task.id}" onclick='installUpdate({$task.id});'>Install</button>
                            <strong>new Task</strong>
                        {else}
                            <button class='btn btn-info' data-update="{$task.id}" onclick='installUpdate({$task.id});'>Upgrade</button>
                            <strong>Update available</strong>
                        {/if}
                    {else}
                        {if $task.prevent === false}
                            <a href="#" alt="Redownload" title="Redownload" onclick="installUpdate({$task.id}, false, 1);">no  ({vtranslate('Redownload', 'Settings:Workflow2')})</a>
                        {else}<span style="font-size: 10px;font-weight: bold;">
                            {$task.prevent}
                        {/if}
                    {/if}
                    {if $download eq true OR $task.repo_id == 0}
                        <a href="index.php?module=Workflow2&action=DownloadTask&parent=Settings&task={$task.type_id}">
                            <img src="modules/Workflow2/icons/download.png" class="pull-right" />
                        </a>
                    {/if}
                </td>
            </tr>
        {/foreach}
    {/foreach}
</table>
<div style="margin: 10px 0 0 0; width: 750px">
    <button class="btn btn-warning" onclick="createManualType();">{vtranslate('LBL_CREATE_TYPE','Settings:Workflow2')}</button>
</div>
<link href="modules/Workflow2/views/resources/js/notifications/main.css" rel="stylesheet" type="text/css" media="screen" />
<script src="modules/Workflow2/views/resources/js/notifications/js/notification-min.js"></script>

</div>
</div>