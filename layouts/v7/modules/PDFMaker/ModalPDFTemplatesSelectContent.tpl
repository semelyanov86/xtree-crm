{*
/*********************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
*}
{strip}
<div class="PDFMakerContainer modal-dialog modelContainer">
    <div class="modal-content" style="width:675px;">
        {assign var=HEADER_TITLE value={vtranslate('LBL_PDF_ACTIONS', $MODULE)}}
        {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
        <div class="modal-body">
            <div class="container-fluid">
                <div>
                   <form class="form-horizontal contentsBackground" id="exportSelectDFMakerForm" method="post" action="index.php{if $ATTR_PATH neq ""}?{$ATTR_PATH}{/if}" novalidate="novalidate">
                        <input type="hidden" name="module" value="PDFMaker" />
                        <input type="hidden" name="source_module" value="{$SOURCE_MODULE}" />
                        <input type="hidden" name="relmodule" value="{$SOURCE_MODULE}" />
                        <input type="hidden" name="action" value="CreatePDFFromTemplate" />
                        <input type="hidden" name="idslist" value="{$idslist}">
                        <input type="hidden" name="commontemplateid" value="">
                        <input type="hidden" name="language" value="">
                        {foreach from=$ATTRIBUTES key=ATTR_NAME item=ATTR_VAL}
                           <input type="hidden" name="{$ATTR_NAME}" value="{$ATTR_VAL}"/>
                        {/foreach}
                        <div class="modal-body tabbable">
                            <div class="row">
                                <h5>{vtranslate('LBL_PDF_TEMPLATE', $MODULE)}</h5>
                            </div>
                            <div class="row">
                                <select class="form-control" data-rule-required="true" name="use_common_template" id="use_common_template" multiple>
                                    {foreach from=$CRM_TEMPLATES item=templateInfo key=templateid}
                                        <option data-export_edit_disabled="{$templateInfo.disable_export_edit}" value="{$templateid}" {if $templateInfo.title neq ""}title="{$templateInfo.title}"{/if} {if $templateInfo.is_default eq '1' || $templateInfo.is_default eq '3'}selected="selected"{/if}>{$templateInfo.templatename}</option>
                                    {/foreach}
                                </select>
                            </div>
                            {if $TEMPLATE_LANGUAGES|@sizeof > 1}
                                <br>
                                <div class="row">
                                    <h5>{vtranslate('LBL_PDF_LANGUAGE', $MODULE)}</h5>
                                </div>
                                <div class="row">
                                    <select name="template_language" id="template_language" class="col-lg-12">
                                        {html_options  options=$TEMPLATE_LANGUAGES selected=$CURRENT_LANGUAGE}
                                    </select>
                                </div>
                            {else}
                                {foreach from=$TEMPLATE_LANGUAGES item="lang" key="lang_key"}
                                    <input type="hidden" name="template_language" id="template_language" value="{$lang_key}"/>
                                {/foreach}
                            {/if}
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <center>
                <button class="btn btn-success PDFMakerDownloadPDF" type="button" name="generateButton"><strong>{vtranslate('LBL_DOWNLOAD_FILE', $MODULE)}</strong></button>
                {if $PDF_DOWNLOAD_ZIP eq "1"}
                    <button class="btn btn-success PDFMakerDownloadZIP" type="button" name="PDFMakerDownloadZIP"><strong>{vtranslate('LBL_DOWNLOAD_ZIP', $MODULE)}</strong></button>
                {/if}
                {if $PDF_PREVIEW_ACTION eq "1"}
                    <button class="btn btn-success PDFModalPreview" type="button" name="PDFModalPreview"><strong>{vtranslate('LBL_PREVIEW')}</strong></button>
                {/if}
                {if $SEND_EMAIL_PDF_ACTION eq "1"}
                    <button class="btn btn-success sendEmailWithPDF" data-sendtype="{$SEND_EMAIL_PDF_ACTION_TYPE}" type="button" name="sendEmailWithPDF"><strong>{vtranslate('LBL_SEND_EMAIL')}</strong></button>
                {/if}
                {if $EDIT_AND_EXPORT_ACTION eq "1"}
                    <button class="btn btn-success editPDF" type="button" name="editPDF"><strong>{vtranslate('LBL_EDIT')}</strong></button>
                {/if}
                {if $SAVE_AS_DOC_ACTION eq "1"}
                    <button class="btn btn-success savePDFToDoc" type="button" name="savePDFToDoc"><strong>{vtranslate('LBL_SAVEASDOC', $MODULE)}</strong></button>
                {/if}
                <a href="#" class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
            </center>
        </div>
    </div>
</div>
{/strip}