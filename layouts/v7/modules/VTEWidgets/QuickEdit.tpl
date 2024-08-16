{*/* ********************************************************************************
* The content of this file is subject to the VTEWidgets ("License");
* You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is VTExperts.com
* Portions created by VTExperts.com. are Copyright(C) VTExperts.com.
* All Rights Reserved.
* ****************************************************************************** */*}
{strip}
    {if $MODULE eq "Events"}
        <script type="text/javascript" src="layouts/v7/modules/Calendar/resources/Edit.js"></script>
    {/if}
    {foreach key=index item=jsModel from=$SCRIPTS}
        <script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
    {/foreach}

    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" aria-label="Close" data-dismiss="modal"><span aria-hidden="true" class="fa fa-close"></span></button>
                <h4>{vtranslate('Quick Edit')} {vtranslate($SINGLE_MODULE, $MODULE)}</h4>
            </div>
            <form class="form-horizontal recordEditView" name="VTEWidgets" method="post" action="index.php">
                <div class="modal-body">
                    {if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
                        <input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
                    {/if}
                    <input type="hidden" name="module" value="{$MODULE}">
                    <input type="hidden" name="moduleEditName" value="{$MODULE}">
                    <input type="hidden" name="action" value="saveQuickEdit">
                    <input type="hidden" name="record" value="{$RECORD_ID}" />
                    <div class="quickCreateContent">
                        <table class="massEditTable table no-border">
                            <tr>
                                {assign var=COUNTER value=0}
                                {foreach key=FIELD_NAME item=FIELD_MODEL from=$RECORD_STRUCTURE name=blockfields}
                                {assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
                                {assign var="refrenceList" value=$FIELD_MODEL->getReferenceList()}
                                {assign var="refrenceListCount" value=count($refrenceList)}
                                {if $FIELD_MODEL->get('uitype') eq "19"}
                                {if $COUNTER eq '1'}
                                <td></td><td></td></tr><tr>
                                {assign var=COUNTER value=0}
                                {/if}
                                {/if}
                                {if $COUNTER eq 2}
                            </tr><tr>
                                {assign var=COUNTER value=1}
                                {else}
                                {assign var=COUNTER value=$COUNTER+1}
                                {/if}
                                <td class='fieldLabel'>
                                    {if $isReferenceField neq "reference"}<label class="muted pull-right">{/if}
                                        {if $FIELD_MODEL->isMandatory() eq true && $isReferenceField neq "reference"} <span class="redColor">*</span> {/if}
                                        {if $isReferenceField eq "reference"}
                                            {if $refrenceListCount > 1}
                                                {assign var="DISPLAYID" value=$FIELD_MODEL->get('fieldvalue')}
                                                {assign var="REFERENCED_MODULE_STRUCT" value=$FIELD_MODEL->getUITypeModel()->getReferenceModule($DISPLAYID)}
                                                {if !empty($REFERENCED_MODULE_STRUCT)}
                                                    {assign var="REFERENCED_MODULE_NAME" value=$REFERENCED_MODULE_STRUCT->get('name')}
                                                {/if}
                                                <span class="pull-right">
                                    {if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}
                                                    <select style="width: 150px;" class="chzn-select referenceModulesList" id="referenceModulesList">
                                                        <optgroup>
                                                            {foreach key=index item=value from=$refrenceList}
                                                                <option value="{$value}" {if $value eq $REFERENCED_MODULE_NAME} selected {/if} >{vtranslate($value, $value)}</option>
                                                            {/foreach}
                                                        </optgroup>
                                                    </select>
                                </span>
                                            {else}
                                                <label class="muted pull-right">{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}{vtranslate($FIELD_MODEL->get('label'), $MODULE)}</label>
                                            {/if}
                                        {else}
                                            {vtranslate($FIELD_MODEL->get('label'), $MODULE)}
                                        {/if}
                                        {if $isReferenceField neq "reference"}</label>{/if}
                                </td>
                                <td class="fieldValue" {if $FIELD_MODEL->get('uitype') eq '19'} colspan="3" {assign var=COUNTER value=$COUNTER+1} {/if}>
                                    {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
                                </td>
                                {/foreach}
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    {assign var="EDIT_VIEW_URL" value=$MODULE_MODEL->getCreateRecordUrl()}
                    <button class="btn btn-default" id="goToFullForm" data-edit-view-url="{$EDIT_VIEW_URL}" type="button"><strong>{vtranslate('LBL_GO_TO_FULL_FORM', $MODULE)}</strong></button>
                    <button class="btn btn-success" name="VTEWidgetsSaveButton" type="button"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
                    <a class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                </div>
            </form>
        </div>

    </div>
{/strip}