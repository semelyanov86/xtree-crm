{*<!--
/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */
-->*}
{strip}
    <script type="text/javascript" charset="utf-8">
        var moduleName = '{$entityName}';
        var taskStatus = '{$TASK_OBJECT->status}';
        var taskPriority = '{$TASK_OBJECT->priority}';
    </script>
    <div class="row">
        <div class="col-sm-9 col-xs-9">
            <div class="row form-group">
                <div class="col-sm-2 col-xs-2">{vtranslate('LBL_DOC_TITLE','PDFMaker')}<span class="redColor">*</span></div>
                <div class="col-sm-8 col-xs-8">
                    <input type="text" name="title" value="{$TASK_OBJECT->title}" id="task_title" data-rule-required="true" class="inputElement">
                </div>
            </div>

            <div class="row form-group">
                <div class="col-sm-2 col-xs-2">{vtranslate('LBL_DOC_DESC','PDFMaker')}</div>
                <div class="col-sm-8 col-xs-8">
                    <textarea name="description" style="resize: vertical;" class="inputElement">{$TASK_OBJECT->description}</textarea>
                </div>
            </div>

            <div class="row form-group">
                <div class="col-sm-2 col-xs-2">{vtranslate('LBL_FLD_NAME','PDFMaker')}</div>
                <div class="col-sm-8 col-xs-8">
                    <select id="task_folder" name="folder" class="select2 inputElement">
                        {html_options  options=$TASK_OBJECT->getFolders() selected=$TASK_OBJECT->folder}
                    </select>
                    <input type="hidden" id="task_folder_value" value="{$TASK_OBJECT->folder}">
                </div>
            </div>

            <div class="row form-group">
                <div class="col-sm-2 col-xs-2">{vtranslate('LBL_PDF_TEMPLATE','PDFMaker')}</div>
                <div class="col-sm-8 col-xs-8">
                    <select id="task_template" name="template" class="select2 inputElement">
                        {html_options  options=$TASK_OBJECT->getTemplates($SOURCE_MODULE) selected=$TASK_OBJECT->template}
                    </select>
                    <input type="hidden" id="task_template_value" value="{$TASK_OBJECT->template}">
                </div>
            </div>

            <div class="row form-group">
                <div class="col-sm-2 col-xs-2">{vtranslate('LBL_PDF_LANGUAGE','PDFMaker')}</div>
                <div class="col-sm-8 col-xs-8">
                    {assign var=LANGUAGES_ARRAY value=$TASK_OBJECT->getLanguages()}
                    <select id="task_template_language" name="template_language" class="select2 inputElement">
                        {html_options  options=$LANGUAGES_ARRAY selected=$TASK_OBJECT->template_language}
                    </select>
                    <input type="hidden" id="template_language_value" value="{$TASK_OBJECT->template_language}">
                </div>
            </div>

            <div class="row form-group">
                <div class="col-sm-2 col-xs-2">{vtranslate('LBL_ASSIGNED_TO_FIELD','PDFMaker')}</div>
                <div class="col-sm-8 col-xs-8">
                    <select name="assigned_to_field" id="assigned_to_field" class="select2 inputElement">
                        <option value="">{vtranslate('LBL_SELECT_OPTION','PDFMaker')}</option>
                        {assign var=ASSIGNED_TO_FIELDS value=$TASK_OBJECT->getAssignedToFields()}
                        {foreach from=$ASSIGNED_TO_FIELDS item=FIELD_NAMES key=GROUP_NAME}
                            <optgroup label="{vtranslate($GROUP_NAME, $GROUP_NAME)}">
                                {foreach from=$FIELD_NAMES key=FIELD_ID item=FIELD_NAME}
                                    <option value="{$FIELD_ID}" {if $FIELD_ID eq $TASK_OBJECT->assigned_to_field}selected{/if}>{vtranslate($FIELD_NAME, $GROUP_NAME)}</option>
                                {/foreach}
                            </optgroup>
                        {/foreach}
                    </select>
                    <input type="hidden" id="assigned_to_field_value" value="{$TASK_OBJECT->assigned_to_field}">
                </div>
            </div>
        </div>
    </div>
{/strip}