{*<!--
/*********************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
-->*}

<div style="width: 80%; max-width: 1000px; padding: 15px; margin: 0 auto;">
    <div class="modal-content">
        <div class="modal-header">
            <h4>{vtranslate('LBL_MODULE_NAME','PDFMaker')} {vtranslate('LBL_INSTALL','PDFMaker')}</h4>
        </div>
        <form name="install" id="editLicense" method="POST" action="index.php" class="form-horizontal">
            <input type="hidden" name="module" value="PDFMaker"/>
            <input type="hidden" name="view" value="List"/>
            <div class="modal-body">
                <input type="hidden" name="installtype" value="download_src"/>
                <div class="controls">
                    <div>
                        <strong>{vtranslate('LBL_DOWNLOAD_SRC','PDFMaker')}</strong>
                    </div>
                    <br>
                    <div class="clearfix">
                    </div>
                </div>
                <div class="controls">
                    <div>
                        {vtranslate('LBL_DOWNLOAD_SRC_DESC','PDFMaker')}
                        {if $MB_STRING_EXISTS eq 'false'}
                            <br>
                            {vtranslate('LBL_MB_STRING_ERROR','PDFMaker')}
                        {/if}
                    </div>
                    <br>
                    <div class="clearfix">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div style="text-align: center;">
                    <button type="button" id="download_button" class="btn btn-success">
                        <strong>{vtranslate('LBL_DOWNLOAD','PDFMaker')}</strong>
                    </button>&nbsp;&nbsp;
                </div>
            </div>
        </form>
    </div>
</div>