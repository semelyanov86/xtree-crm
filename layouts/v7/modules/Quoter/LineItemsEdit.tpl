{*/* * *******************************************************************************
* The content of this file is subject to the Quoter ("License");
* You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is VTExperts.com
* Portions created by VTExperts.com. are Copyright(C)VTExperts.com.
* All Rights Reserved.
* ****************************************************************************** */*}

{strip}
    {assign var="FINAL" value=$RELATED_PRODUCTS.1.final_details}

    {assign var="IS_INDIVIDUAL_TAX_TYPE" value=false}
    {assign var="IS_GROUP_TAX_TYPE" value=true}

    {if $FINAL.taxtype eq 'individual'}
        {assign var="IS_GROUP_TAX_TYPE" value=false}
        {assign var="IS_INDIVIDUAL_TAX_TYPE" value=true}
    {/if}

    <tr>
        <td style="padding:0;margin: 0; border: 0;">
            <div class="divLineItemContainer" style="overflow-y: hidden !important; overflow-x: auto !important; width: 100%;">
                {if is_array($SETTING)}
                    {assign var="COUNT_COLUMN_ACTIVE" value=count($SETTING)}
                {/if}
                {if $IS_GROUP_TAX_TYPE}
                    {assign var="COUNT_COLUMN_ACTIVE" value=$COUNT_COLUMN_ACTIVE-1 }
                {/if}
                <table class="table table-bordered lineItemContainer" style="border: none; table-layout: fixed;  border-collapse: separate !important;" data-currency-id = "{$SELECTED_CURRENCY.currency_id}">
                    <thead>
                        <tr>
                            {foreach from = $SETTING  item = COLUMN}
                                {if $COLUMN->isActive != 'active' || $COLUMN->columnName == 'tax_totalamount'}
                                    {assign var="COUNT_COLUMN_ACTIVE" value=$COUNT_COLUMN_ACTIVE-1 }
                                {/if}
                                <th class="cellItem th_{$COLUMN->columnName} {if $COLUMN->columnName == 'tax_totalamount'}hide tax_totalamount_column {if $IS_GROUP_TAX_TYPE}hide{/if}{/if} {if  $COLUMN->columnName == 'tax_total'}tax_column {if $IS_GROUP_TAX_TYPE}hide{/if}{/if}" {if $COLUMN->columnWidth > 0} width = "{$COLUMN->columnWidth}px" {elseif $COLUMN->columnName eq 'item_name'} width="160px" {else} width="120px" {/if}   style="{if $COLUMN->columnName != 'item_name' && $COLUMN->columnName != 'comment'}text-align: right;{/if} {if $COLUMN->isActive != 'active'}display: none;{/if}">
                                    {if $COLUMN->isMandatory eq 1}
                                        <span class="redColor">*</span>
                                    {/if}
                                    {if in_array($COLUMN->columnName,$COLUMN_DEFAULT)}
                                        <b style="font-size: 12px">{if $COLUMN->columnName == 'tax_total'}Tax{else}{vtranslate($COLUMN->columnName,'Quoter')}{/if}</b>
                                    {else}
                                        <b style="font-size: 12px">{$COLUMN->customHeader}</b>
                                    {/if}
                                </th>
                            {/foreach}
                        </tr>
                    </thead>
                    <tbody class="itemBase">
                        <tr class="hide lineItemCloneCopyForProduct" level="1" rowName="1">
                            {include file="LineItemsContent.tpl"|@vtemplate_path:'Quoter' row_no=0 data=[] BASE_ROW = 'Products'}
                        </tr>

                        <tr class="hide lineItemCloneCopyForService" level="1" rowName="1">
                            {include file="LineItemsContent.tpl"|@vtemplate_path:'Quoter' row_no=0 data=[] BASE_ROW = 'Services'}
                        </tr>
                    </tbody>

                    <tbody class="listItem">
                        {foreach key=row_no item=data from=$RELATED_PRODUCTS}
                            {assign var="hdnProductId" value="hdnProductId"|cat:$row_no}
                            {assign var="parentProductId" value="parentProductId"|cat:$row_no}
                            {assign var="next_row" value=$row_no+1}
                            {assign var="pre_row" value=$row_no-1}
                            {assign var="nextParentProductId" value="parentProductId"|cat:$next_row}
                            {assign var="preData" value=$RELATED_PRODUCTS[$pre_row]}
                            {assign var="nextData" value=$RELATED_PRODUCTS[$next_row]}

                            {if !empty($data["section$row_no"])}
                                <tr class="section" style="background-color: #f9f9f9;font-size: 12px;"">
                                    <td colspan="{if $IS_GROUP_TAX_TYPE}{$COUNT_COLUMN_ACTIVE}{else}{$COUNT_COLUMN_ACTIVE-1}{/if}" style = "border-left:0;">
                                        <span class="section_tool" style="display: inline-block; width:40px; text-align: left;">
                                            <img class="section_move_icon" src="layouts/v7/skins/images/drag.png"  border="0" title="Drag" >&nbsp;
                                            <i class="fa fa-trash deleteSection cursorPointer" title="{vtranslate('LBL_DELETE',$MODULE)}" ></i>
                                        </span>
                                        <span style="text-align: left; font-size: 12px;"><b>{$data["section$row_no"]}</b></span>
                                        <input type = "hidden" class ="section_value" name="section{$row_no}" value="{$data["section$row_no"]}" data-rowno ="{$row_no}" />
                                    </td>
                                    <td class="tdSpace{if $IS_GROUP_TAX_TYPE} hide{/if}" style="border-left:0;">&nbsp;</td>
                                </tr>
                            {/if}
                            <tr  id="row{$row_no}" class="lineItemRow" level="{if $data["level$row_no"]}{$data["level$row_no"]}{else}1{/if}" {if $data["entityType$row_no"] eq 'Products'}data-quantity-in-stock={$data["qtyInStock$row_no"] }{/if} rowName = "{$data['rowName']}">
                                {if $data["entityType$row_no"] eq 'Products'}
                                    {include file="LineItemsContent.tpl"|@vtemplate_path:'Quoter' row_no=$row_no data=$data SETTING =$SETTING BASE_ROW = 'Products'}
                                {else}
                                    {include file="LineItemsContent.tpl"|@vtemplate_path:'Quoter' row_no=$row_no data=$data SETTING =$SETTING BASE_ROW = 'Services'}
                                {/if}
                            </tr>
                            {assign var=arrRowName value=$data['arrRowName']}
                            {assign var='arr' value=array()}

                            {if !$nextData["level$next_row"]}
                                {if $data['isParentProduct']}
                                    {assign var='var' value=$data["level$row_no"]}
                                    {if is_array($arrRowName)}
                                        {for $i = 0 to count($arrRowName)-1}
                                            {$arr.$i = $arrRowName.$i}
                                        {/for}
                                    {/if}
                                {else}
                                    {assign var='var' value=$data["level$row_no"]-1}
                                    {if is_array($arrRowName)}
                                        {for $i = 0 to count($arrRowName)-2}
                                            {$arr.$i = $arrRowName.$i}
                                        {/for}
                                    {/if}
                                {/if}
                            {elseif $nextData["level$next_row"] <= $data["level$row_no"]}
                                {if $data['isParentProduct']}
                                    {assign var='var' value=$data["level$row_no"]-$nextData["level$next_row"]+1}
                                    {if is_array($arrRowName)}
                                        {for $i = 0 to count($arrRowName)-1}
                                            {$arr.$i = $arrRowName.$i}
                                        {/for}
                                    {/if}
                                {else}
                                    {assign var='var' value=$data["level$row_no"]-$nextData["level$next_row"]}
                                    {if is_array($arrRowName)}
                                        {for $i = 0 to count($arrRowName)-2}
                                            {$arr.$i = $arrRowName.$i}
                                        {/for}
                                    {/if}
                                {/if}
                            {/if}
                            {if is_array($arr)}
                                {assign var='levelAction' value=count($arr)}
                            {/if}
                            {while $var > 0}
                                <tr class="lineItemAction" rowName = "{for $i = 0 to $levelAction-1}{$arr.$i}{if $i neq $levelAction-1}-{/if}{/for}">
                                    <td><i class="muted addSubProduct "
                                           data-level = "{$levelAction+1}"
                                           {$parrentRow = $arr[$levelAction-1]}
                                           {$parrentRowId = $RELATED_PRODUCTS[$parrentRow]["hdnProductId$parrentRow"]}
                                           data-parent-id = "{$parrentRowId}">
                                            {for $i = 1 to $levelAction} &#8594; &nbsp; {/for}
                                            {vtranslate('LBL_ADD_ITEM','Quoter')}...</i>
                                    </td>
                                    {$var = $var-1}
                                    {$levelAction = $levelAction-1}
                                    {for $i = 1 to $COUNT_COLUMN_ACTIVE-1}
                                        <td {if $i ==($COUNT_COLUMN_ACTIVE -1) && $IS_GROUP_TAX_TYPE}class="hide"{/if}>&nbsp;</td>
                                    {/for}
                                </tr>
                            {/while}
                            {if !empty($data["running_item_value$row_no"])}
                                {assign var=RUNNING_ITEMS value=$data["running_item_value$row_no"]}
                                {foreach key=RUNNING_NAME item=RUNNING_VALUE from=$RUNNING_ITEMS}
                                    <tr style="background-color: #f9f9f9;font-size: 12px;" class="running_item" data-running-item-name = "{$RUNNING_NAME}" data-running-item-rowno = "{$row_no}">
                                        <td>
                                            <span class="running_item_tool" style="display: inline-block; width:40px; text-align: left;">
                                                <img class="running_item_move_icon" src="layouts/v7/skins/images/drag.png" border="0" title="Drag">&nbsp;
                                                <i class="fa fa-trash delete_running_item cursorPointer" title="Delete"></i>
                                            </span>
                                        </td>
                                        <td class="tdSpace{if $IS_GROUP_TAX_TYPE} hide{/if}" style="border-left:0;">&nbsp;</td>
                                        <td colspan="{if $IS_GROUP_TAX_TYPE}{$COUNT_COLUMN_ACTIVE-1}{else}{$COUNT_COLUMN_ACTIVE-2}{/if}" style="border-left:0;">
                                            <span class="pull-right" style="text-align: left;"><b>Running {vtranslate($TOTAL_SETTING[$RUNNING_NAME]['fieldLabel'],'Quoter')}: </b><b class="running_item_display">{$RUNNING_VALUE}</b></span>
                                            <input type = "hidden" class ="running_item_name" name="running_item_name{$row_no}[]" value="{$RUNNING_NAME}" />
                                            <input type = "hidden" class ="running_item_value" name="running_item_value{$row_no}[]" value="{$RUNNING_VALUE}" />
                                            {foreach from = $TOTAL_SETTING item=TOTAL_FIELD key=TOTAL_FIELD_NAME}
                                                <input type = "hidden" class ="running_{$TOTAL_FIELD_NAME}" value="" />
                                            {/foreach}
                                        </td>
                                    </tr>
                                {/foreach}
                            {/if}

                        {/foreach}
                        {if is_array($RELATED_PRODUCTS)}
                            {if count($RELATED_PRODUCTS) eq 0}
                                <tr id="row1" class="lineItemRow" level="1" rowName="1">
                                    {include file="LineItemsContent.tpl"|@vtemplate_path:'Quoter' row_no=1 data=[] SETTING =$SETTING BASE_ROW = 'Products'}
                                </tr>
                            {/if}
                        {/if}
                    </tbody>
                </table>
            </div>
        </td>
    </tr>
{/strip}