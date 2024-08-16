{*/* * *******************************************************************************
* The content of this file is subject to the Quoter ("License");
* You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is VTExperts.com
* Portions created by VTExperts.com. are Copyright(C)VTExperts.com.
* All Rights Reserved.
* ****************************************************************************** */*}

{strip}
{assign var="deleted" value="deleted"|cat:$row_no}
{assign var="hdnProductId" value="hdnProductId"|cat:$row_no}
{assign var="productName" value="productName"|cat:$row_no}
{assign var="comment" value="comment"|cat:$row_no}
{assign var="qtyInStock" value="qtyInStock"|cat:$row_no}
{assign var="quantity" value="quantity"|cat:$row_no}
{assign var="qty" value="qty"|cat:$row_no}
{assign var="listprice" value="listprice"|cat:$row_no}
{if $data.$listprice eq '' || $data.$listprice eq '0'}
    {assign var="otherListPrice" value="listPrice"|cat:$row_no}
{/if}
{assign var="total" value="total"|cat:$row_no}
{assign var="tax_totalamount" value="tax_totalamount"|cat:$row_no}
{assign var="total_format" value="total_format"|cat:$row_no}
{assign var="subproduct_ids" value="subproduct_ids"|cat:$row_no}
{assign var="subprod_names" value="subprod_names"|cat:$row_no}
{assign var="entityIdentifier" value="entityType"|cat:$row_no}
{assign var="entityType" value=$data.$entityIdentifier}

{assign var="discount_type" value="discount_type"|cat:$row_no}
{assign var="discount_percent" value="discount_percent"|cat:$row_no}
{assign var="checked_discount_percent" value="checked_discount_percent"|cat:$row_no}
{assign var="style_discount_percent" value="style_discount_percent"|cat:$row_no}
{assign var="discount_amount" value="discount_amount"|cat:$row_no}
{assign var="checked_discount_amount" value="checked_discount_amount"|cat:$row_no}
{assign var="style_discount_amount" value="style_discount_amount"|cat:$row_no}
{assign var="checked_discount_zero" value="checked_discount_zero"|cat:$row_no}
{assign var="net_price" value="net_price"|cat:$row_no}
{assign var="level" value="level"|cat:$row_no}
{assign var="net_price_format" value="net_price_format"|cat:$row_no}
{assign var="tax_total" value="tax_total"|cat:$row_no}

{assign var="productDeleted" value="productDeleted"|cat:$row_no}
{assign var="productId" value=$data[$hdnProductId]}
{assign var="listPriceValues" value=Products_Record_Model::getListPriceValues($productId)}


