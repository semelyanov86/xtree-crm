{*/* ********************************************************************************
* The content of this file is subject to the Field Autofill ("License");
* You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is VTExperts.com
* Portions created by VTExperts.com. are Copyright(C) VTExperts.com.
* All Rights Reserved.
* ****************************************************************************** */*}

<table class="table table-bordered blockContainer showInlineTable equalSplit" style="width: 500px;">
    <tr>
        <td colspan="3" class="blockHeader">
            <b>{vtranslate('LBL_CONFIRM_BEFORE_OVERWRITING', 'FieldAutofill')}</b> &nbsp;
            <input type="checkbox" name="show_popup" value="1" {if $SHOW_POPUP eq '1'}checked="" {/if}/>
        </td>
    </tr>
    <tr>
        <td class="fieldValue medium" style="width: 45%;"><b>{vtranslate($PRIMODULE_NAME, $PRIMODULE_NAME)}</b></td>
        <td class="fieldValue medium" style="width: 45%;"><b>{vtranslate($SECMODULE, $SECMODULE)}</b></td>
        <td class="fieldValue">&nbsp;
            {*<input type="hidden" name="countMappings" value="{$MAPPINGS|count}"*}
        </td>
    </tr>
    {foreach item=mapping key=mapid from=$MAPPINGS}
        <tr data-mapping-id="{$mapid}">
            <td class="fieldValue medium">
                <select class="chzn-select mappingField" data-field="primary" style="width: 200px">
                    <option value="">{vtranslate('LBL_SELECT_OPTION', 'FieldAutofill')}</option>
                    {foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$PRI_RECORD_STRUCTURE}
                        <optgroup label='{vtranslate($BLOCK_LABEL, $PRIMODULE)}'>
                            {foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
                                <option value="{$FIELD_MODEL->getCustomViewColumnName()}" data-field-name="{$FIELD_NAME}"
                                        {if $FIELD_MODEL->getCustomViewColumnName() eq $mapping['primary']}
                                            selected
                                        {/if}
                                        >{vtranslate($FIELD_MODEL->get('label'), $PRIMODULE)}
                                </option>
                            {/foreach}
                        </optgroup>
                    {/foreach}
                </select>
            </td>
            <td class="fieldValue medium">
                <select class="chzn-select mappingField" data-field="secondary" style="width: 200px">
                    <option value="">{vtranslate('LBL_SELECT_OPTION', 'FieldAutofill')}</option>
                    {foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$SEC_RECORD_STRUCTURE}
                        <optgroup label='{vtranslate($BLOCK_LABEL, $SECMODULE)}'>
                            {foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
                                <option value="{$FIELD_MODEL->getCustomViewColumnName()}" data-field-name="{$FIELD_NAME}"
                                        {if $FIELD_MODEL->getCustomViewColumnName() eq $mapping['secondary']}
                                            selected
                                        {/if}
                                        >{vtranslate($FIELD_MODEL->get('label'), $SECMODULE)}
                                </option>
                            {/foreach}
                        </optgroup>
                    {/foreach}
                </select>
            </td>
            <td>
                <a class="deleteMappingButton"><i class="icon-trash alignMiddle" title="Delete"></i></a>
            </td>
        </tr>
    {/foreach}
    <tr>
        <td colspan="3">
            <button type="button" id="addMappingButton" class="btn addButton">
                <i class="icon-plus"></i>
                <strong>{vtranslate('LBL_ADD_MAPPING', 'FieldAutofill')}</strong>
            </button>
        </td>
    </tr>
    <tr>
        <td colspan="3">
            <button type="submit" id="btnSaveSetting" class="btn btn-success">
                <strong>{vtranslate('LBL_SAVE', 'FieldAutofill')}</strong>
            </button>
        </td>
    </tr>
    <tr id="mapping_template" style="display: none;">
        <td class="fieldValue medium">
            <select class="templateselect mappingField" data-field="primary" style="width: 200px">
                <option value="">{vtranslate('LBL_SELECT_OPTION', 'FieldAutofill')}</option>
                {foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$PRI_RECORD_STRUCTURE}
                    <optgroup label='{vtranslate($BLOCK_LABEL, $PRIMODULE)}'>
                        {foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
                            <option value="{$FIELD_MODEL->getCustomViewColumnName()}" data-field-name="{$FIELD_NAME}">
                                {vtranslate($FIELD_MODEL->get('label'), $PRIMODULE)}
                            </option>
                        {/foreach}
                    </optgroup>
                {/foreach}
            </select>
        </td>
        <td class="fieldValue medium">
            <select class="templateselect mappingField" data-field="secondary" style="width: 200px">
                <option value="">{vtranslate('LBL_SELECT_OPTION', 'FieldAutofill')}</option>
                {foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$SEC_RECORD_STRUCTURE}
                    <optgroup label='{vtranslate($BLOCK_LABEL, $SECMODULE)}'>
                        {foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
                            <option value="{$FIELD_MODEL->getCustomViewColumnName()}" data-field-name="{$FIELD_NAME}">
                                {vtranslate($FIELD_MODEL->get('label'), $SECMODULE)}
                            </option>
                        {/foreach}
                    </optgroup>
                {/foreach}
            </select>
        </td>
        <td>
            <a class="deleteMappingButton"><i class="icon-trash alignMiddle" title="{vtranslate('LBL_DELETE', 'FieldAutofill')}"></i></a>
        </td>
    </tr>
</table>