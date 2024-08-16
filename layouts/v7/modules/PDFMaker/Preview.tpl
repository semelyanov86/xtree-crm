{*<!--
/*********************************************************************************
* The content of this file is subject to the PDF Maker license.
* ("License"); You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
* Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
* All Rights Reserved.
********************************************************************************/
-->*}
<div class='fc-overlay-modal filePreview'>
	<div class = "modal-content">
		<div class="overlayHeader">
			{assign var=TITLE value="{$PDFCONTENTDATA.filename}"}
			{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$TITLE}
		</div>
		<div class='modal-body' style="margin-bottom:100%" id ="landingPageDiv">
			<hr>
                        <div class="landingPage container-fluid importServiceSelectionContainer">
                                <form enctype="multipart/form-data" name="previewBasic" method="POST" action="index.php">
                                        <input type="hidden" name="module" value="PDFMaker">
                                        <input type="hidden" name="action" value="Import">
                                        <div class="preview-area">
                                            <iframe src="{$FILE_PATH}" width="100%" height="100%"></iframe> 
                                        </div>
                                        <div class="modal-overlay-footer border1px clearfix">
                                            <div class="row clearfix">
                                                <div class="textAlignCenter col-lg-12 col-md-12 col-sm-12 ">
                                                        <button type="submit" name="import" id="importButton" class="btn btn-success btn-lg" onclick="return PDFMaker_List_Js.uploadAndParse()"><strong>{vtranslate('LBL_IMPORT_BUTTON_LABEL','Import')}</strong></button> &nbsp;&nbsp;
                                                        <a class="cancelLink" data-dismiss="modal" href="#">{vtranslate('LBL_CANCEL')}</a>
                                                </div>
                                            </div>
                                        </div>
                                </form>
                        </div>
		</div>
	</div>
</div>