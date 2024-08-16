{if $show_moduleselect eq true}
    <div>
        <table width="100%" cellspacing="0" cellpadding="5" class="newTable">
            <tr>
                <td class="dvtCellLabel" align="right" width="15%">{vtranslate('LBL_SEARCH_IN_MODULE', 'Settings:Workflow2')}</td>
                <td class="dvtCellInfo" align="left">
                    <select class="select2" name='task[moduleselect][search_module]' style="width:350px;" onchange="jQuery('#search_module_hidden').val(jQuery(this).val());document.forms['hidden_search_form'].submit();">
                        <option {if $moduleselection.related_tabid == 0}selected='selected'{/if} value="0">{vtranslate('LBL_CHOOSE', 'Settings:Workflow2')}</option>
                        {foreach from=$moduleselection.related_modules item=module key=tabid}
                            <option {if $task.moduleselect.search_module == $tabid}selected='selected'{/if} value="{$tabid}">{$module.1}</option>
                        {/foreach}
                    </select>
                </td>
            </tr>
            <tr>
                <td class="dvtCellLabel" align="right" width="15%">{vtranslate('LBL_EXEC_FOR_THIS_NUM_ROWS', 'Settings:Workflow2')}</td>
                <td class="dvtCellInfo" align="left">
                    <input type='text' name='task[moduleselect][found_rows]' class="defaultTextfield" id='found_rows' value="{$task.moduleselect.found_rows}" style="width:50px;"> ({vtranslate('LBL_EMPTY_ALL_RECORDS', 'Settings:Workflow2')})
                </td>
            </tr>
            <tr>
                <td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{vtranslate('SORT_RESULTS_WITH', 'Settings:Workflow2')}</td>
                <td class='dvtCellInfo'>
                    <select name="task[moduleselect][sort_field]" class="select2" style="width:350px;">
                        <option value="0" {if $task.workflow_id eq ""}selected='selected'{/if}>-</option>
                        {foreach from=$moduleselection.sort_fields item=loop_block key=loop_blockLabel}
                            <optgroup label="{$blockLabel}">
                                {foreach from=$loop_block item=loop_field key=loop_fieldLabel}
                                    <option value="{$loop_field->name}" {if $loop_field->name eq $task.moduleselect.sort_field}selected='selected'{/if}>{$loop_field->label}</option>
                                {/foreach}
                            </optgroup>
                        {/foreach}
                    </select>
                    <select class="select2" style="width:100px;"name="task[sortDirection]"><option value="ASC"{if $task.sortDirection eq "ASC"}selected='selected'{/if}>ASC</option><option value="DESC"{if $task.sortDirection eq "DESC"}selected='selected'{/if}>DESC</option></select>
                </td>
            </tr>
        </table>
    </div>
    <script type="text/javascript">
        jQuery(function() {
            addMiniFormModuleSelect()
        });
    </script>
{/if}
{if $show_selection_methods eq true}
<script type="text/javascript">
    var Sources = {ldelim}{rdelim};
    var CurrentSource = "{$selected_source}";
</script>
{foreach from=$sources item=source}
    <label style="margin:5px 0 0 0; color:#fff;font-size: 13px;font-weight:bold;padding:3px 5px;" class="RecordStatus {if empty($selected_source.id)}Empty{/if} {if $selected_source eq $source.id}Active{/if}">
        <input type="radio" name="task[{$field}][sourceid]" class="RecordSourceChooser" value="{$source.id}" {if $selected_source eq $source.id}checked="true"{/if} style="display: inline-block;" />
        {vtranslate($source.title,'Settings:Workflow2')|ucfirst}
    </label>

    <script type="text/javascript" class="RecordSourceJS">
        Sources["{$source.id}"] = function (container) {
            {$sourceObj[$source.id]->getConfigInlineJS()}
        }
    </script>
    <style type="text/less">
        .SourceContainer[data-source="{$source.id}"] {ldelim}
            {$sourceObj[$source.id]->getConfigInlineCSS()}
        {rdelim}
    </style>
    <div class="SourceContainer" data-source="{$source.id}" style="display:none;padding:5px 0 0 5px;border-left:5px solid #6699cc;">
        {$source.HTML}
    </div>
{/foreach}

{/if}