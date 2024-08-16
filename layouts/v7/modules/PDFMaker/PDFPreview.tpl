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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="filePreview container-fluid">
                <div class="modal-header row">
                    <div class="filename col-lg-8">
                        <h4 class="textOverflowEllipsis maxWidth50" title="{vtranslate('LBL_PREVIEW',$MODULE)}"><b>{vtranslate('LBL_PREVIEW',$MODULE)}</b></h4>
                    </div>
                    <div class="col-lg-1 pull-right">
                        <button type="button" class="close" aria-label="Close" data-dismiss="modal">
                                <span aria-hidden="true" class='fa fa-close'></span>
                        </button>
                    </div>
                </div>
                <div class="modal-body row" style="height:550px;">
                    <input type="hidden" name="commontemplateid" value='{$COMMONTEMPLATEIDS}' />
                    <iframe id='PDFMakerPreviewContent' src="{$FILE_PATH}" data-desc="{$FILE_PATH}" height="100%" width="100%"></iframe>
                </div>
            </div>
            <div class="modal-footer">
                <div class='clearfix modal-footer-overwrite-style'>
                    <div class="row clearfix ">
                            <div class=' textAlignCenter col-lg-12 col-md-12 col-sm-12 '>
                                    <button type='button' class='btn btn-success downloadButton' data-desc="{$DOWNLOAD_URL}"><i title="{vtranslate('LBL_EXPORT','PDFMaker')}" class="fa fa-download"></i>&nbsp;<strong>{vtranslate('LBL_DOWNLOAD_FILE',$MODULE)}</strong></button>&nbsp;&nbsp;
                                    {if $PRINT_ACTION eq "1"}
                                            <button type='button' class='btn btn-success printButton'><i class="fa fa-print" aria-hidden="true"></i>&nbsp;<strong>{vtranslate('LBL_PRINT', $MODULE)}</strong></button>&nbsp;&nbsp;
                                    {/if}
                                    {if $SEND_EMAIL_PDF_ACTION eq "1"}
                                            <button type='button' class='btn btn-success sendEmailWithPDF' data-sendtype="{$SEND_EMAIL_PDF_ACTION_TYPE}"><i class="fa fa-send" aria-hidden="true"></i>&nbsp;<strong>{vtranslate('LBL_SEND_EMAIL')}</strong></button>&nbsp;&nbsp;
                                    {/if}
                                    {if $EDIT_AND_EXPORT_ACTION eq "1"}
                                            <button type='button' {if $DISABLED_EXPORT_EDIT}disabled="disabled"{/if} class='btn btn-success editPDF'><i class="fa fa-edit" aria-hidden="true"></i>&nbsp;<strong>{vtranslate('LBL_EDIT')}</strong></button>&nbsp;&nbsp;
                                    {/if}
                                    {if $SAVE_AS_DOC_ACTION eq "1"}
                                            <button type='button' class='btn btn-success savePDFToDoc'><i class="fa fa-save" aria-hidden="true"></i>&nbsp;<strong>{vtranslate('LBL_SAVEASDOC','PDFMaker')}</strong></button>&nbsp;&nbsp;
                                    {/if}
                                    <a class='cancelLink' href="javascript:void(0);" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/strip}