<style>
tr.listViewEntries1 td.custom-document  {
    display: table;
    margin-top: 8px;
    border-top: unset;
}
tr.listViewEntries1 td.custom-document a {
    display: table-cell;
    padding-right: 5px;
}
.relatedContents{
    display:block;width:100%
}
.customwidgetContainer_ h4{
    width: inherit !important;
}
@media screen and (max-width: 1300px) {
    .widget_header .span2{
        margin-left: 5px !important;
    }
    .widget_header .select2-container .select2-choice > .select2-chosen{
        margin-right: 10px !important;
    }
}
.btn-event{
    float: right !important;
}
</style>
{*{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}*}
<div class="relatedContents contents-bottomscroll" style="border: none;">
    <table  class="table table-strip listViewEntriesTable">
        <thead>
        <tr class="listViewHeaders">
            <th nowrap></th>
            {foreach item=HEADER_FIELD from=$RELATED_HEADERS}
                <th {if $HEADER_FIELD@last} colspan="2" {/if} nowrap>
                    {if $HEADER_FIELD->get('label')=='Full Name'}
                        {$HEADER_FIELD->get('label')}
                    {else}
                        {vtranslate($HEADER_FIELD->get('label'), $RELATED_MODULE_MODEL->get('name'))}
                    {/if}
                </th>
            {/foreach}

            {if $SHOW_CREATOR_DETAIL}
                <th>{vtranslate('LBL_RELATION_CREATED_TIME', $RELATED_MODULE_MODEL->get('name'))}</th>
                <th>{vtranslate('LBL_RELATION_CREATED_USER', $RELATED_MODULE_MODEL->get('name'))}</th>
            {/if}
        </tr>
        </thead>

        {foreach item=RELATED_RECORD from=$RELATED_RECORDS}
            <tr class="listViewEntries1" data-id='{$RELATED_RECORD->getId()}'
                    {if $RELATED_MODULE_NAME eq 'Calendar'}
                        {assign var=DETAILVIEWPERMITTED value=isPermitted($RELATED_MODULE_MODEL->get('name'), 'DetailView', $RELATED_RECORD->getId())}
                        {if $DETAILVIEWPERMITTED eq 'yes'}
                            data-recordUrl='{$RELATED_RECORD->getDetailViewUrl()}'
                        {/if}
                    {else}
                            data-recordUrl='{$RELATED_RECORD->getDetailViewUrl()}'
                    {/if}>
                <td nowrap style="padding: 8px 1px 0px 1px; {if $RELATED_MODULE_NAME eq 'Documents'}width:15%{/if}" {if $RELATED_MODULE_NAME eq 'Documents'} class="custom-document"{/if}>
                    {if $RELATED_MODULE_NAME eq 'Documents'}
                    <a name="viewfile" style="margin-right: 8px" href="javascript:void(0)" data-filelocationtype="I" data-filename="{$RELATED_RECORD->getDisplayName()}" onclick="Vtiger_Header_Js.previewFile(event,{$RELATED_RECORD->getId()})"><i title="View File" class="fa fa-picture-o alignMiddle"></i></a>
                     <a name="downloadfile" href="{$RELATED_RECORD->getDownloadFileURL()}" onclick="event.stopImmediatePropagation();"><i title="{vtranslate('LBL_DOWNLOAD_FILE', $RELATED_MODULE_NAME)}" class="fa fa-download alignMiddle"></i></a>
                    {/if}
                    <a data-id='{$RELATED_RECORD->getId()}' data-module='{$RELATED_RECORD->getModuleName()}' class="related-detail-view" href='{$RELATED_RECORD->getDetailViewUrl()}'><i class="fa fa-eye"></i></a>
                    {if $RELATED_MODULE_MODEL->get('name') != 'Quotes' && $RELATED_MODULE_MODEL->get('name') != 'Invoice' && $RELATED_MODULE_MODEL->get('name') != 'SalesOrder' && $RELATED_MODULE_MODEL->get('name') != 'PurchaseOrder'}
                        <a data-id='{$RELATED_RECORD->getId()}' data-module='{$RELATED_RECORD->getModuleName()}' data-activitytype='{$RELATED_RECORD->get('activitytype')}'  class="related-quick-edit" href="javascript:void(0)"><i class="fa fa-pencil"></i></a>
                    {/if}

                    {if $RELATED_MODULE_NAME eq 'Calendar'}
                            <a data-id='{$RELATED_RECORD->getId()}' data-module='{$RELATED_RECORD->getModuleName()}' data-activitytype='{$RELATED_RECORD->get('activitytype')}'  class="related-check-markAsHeld" title="Mark as held"><i class="fa fa-check"></i></a>
                    {/if}

                </td>
                {foreach item=HEADER_FIELD from=$RELATED_HEADERS}
                    {assign var=RELATED_HEADERNAME value=$HEADER_FIELD->get('name')}

                    <td class="{$WIDTHTYPE} fieldValue" data-relmodule-name="{$RELATED_MODULE_MODEL->get('name')}" data-field-name="{$HEADER_FIELD->get('name')}" data-field-type="{$HEADER_FIELD->getFieldDataType()}" nowrap title="{strip_tags($RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME))}">
                        <span  {if $HEADER_FIELD->getFieldDataType() == 'picklist'}
                            {assign var=PICKLIST_COLOR value=Settings_Picklist_Module_Model::getPicklistColorByValue($HEADER_FIELD->get('name'), $RELATED_RECORD->get($RELATED_HEADERNAME))}
                            {if !empty($PICKLIST_COLOR)} class="value picklist-color" style="background-color: {$PICKLIST_COLOR}; line-height: 15px;color: {Settings_Picklist_Module_Model::getTextColor($PICKLIST_COLOR)};" {else}class="value"{/if}
                        {else}class="value"{/if} data-field-type="{$HEADER_FIELD->getFieldDataType()}" {if $HEADER_FIELD->get('uitype') eq '19' or $HEADER_FIELD->get('uitype') eq '20' or $HEADER_FIELD->get('uitype') eq '21'} style="white-space:normal;" {/if}>
                        {if $HEADER_FIELD->isNameField() eq true or $HEADER_FIELD->get('uitype') eq '4'}
                            <a href="{$RELATED_RECORD->getDetailViewUrl()}">{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}</a>
                        {elseif $RELATED_HEADERNAME eq 'access_count'}
                            {$RELATED_RECORD->getAccessCountValue($PARENT_RECORD->getId())}
                        {elseif $RELATED_HEADERNAME eq 'time_start'}
                            {assign var=DATE_TIME_VALUE value=$RELATED_RECORD->getDisplayValue('date_start')}
                            {assign var=DATE_TIME_COMPONENTS value=explode(' ' ,$DATE_TIME_VALUE)}
                            {textlength_check($DATE_TIME_COMPONENTS[1])}
                        {elseif $RELATED_HEADERNAME eq 'time_end'}
                            {assign var=DATE_TIME_VALUE value=$RELATED_RECORD->getDisplayValue('due_date')}
                            {assign var=DATE_TIME_COMPONENTS value=explode(' ' ,$DATE_TIME_VALUE)}
                            {textlength_check($DATE_TIME_COMPONENTS[1])}
                        {elseif $RELATED_HEADERNAME eq 'listprice' || $RELATED_HEADERNAME eq 'unit_price'}
                            {CurrencyField::convertToUserFormat($RELATED_RECORD->get($RELATED_HEADERNAME), null, true)}
                            {if $RELATED_HEADERNAME eq 'listprice'}
                                {assign var="LISTPRICE" value=CurrencyField::convertToUserFormat($RELATED_RECORD->get($RELATED_HEADERNAME), null, true)}
                            {/if}
                        {elseif $RELATED_HEADERNAME eq 'filename'}
                            {$RELATED_RECORD->get($RELATED_HEADERNAME)}
                        {else}
                            {if $RELATED_HEADERNAME=='fullname' }
                                {if $RELATED_MODULE_MODEL->get('name') =='Contacts' || $RELATED_MODULE_MODEL->get('name')=='Leads' }
                                    <a href="{$RELATED_RECORD->getDetailViewUrl()}">{$RELATED_RECORD->getDisplayValue('firstname')} {$RELATED_RECORD->getDisplayValue('lastname')}</a>
                                {else}
                                    {$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}
                                {/if}
                            {else}
                                {if $HEADER_FIELD->getFieldDataType() eq 'phone'}
                                    {assign var=MODULE value='PBXManager'}
                                    {assign var=MODULEMODEL value=Vtiger_Module_Model::getInstance($MODULE)}
                                    {assign var=FIELD_VALUE value=$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}
                                    {if $MODULEMODEL and $MODULEMODEL->isActive() and $FIELD_VALUE}
                                        {assign var=PERMISSION value=PBXManager_Server_Model::checkPermissionForOutgoingCall()}
                                        {if $PERMISSION}
                                            {assign var=PHONE_FIELD_VALUE value=$FIELD_VALUE}
                                            {assign var=PHONE_NUMBER value=$PHONE_FIELD_VALUE|regex_replace:"/[-()\s]/":""}
                                            <a class="phoneField" data-value="{$PHONE_NUMBER}" record="{$RELATED_RECORD->getId()}" onclick="Vtiger_PBXManager_Js.registerPBXOutboundCall('{$PHONE_NUMBER}',{$RELATED_RECORD->getId()})">{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}</a>
                                        {else}
                                            {$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}
                                        {/if}
                                    {else}
                                        {$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}
                                    {/if}
                                {else}
                                    {if $RELATED_MODULE_MODEL->get('name') == 'Emails'}
                                        {if $RELATED_HEADERNAME == 'from_email'}
                                            {$RELATED_RECORD->getSenderName($RELATED_MODULE_MODEL->get('name'), $RELATED_RECORD->getId())}
                                        {elseif $RELATED_HEADERNAME =='saved_toid'}
                                            {$QUALIFIED_MODEL->getSenderName('to_email',$RELATED_RECORD->getId())}
                                        {elseif $RELATED_HEADERNAME =='ccmail'}
                                            {$QUALIFIED_MODEL->getSenderName('cc_email',$RELATED_RECORD->getId())}
                                        {elseif $RELATED_HEADERNAME =='bccmail'}
                                            {$QUALIFIED_MODEL->getSenderName('bcc_email',$RELATED_RECORD->getId())}
                                        {else}
                                            {textlength_check($RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME))}
                                        {/if}
                                    {else}
                                        {if $RELATED_MODULE_NAME=='Calendar'}
                                            {assign var=FVAL value=$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}
                                            {if $FVAL!=''}
                                                {textlength_check($FVAL)}
                                            {else}
                                                {textlength_check($RELATED_RECORD->get($RELATED_HEADERNAME))}
                                            {/if}
                                        {else}
                                            {textlength_check($RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME))}
                                        {/if}
                                    {/if}
                                {/if}
                            {/if}
                        {/if}
                        </span>

                    </td>
                {/foreach}
            </tr>
        {/foreach}

    </table>
</div>


