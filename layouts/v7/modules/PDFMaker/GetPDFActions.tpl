{*
/*********************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
*}
{if $ENABLE_PDFMAKER eq 'true'}
    {* Export to PDF*}
    <li>
        <a href="javascript:;" class="PDFMakerDownloadPDF PDFMakerTemplateAction"><i title="{vtranslate('LBL_EXPORT','PDFMaker')}" class="fa fa-download"></i>&nbsp;{vtranslate('LBL_EXPORT','PDFMaker')}</a>
    </li>
    {* Print PDF *}
    <li>
        <a href="javascript:;" class="PDFModalPreview PDFMakerTemplateAction"><i title="{vtranslate('LBL_EXPORT','PDFMaker')}" class="fa fa-file-pdf-o"></i>&nbsp;{vtranslate('LBL_PREVIEW','PDFMaker')}</a>
    </li>
    {* Send Email with PDF *}
    {if $SEND_EMAIL_PDF_ACTION eq "1"}
        <li>
            <a href="javascript:;" class="sendEmailWithPDF PDFMakerTemplateAction" data-sendtype="{$SEND_EMAIL_PDF_ACTION_TYPE}"><i class="fa fa-send" aria-hidden="true"></i>&nbsp;{vtranslate('LBL_SEND_EMAIL')}</a>
        </li>
    {/if}
    {* Edit and Export to PDF *}
    {if $EDIT_AND_EXPORT_ACTION eq "1"}
        <li>
            <a href="javascript:;" class="editPDF PDFMakerTemplateAction"><i class="fa fa-edit" aria-hidden="true"></i>&nbsp;{vtranslate('LBL_EDIT')} {vtranslate('LBL_AND')} {vtranslate('LBL_EXPORT','PDFMaker')}</a>
        </li>
    {/if}
    {* Save PDF into documents *}
    {if $SAVE_AS_DOC_ACTION eq "1"}
        <li>
            <a href="javascript:;" class="savePDFToDoc PDFMakerTemplateAction"><i class="fa fa-save" aria-hidden="true"></i>&nbsp;{vtranslate('LBL_SAVEASDOC','PDFMaker')}</a>
        </li>
    {/if}
    {* Export to RTF *}
    {if $EXPORT_TO_RTF_ACTION eq "1"}
        <li>
            <a href="javascript:;" class="PDFMakerTemplateAction">{vtranslate('LBL_EXPORT_TO_RTF', 'PDFMaker')}</a>
        </li>
    {/if}
    <li class="dropdown-header">
        <span class="fa fa-wrench" aria-hidden="true" title="{vtranslate('LBL_SETTINGS', 'PDFMaker')}"></span> {vtranslate('LBL_SETTINGS', 'PDFMaker')}
    </li>
    {* PDF product images*}
    {if $MODULE eq 'Invoice' || $MODULE eq 'SalesOrder' || $MODULE eq 'PurchaseOrder' || $MODULE eq 'Quotes' || $MODULE eq 'Receiptcards' || $MODULE eq 'Issuecards'}
        <li>
            <a href="javascript:;" class="showPDFBreakline">{vtranslate('LBL_PRODUCT_BREAKLINE','PDFMaker')}</a>
        </li>
    {/if}
    {if $MODULE eq 'Invoice' || $MODULE eq 'SalesOrder' || $MODULE eq 'PurchaseOrder' || $MODULE eq 'Quotes' || $MODULE eq 'Receiptcards' || $MODULE eq 'Issuecards' || $MODULE eq 'Products'}
    <li>
        <a href="javascript:;" class="showProductImages">{vtranslate('LBL_PRODUCT_IMAGE', 'PDFMaker')}</a>
    </li>
    {/if}

    {if $TEMPLATE_LANGUAGES|@sizeof > 1}
        <li class="dropdown-header">
            <i class="fa fa-language" title="{vtranslate('LBL_PDF_LANGUAGE', 'PDFMaker')}"></i> {vtranslate('LBL_PDF_LANGUAGE', 'PDFMaker')}
        </li>
        <li>
            <select name="template_language" id="template_language" class="col-lg-12">
                {html_options  options=$TEMPLATE_LANGUAGES selected=$CURRENT_LANGUAGE}
            </select>
        </li>
    {else}
        {foreach from="$TEMPLATE_LANGUAGES" item="lang" key="lang_key"}
            <input type="hidden" name="template_language" id="template_language" value="{$lang_key}"/>
        {/foreach}
    {/if}
{else}
    <div class="row-fluid">
        <div class="span10">
            <ul class="nav nav-list">
                <li><a href="index.php?module=PDFMaker&view=List">{vtranslate('LBL_PLEASE_FINISH_INSTALLATION', 'PDFMaker')}</a></li>
            </ul>
        </div>
    </div>
{/if}