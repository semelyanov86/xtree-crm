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
    <td style="padding: 0">
        <div class="divLineItemContainer " style="overflow-x: auto !important; width: 100%;">
            <table class="lineItemContainer" style="width: 100%; border: none; table-layout: fixed;">
                <tbody>
                    <tr>
                        {foreach from = $SETTING  item = COLUMN}
                            <th class="cellItem {if $IS_GROUP_TAX_TYPE && $COLUMN->columnName == 'tax_total'}hide{/if}"  {if $COLUMN->columnWidth > 0} width = "{$COLUMN->columnWidth}px" {elseif $COLUMN->columnName eq 'item_name'} width="160px" {else} width="100px" {/if}>
                                {if $COLUMN->isMandatory eq 1}
                                    <span class="redColor">*</span>
                                {/if}
                                {if in_array($COLUMN->columnName,$COLUMN_DEFAULT)}
                                    <b>{vtranslate($COLUMN->columnName,'Quoter')}</b>
                                {else}
                                    <b>{$COLUMN->customHeader}</b>
                                {/if}
                            </th>
                        {/foreach}
                    </tr>

                    {foreach key=INDEX item=LINE_ITEM_DETAIL from=$RELATED_PRODUCTS}
                        {if !empty($LINE_ITEM_DETAIL["section$INDEX"])}
                            <tr class="section">
                                <td class="fieldLabel " colspan="{count($SETTING)}" style = "font-size: 14px;">
                                    <span style="display: inline-block; width:100%; text-align: left;"><b>{$LINE_ITEM_DETAIL["section$INDEX"]}</b></span>
                                </td>
                            </tr>
                        {/if}
                        <tr>
                            {assign var="entityType" value=$LINE_ITEM_DETAIL["entityType$INDEX"]}

                            {foreach from = $SETTING  item = COLUMN}

                                {assign var = COLUMN_NAME value = $COLUMN->columnName}
                                {if $COLUMN_NAME eq "item_name"}
                                    <td >
                                        <div class="row-fluid"  >
                                            {if $COLUMN->index eq '0' }{for $var = 1 to $LINE_ITEM_DETAIL["level$INDEX"] - 1}&#8594; &nbsp; {/for}{/if}
                                            {$LINE_ITEM_DETAIL["productName$INDEX"]}
                                        </div>
                                    </td>
                                {elseif $COLUMN_NAME eq "quantity"}
                                    <td>
                                        <div >
                                            {if $COLUMN->index eq '0' }{for $var = 1 to $LINE_ITEM_DETAIL["level$INDEX"] - 1}&#8594; &nbsp; {/for}{/if}
                                            {$RECORD->numberFormat($LINE_ITEM_DETAIL["quantity$INDEX"])}
                                        </div>
                                    </td>
                                {elseif $COLUMN_NAME == "listprice"}
                                    <td>
                                        <div >
                                            {if $COLUMN->index eq '0' }{for $var = 1 to $LINE_ITEM_DETAIL["level$INDEX"] - 1}&#8594; &nbsp; {/for}{/if}
                                            {$RECORD->numberFormat($LINE_ITEM_DETAIL["listprice$INDEX"])}
                                        </div>
                                    </td>
                                {elseif $COLUMN_NAME == "tax_total"}
                                    <td {if $IS_GROUP_TAX_TYPE}class="hide"{/if}>
                                        <div >
                                            {if $COLUMN->index eq '0' }{for $var = 1 to $LINE_ITEM_DETAIL["level$INDEX"] - 1}&#8594; &nbsp; {/for}{/if}
                                            {$RECORD->numberFormat($LINE_ITEM_DETAIL["tax_total$INDEX"])}
                                        </div>
                                    </td>
                                {elseif $COLUMN_NAME eq "comment" }
                                    <td>
                                        <div>
                                            {if $COLUMN->index eq '0' }{for $var = 1 to $LINE_ITEM_DETAIL["level$INDEX"] - 1}&#8594; &nbsp; {/for}{/if}
                                            <span {*style="display:block;height:35px;  overflow: hidden;white-space: nowrap;-o-text-overflow: ellipsis;-ms-text-overflow: ellipsis; text-overflow: ellipsis;"*} title="{$LINE_ITEM_DETAIL["comment$INDEX"]}">
                                                {$LINE_ITEM_DETAIL["comment$INDEX"]|nl2br}
                                            </span>
                                        </div>
                                    </td>
                                {elseif $COLUMN_NAME eq "discount_amount"}
                                    <td>
                                        <div >
                                            {if $COLUMN->index eq '0' }{for $var = 1 to $LINE_ITEM_DETAIL["level$INDEX"] - 1}&#8594; &nbsp; {/for}{/if}
                                            {$RECORD->numberFormat($LINE_ITEM_DETAIL["discount_amount$INDEX"])}
                                        </div>
                                    </td>
                                {elseif $COLUMN_NAME eq "discount_percent"}
                                    <td>
                                        <div >
                                            {if $COLUMN->index eq '0' }{for $var = 1 to $LINE_ITEM_DETAIL["level$INDEX"] - 1}&#8594; &nbsp; {/for}{/if}
                                            {$RECORD->numberFormat($LINE_ITEM_DETAIL["discount_percent$INDEX"])}
                                        </div>
                                    </td>
                                {elseif $COLUMN_NAME eq "total"}
                                    <td>
                                        <div >
                                            {if $COLUMN->index eq '0' }{for $var = 1 to $LINE_ITEM_DETAIL["level$INDEX"] - 1}&#8594; &nbsp; {/for}{/if}
                                            {$RECORD->numberFormat($LINE_ITEM_DETAIL["total$INDEX"])}
                                        </div>
                                    </td>
                                {elseif $COLUMN_NAME eq "tax_totalamount"}
                                    <td>
                                        <div >
                                            {if $COLUMN->index eq '0' }{for $var = 1 to $LINE_ITEM_DETAIL["level$INDEX"] - 1}&#8594; &nbsp; {/for}{/if}
                                            {$RECORD->numberFormat($LINE_ITEM_DETAIL["tax_totalamount$INDEX"])}
                                        </div>
                                    </td>
                                {elseif $COLUMN_NAME eq "net_price"}
                                    <td>
                                        <div >
                                {if $COLUMN->index eq '0' }{for $var = 1 to $LINE_ITEM_DETAIL["level$INDEX"] - 1}&#8594; &nbsp; {/for}{/if}
                                            <span class="pull-right">
                                            {$RECORD->numberFormat($LINE_ITEM_DETAIL["net_price$INDEX"])}
                                            </span>
                                        </div>
                                    </td>
                                {elseif $CUSTOM_COLUMN_SETTING AND in_array($COLUMN_NAME,$CUSTOM_COLUMN_SETTING)}
                                    <td>
                                        <div >
                                            {if $LINE_ITEM_DETAIL["parentProductId$INDEX"] AND $COLUMN->index eq '0' }<i>&#8594; &nbsp;</i>{/if}
                                            {assign var=FIELD_MODEL value=$LINE_ITEM_DETAIL[$COLUMN_NAME|cat:$INDEX]}
                                            {if $FIELD_MODEL}
                                                {if $FIELD_MODEL->getFieldDataType() == 'image'}
                                                    {assign var=IMAGES value=$RECORD->getImageDetails($LINE_ITEM_DETAIL["hdnProductId$INDEX"])}
                                                    {if count($IMAGES) > 0}
                                                        <img class="product_image" src="layouts\vlayout\modules\Quoter\images\images_icon.png" data-productid = "{$LINE_ITEM_DETAIL["hdnProductId$INDEX"]}" width="32" height="32"  style="cursor: pointer">
                                                    {/if}
                                                {else}
                                                    {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName(),$entityType) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$entityType}
                                                {/if}
                                            {/if}
                                        </div>
                                    </td>
                                {/if}
                            {/foreach}
                        </tr>
                        {if !empty($LINE_ITEM_DETAIL["running_item_value$INDEX"])}
                            {assign var=RUNNING_ITEMS value=$LINE_ITEM_DETAIL["running_item_value$INDEX"]}
                            {foreach key=RUNNING_NAME item=RUNNING_VALUE from=$RUNNING_ITEMS}
                                <tr class="running_item">
                                    <td colspan="{count($SETTING)}">
                                        <span class="pull-right" style="text-align: left;"><b>Running {vtranslate($TOTAL_SETTING[$RUNNING_NAME]['fieldLabel'],'Quoter')}: </b><b class="running_item_display">{$RUNNING_VALUE}</b></span>
                                    </td>
                                </tr>
                            {/foreach}
                        {/if}
                    {/foreach}
                </tbody>
            </table>
        </div>
    </td>
</tr>

</tbody>
{/strip}