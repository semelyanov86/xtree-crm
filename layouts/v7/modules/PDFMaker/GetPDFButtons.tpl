{*/*********************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/*}
{strip}
    {if $ENABLE_PDFMAKER eq 'true' && $CRM_TEMPLATES_EXIST eq '0'}
        <div class="pull-right" id="PDFMakerContentDiv" style="padding-left: 5px;">
            <div class="clearfix">
                <div class="btn-group pull-right">
                    <button class="btn btn-default selectPDFTemplates"><i title="{vtranslate('LBL_EXPORT_TO_PDF','PDFMaker')}" class="fa fa-file-pdf-o" aria-hidden="true"></i>&nbsp;{vtranslate('LBL_EXPORT_TO_PDF','PDFMaker')}</button>
                    <button type="button" class="btn btn-default dropdown-toggle dropdown-toggle-split PDFMoreAction" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        {vtranslate('LBL_MORE','PDFMaker')}&nbsp;&nbsp;<span class="caret"></span></button>
                    </button>
                    <ul class="dropdown-menu">
                        <li class="dropdown-header">
                            <select class="form-control" name="use_common_template" id="use_common_template" multiple>
                                {foreach from=$CRM_TEMPLATES item=TEMPLATE_ITEM key=TEMPLATE_KEY}
                                    <option data-export_edit_disabled="{$TEMPLATE_ITEM['disable_export_edit']}"
                                            value="{$TEMPLATE_KEY}"
                                            {if $TEMPLATE_ITEM['title'] neq ''} title="{$TEMPLATE_ITEM['title']}" {/if}
                                            {if $TEMPLATE_ITEM['is_default'] eq '1' || $TEMPLATE_ITEM['is_default'] eq '3'} selected="selected" {/if}>{$TEMPLATE_ITEM['templatename']}</option>
                                {/foreach}
                            </select>
                        </li>
                        {include file="GetPDFActions.tpl"|vtemplate_path:'PDFMaker'}
                    </ul>
                </div>
            </div>
        </div>
    {/if}
{/strip}