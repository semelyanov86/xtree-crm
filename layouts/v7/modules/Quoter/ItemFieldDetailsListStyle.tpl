{*/* * *******************************************************************************
* The content of this file is subject to the Quoter ("License");
* You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is VTExperts.com
* Portions created by VTExperts.com. are Copyright(C)VTExperts.com.
* All Rights Reserved.
* ****************************************************************************** */*}
{strip}
    {if in_array($SETTING->columnName,$COLUMN_DEFAULT)}
        {assign var = IS_DEFAULT value = 1}
    {else}
        {assign var = IS_DEFAULT value = 0}
    {/if}
    {if is_numeric($COLUMN)}
        {assign var = INDEX value = $COLUMN}
    {else}
        {assign var = INDEX value = ''}
    {/if}
    <tr class="rowItemField {$BASE} {$BASE_CLASS} {if $SETTING->columnName == 'item_name'}noSortable{/if}" data-is-default = "{$IS_DEFAULT}" style="{if $SETTING->presence eq 1}display:none;{/if}">
        <td style="width: 5%;">
            <img src="layouts/v7/skins/images/drag.png" class="moveIcon" border="0" title="Drag" style="cursor: move;">
            &nbsp;&nbsp;
            {if $IS_DEFAULT neq 1}
                <i class="fa fa-trash deleteColumn cursorPointer" title="{vtranslate('LBL_DELETE',$MODULE)}"></i>
            {/if}
        </td>
        <td style="width: 15%;">
            <input type="hidden" name="itemColumn" value="{$SETTING->columnName}">
            {if $IS_DEFAULT eq 1}
                <span style="">{vtranslate($SETTING->columnName,$QUALIFIED_MODULE)}</span>&nbsp;
                <span class="dropdown">
                    <a class="dropdown-toggle fieldInfo" data-toggle="dropdown" href="#" title="Show column name"><span class="fa fa-info-circle"></span></a>
                    <ul class="dropdown-menu _tooltip">
                        <span style="color: #000">${$SETTING->columnName}$</span>
                    </ul>
                </span>
            {else}
                <input class="inputElement" style="width: 60%" name="customHeader{$INDEX}" maxlength="40" placeholder="{vtranslate('LBL_TYPE_IN_THE_HEADER_FIELD',$QUALIFIED_MODULE)}" value="{if $SETTING->customHeader}{$SETTING->customHeader}{/if}" data-rule-required="true">
                &nbsp;<span class="redColor" style="margin-right: 2px;">*</span>&nbsp;
                <span class="dropdown">
                    <a class="dropdown-toggle fieldInfo" data-toggle="dropdown" href="#" title="Show column name"><span class="fa fa-info-circle"></span></a>
                    <ul class="dropdown-menu _tooltip">
                        <span style="color: #000">${$SETTING->columnName}$</span>
                    </ul>
                </span>
            {/if}
        </td>
        <td style="width: 10%;">
            <span class="productField" >
                {if $IS_DEFAULT eq 1 AND $SETTING->columnName neq 'item_name'}
                    <select class="select2" {if $IS_DEFAULT eq 1} disabled="disabled" {/if} style="width: 100%">
                        <option value="{$SETTING->productField}" selected>{vtranslate($SETTING->productField,$QUALIFIED_MODULE)}</option>
                    </select>
                {else}
                    {assign var=CURRENT_FIELD  value= $SETTING->productField}
                    {include file="FieldSelect.tpl"|@vtemplate_path:$QUALIFIED_MODULE MULTIPLE = 0 NOCHOSEN=0 RECORD_STRUCTURE = $PRODUCT_RECORD_STRUCTURE CURRENT_FIELD=$CURRENT_FIELD SOURCE_MODULE = 'Products' IS_DEFAULT = $IS_DEFAULT BASE =$BASE}
                {/if}

            </span>
        </td>
        <td style="width: 10%;">
            <span class="serviceField" >
                {if $IS_DEFAULT eq 1 AND $SETTING->columnName neq 'item_name'}
                    <select class="select2" {if $IS_DEFAULT eq 1} disabled="disabled" {/if}  style="width: 100%">
                        <option value="{$SETTING->serviceField}" selected>{vtranslate($SETTING->serviceField,$QUALIFIED_MODULE)}</option>
                    </select>
                {else}
                    {assign var=CURRENT_FIELD  value= $SETTING->serviceField}
                    {include file="FieldSelect.tpl"|@vtemplate_path:$QUALIFIED_MODULE MULTIPLE = 0 NOCHOSEN=0 RECORD_STRUCTURE = $SERVICE_RECORD_STRUCTURE SOURCE_MODULE = 'Services' CURRENT_FIELD = $CURRENT_FIELD IS_DEFAULT = $IS_DEFAULT BASE =$BASE}
                {/if}
            </span>
        </td>
        <td style="width: 8%;">
            <span class=" ">
                <input name="isMandatory" {if $IS_DEFAULT eq 1} disabled="disabled" {/if} type="checkbox" value="{$SETTING->isMandatory}" {if $SETTING->isMandatory eq '1'} checked {/if}/>
                </select>
            </span>
        </td>
        <td style="width: 5%;">
            <span class="">
                <input name="isActive" type="checkbox" value="{$SETTING->isActive}" {if $SETTING->isActive eq 'active'} checked {/if}/>
            </span>
        </td>
        <td style="width: 5%;">
            <span class="">
                <input name="editAble" type="checkbox" value="{$SETTING->editAble}" {if $SETTING->editAble eq 1} checked {/if}/>
            </span>
        </td>
        <td style="width: 7%;">
            <span class="">
                <input name="columnWidth" class="inputElement" type="text" style="width: 53px;margin-top: 4px;" value="{if $SETTING->columnWidth}{$SETTING->columnWidth}{else}100%{/if}">
                <input type="hidden" name="columnWidthUnit" value="px">
            </span>
        </td>
        <td>
            <div  style="width: 100%;" >
                <textarea class="textAreaElement" rows="3" name="formula">{$SETTING->formula}</textarea>
            </div>
        </td>

    </tr>
{/strip}