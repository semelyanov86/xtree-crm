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
<div class="colItemField {$BASE} {if $SETTING->columnName == 'item_name'}noSortable{/if}" data-is-default = "{$IS_DEFAULT}">
    <input type="hidden" name="itemColumn" value="{$SETTING->columnName}">

    {****************************************Header column*************************************}
    <div class="colHeader">
        {if $IS_DEFAULT eq 1}
            <b style="margin: 5px 0 5px 0;display: inline-block;">{vtranslate($SETTING->columnName,$QUALIFIED_MODULE)}</b>
            <span class="dropdown">
                <a class="dropdown-toggle fieldInfo" data-toggle="dropdown" href="#" title="Show column name"><span class="icon-info-sign"></span></a>
                <ul class="dropdown-menu _tooltip">
                    <span style="color: #000">${$SETTING->columnName}$</span>
                </ul>
            </span>
        {else}
            <span class="redColor" style="margin-right: 2px;">*</span>
            <input name="customHeader" maxlength="40" placeholder="{vtranslate('LBL_TYPE_IN_THE_HEADER_FIELD',$QUALIFIED_MODULE)}" value="{if $SETTING->customHeader}{$SETTING->customHeader}{/if}" data-validation-engine="validate[required]" style="width: 65%;margin: 0;">
            <span class="dropdown">
                <a class="dropdown-toggle fieldInfo" data-toggle="dropdown" href="#" title="Show column name"><span class="icon-info-sign"></span></a>
                <ul class="dropdown-menu _tooltip">
                    <span style="color: #000">${$SETTING->columnName}$</span>
                </ul>
            </span>
            <i class="icon-trash deleteColumn cursorPointer" title="{vtranslate('LBL_DELETE',$MODULE)}"></i>
            <span class="clearfix"></span>
        {/if}

    </div>
    <div class="colContent">

        {****************************************Product field**************************************}
        <div class="rowCell">
            <span class="pull-left textAlignRight"><span class="redColor" style="margin-right: 2px;">*</span>{vtranslate('LBL_PRODUCT',$QUALIFIED_MODULE)}</span>
            <span class="pull-right productField" style="width: 60%">
                {if $IS_DEFAULT eq 1 AND $SETTING->columnName neq 'item_name'}
                    <select class="chzn-select" {if $IS_DEFAULT eq 1} disabled="disabled" {/if} style="width: 100%"  data-validation-engine="validate[required]">
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
            <span class="pull-left textAlignRight" ><span class="redColor" style="margin-right: 2px;">*</span>{vtranslate('LBL_SERVICE',$QUALIFIED_MODULE)}</span>
            <span class="pull-right serviceField" style="width: 60%">
                {if $IS_DEFAULT eq 1 AND $SETTING->columnName neq 'item_name'}
                    <select class="chzn-select" {if $IS_DEFAULT eq 1} disabled="disabled" {/if} data-validation-engine="validate[required]"  style="width: 100%">
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
            <span class="pull-left textAlignRight" >{vtranslate('LBL_MANDATORY',$QUALIFIED_MODULE)}</span>
            <span class="pull-right " style="width: 60%">
                <select name="isMandatory" class="{if empty($BASE)}chzn-select{/if}" {if $IS_DEFAULT eq 1} disabled="disabled" {/if} style="width: 100%">
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
                <select name="isActive" class="{if empty($BASE)}chzn-select{/if}"   style="width: 100%">
                    <option {if $SETTING->isActive == 'active'} selected {/if} value="active">{vtranslate('Active')}</option>
                    <option {if $SETTING->isActive == 'inactive'} selected {/if} value="inactive">{vtranslate('Inactive')}</option>
                </select>
            </span>
            <span class="clearfix"></span>
        </div>

        {****************************************Setting width column**************************************}
        <div class="rowCell">
            <span class="pull-left textAlignRight" >{{vtranslate('LBL_WIDTH',$QUALIFIED_MODULE)}}</span>
            <span class="pull-right" style="width: 60%">
                <input name="columnWidth" type="text" style="width: 35%;margin-right: 5%;margin-top: 4px;" value="{$SETTING->columnWidth}">px
                <input type="hidden" name="columnWidthUnit" value="px">
            </span>
            <span class="clearfix"></span>
        </div>

        {****************************************Setting formula **************************************}
        <div class="rowCell">
            <div class="textAlignCenter" style="width: 100%;margin-bottom: 5px;" ><strong>{{vtranslate('LBL_FORMULA',$QUALIFIED_MODULE)}}</strong></div>
            <div  style="width: 100%;" >
                <textarea name="formula">{$SETTING->formula}</textarea>
            </div>
            <span class="clearfix"></span>
        </div>
    </div>
</div>
{/strip}