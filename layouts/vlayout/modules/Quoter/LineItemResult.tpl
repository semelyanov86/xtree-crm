{*/* * *******************************************************************************
* The content of this file is subject to the Quoter ("License");
* You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is VTExperts.com
* Portions created by VTExperts.com. are Copyright(C)VTExperts.com.
* All Rights Reserved.
* ****************************************************************************** */*}

{strip}
    {if $MODE eq 'Edit'}
        {assign var="IS_INDIVIDUAL_TAX_TYPE" value=false}
        {assign var="IS_GROUP_TAX_TYPE" value=true}

        {if $FINAL.taxtype eq 'individual'}
            {assign var="IS_GROUP_TAX_TYPE" value=false}
            {assign var="IS_INDIVIDUAL_TAX_TYPE" value=true}
        {/if}
        {if !empty($TOTAL_SETTINGS)}
            <tbody>
                {foreach item = VALUE key = ROW_NAME from=$TOTAL_SETTINGS}
                    {if $ROW_NAME =='tax'}
                        <!-- Group Tax - starts -->
                        <tr id="group_tax_row" valign="top" class="{if $IS_INDIVIDUAL_TAX_TYPE}hide{/if}">
                            <td>
                                <span class="pull-right">(+)&nbsp;<b><a href="javascript:void(0)" id="finalTax">{vtranslate('LBL_TAX','Quoter')}</a></b></span>
                                <!-- Pop Div For Group TAX -->
                                <div class="hide finalTaxUI validCheck" id="group_tax_div">
                                    <table width="100%" border="0" cellpadding="5" cellspacing="0" class="table table-nobordered popupTable">
                                        <tr>
                                            <th id="group_tax_div_title" nowrap align="left" >{vtranslate('LBL_GROUP_TAX',$MODULE)}</th>
                                            <th align="right">
                                                <button type="button" class="close closeDiv">x</button>
                                            </th>
                                        </tr>
                                        {foreach item=tax_detail name=group_tax_loop key=loop_count from=$TAXES}
                                            <tr>
                                                <td align="left" class="lineOnTop">
                                                    <input type="text" size="5" data-validation-engine="validate[funcCall[Vtiger_PositiveNumber_Validator_Js.invokeValidation]]" name="{$tax_detail.taxname}_group_percentage" id="group_tax_percentage{$smarty.foreach.group_tax_loop.iteration}" value="{$tax_detail.percentage}" class="smallInputBox groupTaxPercentage" />&nbsp;%
                                                </td>
                                                <td align="center" class="lineOnTop"><div class="textOverflowEllipsis">{$tax_detail.taxlabel}</div></td>
                                            </tr>
                                        {/foreach}
                                        <input type="hidden" id="group_tax_count" value="{$smarty.foreach.group_tax_loop.iteration}" />
                                    </table>
                                    <div class="modal-footer lineItemPopupModalFooter modal-footer-padding">
                                        <div class=" pull-right cancelLinkContainer">
                                            <a class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                                        </div>
                                        <button class="btn btn-success" type="button" name="lineItemActionSave"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
                                    </div>
                                </div>
                                <!-- End Popup Div Group Tax -->
                            </td>
                            <td>
                                <div data-fieldType = "{$VALUE.fieldType}" class="pull-right {$ROW_NAME}" align="right">
                                    <input id="{$ROW_NAME}" name="{$ROW_NAME}" type="text" readonly  value="{if $IS_INDIVIDUAL_TAX_TYPE}0{else}{$TOTAL_VALUE.$ROW_NAME}{/if}" style="text-align: right; max-width: 50%">
                                </div>
                            </td>
                        </tr>
                        <!-- Group Tax - ends -->

                    {elseif $ROW_NAME =='s_h_percent'}
                        <tr>
                            <td width="83%">
                                <span class="pull-right">(+)&nbsp;<b><a href="javascript:void(0)" id="shippingHandlingTax">{vtranslate('LBL_TAXES_FOR_SHIPPING_AND_HANDLING','Quoter')} </a></b></span>

                                <!-- Pop Div For Shipping and Handling TAX -->
                                <div class="hide validCheck" id="shipping_handling_div">
                                    <table class="table table-nobordered popupTable">
                                        <thead>
                                        <tr>
                                            <th id="sh_tax_div_title"  nowrap align="left" >{vtranslate('LBL_TAXES_FOR_SHIPPING_AND_HANDLING','Quoter')}: <span id="shAmountForTax" ></th>
                                            <th align="right">
                                                <button type="button" class="close closeDiv">x</button>
                                            </th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        {foreach item=tax_detail name=sh_loop key=loop_count from=$SHIPPING_TAXES}
                                            <tr>
                                                <td><div class="textOverflowEllipsis">{vtranslate($tax_detail.taxlabel,$MODULE)}</div></td>
                                                <td>
                                                    <input type="text" data-validation-engine="validate[funcCall[Vtiger_PositiveNumber_Validator_Js.invokeValidation]]" name="{$tax_detail.taxname}_sh_percent" id="sh_tax_percentage{$smarty.foreach.sh_loop.iteration}" value="{$tax_detail.percentage}" class="smallInputBox shippingTaxPercentage" />&nbsp;%
                                                </td>
                                            </tr>
                                        {/foreach}
                                        <input type="hidden" id="sh_tax_count" value="{$smarty.foreach.sh_loop.iteration}" />
                                        </tbody>
                                    </table>
                                    <div class="modal-footer lineItemPopupModalFooter modal-footer-padding">
                                        <div class=" pull-right cancelLinkContainer">
                                            <a class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                                        </div>
                                        <button class="btn btn-success finalTaxSave" type="button" name="lineItemActionSave"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
                                    </div>
                                </div>
                                <!-- End Popup Div for Shipping and Handling TAX -->
                            </td>
                            <td>
                                <div data-fieldType = "{$VALUE.fieldType}" class="pull-right {$ROW_NAME}" align="right">
                                    <input id="{$ROW_NAME}" name="{$ROW_NAME}" type="text" readonly  value="{$TOTAL_VALUE.$ROW_NAME}" style="text-align: right; max-width: 50%">
                                </div>
                            </td>
                        </tr>
                    {else}
                        <tr>
                            <td style="width: 85% !important">
                                <div class="pull-right"><strong>{vtranslate($VALUE['fieldLabel'], 'Quoter')}</strong></div> 

                            </td>
                            <td>
                                {if $VALUE.fieldType == 1}
                                    <div data-fieldType = "{$VALUE.fieldType}" class="pull-right {$ROW_NAME}" align="right">
                                        <input id="{$ROW_NAME}" name="{$ROW_NAME}" type="text"  value="{$TOTAL_VALUE.$ROW_NAME}" style="text-align: right; max-width: 50%">
                                    </div>
                                {else}
                                    <div  class="pull-right {$ROW_NAME}">
                                        <span>{$RECORD_MODEL->numberFormat($TOTAL_VALUE.$ROW_NAME)}</span>
                                        <input type="hidden" id="{$ROW_NAME}" name= "{$ROW_NAME}" value="{$TOTAL_VALUE.$ROW_NAME}"/>
                                    </div>
                                {/if}

                            </td>
                        </tr>
                    {/if}
                {/foreach}
            </tbody>
        {/if}
    {else}
        {if !empty($TOTAL_SETTINGS)}
            <tbody>
            {foreach item = VALUE key = ROW_NAME from=$TOTAL_SETTINGS}
                <tr>
                    <td style="width: 85% !important">
                        <div class="pull-right"><strong>{vtranslate($VALUE['fieldLabel'], 'Quoter')}</strong></div>

                    </td>
                    <td>
                        <div  class="pull-right {$ROW_NAME}">
                            <span>{$RECORD_MODEL->numberFormat($TOTAL_VALUE.$ROW_NAME)}</span>
                        </div>
                    </td>
                </tr>
            {/foreach}
            </tbody>
        {/if}
    {/if}
{/strip}