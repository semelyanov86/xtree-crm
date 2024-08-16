<div id="ValueList_{$field}"></div>
<input type="button" class="btn btn-primary" id="AddRowBtn_{$field}" value="add Field" />

<script type="text/template" id="PlaceHolder_{$field}">
    <i class="fa fa-minus-square RemoveRow" style="margin:5px 10px 0 0;font-size:16px;" aria-hidden="true"></i>

    {if $no_headlines neq true}
        <input type='text' class="form-control Headline" style="margin-bottom:0;vertical-align:top;" id='staticfields_##SETID##_label' name='task[{$field}][##SETID##][label]' value='' placeholder="{$placeholder_value}" />
    {/if}

    <div style="height:31px;width:150px;">
    <select style="vertical-align:top;width:100%;" name="task[{$field}][##SETID##][mode]" id="staticfields_##SETID##_mode" class="Mode MakeSelect2">
        {if empty($fixedmode) || $fixedmode == 'value'}
            <option value="value" >{vtranslate("LBL_STATIC_VALUE", "Settings:Workflow2")}</option>
        {/if}

        {if $ColumnMode neq true}
            {if empty($fixedmode) || $fixedmode == 'field'}
                <option value="field" selected="selected">{vtranslate("LBL_FIELD_VALUE", "Settings:Workflow2")}</option>
            {/if}
        {/if}

        <option value="function">{vtranslate("LBL_FUNCTION_VALUE", "Settings:Workflow2")}</option>

        {if $ColumnMode eq true}
            <option value="column">{vtranslate("column in array", "Settings:Workflow2")}</option>
        {/if}
    </select>
    </div>

    <div style="display:inline-block; width:400px;" class="ValueContainer" data-placeholder="{$placeholder_value}" data-prefix='task[{$field}][##SETID##]'></div>
</script>

<style type="text/css">
    .ValueListRow {
        display:flex;
        flex-direction: row;
        margin-bottom:5px;
    }
    .Headline {
        width:20%;
        min-width:200px;
        flex-grow:1;
    }
    .ValueContainer {
        flex-grow:2;
        margin-left:5px;
    }
</style>