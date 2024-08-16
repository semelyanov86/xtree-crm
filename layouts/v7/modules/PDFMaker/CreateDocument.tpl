{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}

{strip}
<div class="modal-dialog modelContainer">
	<div class="modal-content" style="width:675px;">
	{assign var=HEADER_TITLE value={vtranslate('LBL_SAVEASDOC', {$MODULE_NAME})}}
	{assign var=MODULE value="Documents"}
	{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
	<div class="modal-body">
		<div class="uploadview-content container-fluid">
			<div id="create">
				<form class="form-horizontal recordEditView" name="upload" method="post" action="index.php">
					<input type="hidden" name="module" value="{$MODULE_NAME}" />
					<input type="hidden" name="action" value="SaveIntoDocuments" />
					<input type="hidden" name="pmodule" value="{$PMODULE}" />
					<input type="hidden" name="pid" value="{$PID}" />
                    {foreach item=ATTR_VALUE key=ATTR_NAME from=$FORM_ATTRIBUTES}
						<input type="hidden" name="{$ATTR_NAME}" value="{$ATTR_VALUE}" />
					{/foreach}
					{foreach item=TEMPLATE_VALUE key=TEMPLATE_KEY from=$TEMPLATE_ATTRIBUTES}
						<textarea name="{$TEMPLATE_KEY}" class="hide">{$TEMPLATE_VALUE}</textarea>
					{/foreach}
					<table class="massEditTable table no-border">
						<tr>
							{assign var="FIELD_MODEL" value=$FIELD_MODELS['notes_title']}
							<td class="fieldLabel col-lg-2">
								<label class="muted pull-right">
									{vtranslate($FIELD_MODEL->get('label'), $MODULE)}&nbsp;
									{if $FIELD_MODEL->isMandatory() eq true}
										<span class="redColor">*</span>
									{/if}
								</label>
							</td>
							<td class="fieldValue col-lg-4" colspan="3">
								{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
							</td>
						</tr>
						<tr>
							{assign var="FIELD_MODEL" value=$FIELD_MODELS['assigned_user_id']}
							<td class="fieldLabel col-lg-2">
								<label class="muted pull-right">
									{vtranslate($FIELD_MODEL->get('label'), $MODULE)}&nbsp;
									{if $FIELD_MODEL->isMandatory() eq true}
										<span class="redColor">*</span>
									{/if}
								</label>
							</td>
							<td class="fieldValue col-lg-4">
								{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
							</td>
							{assign var="FIELD_MODEL" value=$FIELD_MODELS['folderid']}
							{if $FIELD_MODELS['folderid']}
								<td class="fieldLabel col-lg-2">
									<label class="muted pull-right">
										{vtranslate($FIELD_MODEL->get('label'), $MODULE)}&nbsp;
										{if $FIELD_MODEL->isMandatory() eq true}
											<span class="redColor">*</span>
										{/if}
									</label>
								</td>
								<td class="fieldValue col-lg-4">
									{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
								</td>
							{/if}
						</tr>
						<input type="hidden" name='filelocationtype' value="I" />
                        {assign var="FIELD_MODEL" value=$FIELD_MODELS['notecontent']}
                        {if $FIELD_MODELS['notecontent']}
							</tr>
								<td class="fieldLabel col-lg-2">
									<label class="muted pull-right">
										{vtranslate($FIELD_MODEL->get('label'), $MODULE)}&nbsp;
										{if $FIELD_MODEL->isMandatory() eq true}
											<span class="redColor">*</span>
										{/if}
									</label>
								</td>
								<td class="fieldValue col-lg-4" colspan="3">
									{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
								</td>
							</tr>
                        {/if}
						<tr>
							{assign var=HARDCODED_FIELDS value=','|explode:"filename,assigned_user_id,folderid,notecontent,notes_title,filelocationtype"}
							{assign var=COUNTER value=0}
							{foreach key=FIELD_NAME item=FIELD_MODEL from=$FIELD_MODELS}
								{if !in_array($FIELD_NAME,$HARDCODED_FIELDS) && ($FIELD_MODEL->isMandatory() || $FIELD_MODEL->isQuickCreateEnabled())}
									{assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
									{assign var="referenceList" value=$FIELD_MODEL->getReferenceList()}
									{assign var="referenceListCount" value=count($referenceList)}
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
									<td class='fieldLabel col-lg-2'>
										{if $isReferenceField neq "reference"}<label class="muted pull-right">{/if}
											{if $isReferenceField eq "reference"}
												{if $referenceListCount > 1}
													{assign var="DISPLAYID" value=$FIELD_MODEL->get('fieldvalue')}
													{assign var="REFERENCED_MODULE_STRUCT" value=$FIELD_MODEL->getUITypeModel()->getReferenceModule($DISPLAYID)}
													{if !empty($REFERENCED_MODULE_STRUCT)}
														{assign var="REFERENCED_MODULE_NAME" value=$REFERENCED_MODULE_STRUCT->get('name')}
													{/if}
													<span class="pull-right">
														<select style="width:150px;" class="select2 referenceModulesList {if $FIELD_MODEL->isMandatory() eq true}reference-mandatory{/if}">
															{foreach key=index item=value from=$referenceList}
																<option value="{$value}" {if $value eq $REFERENCED_MODULE_NAME} selected {/if} >{vtranslate($value, $value)}</option>
															{/foreach}
														</select>
													</span>
												{else}
													<label class="muted pull-right">{vtranslate($FIELD_MODEL->get('label'), $MODULE)}&nbsp;{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}</label>
												{/if}
											{else if $FIELD_MODEL->get('uitype') eq '83'}
												{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE) COUNTER=$COUNTER MODULE=$MODULE}
												{if $TAXCLASS_DETAILS}
													{assign 'taxCount' count($TAXCLASS_DETAILS)%2}
													{if $taxCount eq 0}
														{if $COUNTER eq 2}
															{assign var=COUNTER value=1}
														{else}
															{assign var=COUNTER value=2}
														{/if}
													{/if}
												{/if}
											{else}
												{vtranslate($FIELD_MODEL->get('label'), $MODULE)}&nbsp;{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}
											{/if}
											{if $isReferenceField neq "reference"}</label>{/if}
									</td>
									{if $FIELD_MODEL->get('uitype') neq '83'}
										<td class="fieldValue col-lg-4" {if $FIELD_MODEL->get('uitype') eq '19'} colspan="3" {assign var=COUNTER value=$COUNTER+1} {/if}>
											{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
										</td>
									{/if}
								{/if}
							{/foreach}
						</tr>
					</table>
				</form>
			</div>
		</div>
	</div>
	{assign var=BUTTON_NAME value={vtranslate('LBL_CREATE', $MODULE)}}
	{assign var=BUTTON_ID value="js-create-document"}
	{include file="ModalFooter.tpl"|vtemplate_path:$MODULE}
	</div>
</div>
{/strip}