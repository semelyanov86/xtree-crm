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
<div class="colItemField {$BASE} {if $SETTING->columnName == 'item_name'}noSortable{/if}" data-is-default = "{$IS_DEFAULT}">
    <input type="hidden" name="itemColumn" value="{$SETTING->columnName}">

    {****************************************Header column*************************************}
    <div class="colHeader">
        {if $IS_DEFAULT eq 1}
            <span style="margin: 3px 0;display: inline-block;">{vtranslate($SETTING->columnName,$QUALIFIED_MODULE)}</span>
            <span class="dropdown">
                <a class="dropdown-toggle fieldInfo" data-toggle="dropdown" href="#" title="Show column name"><span class="fa fa-info-circle"></span></a>
                <ul class="dropdown-menu _tooltip">
                    <span style="color: #000">${$SETTING->columnName}$</span>
                </ul>
            </span>
        {else}
            <span class="redColor" style="margin-right: 2px;">*</span>
            <input name="customHeader{$INDEX}" maxlength="40" placeholder="{vtranslate('LBL_TYPE_IN_THE_HEADER_FIELD',$QUALIFIED_MODULE)}" value="{if $SETTING->customHeader}{$SETTING->customHeader}{/if}" data-rule-required="true" style="width: 65%;margin: 0;color: #333;line-height: 19px;">
            <span class="dropdown">
                <a class="dropdown-toggle fieldInfo" data-toggle="dropdown" href="#" title="Show column name"><span class="fa fa-info-circle"></span></a>
                <ul class="dropdown-menu _tooltip">
                    <span style="color: #000">${$SETTING->columnName}$</span>
                </ul>
            </span>
            <i class="fa fa-trash deleteColumn cursorPointer" title="{vtranslate('LBL_DELETE',$MODULE)}"></i>
            <span class="clearfix"></span>
        {/if}

    </div>
    <div class="colContent">

        {****************************************Product field**************************************}
        <div class="rowCell">
            <span class="pull-left textAlignRight"><a class="info-icon" data-toggle="popover" data-placement="top" style="color: #16a5e7" data-content="Select field to be prefilled from product. Optional" href="javascript:void(0)"><i class="fa fa fa-info-circle"></i></a> {if $IS_DEFAULT eq 1}<span class="redColor" style="margin-right: 2px;">*</span>{/if}{vtranslate('LBL_PRODUCT',$QUALIFIED_MODULE)}</span>
            <span class="pull-right productField" style="width: 60%">
                {if $IS_DEFAULT eq 1 AND $SETTING->columnName neq 'item_name'}
                    <select class="select2" {if $IS_DEFAULT eq 1} disabled="disabled" {/if} style="width: 100%">
                        <option value="{$SETTING->productField}" selected>{vtranslate($SETTING->productField,$QUALIFIED_MODULE)}</option>
                    </select>
                {else}
                    {assign var=CURRENT_FIELD  value= $SETTING->productField}
                    {include file="FieldSelect.tpl"|@vtemplate_path:$QUALIFIED_MODULE MULTIPLE = 0 NOCHOSEN=0 RECORD_STRUCTURE = $PRODUCT_RECORD_STRUCTURE CURRENT_FIELD=$CURRENT_FIELD SOURCE_MODULE = 'Products' IS_DEFAULT = $IS_DEFAULT BASE =$BASE}
                {/if}

            </span>
            <span class="clearfix"></span>
        </div>
        {****************************************Service field**************************************}
        <div class="rowCell">
            <span class="pull-left textAlignRight" ><a class="info-icon" data-toggle="popover" data-placement="top" style="color: #16a5e7" data-content="Select field to be prefilled from service. Optional" href="javascript:void(0)"><i class="fa fa fa-info-circle"></i></a> {if $IS_DEFAULT eq 1}<span class="redColor" style="margin-right: 2px;">*</span>{/if}{vtranslate('LBL_SERVICE',$QUALIFIED_MODULE)}</span>
            <span class="pull-right serviceField" style="width: 60%">
                {if $IS_DEFAULT eq 1 AND $SETTING->columnName neq 'item_name'}
                    <select class="select2" {if $IS_DEFAULT eq 1} disabled="disabled" {/if}  style="width: 100%">
                        <option value="{$SETTING->serviceField}" selected>{vtranslate($SETTING->serviceField,$QUALIFIED_MODULE)}</option>
                    </select>
                {else}
                    {assign var=CURRENT_FIELD  value= $SETTING->serviceField}
                    {include file="FieldSelect.tpl"|@vtemplate_path:$QUALIFIED_MODULE MULTIPLE = 0 NOCHOSEN=0 RECORD_STRUCTURE = $SERVICE_RECORD_STRUCTURE SOURCE_MODULE = 'Services' CURRENT_FIELD = $CURRENT_FIELD IS_DEFAULT = $IS_DEFAULT BASE =$BASE}
                {/if}
            </span>
            <span class="clearfix"></span>
        </div>

        {****************************************Mandatory field**************************************}
        <div class="rowCell">
            <span class="pull-left textAlignRight" ><a class="info-icon" data-toggle="popover" data-placement="top" style="color: #16a5e7" data-content="If set to yes, the user will be required to enter a value in this column." href="javascript:void(0)"><i class="fa fa fa-info-circle"></i></a> {vtranslate('LBL_MANDATORY',$QUALIFIED_MODULE)}</span>
            <span class="pull-right " style="width: 60%">
                <select name="isMandatory" class="{if empty($BASE)}select2{/if}" {if $IS_DEFAULT eq 1} disabled="disabled" {/if} style="width: 100%">
                    <option {if $SETTING->isMandatory eq '1'} selected {/if} value="1">{vtranslate('LBL_YES')}</option>
                    <option {if $SETTING->isMandatory eq '0'} selected {/if} value="0">{vtranslate('LBL_NO')}</option>
                </select>
            </span>
            <span class="clearfix"></span>
        </div>

        {****************************************Active field**************************************}
        <div class="rowCell">
            <span class="pull-left textAlignRight" >{vtranslate('Active')}</span>
            <span class="pull-right" style="width: 60%">
                <select name="isActive" class="{if empty($BASE)}select2{/if}"   style="width: 100%">
                    <option {if $SETTING->isActive == 'active'} selected {/if} value="active">{vtranslate('Active')}</option>
                    <option {if $SETTING->isActive == 'inactive'} selected {/if} value="inactive">{vtranslate('Inactive')}</option>
                </select>
            </span>
            <span class="clearfix"></span>
        </div>
        <div class="rowCell">
            <span class="pull-left textAlignRight" ><a class="info-icon" data-toggle="popover" data-placement="top" style="color: #16a5e7" data-content="If set to yes, the field will be read only for the user. The formulas will still apply, however user will not be able to change the value." href="javascript:void(0)"><i class="fa fa fa-info-circle"></i></a> {vtranslate('Editable')}</span>
            <span class="pull-right" style="width: 60%">
                <select name="editAble" class="{if empty($BASE)}select2{/if}"   style="width: 100%">
                    <option {if $SETTING->editAble eq '1'} selected {/if} value="1">{vtranslate('LBL_YES')}</option>
                    <option {if $SETTING->editAble eq '0' || $SETTING->columnName eq 'total' || $SETTING->columnName eq 'net_price'} selected {/if} value="0">{vtranslate('LBL_NO')}</option>
                </select>
            </span>
            <span class="clearfix"></span>
        </div>

        {****************************************Setting width column**************************************}
        <div class="rowCell">
            <span class="pull-left textAlignRight" style="padding-top: 12px;" ><a class="info-icon" data-toggle="popover" data-placement="top" style="color: #16a5e7" data-content='Default is 100%. You can also put it 20px, 40px, etc. Note, that all columns default to 100%, if you want to change the width of any column, you will need to update others as well, so it all adds up to 100%. Would be something like "Item Name=20%, Description=20%, Quantity, 7%, ListPrice=7%, etc...' href="javascript:void(0)"><i class="fa fa fa-info-circle"></i></a> {{vtranslate('LBL_WIDTH',$QUALIFIED_MODULE)}}</span>
            <span class="pull-right" style="width: 60%">
                <input name="columnWidth" class="inputElement" type="text" style="width: 80%;margin-right: 5%;margin-top: 4px;" value="{if $SETTING->columnWidth}{$SETTING->columnWidth}{else}100%{/if}">px
                <input type="hidden" name="columnWidthUnit" value="px">
            </span>
            <span class="clearfix"></span>
        </div>

        {****************************************Setting formula **************************************}
        <div class="rowCell">
            <div class="textAlignCenter" style="width: 100%;margin-bottom: 5px;" ><a class="info-icon" data-toggle="popover" data-placement="top" style="color: #16a5e7" data-content='Create your own formula utilizing any of the columns or fields from the record. Please see documentation for more details.' href="javascript:void(0)"><i class="fa fa fa-info-circle"></i></a> <strong style="font-size: 1em;">{{vtranslate('LBL_FORMULA',$QUALIFIED_MODULE)}}</strong></div>
            <div  style="width: 100%;" >
                <textarea class="textAreaElement" rows="3" name="formula">{$SETTING->formula}</textarea>
            </div>
            <span class="clearfix"></span>
        </div>
    </div>
</div>
{/strip}