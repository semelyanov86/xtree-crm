<div class="container-fluid" id="moduleManagerContents">

    <div class="widget_header row-fluid">
        <div class="span12">
            <h3>
                <b>
                    <a href="index.php?module=Workflow2&view=Index&parent=Settings">{vtranslate('Workflow2', 'Workflow2')}</a> &raquo;
                    <a href="index.php?module=Workflow2&view=TaskManagement&parent=Settings">{vtranslate('LBL_TASK_MANAGEMENT', 'Workflow2')}</a> &raquo;
                    Task Management
                </b>
            </h3>
        </div>
    </div>
    <hr>

    <div style="padding: 10px;">
        <table class="table table-condensed" style="-webkit-user-select:initial;user-select:initial;">
        {foreach from=$repos item=repo}
            <tr data-id="{$repo->get('id')}">
                <td width='200'>{$repo->get('title')}</td>
                <td width='250'>{$repo->get('url')}</td>
                <td width='120'>{if $repo->hasLicenseKey() eq true && $repo->get('id') neq 1}Systemkey:<br/><em>{$repo->get('licensecode')}</em>{/if}</td>
                <td width='170'>{$repo->getLastUpdateDate()}</td>
                <td width='100'>
                    <select name="dummy" class="chzn-select" style="width:150px;" onchange="updateRepositoryStatus({$repo->get('id')}, this.value)">
                    {foreach from=$repo->get('available_status') item=status}
                        <option value="{$status.1}" {if $repo->get('status') eq $status.1}selected='selected'{/if}>{$status.0}</option>
                    {/foreach}
                        <option value="none" {if $repo->get('status') eq 'none'}selected='selected'{/if}>{vtranslate('Deactivate', 'Settings:Workflow2')}</option>
                    </select>
                </td>
                <td width="200">
                    {if $repo->get('id') neq 1}<input type="button" class="btn btn-info pushLicense" value="{vtranslate('push Package license', 'Settings:Workflow2')}" />{/if}
                    {if $repo->get('id') neq 1}<input type="button" class="btn btn-danger deleteRepository" value="{vtranslate('delete', 'Settings:Workflow2')}" />{/if}
                </td>
            </tr>
        {/foreach}
        </table>
    </div>
    <button type="button" class="btn btn-primary" onclick="addRepositoryPopup()">{vtranslate('LBL_ADD_REPOSITORY','Settings:Workflow2')}</button>

    <link href="modules/Workflow2/views/resources/js/notifications/main.css" rel="stylesheet" type="text/css" media="screen" />
    <script src="modules/Workflow2/views/resources/js/notifications/js/notification-min.js"></script>
</div>