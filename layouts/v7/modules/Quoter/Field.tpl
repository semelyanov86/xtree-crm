{strip}
    {if $FIELD_MODEL->getFieldDataType() == 'image'}
        {assign var=IMAGES value=$RECORD_MODEL->getImageDetails($productId)}
        {if count($IMAGES) > 0}
            <img class="product_image" src="layouts\v7\modules\Quoter\images\images_icon.png" data-productid = "{$productId}" width="32" height="32" style="cursor: pointer">
        {/if}
    {elseif $FIELD_MODEL->get('uitype') eq '71' or $FIELD_MODEL->get('uitype') eq '72'}
        {assign var="FIELD_INFO" value=$FIELD_MODEL->getFieldInfo()}
        {assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
        {assign var="FIELD_NAME" value=$FIELD_MODEL->get('name')}
        <div class="input-group">
            <span class="input-group-addon">{if $SELECTED_CURRENCY.currency_symbol}{$SELECTED_CURRENCY.currency_symbol}{else}{$USER_MODEL->get('currency_symbol')}{/if}</span>
            <input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="text" class="inputElement currencyField" name="{$FIELD_NAME}{$row_no}"
                   value="{$FIELD_MODEL->get('fieldvalue')}" {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if}
                    {if $FIELD_INFO["mandatory"] eq true} data-rule-required = "true" {/if} data-rule-currency='true'
                    {if count($FIELD_INFO['validator'])}
                        data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
                    {/if}
            />
        </div>
    {elseif $FIELD_MODEL->get('uitype') eq '9'}
        {assign var="FIELD_INFO" value=$FIELD_MODEL->getFieldInfo()}
        {assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
        {assign var="FIELD_NAME" value=$FIELD_MODEL->get('name')}
        <div class="input-group">
            <input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="text" class="form-control inputElement" name="{$FIELD_NAME}{$row_no}"
                   value="{$FIELD_MODEL->get('fieldvalue')}" {if !empty($SPECIAL_VALIDATOR)}data-validator="{Zend_Json::encode($SPECIAL_VALIDATOR)}"{/if}
                    {if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
                    {if count($FIELD_INFO['validator'])}
                        data-specific-rules="{ZEND_JSON::encode($FIELD_INFO["validator"])}"
                    {/if}
            />
            <span class="input-group-addon">%</span>
        </div>
    {elseif  $FIELD_MODEL->get('uitype') eq '10'}
        {assign var=FIELD_NAME value=$FIELD_MODEL->get('name')}
        {assign var="FIELD_INFO" value=$FIELD_MODEL->getFieldInfo()}
        {assign var=FIELD_VALUE value=$FIELD_MODEL->get('fieldvalue')}
        {assign var="REFERENCE_LIST" value=$FIELD_MODEL->referenceList}
        {assign var="REFERENCE_LIST_COUNT" value=count($REFERENCE_LIST)}
        {assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
        {assign var="AUTOFILL_VALUE" value=$FIELD_MODEL->getAutoFillValue()}
        {assign var="QUICKCREATE_RESTRICTED_MODULES" value=Vtiger_Functions::getNonQuickCreateSupportedModules()}
        <div class="referencefield-wrapper {if $FIELD_VALUE neq 0} selected {/if}">
            <input name="popupReferenceModule" type="hidden" value="{$FIELD_MODEL->get('relmodule')}" />
            {assign var="displayId" value=$FIELD_VALUE}
            <div class="input-group">
                <input name="{$FIELD_MODEL->getFieldName()}{$row_no}" type="hidden" value="{$FIELD_VALUE}" class="sourceField" data-displayvalue='{$FIELD_MODEL->get('fieldvalue')}' {if $AUTOFILL_VALUE} data-autofill={Zend_Json::encode($AUTOFILL_VALUE)} {/if}/>
                <input id="{$FIELD_NAME}_display" name="{$FIELD_MODEL->getFieldName()}_display" data-fieldname="{$FIELD_MODEL->getFieldName()}" data-fieldtype="reference" type="text"
                       class="marginLeftZero inputElement autoCompleteNew"
                       value="{$FIELD_MODEL->getEditViewDisplayValue($displayId)}"
                       placeholder="{vtranslate('LBL_TYPE_SEARCH',$MODULE)}"
                       {if $displayId neq 0}disabled="disabled"{/if}
                        {if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
                        {if count($FIELD_INFO['validator'])}
                            data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
                        {/if}
                />
                <a href="#" class="clearReferenceSelection {if $FIELD_VALUE eq 0}hide{/if}"> x </a>
                <span class="input-group-addon relatedPopup cursorPointer" title="{vtranslate('LBL_SELECT', $MODULE)}">
                    <i id="{$MODULE}_editView_fieldName_{$FIELD_NAME}_select" class="fa fa-search"></i>
                </span>
            </div>
        </div>
    {else}
        {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
    {/if}
{/strip}
