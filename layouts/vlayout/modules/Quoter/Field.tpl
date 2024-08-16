{strip}
    {if $FIELD_MODEL->getFieldDataType() == 'image'}
        {assign var=IMAGES value=$RECORD_MODEL->getImageDetails($productId)}
        {if count($IMAGES) > 0}
            <img class="product_image" src="layouts\vlayout\modules\Quoter\images\images_icon.png" data-productid = "{$productId}" width="32" height="32" style="cursor: pointer">
        {/if}
    {elseif $FIELD_MODEL->get('uitype') eq '71' or $FIELD_MODEL->get('uitype') eq '72'}
        {assign var="FIELD_INFO" value=Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($FIELD_MODEL->getFieldInfo()))}
        {assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
        {assign var="FIELD_NAME" value=$FIELD_MODEL->get('name')}
        <div class="input-prepend">
            <span class="add-on">{if $SELECTED_CURRENCY.currency_symbol}{$SELECTED_CURRENCY.currency_symbol}{else}{$SELECTED_CURRENCY.currencysymbol}{/if}</span>
            <input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="text" class="input-medium currencyField" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" name="{$FIELD_MODEL->getFieldName()}"
                   data-fieldinfo='{$FIELD_INFO}' value="{$FIELD_MODEL->get('fieldvalue')}" {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if}
                   data-decimal-seperator='{$USER_MODEL->get('currency_decimal_separator')}' data-group-seperator='{$USER_MODEL->get('currency_grouping_separator')}' data-number-of-decimal-places='{$USER_MODEL->get('no_of_currency_decimals')}'/>
        </div>
    {elseif $FIELD_MODEL->get('uitype') eq '10'}
        {assign var=FIELD_NAME value=$FIELD_MODEL->get('name')}
        {assign var="REFERENCE_LIST" value=$FIELD_MODEL->referenceList}
        {assign var="REFERENCE_LIST_COUNT" value=count($REFERENCE_LIST)}
        {assign var="FIELD_INFO" value=Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($FIELD_MODEL->getFieldInfo()))}
        {assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
        <input name="popupReferenceModule" type="hidden" value="{$FIELD_MODEL->get('relmodule')}" />
        <input name="{$FIELD_MODEL->getFieldName()}" type="hidden" value="{$FIELD_MODEL->get('fieldvalue')}" class="sourceField" data-displayvalue='{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'))}' data-fieldinfo='{$FIELD_INFO}' />
        {assign var="displayId" value=$FIELD_MODEL->get('fieldvalue')}
        <div class="row-fluid input-prepend input-append">
            <span class="add-on clearReferenceSelection cursorPointer">
                <i id="{$MODULE}_editView_fieldName_{$FIELD_NAME}_clear" class='icon-remove-sign' title="{vtranslate('LBL_CLEAR', $MODULE)}"></i>
            </span>
            <input id="{$FIELD_NAME}_display" name="{$FIELD_MODEL->getFieldName()}_display" type="text" class="{if (($smarty.request.view eq 'Edit') or ($smarty.request.module eq 'Webforms'))} span7 {else} span8 {/if}	marginLeftZero autoCompleteNew" {if !empty($displayId)}readonly="true"{/if}
                   value="{$FIELD_MODEL->get('displayName')}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
                   data-fieldinfo='{$FIELD_INFO}' placeholder="{vtranslate('LBL_TYPE_SEARCH',$MODULE)}"
                    {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if}/>
            <span class="add-on relatedPopup cursorPointer">
                <i id="{$MODULE}_editView_fieldName_{$FIELD_NAME}_select" class="icon-search relatedPopup" title="{vtranslate('LBL_SELECT', $MODULE)}" ></i>
            </span>
            {assign var=QUICKCREATE_RESTRICTED_MODULES value=['SalesOrder','Quotes','Invoice','PurchaseOrder']}
                        <!-- Show the add button only if it is edit view  -->
            {if (($smarty.request.view eq 'Edit') or ($MODULE_NAME eq 'Webforms')) && !in_array($REFERENCE_LIST[0],$QUICKCREATE_RESTRICTED_MODULES)}
                <span class="add-on cursorPointer createReferenceRecord">
                <i id="{$MODULE}_editView_fieldName_{$FIELD_NAME}_create" class='icon-plus' title="{vtranslate('LBL_CREATE', $MODULE)}"></i>
                </span>
            {/if}
        </div>
    {else}
        {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
    {/if}
{/strip}