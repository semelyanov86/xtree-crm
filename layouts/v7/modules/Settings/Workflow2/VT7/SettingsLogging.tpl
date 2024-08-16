<div class="container-fluid" id="moduleManagerContents">
    <div class="editViewHeader">
        <h4>
            <a href="index.php?module=Workflow2&view=Index&parent=Settings">{vtranslate('Workflow Designer', 'Workflow2')}</a> &raquo;
            {vtranslate('LBL_SETTINGS_LOGGING','Settings:Workflow2')}
        </h4>
    </div>
    <hr>
    <div class="listViewActionsDiv">

        <form method="POST" action="#">
        <table width="100%" cellspacing="0" cellpadding="4" style="margin-left:3px; border-collapse:collapse;">
            <tr>
                <td colspan=4 class="dvInnerHeader">
                    <h4>
                            <b>&nbsp;{vtranslate('Statistics', 'Settings:Workflow2')} <a href=''><img border=0 src='modules/Workflow2/icons/question.png'></a></b>
                    </h4>
                </td>
            </tr>
            <tr>
                <td class="dvtCellInfo" style="background-color:#fff; width:20%;">{vtranslate('LBL_MINIFY_LOGS_AFTER', 'Settings:Workflow2')}</td>
                <td class="dvtCellInfo" style="background-color:#fff; width:20%;font-weight:bold;"><input type="text" name="minify_logs_after" style="padding:2px 5px;width:50px;text-align:right;" value="{if $config.minify_logs_after != 'none'}{$config.minify_logs_after}{/if}"> {vtranslate('LBL_DAYS', 'Settings:Workflow2')}</td>

                <td class="dvtCellInfo" style="background-color:#fff; width:20%;">{vtranslate('LBL_REMOVE_LOGS_AFTER', 'Settings:Workflow2')}</td>
                <td class="dvtCellInfo" style="background-color:#fff; width:20%;font-weight:bold;"><input type="text" name="remove_logs_after" style="width:50px;text-align:right;padding:2px 5px;"  class="" value="{if $config.remove_logs_after != 'none'}{$config.remove_logs_after}{/if}"> {vtranslate('LBL_DAYS', 'Settings:Workflow2')}</td>
            </tr>

            <tr>
                <td colspan=4 class="dvInnerHeader">
                    <h4>
                            <b>&nbsp;{vtranslate('LBL_LOGS_ERROR_HEAD', 'Settings:Workflow2')}</b>
                    </h4>
                </td>
            </tr>
            <tr>
                <td class="dvtCellInfo" style="background-color:#fff; width:20%;">{vtranslate('LBL_LOGS_ERROR', 'Settings:Workflow2')}</td>
                <td class="dvtCellInfo" style="background-color:#fff; width:20%;font-weight:bold;">
                    <select name="error_handler" class="select2">
                        <option value="email" {if $config.error_handler eq 'email'}selected='selected'{/if}>{vtranslate('LOG_ERROR_EMAIL', 'Settings:Workflow2')}</option>
                        <option value="file" {if $config.error_handler eq 'file'}selected='selected'{/if}>{vtranslate('LOG_ERROR_FILE', 'Settings:Workflow2')}</option>
                        <option value="none" {if $config.error_handler eq 'none'}selected='selected'{/if}>{vtranslate('LOG_ERROR_NONE', 'Settings:Workflow2')}</option>
                    </select>
                </td>
                <td class="dvtCellInfo" style="background-color:#fff; width:20%;">{vtranslate('LBL_LOGS_ERROR_VALUE', 'Settings:Workflow2')}</td>
                <td class="dvtCellInfo" style="background-color:#fff; width:20%;font-weight:bold;"><input type="text" name="error_handler_value" style="padding:2px 5px;" value="{if $config.error_handler neq 'none'}{$config.error_handler_value}{/if}"></td>
            </tr>

            <tr>
                <td colspan=4 class="dvInnerHeader">
                    <h4>
                            <b>&nbsp;{vtranslate('LBL_LOGS_HEAD', 'Settings:Workflow2')}</b>
                    </h4>
                </td>
            </tr>
            <tr>
                <td class="dvtCellInfo" style="background-color:#fff; width:20%;">{vtranslate('LBL_ALL_LOGS', 'Settings:Workflow2')}<br/><strong>{vtranslate('Not enable log for a long time. It generates a lot of data.', 'Settings:Workflow2')}</strong></td>
                <td class="dvtCellInfo" style="background-color:#fff; width:20%;font-weight:bold;">
                    <select name="log_handler" class="select2">
                        <option value="none" {if $config.log_handler eq 'none'}selected='selected'{/if}>{vtranslate('LOG_ERROR_NONE', 'Settings:Workflow2')}</option>
                        <option value="file" {if $config.log_handler eq 'file'}selected='selected'{/if}>{vtranslate('LOG_ERROR_FILE', 'Settings:Workflow2')}</option>
                        <option value="table" {if $config.log_handler eq 'table'}selected='selected'{/if}>in vtiger_wf_logtbl table</option>
                    </select>
                </td>
                <td class="dvtCellInfo" style="background-color:#fff; width:20%;">{vtranslate('LBL_ALL_LOGS_VALUE', 'Settings:Workflow2')}</td>
                <td class="dvtCellInfo" style="background-color:#fff; width:20%;font-weight:bold;"><input type="text" style="padding:2px 5px;" name="log_handler_value" value="{if $config.log_handler eq 'file'}{$config.log_handler_value}{/if}"></td>
            </tr>
            <tr>
                <td colspan=4 class="dvInnerHeader">
                        <h4>
                            <b>Debug Mode</b>
                        </h4>
                </td>
            </tr>
            <tr>
                <td class="dvtCellInfo" style="background-color:#fff; " colspan="3">Enable PHP Debug Output for 60 minutes in this Browser to give you any help you need to report errors with details.<br/><strong>You cannot use VtigerCRM without issues if you keep this enabled.</strong></td>
                <td class="dvtCellInfo" style="background-color:#fff; width:20%;font-weight:bold;">
                    {if $DEBUG eq true}
                        <a class="btn btn-danger" href="index.php?module=Workflow2&view=SettingsLogging&parent=Settings&debug=disable">Disable Debug Mode</a>
                    {else}
                        <a class="btn btn-danger" href="index.php?module=Workflow2&view=SettingsLogging&parent=Settings&debug=enable">Enable Debug Mode</a>
                    {/if}
            </tr>

        </table>
            <br/>
        <p style="margin-left:3px; border-collapse:collapse;">
            <input type="hidden" name="save" value='1'/>
            <button type="submit" class='btn btn-primary'>{vtranslate('LBL_SAVE', 'Settings:Workflow2')}</button>
        </p>
        </form>
    {if $logs neq ''}
        <table width="100%">
            <tr>
                <td class="dvInnerHeader">
                        <div style="float:left;font-weight:bold;text-transform:uppercase;">
                            <b>&nbsp;{vtranslate('LBL_LOGS_HEAD_ENTRIES', 'Settings:Workflow2')}</b>
                        </div>
                </td>
            </tr>
            <tr>
                <td class="dvtCellInfo" style="background-color:#fff;">
                    <button type="button" onclick='window.location.href="index.php?module=Workflow2&view=SettingsLogging&parent=Settings&clearAllLog=1"' class='btn btn-info'>{vtranslate('LBL_ALL_LOGS_CLEAR', 'Settings:Workflow2')}</button><br/>
                    {$logs}
                </td>
            </tr>
        </table>
    {/if}

    </div>
</div>

