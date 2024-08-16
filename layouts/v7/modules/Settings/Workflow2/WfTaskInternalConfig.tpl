{foreach key=title item=GROUP from=$CONFIG_FIELDS}
{if $title neq ""}
<h5>{$title}</h5>
{/if}

<table border="0" cellpadding="5" cellspacing="0" width="100%" class="newTable">
    {foreach item=field from=$GROUP}
        <tr>
            <td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{$field.label}:</td>
            <td class='dvtCellInfo'>
                {if $field.type eq "templatefield"}
                    <div class="insertTextfield" style="display: inline-block;" data-name="task[{$field.key}]" data-id="{$field.key}">{$task[$field.key]}</div>
                {elseif $field.type eq "templatearea"}
                    <script type="text/javascript">document.write(createTemplateTextarea("task[{$field.key}]", "btn_accept", "{$task[$field.key]|replace:"\"":"\\\""}"));</script>
                {elseif $field.type eq "datefield"}
                    <script type="text/javascript">document.write(createTemplateDatefield("task[{$field.key}]", "btn_accept", "{$task[$field.key]|replace:"\"":"\\\""}"));</script>
                {elseif $field.type eq "envvar"}
                    $env[<input type="text"name="task[{$field.key}]" class="defaultTextfield" value="{$task[$field.key]|htmlentities}">]
                {elseif $field.type eq "field"}
                    <select style="vertical-align:top;width:300px;" class="select2" name="task[{$field.key}]">
                        {foreach from=$moduleFields key=label item=block}
                            <optgroup label="{$label}">
                                {foreach from=$block item=fieldSelect}
                                    {if $fieldSelect->name neq "smownerid"}
                                        <option value='{$fieldSelect->name}' {if $task[$field.key] eq $fieldSelect->name}selected="selected"{/if}>{$fieldSelect->label}</option>
                                    {else}
                                        <option value='faxassigned_user_id' {if $task[$field.key] eq '$assigned_user_id'}selected="selected"{/if}>{$fieldSelect->label}</option>
                                    {/if}
                                {/foreach}
                            </optgroup>
                        {/foreach}
                    </select>
                {/if}
            </td>
        </tr>
    {/foreach}
</table>

{/foreach}
