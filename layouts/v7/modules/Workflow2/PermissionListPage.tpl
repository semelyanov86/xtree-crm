<div class="contents" style="padding:20px;">
    <!-- List View's Buttons and Filters ends -->
    <p>
        {vtranslate('LBL_PERMISSION_TOP_HINT','Workflow2')}
    </p>

    <div>

    {if count($blocks) == 0}
        <div style="text-align:center;font-weight:bold;">{vtranslate('NO_ENTRY', 'Workflow2')}</div>
    {/if}

    {foreach from=$blocks item=workflows key=block_title}
        <h5>{$block_title}</h5>
        <table border=0 cellspacing=1 cellpadding=3 width=100% class="table table-condensed block{$workflows.blockid}" >
        <!-- Table Headers -->
            <thead>
            <tr>
                <td class="lvtCol"><input type="checkbox"  name="selectall" class="selectAllCheckboxes"></td>
                <td class="lvtCol" width=100><strong>{vtranslate('Module', 'Settings:Workflow2')}</strong></td>

                <td class="lvtCol" width=100><strong>{vtranslate('Record', 'Settings:Workflow2')}</strong></td>
                <td class="lvtCol" width=100><strong>{vtranslate('LBL_INFO_MESSAGE', 'Settings:Workflow2')}</strong></td>

                <td class="lvtCol" width=100><strong>{vtranslate('Eingestellt', 'Workflow2')}</strong></td>
                <td class="lvtCol" width=100><strong>{vtranslate('Bearbeitet', 'Workflow2')}</strong></td>
                <td class="lvtCol" style="width:260px;"><strong>{vtranslate('Aktionen', 'Workflow2')}</strong></td>
            </tr>

            </thead>
            <tbody>
        {foreach from=$workflows.records item=workflow}
            <tr title="{$workflow.infomessage}" class="permissionRow ExecId{$workflow.execid}" alt="{$workflow.infomessage}" data-execid="{$workflow.execid}##{$workflow.blockid}" data-id="{$workflow.conf_id}" data-hash="{$workflow.hash1}" data-already="{if !empty($workflow.result_user_id)}1{else}0{/if}" style="color:{$workflow.textcolor};background-color:{$workflow.backgroundcolor} !important;" id="row_{$workflow.conf_id}">
                <td width="2%"><input type="checkbox" class="selectRows"></td>
                <td>{vtranslate($workflow.module, $workflow.module)}</td>
                <td>{$workflow.numberField} - {$workflow.recordLink}</td>
                <td>{$workflow.infomessage}</td>
                <td>{vtranslate('by', 'Settings:Workflow2')} {$workflow.first_name} {$workflow.last_name}<br>{$workflow.timestamp}</td>
                <td>{vtranslate('by', 'Settings:Workflow2')} {if !empty($workflow.result_user_id)}{$workflow.result_first_name} {$workflow.result_last_name}<br>{$workflow.result_timestamp}{else}{vtranslate("LBL_NO_PERSON", "Workflow2")}{/if}</td>
                <td class="workflowActions" style="padding:10px 20px;">
                    {if !empty($workflow.block.btn_accept)}
                        <a onclick="return WorkflowPermissions.submit('{$workflow.execid}##{$workflow.blockid}', '{$workflow.conf_id}', '{$workflow.hash1}', 'ok');" class="btn btn-success decision decision_ok {$workflow.btn_accept_class}"  href="index.php?module=Workflow2&view=List&aid={$workflow.conf_id}&a=ok&h={$workflow.hash1}">{vtranslate($workflow.block.btn_accept, 'Workflow2')}</a>
                    {/if}
                    {if !empty($workflow.block.btn_rework)}
                        <a onclick="return WorkflowPermissions.submit('{$workflow.execid}##{$workflow.blockid}', '{$workflow.conf_id}', '{$workflow.hash1}', 'rework');" class="btn btn-warning decision decision_rework {$workflow.btn_rework_class}"  href="index.php?module=Workflow2&view=List&aid={$workflow.conf_id}&a=rework&h={$workflow.hash2}">{vtranslate($workflow.block.btn_rework, 'Workflow2')}</a>
                    {/if}
                    {if !empty($workflow.block.btn_decline)}
                        <a onclick="return WorkflowPermissions.submit('{$workflow.execid}##{$workflow.blockid}', '{$workflow.conf_id}', '{$workflow.hash1}', 'decline');" class="btn btn-danger decision decision_decline {$workflow.btn_decline_class}"  href="index.php?module=Workflow2&view=List&aid={$workflow.conf_id}&a=decline&h={$workflow.hash3}">{vtranslate($workflow.block.btn_decline, 'Workflow2')}</a>
                    {/if}
                </td>
            </tr>
        {/foreach}
            </tbody>
            <tfoot>
            <tr>
                <td colspan="9" style="line-height:30px;">{vtranslate('selected records', $MODULE)}:&nbsp;&nbsp;&nbsp;
                    {if !empty($workflows.buttons.btn_accept)}
                        <a onclick="return WorkflowPermissions.submitAll('{$workflow.blockid}', 'ok');" class="btn btn-success decision decision_ok {$workflow.btn_accept_class}"  href="index.php?module=Workflow2&view=List&aid={$workflow.conf_id}&a=ok&h={$workflow.hash1}">{vtranslate($workflows.buttons.btn_accept, 'Workflow2')}</a>
                    {/if}
                    {if !empty($workflows.buttons.btn_rework)}
                        <a onclick="return WorkflowPermissions.submitAll('{$workflow.blockid}', 'rework');" class="btn btn-warning decision decision_rework {$workflow.btn_rework_class}"  href="index.php?module=Workflow2&view=List&aid={$workflow.conf_id}&a=rework&h={$workflow.hash2}">{vtranslate($workflows.buttons.btn_rework, 'Workflow2')}</a>
                    {/if}
                    {if !empty($workflows.buttons.btn_decline)}
                        <a onclick="return WorkflowPermissions.submitAll('{$workflow.blockid}', 'decline');" class="btn btn-danger decision decision_decline {$workflow.btn_decline_class}"  href="index.php?module=Workflow2&view=List&aid={$workflow.conf_id}&a=decline&h={$workflow.hash3}">{vtranslate($workflows.buttons.btn_decline, 'Workflow2')}</a>
                    {/if}
                </td>
            </tr>
            </tfoot>
        </table>
    {/foreach}