{foreach from = $SETTING  item = COLUMN}
    {assign var = COLUMN_NAME value = $COLUMN->columnName}
    {assign var = EDITABLE value = $COLUMN->editAble}
    {if $COLUMN_NAME eq "item_name"}
        <td {if $COLUMN->isActive != 'active'}style="display: none;" {/if} class="cellItem tr_{$COLUMN_NAME}" >
            <div style="min-width: 200px;">
                <input type="hidden" class="rowNumber" value="{$row_no}" />
                <input type="hidden" id="{$hdnProductId}" name="{$hdnProductId}" value="{$data.$hdnProductId}" class="selectedModuleId"/>
                <input type="hidden" id="lineItemType{$row_no}" name="lineItemType{$row_no}" value="{$entityType}" class="lineItemType"/>
                <input type="hidden" id="level{$row_no}" name="level{$row_no}" value="{if $data.$level}{$data.$level}{else}1{/if}" class="level"/>

                <input type="hidden" id="parentProductId{$row_no}" value="{if $data.$parentProductId}{$data.$parentProductId}{/if}" name="parentProductId{$row_no}" class="parentId"/>
                {if $data.$level >1}<i>{for $var=2 to $data.$level} &#8594; &nbsp; {/for}</i>{/if}


                <a><img src="{vimage_path('drag.png')}" border="0" title="{vtranslate('LBL_DRAG',$MODULE)}"/></a>
                &nbsp;
                <i class="fa fa-trash deleteRow cursorPointer" title="{vtranslate('LBL_DELETE',$MODULE)}"></i>
                &nbsp;
                <i class="fa fa-times-circle clearLineItemNew cursorPointer" style="margin-top: 9px;" title="Clear" ></i>
                &nbsp;&nbsp;
                <input  type="text" id="{$productName}" name="{$productName}" value="{$data.$productName}" class="productName inputElement {if $row_no neq 0} autoComplete {/if} td_{$COLUMN_NAME}" placeholder="{vtranslate('LBL_TYPE_SEARCH',$MODULE)}" data-rule-required="true" {if !empty($data.$productName)} disabled="disabled" {/if}  style="vertical-align: top;margin-right: 5px;"/>
                {if $row_no eq 0}
                    <i class="quoterLineItemPopup cursorPointer vicon-services" data-popup="ServicesPopup" data-parent-id="{if $data.$parentProductId}{$data.$parentProductId}{/if}" title="{vtranslate('Services',$MODULE)}" data-module-name="Services" data-field-name="serviceid"  width="16px"/></i>
                    <i class="quoterLineItemPopup cursorPointer vicon-products" data-popup="ProductsPopup" data-parent-id="{if $data.$parentProductId}{$data.$parentProductId}{/if}" title="{vtranslate('Products',$MODULE)}" data-module-name="Products" data-field-name="productid"  width="16px"/></i>

                {else}
                    {if !$RECORD_ID}
                        {if ($entityType eq 'Services') and (!$data.$productDeleted) or $PRODUCT_ACTIVE neq 'true'}
                            <i class="quoterLineItemPopup cursorPointer vicon-services" data-popup="ServicesPopup" data-module-name="Services" data-parent-id="{if $data.$parentProductId}{$data.$parentProductId}{/if}" title="{vtranslate('Services',$MODULE)}" data-field-name="serviceid"  width="16px"/></i>
                        {elseif (!$data.$productDeleted)}
                            <i class="quoterLineItemPopup cursorPointer vicon-products " data-popup="ProductsPopup" data-module-name="Products" data-parent-id="{if $data.$parentProductId}{$data.$parentProductId}{/if}" title="{vtranslate('Products',$MODULE)}" data-field-name="productid"  width="16px"/></i>
                        {/if}
                    {else}
                        {if ($entityType eq 'Services') and (!$data.$productDeleted)}
                            <i class="{if $SERVICE_ACTIVE}quoterLineItemPopup{/if} cursorPointer vicon-services" data-popup="ServicesPopup" data-module-name="Services" data-parent-id="{if $data.$parentProductId}{$data.$parentProductId}{/if}" title="{vtranslate('Services',$MODULE)}" data-field-name="serviceid"  width="16px"/></i>
                        {elseif (!$data.$productDeleted)}
                            <i class="{if $PRODUCT_ACTIVE}quoterLineItemPopup{/if} cursorPointer vicon-products" data-popup="ProductsPopup" data-module-name="Products" data-parent-id="{if $data.$parentProductId}{$data.$parentProductId}{/if}" title="{vtranslate('Products',$MODULE)}" data-field-name="productid"  width="16px"/></i>

                        {/if}
                    {/if}
                {/if}
            </div>

            {if $data.$productDeleted}
                <div class="row-fluid deletedItem redColor">
                    {if empty($data.$productName)}
                        {vtranslate('LBL_THIS_LINE_ITEM_IS_DELETED_FROM_THE_SYSTEM_PLEASE_REMOVE_THIS_LINE_ITEM',$MODULE)}
                    {else}
                        {vtranslate('LBL_THIS',$MODULE)} {$entityType} {vtranslate('LBL_IS_DELETED_FROM_THE_SYSTEM_PLEASE_REMOVE_OR_REPLACE_THIS_ITEM',$MODULE)}
                    {/if}
                </div>
            {/if}
        </td>

    {elseif $COLUMN_NAME eq "quantity"}
        <td {if $COLUMN->isActive != 'active'}style="display: none;" {/if} class="cellItem tr_{$COLUMN_NAME}"  >
            {if $EDITABLE eq "0"}
                <input id="{$quantity}" name="{$quantity}" type="text" class="hide qty inputElement" style="width: 89%;"  value="{if !empty($data.$quantity)}{$data.$quantity}{else}1{/if}"/>
                <span id="{$quantity}_{$row_no}" name="{$quantity}_{$row_no}" class="td_{$COLUMN_NAME}">{if !empty($data.$quantity)}{$data.$quantity}{else}1{/if}</span>
                <input id="{$qty}" name="{$qty}" type="hidden" class="hide qty inputElement" style="width: 89%;"  value="{if !empty($data.$quantity)}{$data.$quantity}{else}1{/if}"/>
            {else}
                <input id="{$quantity}" name="{$quantity}" type="text" data-rule-required=true data-rule-positive=true data-rule-greater_than_zero=true class="qty inputElement td_{$COLUMN_NAME}" style="width: 89%;"  value="{if !empty($data.$quantity)}{$data.$quantity}{else}1{/if}"/>
                <input id="{$qty}" name="{$qty}" type="hidden" class="qty inputElement td_{$COLUMN_NAME}" style="width: 89%;"  value="{if !empty($data.$quantity)}{$data.$quantity}{else}1{/if}"/>
            {/if}
        </td>
    {elseif $COLUMN_NAME eq "listprice" }
        <td {if $COLUMN->isActive != 'active'}style="display: none;" {/if} class="cellItem tr_{$COLUMN_NAME}"  >
            <div>
                {if $EDITABLE eq "0"}
                    <input id="{$listprice}" name="{$listprice}" value="{if !empty($data.$listprice)}{$data.$listprice}{elseif !empty($data.$otherListPrice)}{$data.$otherListPrice}{else}0{/if}" type="text"  class="hide listPrice inputElement" list-info='{if !empty($data.$listprice)}{Zend_Json::encode($listPriceValues)}{/if}' style="width: 89%;"/>
                    <span id="{$listprice}_{$row_no}" class="td_{$COLUMN_NAME}" name="{$listprice}_{$row_no}" >{if !empty($data.$listprice)}{$data.$listprice}{elseif !empty($data.$otherListPrice)}{$data.$otherListPrice}{else}0{/if}</span>

                {else}
                    <input id="{$listprice}" name="{$listprice}" value="{if !empty($data.$listprice)}{$data.$listprice}{elseif !empty($data.$otherListPrice)}{$data.$otherListPrice}{else}0{/if}" type="text"  class="listPrice inputElement td_{$COLUMN_NAME}" list-info='{if !empty($data.$listprice)}{Zend_Json::encode($listPriceValues)}{/if}' style="width: 89%;"/>
                    {assign var=PRICEBOOK_MODULE_MODEL value=Vtiger_Module_Model::getInstance('PriceBooks')}
                    {if $PRICEBOOK_MODULE_MODEL->isPermitted('DetailView')}
                        <i class="cursorPointer alignMiddle quoterPriceBookPopup vicon-pricebooks" data-popup="Popup" data-module-name="PriceBooks" title="Price Books"></i>
                    {/if}
                {/if}

            </div>
        </td>
    {elseif $COLUMN_NAME eq "tax_total"}
        <td {if $COLUMN->isActive != 'active'}style="display: none;" {/if} class="cellItem tax_column {if $IS_GROUP_TAX_TYPE}hide{/if} tr_{$COLUMN_NAME}"  >

            <div class="individualTaxContainer">
                &nbsp;<span style="top: -2px;"><span class="individualTax1_Amount">{if $data.$tax_totalamount}{number_format($data.$tax_totalamount, 2, '.', '')}{else}
                    {if is_array($data.taxes) && count($data.taxes) > 0}
                        {assign var="sum_tax_data_value" value=0}
                        {foreach key=tax_row_no item=tax_data from=$data.taxes}
                            {assign var="sum_tax_data_value" value=$sum_tax_data_value + (($tax_data.percentage * $data.$total) / 100)}
                        {/foreach}
                        {$sum_tax_data_value}
                    {/if}
                {/if}</span>&nbsp;<a href="javascript:void(0)" class="individualTax1">(<span style="font-style: italic">{if !empty($data.$tax_total)}{$data.$tax_total}{else}
                                {if is_array($data.taxes) && count($data.taxes) > 0}
                                    {assign var="sum_tax_data_percent" value=0}
                                    {foreach key=tax_row_no item=tax_data from=$data.taxes}
                                        {assign var="sum_tax_data_percent" value=$sum_tax_data_percent + $tax_data.percentage}
                                    {/foreach}
                                    {$sum_tax_data_percent}
                                {/if}
                            {/if}%</span>) </a></span>
            </div>
            <span class="taxDivContainer">
                <div class="taxUI hide" id="tax_div{$row_no}">
                    <!-- we will form the table with all taxes -->
                    <table width="100%" border="0" cellpadding="5" cellspacing="0" class="table table-nobordered popupTable" id="tax_table{$row_no}">
                        <tr>
                            <th colspan="2" id="tax_div_title{$row_no}" nowrap align="left" ><b>{vtranslate('LBL_TAX_TOTAL','Quoter')} </b></th>
                            <th>
                                <button type="button" class="close closeDiv">x</button>
                            </th>
                        </tr>
                        {if is_array($data.taxes) && count($data.taxes) > 0}
                            {foreach key=tax_row_no item=tax_data from=$data.taxes}
                                {assign var="taxname" value=$tax_data.taxname|cat:"_percentage"|cat:$row_no}
                                {assign var="taxlabel" value=$tax_data.taxlabel|cat:"_percentage"|cat:$row_no}
                                <tr>
                                    <td>
                                        {$tax_data.taxlabel}
                                    </td>
                                    <td>
                                        <input style="width: 70px;" type="text"  name="{$taxname}" id="{$taxname}" value="{$tax_data.percentage}" class="smallInputBox taxPercentage inputElement"  data-regions-list="{Vtiger_Util_Helper::toSafeHTML($tax_data.regionsList)}"/>&nbsp;%
                                    </td>
                                    <td>
                                        <input style="width: 70px;" type="text" readonly name="" id="" value="{(($tax_data.percentage * $data.$total) / 100)}" class="smallInputBox taxValue inputElement" /></td>
                                </tr>
                            {/foreach}
                        {else}
                            <tr>
                                <td colspan="3">No Taxes</td>
                            </tr>
                        {/if}

                    </table>

                </div>
            </span>
            <div class="input-append">
                <input style="opacity: 0;height: 1px;padding:  0px;margin: 0px;width:  10px;" id="{$tax_total}" name="{$tax_total}" type="text" class="tax_total inputElement td_{$COLUMN_NAME}" readonly value="{if !empty($data.$tax_total)}{$data.$tax_total}{else}
                {if is_array($data.taxes) && count($data.taxes) > 0}
                    {assign var="sum_tax_data_percent" value=0}
                    {foreach key=tax_row_no item=tax_data from=$data.taxes}
                        {assign var="sum_tax_data_percent" value=$sum_tax_data_percent + $tax_data.percentage}
                    {/foreach}
                    {$sum_tax_data_percent}
                {/if}
            {/if}"/>
            </div>
        </td>
    {elseif $COLUMN_NAME eq "tax_totalamount"}
        <td {if $COLUMN->isActive != 'active'}style="display: none;" {/if} class="hide cellItem tax_totalamount_column {if $IS_GROUP_TAX_TYPE}hide{/if} tr_{$COLUMN_NAME}">
            <input type="hidden" name = "tax_totalamount{$row_no}" value="
            {if $IS_INDIVIDUAL_TAX_TYPE}
                {if $data.$tax_totalamount}
                    {$data.$tax_totalamount}
                {else}
                    {if is_array($data.taxes) && count($data.taxes) > 0}
                        {assign var="sum_tax_data_value" value=0}
                        {foreach key=tax_row_no item=tax_data from=$data.taxes}
                            {assign var="sum_tax_data_value" value=$sum_tax_data_value + (($tax_data.percentage * $data.$total) / 100)}
                        {/foreach}
                        {$sum_tax_data_value}
                    {/if}
                {/if}
            {/if}" style="width: 89%;"/>
            <div id="tax_totalamount{$row_no}" align="right" class="tax_totalamount">
                {if $data.$tax_totalamount}
                    {$data.$tax_totalamount}
                {else}
                    {if is_array($data.taxes) && count($data.taxes) > 0}
                        {assign var="sum_tax_data_value" value=0}
                        {foreach key=tax_row_no item=tax_data from=$data.taxes}
                            {assign var="sum_tax_data_value" value=$sum_tax_data_value + (($tax_data.percentage * $data.$total) / 100)}
                        {/foreach}
                        {$sum_tax_data_value}
                    {/if}
                {/if}
            </div>
        </td>
    {elseif $COLUMN_NAME eq "total"  }
        <td {if $COLUMN->isActive != 'active'}style="display: none;" {/if} class="cellItem tr_{$COLUMN_NAME}"  >
            {if $EDITABLE eq "0"}
                <input type="hidden" name = "total{$row_no}" value="{if $data.$total}{$data.$total}{else}0{/if}" style="width: 89%;"/>
                <div id="total{$row_no}" align="right" class="total td_{$COLUMN_NAME}">{if $data.$total_format}{$data.$total_format}{else}0{/if}</div>
            {else}
                <input type="text" class="inputElement td_{$COLUMN_NAME}" name = "total{$row_no}" value="{if $data.$total}{$data.$total}{else}0{/if}" style="width: 89%;"/>
            {/if}
        </td>
    {elseif $COLUMN_NAME eq "net_price"}
        <td {if $COLUMN->isActive != 'active'}style="display: none;" {/if} class="cellItem tr_{$COLUMN_NAME}" >
            {if $EDITABLE eq "0"}
                <input type="hidden" name = "net_price{$row_no}" value="{if $data.$net_price}{$data.$net_price}{else}0{/if}" style="width: 89%;"/>
                <span id="net_price{$row_no}" class="pull-right net_price td_{$COLUMN_NAME}">{if $data.$net_price_format}{$data.$net_price_format}{else}0{/if}</span>
            {else}
                <input type="text" class="inputElement td_{$COLUMN_NAME}" name = "net_price{$row_no}" value="{if $data.$net_price}{$data.$net_price}{else}0{/if}" style="width: 89%;"/>
            {/if}
        </td>
    {elseif $COLUMN_NAME eq "comment" }
        <td {if $COLUMN->isActive != 'active'}style="display: none;" {/if} class="cellItem tr_{$COLUMN_NAME}"  >
            {if $EDITABLE eq "0"}
                <textarea id="{$comment}" name="{$comment}" class="hide lineItemCommentBox inputElement textAreaElement" style="resize: vertical;">{$data.$comment}</textarea>
                <span id="{$comment}_{$row_no}" name="{$comment}_{$row_no}" class="td_{$COLUMN_NAME}">{$data.$comment}</span>
            {else}
                <textarea id="{$comment}" name="{$comment}" class="lineItemCommentBox inputElement td_{$COLUMN_NAME} textAreaElement " style="height:30px;resize: vertical;">{$data.$comment}</textarea>
            {/if}
        </td>
    {elseif $COLUMN_NAME eq "discount_amount"}
        <td {if $COLUMN->isActive != 'active'}style="display: none;" {/if} class="cellItem tr_{$COLUMN_NAME}"  >
            {if $EDITABLE eq "0"}
                <input id="{$discount_amount}" name="{$discount_amount}" type="text" value = "{if $data.$discount_amount}{$data.$discount_amount}{/if}" class="hide discount_amount inputElement" style="width: 89%;"/>
                <span id="{$discount_amount}_{$row_no}" name="{$discount_amount}_{$row_no}" class="td_{$COLUMN_NAME}">{if $data.$discount_amount}{$data.$discount_amount}{/if}</span>
            {else}
                <input id="{$discount_amount}" name="{$discount_amount}" type="text" value = "{if $data.$discount_amount}{$data.$discount_amount}{/if}" class="discount_amount inputElement td_{$COLUMN_NAME}" style="width: 89%;"/>
            {/if}

        </td>
    {elseif $COLUMN_NAME eq "discount_percent"}
        <td {if $COLUMN->isActive != 'active'}style="display: none;" {/if} class="cellItem tr_{$COLUMN_NAME}" >
            {if $EDITABLE eq "0"}
                <input id="{$discount_percent}" name="{$discount_percent}" value = "{if $data.$discount_percent}{$data.$discount_percent}{/if}" type="text" class="hide discount_percent inputElement" style="width: 89%;"/>
                <span id="{$discount_percent}_{$row_no}" name="{$discount_percent}_{$row_no}" class="td_{$COLUMN_NAME}">{if $data.$discount_percent}{$data.$discount_percent}{/if}</span>
            {else}
                <input id="{$discount_percent}" name="{$discount_percent}" value = "{if $data.$discount_percent}{$data.$discount_percent}{/if}" type="text" class="discount_percent inputElement td_{$COLUMN_NAME}" style="width: 89%;"/>
            {/if}

        </td>
    {elseif $CUSTOM_COLUMN_SETTING AND in_array($COLUMN_NAME,array_keys($CUSTOM_COLUMN_SETTING))}
        <td {if $COLUMN->isActive != 'active'}style="display: none;" {/if} class="cellItem customCell tr_{$COLUMN_NAME}" >
            <div class="{$COLUMN_NAME}"  data-rowid="{$COLUMN_NAME}{$row_no}" data-lineitemtype = "{if !empty($data)}{$entityType}{else}{$BASE_ROW}{/if}">
                {if $EDITABLE eq "0"}
                    <div style="opacity: 0;padding: 0px;height: 1px;">
                        <input type="text" value="{if is_array($data[$COLUMN_NAME|cat:$row_no]) || is_object($data[$COLUMN_NAME|cat:$row_no])}{$data[$COLUMN_NAME|cat:$row_no]->fieldvalue}{else}{$data[$COLUMN_NAME|cat:$row_no]}{/if}" name="{$COLUMN_NAME|cat:$row_no}" id="{$COLUMN_NAME|cat:$row_no}" class="inputElement {$COLUMN_NAME}" />
                    </div>
                    <span class="custom-column-field-value td_{$COLUMN_NAME}">
                    {if is_array($data[$COLUMN_NAME|cat:$row_no]) || is_object($data[$COLUMN_NAME|cat:$row_no])}
                        {assign var=FILED_NAME_ROW value=$COLUMN_NAME|cat:$row_no}
                        {if is_numeric($data[$FILED_NAME_ROW]->fieldvalue) && (in_array($COLUMN->productModel->uitype, array('71','72')) || in_array($COLUMN->serviceModel->uitype, array('71','72')))}
                            {number_format($data[$FILED_NAME_ROW]->fieldvalue, $NO_OF_DECIMAL_PLACES, $CURRENCY_DECIMAL_SEPARATOR, $CURRENCY_GROUPING_SEPARATOR)}
                        {else}
                            {$data[$FILED_NAME_ROW]->fieldvalue}
                        {/if}
                    {else}
                        {if is_numeric($data[$COLUMN_NAME|cat:$row_no]) && (in_array($COLUMN->productModel->uitype, array('71','72')) || in_array($COLUMN->serviceModel->uitype, array('71','72')))}
                            {number_format($data[$COLUMN_NAME|cat:$row_no], $NO_OF_DECIMAL_PLACES, $CURRENCY_DECIMAL_SEPARATOR, $CURRENCY_GROUPING_SEPARATOR)}
                        {else}
                            {$data[$COLUMN_NAME|cat:$row_no]}
                        {/if}
                    {/if}
                    </span>
                {else}
                    {if is_array($data[$COLUMN_NAME|cat:$row_no]) || is_object($data[$COLUMN_NAME|cat:$row_no])}
                        {if !empty($data)}
                            {if array_key_exists($COLUMN_NAME|cat:$row_no, $data)}
                                {if $entityType eq 'Services' }
                                    {include file="Field.tpl"|@vtemplate_path:'Quoter' data = $data FIELD_MODEL=$data[$COLUMN_NAME|cat:$row_no] USER_MODEL=$USER_MODEL MODULE='Services'}
                                {else}
                                    {include file="Field.tpl"|@vtemplate_path:'Quoter' data = $data FIELD_MODEL=$data[$COLUMN_NAME|cat:$row_no] USER_MODEL=$USER_MODEL MODULE='Products'}
                                {/if}
                            {else}
                                {if $BASE_ROW eq 'Services'}
                                    {include file="Field.tpl"|@vtemplate_path:'Quoter' data = $data FIELD_MODEL=$CUSTOM_COLUMN_SETTING[$COLUMN_NAME]->serviceModel USER_MODEL=$USER_MODEL MODULE='Services'}
                                {else}
                                    {include file="Field.tpl"|@vtemplate_path:'Quoter' data = $data FIELD_MODEL=$CUSTOM_COLUMN_SETTING[$COLUMN_NAME]->productModel USER_MODEL=$USER_MODEL MODULE='Products'}
                                {/if}
                            {/if}
                        {else}
                            {if $BASE_ROW eq 'Services'}
                                {include file="Field.tpl"|@vtemplate_path:'Quoter' data = $data FIELD_MODEL=$CUSTOM_COLUMN_SETTING[$COLUMN_NAME]->serviceModel USER_MODEL=$USER_MODEL MODULE='Services'}
                            {else}
                                {include file="Field.tpl"|@vtemplate_path:'Quoter' data = $data FIELD_MODEL=$CUSTOM_COLUMN_SETTING[$COLUMN_NAME]->productModel USER_MODEL=$USER_MODEL MODULE='Products'}
                            {/if}
                        {/if}
                    {else}
                        {if $BASE_ROW eq 'Services' && isset($CUSTOM_COLUMN_SETTING[$COLUMN_NAME]->serviceField)}
                            {include file="Field.tpl"|@vtemplate_path:'Quoter' data = '' FIELD_MODEL=$CUSTOM_COLUMN_SETTING[$COLUMN_NAME]->serviceModel USER_MODEL=$USER_MODEL MODULE=$BASE_ROW}
                        {elseif isset($CUSTOM_COLUMN_SETTING[$COLUMN_NAME]->productField)}
                            {include file="Field.tpl"|@vtemplate_path:'Quoter' data = '' FIELD_MODEL=$CUSTOM_COLUMN_SETTING[$COLUMN_NAME]->productModel USER_MODEL=$USER_MODEL MODULE=$BASE_ROW}
                        {else}
                            <input type="text" value="{$data[$COLUMN_NAME|cat:$row_no]}" data-field-type="{$CUSTOM_COLUMN_SETTING[$COLUMN_NAME]->fieldType}" name="{$COLUMN_NAME|cat:$row_no}" id="{$COLUMN_NAME|cat:$row_no}" class="inputElement {$COLUMN_NAME} td_{$COLUMN_NAME}" style="width: 100%" />
                        {/if}
                    {/if}
                {/if}
            </div>
        </td>
    {/if}
{/foreach}
