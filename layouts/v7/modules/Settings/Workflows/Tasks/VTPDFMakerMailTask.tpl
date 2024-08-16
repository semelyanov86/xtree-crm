{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}
{strip}
    <div id="VtEmailTaskContainer" style="padding-bottom: 100px;">
        <hr>
        <div class="row">
            <div class="col-sm-9">
                <div class="row form-group">
                    <div class="col-sm-2">{vtranslate('SMTP', $QUALIFIED_MODULE)}</div>
                    <div class="col-sm-6">
                        <select name="smtp" id="smtp" class="task-fields select2 inputElement">
                            <option>{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}</option>
                            <option value="assigned_user_smtp" {if 'assigned_user_smtp' eq $TASK_OBJECT->smtp}selected="selected"{/if}>{vtranslate('LBL_ASSIGNED_USER_SMTP', 'PDFMaker')}</option>
                            {foreach from=$TASK_OBJECT->getSMTPServers() key=SMTP_SERVER_ID item=SMTP_SERVER}
                                <option value="{$SMTP_SERVER_ID}" {if $SMTP_SERVER_ID eq $TASK_OBJECT->smtp}selected="selected"{/if}>{$SMTP_SERVER->get('server')} &lt;{$SMTP_SERVER->get('server_username')}&gt;</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-sm-2">{vtranslate('LBL_FROM', $QUALIFIED_MODULE)}</div>
                    <div class="col-sm-6">
                        <input name="fromEmail" class=" fields inputElement" type="text" value="{$TASK_OBJECT->fromEmail}" />
                    </div>
                    <div class="col-sm-2">
                        <select id="fromEmailOption" style="width: 100%;" class="select2" data-placeholder="{vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}">
                            <option></option>
                            {$FROM_EMAIL_FIELD_OPTION}
                        </select>
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-sm-2">{vtranslate('Reply To',$QUALIFIED_MODULE)}</div>
                    <div class="col-sm-6">
                        <input name="replyTo" class="fields inputElement" type="text" value="{$TASK_OBJECT->replyTo}"/>
                    </div>
                    <span class="col-sm-2">
						<select style="width: 100%" class="task-fields select2 overwriteSelection" data-placeholder="{vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}">
							<option></option>
                            {$EMAIL_FIELD_OPTION}
						</select>
					</span>
                </div>
                <div class="row form-group">
                    <span class="col-sm-2">{vtranslate('LBL_TO',$QUALIFIED_MODULE)}<span class="redColor">*</span></span>
                    <div class="col-sm-6">
                        <input data-rule-required="true" name="recepient" class="fields inputElement" type="text" value="{$TASK_OBJECT->recepient}" />
                    </div>
                    <div class="col-sm-2">
                        <select style="min-width: 100%;" class="task-fields select2" data-placeholder="{vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}">
                            <option></option>
                            {$EMAIL_FIELD_OPTION}
                        </select>
                    </div>
                </div>
                <div class="row form-group {if empty($TASK_OBJECT->emailcc)}hide {/if}" id="ccContainer">
                    <div class="col-sm-2">{vtranslate('LBL_CC',$QUALIFIED_MODULE)}</div>
                    <div class="col-sm-6">
                        <input class="fields inputElement" type="text" name="emailcc" value="{$TASK_OBJECT->emailcc}" />
                    </div>
                    <span class="col-sm-2">
						<select class="task-fields select2" data-placeholder='{vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}' style="width: 100%;">
							<option></option>
                            {$EMAIL_FIELD_OPTION}
						</select>
					</span>
                </div>
                <div class="row form-group {if empty($TASK_OBJECT->emailbcc)}hide {/if}" id="bccContainer">
                    <div class="col-sm-2">{vtranslate('LBL_BCC',$QUALIFIED_MODULE)}</div>
                    <div class="col-sm-6">
                        <input class="fields inputElement" type="text" name="emailbcc" value="{$TASK_OBJECT->emailbcc}" />
                    </div>
                    <div class="col-sm-2">
                        <select class="task-fields select2" data-placeholder='{vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}' style="width: 100%;">
                            <option></option>
                            {$EMAIL_FIELD_OPTION}
                        </select>
                    </div>
                </div>
                <div class="row form-group {if (!empty($TASK_OBJECT->emailcc)) and (!empty($TASK_OBJECT->emailbcc))} hide {/if}">
                    <div class="col-sm-2">&nbsp;</div>
                    <div class="col-sm-6">
                        <a class="cursorPointer btn btn-default {if (!empty($TASK_OBJECT->emailcc))}hide{/if}" id="ccLink" style="margin-right: 6px;">{vtranslate('LBL_ADD_CC',$QUALIFIED_MODULE)}</a>
                        <a class="cursorPointer btn btn-default {if (!empty($TASK_OBJECT->emailbcc))}hide{/if}" id="bccLink">{vtranslate('LBL_ADD_BCC',$QUALIFIED_MODULE)}</a>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="row form-group">
                    <div class="col-sm-12">{vtranslate('LBL_PDF_TEMPLATE','PDFMaker')}</div>
                </div>
                <div class="row form-group">
                    <div class="col-sm-12">
                        <input type="hidden" id="template" name="template" value={Zend_Json::encode($TASK_OBJECT->template)}>
                        <select multiple id="template_select" data-rule-required="true" name="template_select" class="select2 task-fields" style="width: 100%;">
                            {html_options  options=$TASK_OBJECT->getTemplates($SOURCE_MODULE) selected=$TASK_OBJECT->template}
                        </select>
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-sm-12">{vtranslate('LBL_PDF_LANGUAGE','PDFMaker')}</div>
                </div>
                <div class="row form-group">
                    <div class="col-sm-12">
                        {assign var=LANGUAGES_ARRAY value=$TASK_OBJECT->getLanguages()}
                        <select id="template_language" name="template_language" class="select2 task-fields" style="width: 100%;">
                            {html_options  options=$LANGUAGES_ARRAY selected=$TASK_OBJECT->template_language}
                        </select>
                        <input type="hidden" id="template_language_value" value="{$TASK_OBJECT->template_language}">
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-sm-12">{vtranslate('LBL_MERGE_TEMPLATES','PDFMaker')}</div>
                </div>
                <div class="row form-group">
                    <div class="col-sm-12">
                        <input type="checkbox" id="template_merge" value="Yes" name="template_merge" {if 'Yes' eq $TASK_OBJECT->template_merge}checked{/if} class="task-fields">
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-sm-9">
                <div class="row form-group">
                    <div class="col-sm-2">{vtranslate('LBL_SUBJECT',$QUALIFIED_MODULE)}<span class="redColor">*</span></div>
                    <div class="col-sm-6">
                        <input data-rule-required="true" class="fields inputElement" type="text" name="subject" value="{$TASK_OBJECT->subject}" id="subject" spellcheck="true"/>
                    </div>
                    <div class="col-sm-2">
                        <select style="width: 100%;" class="task-fields select2" data-placeholder="{vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}">
                            <option value="">{vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}</option>
                            {$ALL_FIELD_OPTIONS}
                        </select>
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-sm-2">{vtranslate('LBL_ADD_FIELD',$QUALIFIED_MODULE)}</div>
                    <div class="col-sm-2">
                        <select style="width: 100%" id="task-fieldnames" class="select2" data-placeholder="{vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}">
                            <option value="">{vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}</option>
                            {$ALL_FIELD_OPTIONS}
                        </select>
                    </div>
                    <div class="col-sm-2">{vtranslate('LBL_GENERAL_FIELDS',$QUALIFIED_MODULE)}</div>
                    <div class="col-sm-2">
                        <select style="width: 100%" id="task_timefields" class="select2" data-placeholder="{vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}">
                            <option value="">{vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}</option>
                            {foreach from=$META_VARIABLES item=META_VARIABLE_KEY key=META_VARIABLE_VALUE}
                                <option value="{if strpos(strtolower($META_VARIABLE_VALUE), 'url') === false}${/if}{$META_VARIABLE_KEY}">{vtranslate($META_VARIABLE_VALUE,$QUALIFIED_MODULE)}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                <div class="row form-group">
                    {assign var=EMAILMAKER_TEMPLATES value=$TASK_OBJECT->getEmailTemplates($SOURCE_MODULE)}
                    {if !empty($EMAILMAKER_TEMPLATES)}
                        <div class="col-sm-2">{vtranslate('LBL_EMAILMAKER_TEMPLATES', 'PDFMaker')}</div>
                        <div class="col-sm-2">
                            <select id="task-emailtemplates" class="select2" data-placeholder="{vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}" style="width: 100%">
                                <option value="">{vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}</option>
                                {foreach from=$EMAILMAKER_TEMPLATES item=EMAIL_TEMPLATE}
                                    <option value="{$EMAIL_TEMPLATE['body']}">{vtranslate($EMAIL_TEMPLATE['name'],$QUALIFIED_MODULE)}</option>
                                {/foreach}
                            </select>
                        </div>
                    {/if}
                </div>
            </div>
        </div>
        <div class="row form-group">
            <div class="col-sm-12">
                <textarea id="content" name="content" style="height: 20vh;">{$TASK_OBJECT->content}</textarea>
            </div>
        </div>
        <hr>
        <div class="row form-group">
            <div class="col-sm-9">
                <div class="row">
                    <div class="col-sm-2">{vtranslate('LBL_EXECUTE_AFTER_SAVE','PDFMaker')}</div>
                    <div class="col-sm-10">
                        <input type="hidden" name="executeImmediately" value="">
                        <input type="checkbox" name="executeImmediately" value="1" {if $TASK_OBJECT->executeImmediately}checked="checked"{/if}>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="modules/PDFMaker/workflow/VTPDFMakerMailTask.js" type="text/javascript" charset="utf-8"></script>
{/strip}