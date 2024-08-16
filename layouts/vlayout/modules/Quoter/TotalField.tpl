{*/* * *******************************************************************************
* The content of this file is subject to the Quoter ("License");
* You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is VTExperts.com
* Portions created by VTExperts.com. are Copyright(C)VTExperts.com.
* All Rights Reserved.
* ****************************************************************************** */*}
{strip}
    <tr class="totalField">
        <td width="5%" style="vertical-align: middle; text-align: center;">
            <img src="layouts/vlayout/skins/images/drag.png" class="moveIcon" border="0" title="Drag" style="cursor: move;">
            &nbsp;
            <i class="icon-trash deleteTotalRow cursorPointer" title="{vtranslate('LBL_DELETE',$MODULE)}"></i>
        </td>
        <td class="fieldValue medium" width="30%" style="vertical-align:middle;">
            <input type="hidden" class="fieldName" value="{$FIELD_NAME}">
            <input type="text" class="fieldLabel" maxlength="40" name="custom_totalfield" data-validation-engine="validate[required]" value="{vtranslate($FIELD_VALUE.fieldLabel,'Quoter')}" {if $FIELD_VALUE.isDefault} disabled="disabled"  {/if}>
            <span class="dropdown">
                <a class="dropdown-toggle fieldInfo" data-toggle="dropdown" href="#" title="Show field name"><span class="icon-info-sign"></span></a>
                <ul class="dropdown-menu _tooltip">
                    <span style="color: #000">${$FIELD_NAME}$</span>
                </ul>
            </span>
        </td>
        <td class="fieldValue medium">
            <textarea class="fieldFormula">{$FIELD_VALUE.fieldFormula}</textarea>
        </td>
        <td class="fieldValue " width="10%" style="text-align: center;">
            <input type="checkbox" class="fieldType" {if $FIELD_VALUE.fieldType eq 1} checked="" {/if}/>
        </td>
        <td class="fieldValue " width="15%" style="text-align: center;">
            <input type="checkbox" class="isRunningSubTotal" {if $FIELD_VALUE.isRunningSubTotal eq 1} checked="" {/if}/>
        </td>
        <td class="fieldValue " width="15%" style="text-align: center;">
            {assign var = "SECTION_VALUES" value=$SECTIONS_SETTINGS.$MODULE}
            <select class="sectionInfo"  style="width: 100%" >
                <option value="0" {if $FIELD_VALUE.sectionInfo eq 0}selected{/if}>{vtranslate('LBL_SELECT_OPTION', 'Vtiger')}</option>
                {foreach item = SECTION_VALUE key=INDEX_SECTION from=$SECTION_VALUES}
                    <option value="{$SECTION_VALUE}" {if $FIELD_VALUE.sectionInfo eq $SECTION_VALUE}selected{/if}>{$SECTION_VALUE}</option>
                {/foreach}
            </select>
        </td>
    </tr>
{/strip}