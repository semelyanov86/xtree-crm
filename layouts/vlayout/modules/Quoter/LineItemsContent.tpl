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
    {if $COLUMN_NAME eq "item_name"}
        <td {if $COLUMN->isActive != 'active'}style="display: none;" {/if} class="cellItem" >
            <div style="min-width: 200px;">
                <input type="hidden" class="rowNumber" value="{$row_no}" />
                <input type="hidden" id="{$hdnProductId}" name="{$hdnProductId}" value="{$data.$hdnProductId}" class="selectedModuleId"/>
                <input type="hidden" id="lineItemType{$row_no}" name="lineItemType{$row_no}" value="{$entityType}" class="lineItemType"/>
                <input type="hidden" id="level{$row_no}" name="level{$row_no}" value="{if $data.$level}{$data.$level}{else}1{/if}" class="level"/>

                <input type="hidden" id="parentProductId{$row_no}" value="{if $data.$parentProductId}{$data.$parentProductId}{/if}" name="parentProductId{$row_no}" class="parentId"/>
                {if $data.$level >1}<i>{for $var=2 to $data.$level} &#8594; &nbsp; {/for}</i>{/if}


                <a><img src="{vimage_path('drag.png')}" border="0" title="{vtranslate('LBL_DRAG',$MODULE)}"/></a>
                &nbsp;
                {if $row_no eq 0}
                    <img class="lineItemPopupNew cursorPointer " data-popup="ServicesPopup" data-parent-id="{if $data.$parentProductId}{$data.$parentProductId}{/if}" title="{vtranslate('Services',$MODULE)}" data-module-name="Services" data-field-name="serviceid" src="{vimage_path('Services.png')}" width="16px"/>
                    <img class="lineItemPopupNew cursorPointer " data-popup="ProductsPopup" data-parent-id="{if $data.$parentProductId}{$data.$parentProductId}{/if}" title="{vtranslate('Products',$MODULE)}" data-module-name="Products" data-field-name="productid" src="{vimage_path('Products.png')}" width="16px"/>

                {else}
                    {if !$RECORD_ID}
                        {if ($entityType eq 'Services') and (!$data.$productDeleted) or $PRODUCT_ACTIVE neq 'true'}
                            <img class="lineItemPopupNew cursorPointer " data-popup="ServicesPopup" data-module-name="Services" data-parent-id="{if $data.$parentProductId}{$data.$parentProductId}{/if}" title="{vtranslate('Services',$MODULE)}" data-field-name="serviceid" src="{vimage_path('Services.png')}" width="16px"/>
                        {elseif (!$data.$productDeleted)}
                            <img class="lineItemPopupNew cursorPointer " data-popup="ProductsPopup" data-module-name="Products" data-parent-id="{if $data.$parentProductId}{$data.$parentProductId}{/if}" title="{vtranslate('Products',$MODULE)}" data-field-name="productid" src="{vimage_path('Products.png')}" width="16px"/>
                        {/if}
                    {else}
                        {if ($entityType eq 'Services') and (!$data.$productDeleted)}
                            <img class="{if $SERVICE_ACTIVE}lineItemPopupNew{/if} cursorPointer " data-popup="ServicesPopup" data-module-name="Services" data-parent-id="{if $data.$parentProductId}{$data.$parentProductId}{/if}" title="{vtranslate('Services',$MODULE)}" data-field-name="serviceid" src="{vimage_path('Services.png')}" width="16px"/>
                        {elseif (!$data.$productDeleted)}
                            <img class="{if $PRODUCT_ACTIVE}lineItemPopupNew{/if} cursorPointer " data-popup="ProductsPopup" data-module-name="Products" data-parent-id="{if $data.$parentProductId}{$data.$parentProductId}{/if}" title="{vtranslate('Products',$MODULE)}" data-field-name="productid" src="{vimage_path('Products.png')}" width="16px"/>

                        {/if}
                    {/if}
                {/if}

                <i class="icon-trash deleteRow cursorPointer" title="{vtranslate('LBL_DELETE',$MODULE)}"></i>

                <i class="icon-remove-sign clearLineItemNew cursorPointer" title="Clear" ></i>
                &nbsp;&nbsp;
                <input  type="text" id="{$productName}" name="{$productName}" value="{$data.$productName}" class="productName {if $row_no neq 0} autoComplete {/if}" placeholder="{vtranslate('LBL_TYPE_SEARCH',$MODULE)}" data-validation-engine="validate[required]" {if !empty($data.$productName)} disabled="disabled" {/if}  style="vertical-align: top;"/>
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
        <td {if $COLUMN->isActive != 'active'}style="display: none;" {/if} class="cellItem"  >
            <input id="{$quantity}" name="{$quantity}" type="text" class="qty" style="width: 89%;" data-validation-engine="validate[required,funcCall[Vtiger_GreaterThanZero_Validator_Js.invokeValidation]]" value="{if !empty($data.$quantity)}{$data.$quantity}{else}1{/if}"/>
        </td>
    {elseif $COLUMN_NAME eq "listprice" }
        <td {if $COLUMN->isActive != 'active'}style="display: none;" {/if} class="cellItem"  >
            <div>
                <input id="{$listprice}" name="{$listprice}" value="{if !empty($data.$listprice)}{$data.$listprice}{elseif !empty($data.$otherListPrice)}{$data.$otherListPrice}{else}0{/if}" type="text" data-validation-engine="validate[required,funcCall[Vtiger_PositiveNumber_Validator_Js.invokeValidation]]" class="listPrice " list-info='{if !empty($data.$listprice)}{Zend_Json::encode($listPriceValues)}{/if}' style="width: 89%;"/>
                {assign var=PRICEBOOK_MODULE_MODEL value=Vtiger_Module_Model::getInstance('PriceBooks')}
                {if $PRICEBOOK_MODULE_MODEL->isPermitted('DetailView')}
                    <img src="layouts/vlayout/skins/images/PriceBooks.png" class="cursorPointer alignMiddle priceBookPopupNew" data-popup="Popup" data-module-name="PriceBooks" title="Price Books">
                {/if}
            </div>
        </td>
    {elseif $COLUMN_NAME eq "tax_total"}
        <td {if $COLUMN->isActive != 'active'}style="display: none;" {/if} class="cellItem tax_column {if $IS_GROUP_TAX_TYPE}hide{/if}"  >
            <div class="input-append">
                <input id="{$tax_total}" name="{$tax_total}" type="text" class="tax_total" readonly value="{if !empty($data.$tax_total)}{$data.$tax_total}{else}0{/if}"/><span class="add-on">%</span>
            </div>
            <div class="individualTaxContainer">
                (+)&nbsp;<b><a href="javascript:void(0)" class="individualTax">{vtranslate('LBL_TAX_DETAIL','Quoter')} </a> : </b>
            </div>
            <span class="taxDivContainer">
                <div class="taxUI hide" id="tax_div{$row_no}">
                    <!-- we will form the table with all taxes -->
                    <table width="100%" border="0" cellpadding="5" cellspacing="0" class="table table-nobordered popupTable" id="tax_table{$row_no}">
                        <tr>
                            <th id="tax_div_title{$row_no}" nowrap align="left" ><b>{vtranslate('LBL_TAX_TOTAL','Quoter')} :&nbsp;<span class="lbl_tax_total">{$data.$tax_total}&nbsp;%</span></b></th>
                            <th>
                                <button type="button" class="close closeDiv">x</button>
                            </th>
                        </tr>

                        {foreach key=tax_row_no item=tax_data from=$data.taxes}
                            {assign var="taxname" value=$tax_data.taxname|cat:"_percentage"|cat:$row_no}
                            {assign var="taxlabel" value=$tax_data.taxlabel|cat:"_percentage"|cat:$row_no}
                            <tr>
                                <td>
                                    <input type="text" data-validation-engine="validate[funcCall[Vtiger_PositiveNumber_Validator_Js.invokeValidation]]" name="{$taxname}" id="{$taxname}" value="{$tax_data.percentage}" class="smallInputBox taxPercentage" />&nbsp;%
                                </td>
                                <td><div class="textOverflowEllipsis">{$tax_data.taxlabel}</div></td>
                            </tr>
                        {/foreach}
                    </table>
                    <div class="modal-footer lineItemPopupModalFooter modal-footer-padding">
                        <div class=" pull-right cancelLinkContainer">
                            <a class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                        </div>
                        <button class="btn btn-success taxSave" type="button" name="lineItemActionSave"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
                    </div>
                </div>
            </span>
        </td>
    {elseif $COLUMN_NAME eq "total"  }
        <td {if $COLUMN->isActive != 'active'}style="display: none;" {/if} class="cellItem"  >
            <input type="hidden" name = "total{$row_no}" value="{if $data.$total}{$data.$total}{else}0{/if}" style="width: 89%;"/>
            <div id="total{$row_no}" align="right" class="total">{if $data.$total_format}{$data.$total_format}{else}0{/if}</div>
        </td>
    {elseif $COLUMN_NAME eq "tax_totalamount"}
        <td {if $COLUMN->isActive != 'active'}style="display: none;" {/if} class="cellItem tax_totalamount_column {if $IS_GROUP_TAX_TYPE}hide{/if}">
            <input type="hidden" name = "tax_totalamount{$row_no}" value="{if $IS_INDIVIDUAL_TAX_TYPE}{if $data.$tax_totalamount}{$data.$tax_totalamount}{else}0{/if}{/if}" style="width: 89%;"/>
            <div id="tax_totalamount{$row_no}" align="right" class="tax_totalamount">{if $data.$tax_totalamount}{$data.$tax_totalamount}{else}0{/if}</div>
        </td>
    {elseif $COLUMN_NAME eq "net_price"}
        <td {if $COLUMN->isActive != 'active'}style="display: none;" {/if} class="cellItem" >
            <input type="hidden" name = "net_price{$row_no}" value="{if $data.$net_price}{$data.$net_price}{else}0{/if}" style="width: 89%;"/>
            <span id="net_price{$row_no}" class="pull-right net_price">{if $data.$net_price_format}{$data.$net_price_format}{else}0{/if}</span>
        </td>
    {elseif $COLUMN_NAME eq "comment" }
        <td {if $COLUMN->isActive != 'active'}style="display: none;" {/if} class="cellItem"  >
        <textarea id="{$comment}" name="{$comment}" class="lineItemCommentBox" style="resize: vertical;">{$data.$comment}</textarea>
        </td>
    {elseif $COLUMN_NAME eq "discount_amount"}
        <td {if $COLUMN->isActive != 'active'}style="display: none;" {/if} class="cellItem"  >
            <input id="{$discount_amount}" name="{$discount_amount}" type="text" value = "{if $data.$discount_amount}{$data.$discount_amount}{/if}" class="discount_amount" style="width: 89%;"/>
        </td>
    {elseif $COLUMN_NAME eq "discount_percent"}
        <td {if $COLUMN->isActive != 'active'}style="display: none;" {/if} class="cellItem" >
            <input id="{$discount_percent}" name="{$discount_percent}" value = "{if $data.$discount_percent}{$data.$discount_percent}{/if}" type="text" class="discount_percent" style="width: 89%;"/>
        </td>
    {elseif $CUSTOM_COLUMN_SETTING AND in_array($COLUMN_NAME,array_keys($CUSTOM_COLUMN_SETTING))}
        <td {if $COLUMN->isActive != 'active'}style="display: none;" {/if} class="cellItem customCell" >
            <div class="{$COLUMN_NAME}"  data-rowid="{$COLUMN_NAME}{$row_no}" data-lineitemtype = "{$entityType}">
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
            </div>
        </td>
    {/if}
{/foreach}