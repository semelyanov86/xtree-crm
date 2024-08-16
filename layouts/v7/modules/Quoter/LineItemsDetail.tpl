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
    <table class="table table-bordered ui-sortable" id="lineItemTab" style="margin-top: 10px; table-layout: fixed;">
        <thead>
        <th colspan=1 class="lineItemBlockHeader">
            Item Details
        </th>
        <th colspan=1 class="lineItemBlockHeader">
            {assign var=CURRENCY_INFO value=$PARENT_RECORD_MODEL->getCurrencyInfo()}
            {vtranslate('LBL_CURRENCY', $MODULE_NAME)} : {vtranslate($CURRENCY_INFO['currency_name'],$MODULE_NAME)}({$CURRENCY_INFO['currency_symbol']})
        </th>
        {if $IS_INDIVIDUAL_TAX_TYPE}{assign var=COL_SPAN3 value=$COL_SPAN3 - 1}{else}{assign var=COL_SPAN3 value=$COL_SPAN3 -1}{/if}
        <th colspan="1" class="lineItemBlockHeader">
            {assign var=FINAL_DETAILS value=$RELATED_PRODUCTS.1.final_details}
            {vtranslate('LBL_TAX_MODE', $MODULE_NAME)} : {vtranslate($FINAL_DETAILS.taxtype, $MODULE_NAME)}
        </th>
        </thead>
        <tr>
            <td colspan="3" style="padding:0;margin: 0; border: 0;">
                <div class="divLineItemContainer"
                     style="overflow-y: hidden !important; overflow-x: auto !important; width: 100%;">
                    <table class="lineItemContainer table table-bordered" style="width: 100%; table-layout: fixed; margin-bottom: 5px; margin-top: 20px;">
                        <tbody>
                        <tr>
                            {foreach from = $SETTING  item = COLUMN}
                                <th class="cellItem th_{$COLUMN->columnName} {if ($IS_GROUP_TAX_TYPE && ($COLUMN->columnName == 'tax_total' || $COLUMN->columnName == 'tax_totalamount')) || ($IS_INDIVIDUAL_TAX_TYPE && $COLUMN->columnName == 'tax_totalamount')}hide{/if}" {if $COLUMN->columnWidth > 0} width = "{$COLUMN->columnWidth}px" {elseif $COLUMN->columnName eq 'item_name'} width="160px" {else} width="100px" {/if} style="border-right: 1px solid #ddd; font-size: 12px; {if $COLUMN->columnName != 'item_name' && $COLUMN->columnName != 'comment'}text-align: right;{/if} {if $COLUMN->isActive != 'active'}display: none;{/if}">
                                    {if $COLUMN->isMandatory eq 1}
                                        <span class="redColor">*</span>
                                    {/if}
                                    {if in_array($COLUMN->columnName,$COLUMN_DEFAULT)}
                                        {if $COLUMN->columnName == 'tax_total'}
                                            <b>{vtranslate('Tax','Quoter')}</b>
                                        {else}
                                            <b>{vtranslate($COLUMN->columnName,'Quoter')}</b>
                                        {/if}
                                    {else}
                                        <b>{$COLUMN->customHeader}</b>
                                    {/if}
                                </th>
                            {/foreach}
                        </tr>

                        {foreach key=INDEX item=LINE_ITEM_DETAIL from=$RELATED_PRODUCTS}
                            {if !empty($LINE_ITEM_DETAIL["section$INDEX"])}
                                <tr class="section" style="background-color: #f9f9f9; font-size: 12px;">
                                    <td class="fieldLabel " colspan="{count($SETTING) -1}" style = "font-size: 12px;">
                                        <span style="display: inline-block; width:100%; text-align: left;"><b>{$LINE_ITEM_DETAIL["section$INDEX"]}</b></span>
                                    </td>
                                </tr>
                            {/if}
                            <tr>
                                {assign var="entityType" value=$LINE_ITEM_DETAIL["entityType$INDEX"]}
                                {foreach from = $SETTING  item = COLUMN}
                                    {assign var = COLUMN_NAME value = $COLUMN->columnName}
                                    {if $COLUMN_NAME eq "item_name"}
                                        <td class="tr_{$COLUMN_NAME}">
                                            {if $COLUMN->index eq '0' }{for $var = 1 to $LINE_ITEM_DETAIL["level$INDEX"] - 1}&#8594; &nbsp; {/for}{/if}
                                            {$LINE_ITEM_DETAIL["productName$INDEX"]}
                                        </td>
                                    {elseif $COLUMN_NAME eq "quantity"}
                                        <td style="text-align: right;" class="tr_{$COLUMN_NAME}">
                                            {if $COLUMN->index eq '0' }{for $var = 1 to $LINE_ITEM_DETAIL["level$INDEX"] - 1}&#8594; &nbsp; {/for}{/if}
                                            {$RECORD->numberFormat($LINE_ITEM_DETAIL["quantity$INDEX"])}
                                        </td>
                                    {elseif $COLUMN_NAME == "listprice"}
                                        <td style="text-align: right;" class="tr_{$COLUMN_NAME}">
                                            {if $COLUMN->index eq '0' }{for $var = 1 to $LINE_ITEM_DETAIL["level$INDEX"] - 1}&#8594; &nbsp; {/for}{/if}
                                            {$RECORD->numberFormat($LINE_ITEM_DETAIL["listprice$INDEX"])}
                                        </td>
                                    {elseif $COLUMN_NAME == "tax_total"}
                                        <td  class="tr_{$COLUMN_NAME} {if $IS_GROUP_TAX_TYPE}hide{/if}" style="text-align: right;">
                                            {if $COLUMN->index eq '0' }{for $var = 1 to $LINE_ITEM_DETAIL["level$INDEX"] - 1}&#8594; &nbsp; {/for}{/if}
                                            {if !empty($LINE_ITEM_DETAIL["tax_totalamount$INDEX"])}
                                                {$RECORD->numberFormat($LINE_ITEM_DETAIL["tax_totalamount$INDEX"])}
                                            {else}
                                                {if !empty($LINE_ITEM_DETAIL['taxes'])}
                                                    {assign var="sum_tax_data_value" value=0}
                                                    {foreach key=tax_row_no item=tax_data from=$LINE_ITEM_DETAIL['taxes']}
                                                        {assign var="sum_tax_data_value" value=$sum_tax_data_value + (($tax_data['percentage'] * $LINE_ITEM_DETAIL["total$INDEX"]) / 100)}
                                                    {/foreach}
                                                    {$sum_tax_data_value}
                                                {/if}
                                            {/if}
                                            &nbsp;<a class="itemTaxPercent" title="Tax Total" data-content="<div><table>{if count($LINE_ITEM_DETAIL['taxes']) > 0}{foreach key=tax_row_no item=tax_data from=$LINE_ITEM_DETAIL['taxes']}<tr><td>{$tax_data['taxlabel']}:&nbsp;&nbsp;</td><td>{$tax_data['percentage']}%&nbsp;&nbsp;</td><td>=&nbsp;&nbsp;</td><td>{$RECORD->numberFormat($tax_data['percentage'] * $LINE_ITEM_DETAIL["total$INDEX"] / 100)}</td></tr>{/foreach}{else}<tr><td>No Taxes</td></tr>{/if}</table></div>" data-html="true" data-toggle="popover" data-trigger="focus">(<span style="font-style: italic;">
                                        {if !empty($LINE_ITEM_DETAIL["tax_total$INDEX"])}
                                            {$LINE_ITEM_DETAIL["tax_total$INDEX"]}
                                        {else}
                                            {assign var="sum_tax_data_percent" value=0}
                                            {foreach key=tax_row_no item=tax_data from=$LINE_ITEM_DETAIL['taxes']}
                                                {assign var="sum_tax_data_percent" value=$sum_tax_data_percent + $tax_data['percentage']}
                                            {/foreach}
                                            {$sum_tax_data_percent}
                                        {/if}
                                                    %</span>)</a>
                                        </td>
                                    {elseif $COLUMN_NAME eq "comment" }
                                        <td class="tr_{$COLUMN_NAME}">
                                            {if $COLUMN->index eq '0' }{for $var = 1 to $LINE_ITEM_DETAIL["level$INDEX"] - 1}&#8594; &nbsp; {/for}{/if}
                                            <span style="display:block;  overflow: hidden;-o-text-overflow: ellipsis;-ms-text-overflow: ellipsis; text-overflow: ellipsis;" title="{$LINE_ITEM_DETAIL["comment$INDEX"]}">
                                    {$LINE_ITEM_DETAIL["comment$INDEX"]|nl2br}
                                </span>
                                        </td>
                                    {elseif $COLUMN_NAME eq "discount_amount"}
                                        <td style="text-align: right;" class="tr_{$COLUMN_NAME}">
                                            {if $COLUMN->index eq '0' }{for $var = 1 to $LINE_ITEM_DETAIL["level$INDEX"] - 1}&#8594; &nbsp; {/for}{/if}
                                            {$RECORD->numberFormat($LINE_ITEM_DETAIL["discount_amount$INDEX"])}
                                        </td>
                                    {elseif $COLUMN_NAME eq "discount_percent"}
                                        <td style="text-align: right;" class="tr_{$COLUMN_NAME}">
                                            {if $COLUMN->index eq '0' }{for $var = 1 to $LINE_ITEM_DETAIL["level$INDEX"] - 1}&#8594; &nbsp; {/for}{/if}
                                            {$RECORD->numberFormat($LINE_ITEM_DETAIL["discount_percent$INDEX"])}
                                        </td>
                                    {elseif $COLUMN_NAME eq "total"}
                                        <td style="text-align: right;" class="tr_{$COLUMN_NAME}">
                                            {if $COLUMN->index eq '0' }{for $var = 1 to $LINE_ITEM_DETAIL["level$INDEX"] - 1}&#8594; &nbsp; {/for}{/if}
                                            {$RECORD->numberFormat($LINE_ITEM_DETAIL["total$INDEX"])}
                                        </td>
                                    {elseif $COLUMN_NAME eq "tax_totalamount"}
                                        <td class="hide tr_{$COLUMN_NAME}">
                                            {if $COLUMN->index eq '0' }{for $var = 1 to $LINE_ITEM_DETAIL["level$INDEX"] - 1}&#8594; &nbsp; {/for}{/if}
                                            {$RECORD->numberFormat($LINE_ITEM_DETAIL["tax_totalamount$INDEX"])}
                                        </td>
                                    {elseif $COLUMN_NAME eq "net_price"}
                                        <td style="text-align: right;" class="tr_{$COLUMN_NAME}">
                                            {if $COLUMN->index eq '0' }{for $var = 1 to $LINE_ITEM_DETAIL["level$INDEX"] - 1}&#8594; &nbsp; {/for}{/if}
                                            {$RECORD->numberFormat($LINE_ITEM_DETAIL["net_price$INDEX"])}
                                        </td>
                                    {elseif $CUSTOM_COLUMN_SETTING AND in_array($COLUMN_NAME,$CUSTOM_COLUMN_SETTING)}
                                        <td style="text-align: right;" class="tr_{$COLUMN_NAME}">
                                            {if $LINE_ITEM_DETAIL["parentProductId$INDEX"] AND $COLUMN->index eq '0' }<i>&#8594; &nbsp;</i>{/if}
                                            {assign var=FIELD_MODEL value=$LINE_ITEM_DETAIL[$COLUMN_NAME|cat:$INDEX]}
                                            {if $FIELD_MODEL && (is_array($FIELD_MODEL) || is_object($FIELD_MODEL))}
                                                {if $FIELD_MODEL->getFieldDataType() == 'image'}
                                                    {assign var=IMAGES value=$RECORD->getImageDetails($LINE_ITEM_DETAIL["hdnProductId$INDEX"])}
                                                    {if count($IMAGES) > 0}
                                                        <img class="product_image" src="layouts\vlayout\modules\Quoter\images\images_icon.png" data-productid = "{$LINE_ITEM_DETAIL["hdnProductId$INDEX"]}" width="32" height="32"  style="cursor: pointer">
                                                    {/if}
                                                {else}
                                                    {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName(),$entityType) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$entityType}
                                                {/if}
                                            {else}
                                                {$LINE_ITEM_DETAIL[$COLUMN_NAME|cat:$INDEX]}
                                            {/if}
                                        </td>
                                    {/if}
                                {/foreach}
                            </tr>
                            {if !empty($LINE_ITEM_DETAIL["running_item_value$INDEX"])}
                                {assign var=RUNNING_ITEMS value=$LINE_ITEM_DETAIL["running_item_value$INDEX"]}
                                {foreach key=RUNNING_NAME item=RUNNING_VALUE from=$RUNNING_ITEMS}
                                    <tr style="background-color: #f9f9f9;font-size: 12px;" class="running_item">
                                        <td colspan="{count($SETTING) -1}">
                                            <span class="pull-right" style="text-align: left;"><b>Running {vtranslate($TOTAL_SETTING[$RUNNING_NAME]['fieldLabel'],'Quoter')}: </b><b class="running_item_display">{number_format($RUNNING_VALUE,2)}</b></span>
                                        </td>
                                    </tr>
                                {/foreach}
                            {/if}
                        {/foreach}
                        </tbody>
                    </table><br>
                </div>
            </td>
        </tr>
    </table>
{/strip}