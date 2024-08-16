<div id="staticFields"></div>
<div class="clearfix"></div>
<button type="button" onclick="addCol();" class="btn btn-primary">add Value</button>


<div id="staticFieldsContainer" style="display:none;">
    <div style="margin:15px 0px;" id="setterRow_##SETID##">
        <input type='text' style="margin-bottom:0;" id='staticfields_##SETID##_field' name='task[{$StaticFieldsField}][##SETID##][key]' value=''>

        <select style="vertical-align:top;width: 150px;margin-bottom:0;" disabled="disabled" name="task[{$StaticFieldsField}][##SETID##][mode]" data-fieldindex="##SETID##" id="staticfields_##SETID##_mode">
            <option value="value">{vtranslate("LBL_STATIC_VALUE", "Settings:Workflow2")}</option>
            <option value="field">{vtranslate("LBL_FIELD_VALUE", "Settings:Workflow2")}</option>
        </select>

        <div style="display:inline;" id='staticfields_##SETID##_container'>
            <input type='text'  disabled="disabled" hidden="cols_value_##SETID##"  name='task[{$StaticFieldsField}][##SETID##][value]' id='staticfields_##SETID##_value'>
        </div>
    </div>
</div>

<script type="text/dummy" id='fromStaticFieldsFieldValues'>
<select style="vertical-align:top;width:300px;" class="chzn-select" name='##FIELDNAME##' id='##FIELDID##'>
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