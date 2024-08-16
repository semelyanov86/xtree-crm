{assign var=HEADER_FIELD value=$HEADER_FIELD->set('fieldvalue', $RELATED_RECORD->get($RELATED_HEADERNAME))}
{if $HEADER_FIELD->isEditable() eq 'true' && ($HEADER_FIELD->getFieldDataType()!=Vtiger_Field_Model::REFERENCE_TYPE) && $RELATED_HEADERNAME!='fullname'}
    <div class="hide edit">
        <div style="width: 100%;float: left;">
            {if $RELATED_RECORD->get('isEvent') eq 1}
                {include file=vtemplate_path($HEADER_FIELD->getUITypeModel()->getTemplateName(),'Events') FIELD_MODEL=$HEADER_FIELD USER_MODEL=$USER_MODEL MODULE=$RELMODULE_NAME RECORD_STRUCTURE_MODEL = $RECORD_STRUCTURE_MODEL}
            {else}
                {include file=vtemplate_path($HEADER_FIELD->getUITypeModel()->getTemplateName(),$RELATED_MODULE_NAME) FIELD_MODEL=$HEADER_FIELD USER_MODEL=$USER_MODEL MODULE=$RELATED_MODULE_NAME RECORD_STRUCTURE_MODEL = $RECORD_STRUCTURE_MODEL}
            {/if}
        </div>
        <div style="width: 100%;float: left;">
            <a href="javascript:void(0);" data-field-name="{$HEADER_FIELD->getFieldName()}{if $HEADER_FIELD->get('uitype') eq '33'}[]{/if}" data-record-id="{$RELATED_RECORD->getId()}" data-rel-module="{$RELATED_MODULE_NAME}" class="hoverEditSave">{vtranslate('LBL_SAVE')}</a> |
            <a href="javascript:void(0);" class="hoverEditCancel">{vtranslate('LBL_CANCEL')}</a>
        </div>
    </div>
{/if}