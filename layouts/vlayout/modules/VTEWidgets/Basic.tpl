
<div class="summaryWidgetContainer">
{assign var=RELATED_MODULE_NAME value=Vtiger_Functions::getModuleName($WIDGET['data']['relatedmodule'])}
	<div class="widgetContainer_{$key}" data-url="{$WIDGET['url']}" data-name="{$WIDGET['label']}">
		 <div class="widget_header row-fluid">
			<input type="hidden" name="relatedModule" value="{$RELATED_MODULE_NAME}" />
			<span class="span10 margin0px">
				<div class="row-fluid">
					<span class="span8 margin0px"><h4>{vtranslate($WIDGET['label'],$MODULE_NAME)}</h4></span>

					{if isset($WIDGET['data']['filter']) && $WIDGET['data']['filter'] neq '-'}
						{assign var=filter value=$WIDGET['data']['filter']}
						<input type="hidden" name="filter_data" value="{$filter}" />
						<span class="span7">
							<div class="row-fluid">
                                {assign var=RELATED_MODULE_NAME value=Vtiger_Functions::getModuleName($WIDGET['data']['relatedmodule'])}
								{assign var=RELATED_MODULE_MODEL value=Vtiger_Module_Model::getInstance($RELATED_MODULE_NAME)}
								{assign var=FIELD_MODEL value=$RELATED_MODULE_MODEL->getField($WIDGET['field_name'])}
								{assign var=FIELD_INFO value=Zend_Json::encode($FIELD_MODEL->getFieldInfo())}
								{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
								{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
                                <select class="chzn-select span12 filterField" name="{$FIELD_MODEL->get('name')}"
                                        data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO|escape}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if} data-fieldlable='{vtranslate($FIELD_MODEL->get('label'),$RELATED_MODULE_NAME)}'>
                                  <option>{vtranslate($FIELD_MODEL->get('label'),$RELATED_MODULE_NAME)}</option>
                                    {foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
                                        <option value="{$PICKLIST_NAME}" {if $FIELD_MODEL->get('fieldvalue') eq $PICKLIST_NAME} selected {/if}>{$PICKLIST_VALUE}</option>
                                    {/foreach}
								</select>
							</div>
						</span>
					{/if}
				</div>
			</span>
			 {if $WIDGET['data']['action'] eq 1}
				{assign var=VRM value=Vtiger_Record_Model::getInstanceById($RECORD->getId(), $MODULE_NAME)}
				{assign var=VRMM value=Vtiger_RelationListView_Model::getInstance($VRM, $RELATED_MODULE_NAME)}
				{assign var=RELATIONMODEL value=$VRMM->getRelationModel()}
				{assign var=RELATION_FIELD value=$RELATIONMODEL->getRelationField()}
				<span class="span2">
					<span class="pull-right">
						<button class="btn addButton pull-right vteWidgetCreateButton" type="button" href="javascript:void(0)"
                               data-url="{$WIDGET['actionURL']}" data-name="{$RELATED_MODULE_NAME}"
						{if $RELATION_FIELD} data-prf="{$RELATION_FIELD->getName()}" {/if}>
							<strong>{vtranslate('LBL_ADD',$MODULE_NAME)}</strong>
						</button>
					</span>
				</span>
			{/if}
		</div>
		<div class="widget_contents">
		</div>
	</div>
</div>