<br/>
        <!-- List View's Buttons and Filters ends -->
        <p>
            {vtranslate('LBL_NEED_USERACCESS','Workflow2')}:
        </p>

    {foreach from=$userqueue item=workflows key=block_title}
        <h5>{$block_title}</h5>

        <table border=0 cellspacing=1 cellpadding=3 width=100% class="table table-condensed">
        <!-- Table Headers -->
        <tr>
            <td class="lvtCol"><input type="checkbox"  name="selectall" id="selectCurrentPageRec" onClick=toggleSelect_ListView(this.checked,"selected_id")></td>
            <td class="lvtCol" width=100><strong>{vtranslate('Module', 'Settings:Workflow2')}</strong></td>
            <td class="lvtCol" width=100><strong>{vtranslate('Record ID', 'Settings:Workflow2')}</strong></td>
            <td class="lvtCol" width=100><strong>{vtranslate('Record', 'Settings:Workflow2')}</strong></td>
            <td class="lvtCol" width=100><strong>{vtranslate('Workflow', 'Settings:Workflow2')}</strong></td>
            <td class="lvtCol" width=100><strong>{vtranslate('Eingestellt', 'Workflow2')}</strong></td>
            <td class="lvtCol" style="width:260px;"><strong>{vtranslate('Aktionen', 'Workflow2')}</strong></td>
        </tr>
        {foreach from=$workflows item=workflow}
            <tr title="{$workflow.infomessage}" alt="{$workflow.infomessage}" style="background-color:{$workflow.backgroundcolor} !important;" onMouseOver="this.className='lvtColDataHover'" onMouseOut="this.className='lvtColData'" id="row_{$workflow.id}">
                <td width="2%"><input type="checkbox" NAME="selected_id" id="1049" value= '1049' onClick="check_object(this)"></td>
                <td>{vtranslate($workflow.module_name, $workflow.module_name)}</td>
                <td>{$workflow.numberField}</td>
                <td>{$workflow.recordLink}</td>
                <td>{$workflow.title}</td>
                <td>{$workflow.timestamp}</td>
                <td class="workflowActions" style="padding:10px 20px;">
                    <button type="button" class="btn" onclick="UserQueue.run('{$workflow.execid}','{$workflow.block_id}')">{$workflow.button.value}</button>
                </td>
            </tr>
        {/foreach}
        </table>
    {/foreach}

        </div>