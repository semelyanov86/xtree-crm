{*<!--
/*********************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
-->*}
{if $EXTENSIONS_ERROR}
    <div class="col-sm-12 col-xs-12">
        <a class="alert alert-danger displayInlineBlock" href="index.php?module={$MODULE}&view=Extensions">
            <div>
                <b style="padding-right: 15px; color: #b12d26;">
                    {vtranslate('LBL_EXTENSION_LIBRARY_ERROR', $MODULE)} CKEditor, PHP Simple HTML DOM, mPDF, PHPMailer
                </b>
            </div>
            <br>
            <div class="btn btn-danger">{vtranslate('LBL_EXTENSIONS', $MODULE)}</div>
        </a>
    </div>
{/if}