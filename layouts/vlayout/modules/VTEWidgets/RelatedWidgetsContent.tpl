
{*{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}*}
<div class="relatedContents contents-bottomscroll">
    <table  class="table table-bordered listViewEntriesTable">
        <thead>
        <tr class="listViewHeaders">
            {foreach item=HEADER_FIELD from=$RELATED_HEADERS}

                <th {if $HEADER_FIELD@last} colspan="2" {/if} nowrap>
                    {if $HEADER_FIELD->get('label')=='Full Name'}{$HEADER_FIELD->get('label')}
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
            <tr class="listViewEntries" data-id='{$RELATED_RECORD->getId()}'
                    {if $RELATED_MODULE_NAME eq 'Calendar'}
                    {assign var=DETAILVIEWPERMITTED value=isPermitted($RELATED_MODULE_MODEL->get('name'), 'DetailView', $RELATED_RECORD->getId())}
                {if $DETAILVIEWPERMITTED eq 'yes'}
                    data-recordUrl='{$RELATED_RECORD->getDetailViewUrl()}'
                {/if}
                    {else}
                data-recordUrl='{$RELATED_RECORD->getDetailViewUrl()}'
                    {/if}>
                {foreach item=HEADER_FIELD from=$RELATED_HEADERS}
                    {assign var=RELATED_HEADERNAME value=$HEADER_FIELD->get('name')}
                    <td class="{$WIDTHTYPE}" data-field-type="{$HEADER_FIELD->getFieldDataType()}" nowrap title="{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}">
                        {if $HEADER_FIELD->isNameField() eq true or $HEADER_FIELD->get('uitype') eq '4'}
                            <a href="{$RELATED_RECORD->getDetailViewUrl()}">{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}</a>
                        {elseif $RELATED_HEADERNAME eq 'access_count'}
                            {$RELATED_RECORD->getAccessCountValue($PARENT_RECORD->getId())}
                        {elseif $RELATED_HEADERNAME eq 'time_start'}
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
                                    {$RELATED_RECORD->getDisplayValue('firstname')} {$RELATED_RECORD->getDisplayValue('lastname')}
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
                                    {textlength_check($RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME))}
                                {/if}
                            {/if}
                        {/if}

                    </td>
                {/foreach}
            </tr>
        {/foreach}

    </table>
</div>


