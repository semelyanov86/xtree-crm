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
                    <h4 class="textOverflowEllipsis maxWidth50" title="{$FILE_NAME}"><b>{vtranslate('LBL_EDIT')}</b></h4>
                </div>
                <div class="col-lg-1 pull-right">
                    <button type="button" class="close" aria-label="Close" data-dismiss="modal">
                        <span aria-hidden="true" class='fa fa-close'></span>
                    </button>
                </div>
            </div>
            <div class="modal-body row" style="height:550px;">
                <div id="composePDFContainer tabbable ui-sortable">
                    <form class="form-horizontal recordEditView" id="editPDFForm" method="post" action="index.php" enctype="multipart/form-data" name="editPDFForm">
                        <input type="hidden" name="action" id="action" value='CreatePDFFromTemplate' />
                        <input type="hidden" name="module" value="PDFMaker"/>
                        <input type="hidden" name="commontemplateid" value='{$COMMONTEMPLATEIDS}' />
                        <input type="hidden" name="template_ids" value='{$COMMONTEMPLATEIDS}' />
                        <input type="hidden" name="idslist" value="{$RECORDS}" />
                        <input type="hidden" name="relmodule" value="{$smarty.request.formodule}" />
                        <input type="hidden" name="language" value='{$smarty.request.language}' />
                        <input type="hidden" name="pmodule" value="{$smarty.request.formodule}" />
                        <input type="hidden" name="pid" value="{$smarty.request.record}" />
                        <input type="hidden" name="mode" value="edit" />
                        <input type="hidden" name="print" value="" />

                        <div id='editTemplate'>
                            <div class="row">
                                <div class="col-xs-6">
                                    <ul class="nav nav-pills">
                                        <li class="active" data-type="body"><a data-toggle="tab" href="#pdfbodyTabA" aria-expanded="true"><strong>&nbsp;{vtranslate('LBL_BODY',$MODULE)}&nbsp;</strong></a></li>
                                        <li class="" data-type="header"><a data-toggle="tab" href="#pdfheaderTabA" aria-expanded="true"><strong>&nbsp;{vtranslate('LBL_HEADER_TAB',$MODULE)}&nbsp;</strong></a></li>
                                        <li class="" data-type="footer"><a data-toggle="tab" href="#pdffooterTabA" aria-expanded="true"><strong>&nbsp;{vtranslate('LBL_FOOTER_TAB',$MODULE)}&nbsp;</strong></a></li>
                                    </ul>
                                </div>
                                <div class="col-xs-6">
                                    {vtranslate('LBL_TEMPLATE','PDFMaker')}:&nbsp;{$TEMPLATE_SELECT}
                                </div>
                            </div><br>
                            <div class="tab-content">
                                {foreach name="sections" item=section from=$PDF_SECTIONS}
                                    <div id="pdf{$section}TabA" class="tab-pane {if {$smarty.foreach.sections.index} eq 0}active{/if}" data-section="{$section}">
                                        {foreach name="pdfcontent" item=pdfcontent key=templateid from=$PDF_CONTENTS[{$section}]}
                                            <div class="pdfcontent{$templateid} {if $DEFAULT_TEMPLATEID neq $templateid}hide{/if}" id="{$section}_div{$templateid}">
                                                <textarea name="{$section}{$templateid}" id="{$section}{$templateid}" style="height:470px" class="inputElement textAreaElement col-lg-12" tabindex="5">{$pdfcontent}</textarea>
                                            </div>
                                        {/foreach}
                                    </div>
                                {/foreach}
                            </div>
                            {$PDF_DIVS}
                        </div>
                    </form>
                </div>
                <div class="hide">
                    <textarea id="ckeditorFontsFaces">{$FONTS_FACES}</textarea>
                    <input type="hidden" id="ckeditorFonts" value="{$FONTS}">
                </div>
            </div>
            </div>
            <div class="modal-footer">
                <div class='clearfix modal-footer-overwrite-style'>
                    <div class="row clearfix ">
                        <div class=' textAlignCenter col-lg-12 col-md-12 col-sm-12'>
                            <button type='submit' class='btn btn-success downloadButton' data-desc="{$DOWNLOAD_URL}">{vtranslate('LBL_DOWNLOAD_FILE',$MODULE)}</button>&nbsp;&nbsp;
                            {if $PRINT_ACTION eq "1"}
                                <button type='button' class='btn btn-success printButton'>{vtranslate('LBL_PRINT', $MODULE)}</button>&nbsp;&nbsp;
                            {/if}
                            {if $SEND_EMAIL_PDF_ACTION eq "1"}
                                <button type='button' class='btn btn-success sendEmailWithPDF'>{vtranslate('LBL_SEND_EMAIL')}</button>&nbsp;&nbsp;
                            {/if}
                            {if $SAVE_AS_DOC_ACTION eq "1"}
                                <button type='button' class='btn btn-success savePDFToDoc'>{vtranslate('LBL_SAVEASDOC','PDFMaker')}</button>&nbsp;&nbsp;
                            {/if}
                            <a class='cancelLink' href="javascript:void(0);" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/strip}