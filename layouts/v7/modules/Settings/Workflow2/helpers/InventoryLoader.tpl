<h5>{vtranslate('Insert Products from Loader', 'Settings:Workflow2')}</h5>

<table width="100%" cellspacing="0" cellpadding="0">
    <tr>
        <td class="dvtCellLabel" align="right">{vtranslate('Inventory Loader', 'Settings:Workflow2')}</td>
        <td width="15"></td>
        <td class="dvtCellInfo" align="left">
            <select name='task[{$field}][select][]' style="width:600px;" class="select2 InventorySelectorSelector" multiple="multiple" data-placeholder="{vtranslate('No InventoryLoader', 'Settings:Workflow2')}">
                {foreach from=$InventoryLoader key=id item=data}
                    <option {if in_array($id, $task[$field]['select'])}selected='selected'{/if} value="{$id}">{vtranslate($data.label, 'Settings:Workflow2')}</option>
                {/foreach}
            </select>
        </td>
    </tr>
    <tr class="ConfigInventoryLoaderRow" style="display:{if empty($task[$field]) || empty($task[$field]['select'])}none{else}{/if};">
        <td class="dvtCellLabel" align="right">{vtranslate('Loader Config', 'Settings:Workflow2')}</td>
        <td width="15"></td>
        <td class="dvtCellInfo" align="left">
            {foreach from=$InventoryLoader key=id item=data}
                <div id="config_{$id}" class="InventarLoaderProvider" style="display:{if in_array($id, $task[$field]['select'])}block{else}none{/if};border:1px solid #ccc;padding:5px;box-sizing:border-box;overflow: hidden;margin:2px 0;">
                    <h5>{vtranslate('Settings', 'Settings:Workflow2')}: {vtranslate($data.label, 'Settings:Workflow2')}</h5>
                    <hr/>
                    {foreach from=$data.config key=fieldname item=config}
                        <div style="line-height:26px;">
                            <span class="pull-left">{$config.label}</span>
                            <div style="width:70%;float:left;margin-left:20px;">
                                {assign var="set_fieldname" value="task[`$field`][`$id`][config][`$fieldname`]"}

                                {include file='../VT7/ConfigGenerator.tpl' config=$config fieldname=$set_fieldname value=$task[$field][$id].config[$fieldname]}
                                {if !empty($config.description)}
                                    <span style="font-style: italic;font-size:12px;">{vtranslate($config.description, 'Settings:Workflow2')}</span>
                                {/if}
                            </div>
                        </div>
                    {/foreach}

                </div>
            {/foreach}
        </td>
    </tr>
</table>
