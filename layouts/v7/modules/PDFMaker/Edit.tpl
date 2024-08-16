{*<!--
/*********************************************************************************
* The content of this file is subject to the PDF Maker license.
* ("License"); You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
* Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
* All Rights Reserved.
********************************************************************************/
-->*}
{strip}
<div class="contents tabbable ui-sortable">
    <form class="form-horizontal recordEditView" id="EditView" name="EditView" method="post" action="index.php" enctype="multipart/form-data">
        <input type="hidden" name="module" value="PDFMaker">
        <input type="hidden" name="parenttab" value="{$PARENTTAB}">
        <input type="hidden" name="templateid" id="templateid" value="{$SAVETEMPLATEID}">
        <input type="hidden" name="action" value="SavePDFTemplate">
        <input type="hidden" name="redirect" value="true">
        <input type="hidden" name="return_module" value="{$smarty.request.return_module}">
        <input type="hidden" name="return_view" value="{$smarty.request.return_view}">
        <input type="hidden" name="selectedTab" id="selectedTab" value="properties">
        <input type="hidden" name="selectedTab2" id="selectedTab2" value="body">
        <ul class="nav nav-tabs layoutTabs massEditTabs">
            <li class="PDFMakerToggleLeftBlock">
                <div class="fa fa-chevron-left"></div>
            </li>
            <li class="detailviewTab active">
                <a data-toggle="tab" href="#pdfContentEdit" aria-expanded="true"><strong>{vtranslate('LBL_BASIC_TAB',$MODULE)}</strong></a>
            </li>
            <li class="detailviewTab">
                <a data-toggle="tab" href="#pdfContentOther" aria-expanded="false"><strong>{vtranslate('LBL_OTHER_INFO',$MODULE)}</strong></a>
            </li>
            <li class="detailviewTab">
                <a data-toggle="tab" href="#pdfContentLabels" aria-expanded="false"><strong>{vtranslate('LBL_LABELS',$MODULE)}</strong></a>
            </li>
            {if $IS_BLOCK neq true}
                <li class="detailviewTab">
                    <a data-toggle="tab" href="#pdfContentProducts" aria-expanded="false"><strong>{vtranslate('LBL_ARTICLE',$MODULE)}</strong></a>
                </li>
                <li class="detailviewTab">
                    <a data-toggle="tab" href="#pdfContentHeaderFooter" aria-expanded="false"><strong>{vtranslate('LBL_HEADER_TAB',$MODULE)} / {vtranslate('LBL_FOOTER_TAB',$MODULE)}</strong></a>
                </li>
                <li class="detailviewTab">
                    <a data-toggle="tab" href="#editTabProperties" aria-expanded="false"><strong>{vtranslate('LBL_PROPERTIES_TAB',$MODULE)}</strong></a>
                </li>
                <li class="detailviewTab">
                    <a data-toggle="tab" href="#editTabSettings" aria-expanded="false"><strong>{vtranslate('LBL_SETTINGS_TAB',$MODULE)}</strong></a>
                </li>
                {if $IS_ACTIVE_SIGNATURE}
                    <li class="detailviewTab">
                        <a data-toggle="tab" href="#editTabSignature" aria-expanded="false"><strong>{vtranslate('LBL_SIGNATURE_TAB',$MODULE)}</strong></a>
                    </li>
                {/if}
                <li class="detailviewTab">
                    <a data-toggle="tab" href="#editTabSharing" aria-expanded="false"><strong>{vtranslate('LBL_SHARING_TAB',$MODULE)}</strong></a>
                </li>
            {/if}
        </ul>
        <div >
            {********************************************* Settings DIV *************************************************}
            <div>
                <div class="row PDFMakerContentBlock" >
                    <div class="left-block PDFMakerLeftBlock col-xs-4">
                        <div>
                            <div class="tab-content layoutContent themeTableColor overflowVisible">
                                {include file='tabs/Basic.tpl'|@vtemplate_path:$MODULE}
                                {include file='tabs/Other.tpl'|@vtemplate_path:$MODULE}
                                {include file='tabs/Labels.tpl'|@vtemplate_path:$MODULE}
                                {if $IS_BLOCK neq true}
                                    {include file='tabs/Products.tpl'|@vtemplate_path:$MODULE}
                                    {include file='tabs/HeaderFooter.tpl'|@vtemplate_path:$MODULE}
                                    {include file='tabs/Properties.tpl'|@vtemplate_path:$MODULE}
                                    {include file='tabs/Settings.tpl'|@vtemplate_path:$MODULE}
                                    {if $IS_ACTIVE_SIGNATURE}
                                        {include file='tabs/Signature.tpl'|@vtemplate_path:$MODULE}
                                    {/if}
                                    {include file='tabs/Sharing.tpl'|@vtemplate_path:$MODULE}
                                {/if}
                            </div>
                        </div>
                    </div>

                    {************************************** END OF TABS BLOCK *************************************}
                    <div class="middle-block PDFMakerMiddleBlock col-xs-8">
                        <div class="PDFMakerMiddleBlock_content">
                            {if $IS_BLOCK neq true}
                                <div id="ContentEditorTabs">
                                    <ul class="nav nav-pills">
                                        <li id="bodyDivTab" class="ContentEditorTab active" data-type="body" style="margin-right: 5px">
                                            <a href="#body_div2" aria-expanded="false" data-toggle="tab">{vtranslate('LBL_BODY',$MODULE)}</a>
                                        </li>
                                        <li id="headerDivTab" class="ContentEditorTab" data-type="header" style="margin: 0px 5px 0px 5px">
                                            <a href="#header_div2" aria-expanded="false" data-toggle="tab">{vtranslate('LBL_HEADER_TAB',$MODULE)}</a>
                                        </li>
                                        <li id="footerDivTab" class="ContentEditorTab" data-type="footer" style="margin: 0px 5px 0px 5px">
                                            <a href="#footer_div2" aria-expanded="false" data-toggle="tab">{vtranslate('LBL_FOOTER_TAB',$MODULE)}</a>
                                        </li>
                                        {if $STYLES_CONTENT neq ""}
                                            <li data-type="templateCSSStyleTabLayout" class="ContentEditorTab" style="margin: 0px 5px 0px 5px">
                                                <a href="#cssstyle_div2" aria-expanded="false" data-toggle="tab">{vtranslate('LBL_CSS_STYLE_TAB',$MODULE)}</a>
                                            </li>
                                        {/if}
                                    </ul>
                                </div>
                            {/if}
                            {*********************************************BODY DIV*************************************************}
                            <div class="tab-content">
                                <div class="tab-pane ContentTabPanel active" id="body_div2">
                                    <textarea name="body" id="body" style="width:90%;height:700px" class=small tabindex="5">{$BODY}</textarea>
                                </div>
                                {if $IS_BLOCK neq true}
                                    {*********************************************Header DIV*************************************************}
                                    <div class="tab-pane ContentTabPanel" id="header_div2">
                                        <textarea name="header_body" id="header_body" style="width:90%;height:200px" class="small">{$HEADER}</textarea>
                                    </div>
                                    {*********************************************Footer DIV*************************************************}
                                    <div class="tab-pane ContentTabPanel" id="footer_div2">
                                        <textarea name="footer_body" id="footer_body" style="width:90%;height:200px" class="small">{$FOOTER}</textarea>
                                    </div>
                                    {if $STYLES_CONTENT neq ""}
                                        <div class="tab-pane ContentTabPanel" id="cssstyle_div2">
                                            {foreach item=STYLE_DATA from=$STYLES_CONTENT}
                                                {if $IS_DUPLICATE}
                                                    <input type="hidden" name="its4you_styles[]" value="{$STYLE_DATA.id}">
                                                {/if}
                                                <div class="hide">
                                                    <textarea class="CodeMirrorContent" id="CodeMirrorContent{$STYLE_DATA.id}" style="border: 1px solid black; " class="CodeMirrorTextarea " tabindex="5">{$STYLE_DATA.stylecontent}</textarea>
                                                </div>
                                                <table class="table table-bordered">
                                                    <thead>
                                                    <tr class="listViewHeaders">
                                                        <th>
                                                            <div class="pull-left">
                                                                <a href="index.php?module=ITS4YouStyles&view=Detail&record={$STYLE_DATA.id}" target="_blank">{$STYLE_DATA.name}</a>
                                                            </div>
                                                            <div class="pull-right actions">
                                                                <a href="index.php?module=ITS4YouStyles&view=Detail&record={$STYLE_DATA.id}" target="_blank"><i title="{vtranslate('LBL_SHOW_COMPLETE_DETAILS', $MODULE)}" class="icon-th-list alignMiddle"></i></a>&nbsp;
                                                                {if $STYLE_DATA.iseditable eq "yes"}
                                                                    <a href="index.php?module=ITS4YouStyles&view=Edit&record={$STYLE_DATA.id}" target="_blank" class="cursorPointer"><i class="icon-pencil alignMiddle" title="{vtranslate('LBL_EDIT', $MODULE)}"></i></a>
                                                                {/if}
                                                            </div>
                                                        </th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <tr>
                                                        <td id="CodeMirrorContent{$STYLE_DATA.id}Output" class="cm-s-default">

                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                                <br>
                                            {/foreach}
                                        </div>
                                    {/if}
                                {/if}
                            </div>
                            <div class="hide">
                                <textarea id="fontawesomeclass">
                                    {$FONTAWESOMECLASS}
                                </textarea>
                                <textarea id="ckeditorFontsFaces">
                                    {$FONTS_FACES}
                                </textarea>
                                <input type="hidden" id="ckeditorFonts" value="{$FONTS}">
                                <input type="hidden" id="isBlock" value="{$IS_BLOCK}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-overlay-footer row-fluid">
            <div class="textAlignCenter ">
                <button class="btn" type="submit" onclick="document.EditView.redirect.value = 'false';" ><strong>{vtranslate('LBL_APPLY',$MODULE)}</strong></button>&nbsp;&nbsp;
                <button class="btn btn-success" type="submit" ><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
                {if $smarty.request.return_view neq ''}
                    <a class="cancelLink" type="reset" onclick="window.location.href = 'index.php?module={if $smarty.request.return_module neq ''}{$smarty.request.return_module}{else}PDFMaker{/if}&view={$smarty.request.return_view}{if $smarty.request.templateid neq ""  && $smarty.request.return_view neq "List"}&templateid={$smarty.request.templateid}{/if}';">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                {else}
                    <a class="cancelLink" type="reset" onclick="javascript:window.history.back();">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                {/if}            			
            </div>
        </div>
    </form>
    <div class="hide" style="display: none">
        <div id="div_vat_block_table">{$VATBLOCK_TABLE}</div>
        <div id="div_charges_block_table">{$CHARGESBLOCK_TABLE}</div>
        <div id="div_company_header_signature">{$COMPANY_HEADER_SIGNATURE}</div>
        <div id="div_company_stamp_signature">{$COMPANY_STAMP_SIGNATURE}</div>
        <div class="popupUi modal-dialog modal-md" data-backdrop="false">
            <div class="modal-content">
                {assign var=HEADER_TITLE value={vtranslate('LBL_SET_VALUE',$QUALIFIED_MODULE)}}
                {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12" >
                            <div class="form-group">
                                <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">{vtranslate('LBL_MODULENAMES',$MODULE)}:
                                </label>
                                <div class="controls col-sm-9">
                                    <div class="input-group">
                                        <select name="filename_fields2" id="filename_fields2" class="form-control">
                                            {if $TEMPLATEID eq "" && $SELECTMODULE eq ""}
                                                <option value="">{vtranslate('LBL_SELECT_MODULE_FIELD',$MODULE)}</option>
                                            {else}
                                                {html_options  options=$SELECT_MODULE_FIELD}
                                            {/if}
                                        </select>
                                        <div class="input-group-btn">
                                            <button type="button" class="btn btn-success InsertIntoTextarea" data-type="filename_fields2" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-sm-12" >
                            <div class="form-group">
                                <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                    {vtranslate('LBL_RELATED_MODULES',$MODULE)}:
                                </label>
                                <div class="controls col-sm-9">
                                    <select name="relatedmodulesorce2" id="relatedmodulesorce2" class="form-control">
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row mainContent">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                                </label>
                                <div class="controls col-sm-9">
                                    <div class="input-group">
                                        <select name="relatedmodulefields2" id="relatedmodulefields2" class="form-control">
                                            <option value="">{vtranslate('LBL_SELECT_MODULE_FIELD',$MODULE)}</option>
                                        </select>
                                        <div class="input-group-btn">
                                            <button type="button" class="btn btn-success InsertIntoTextarea" data-type="relatedmodulefields2" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row fieldValueContainer">
                        <div class="col-sm-12">
                            <textarea data-textarea="true" class="fieldValue inputElement hide" style="height: inherit;"></textarea>
                        </div>
                    </div><br>

                </div>
                {include file="ModalFooter.tpl"|vtemplate_path:$MODULE}
            </div>
        </div>
    </div>
    <div class="clonedPopUp"></div>
</div>
<script type="text/javascript">
    var selectedTab = 'properties';
    var selectedTab2 = 'body';
    var module_blocks = new Array();
 
    var selected_module = '{$SELECTMODULE}';

    var constructedOptionValue;
    var constructedOptionName;

    jQuery(document).ready(function() {

        jQuery.fn.scrollBottom = function() {
            return jQuery(document).height() - this.scrollTop() - this.height();
        };
    });
</script>
{/strip}