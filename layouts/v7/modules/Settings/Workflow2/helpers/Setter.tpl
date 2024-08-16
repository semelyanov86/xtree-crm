<div id="settings_base" style="display:none;">
    <div style="margin:10px;" class="FieldSetterRow" id="setterRow_##SETID##">
        <select class="FieldSetterField" style="height:29px;vertical-align:top;" disabled="disabled" name='task[setter][##SETID##][field]' id='setter_##SETID##_field'>
            <option value=''>{vtranslate('LBL_CHOOSE', 'Workflow2')}</option>
            <option value=';;;delete;;;'>{vtranslate('LBL_DELETE_SET_FIELD', 'Workflow2')}</option>
            {foreach from=$fields key=label item=block}
                <optgroup label="{$label}">
                {foreach from=$block item=field}
                    {if $field->name neq "smownerid"}
                        <option value='{$field->name}'>{$field->label}</option>
                    {else}
                        <option value='assigned_user_id'>{$field->label}</option>
                    {/if}
                {/foreach}
                </optgroup>
            {/foreach}
        </select>
        <select class="FieldSetterMode" style="height:29px;vertical-align:top;" disabled="disabled" name="task[setter][##SETID##][mode]" id="setter_##SETID##_mode">
            <option value="value">{vtranslate("LBL_STATIC_VALUE", "Settings:Workflow2")}</option>
            <option value="field">{vtranslate("LBL_FIELD_VALUE", "Settings:Workflow2")}</option>
            <option value="formula">{vtranslate("Formula Result", "Settings:Workflow2")}</option>
            <option value="function">{vtranslate("LBL_FUNCTION_VALUE", "Settings:Workflow2")}</option>
        </select>
        <div style="" id='value_##SETID##_container' class="FieldSetterValueContainer">
            <input type='text' disabled="disabled" name='task[setter][##SETID##][value]' id='setter_##SETID##_value'>
        </div>
    </div>
</div>

<button type="button" class="btn btn-primary"onclick="addRow();">{vtranslate("LBL_ADD_FIELD", "Settings:Workflow2")}</button>
{foreach from=$setter_blocks item=block}
<div class="blockContainer" id="block_{$block.0}" style="display: none;" data-block="{$block.1}"><p class="title">{$block.1}</p></div>
{/foreach}
<div id='setter_container'><div style="text-align:center;"><img src="modules/Workflow2/loader.gif" /></div></div>
<button type="button" class="btn btn-primary"onclick="addRow();">{vtranslate("LBL_ADD_FIELD", "Settings:Workflow2")}</button>

<script type="text/dummy" id='fromFieldsFieldValues'>
<select style="vertical-align:top;width:100%;" class="select2" name='##FIELDNAME##' id='##FIELDID##'>
    <option value=''>{vtranslate('LBL_CHOOSE', 'Workflow2')}</option>
    <option value=';;;delete;;;' class='deleteRow'>{vtranslate('LBL_DELETE_SET_FIELD', 'Workflow2')}</option>
    {foreach from=$fromFields key=label item=block}
        <optgroup label="{$label}">
        {foreach from=$block item=field}
            {if $field->name neq "smownerid"}
                <option value='${$field->name}'>{$field->label}</option>
            {else}
                <option value='$assigned_user_id'>{$field->label}</option>
            {/if}
        {/foreach}
        </optgroup>
    {/foreach}
</select>
</script>