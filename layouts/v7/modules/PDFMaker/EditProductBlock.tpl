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
    <div class="editViewContainer">
        <style>
            .select2-chosen {
                max-width: 300px;
            }
        </style>
        <form class="form-horizontal" id="EditView" name="EditView" method="post" action="index.php" enctype="multipart/form-data" data-detail-url="?module=PDFMaker&view=ProductBlocks">
            <input type="hidden" name="module" value="PDFMaker"/>
            <input type="hidden" name="action" value="IndexAjax"/>
            <input type="hidden" name="mode" value="SaveProductBlock"/>
            <input type="hidden" name="tplid" value="{$EDIT_TEMPLATE.id}">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="contents tabbable ui-sortable">
                            <h4>
                                {if $EMODE eq 'edit'}
                                    {if $MODE neq "duplicate"}
                                        {vtranslate('LBL_EDIT','PDFMaker')} &quot;{$EDIT_TEMPLATE.name}&quot;
                                    {else}
                                        {vtranslate('LBL_DUPLICATE','PDFMaker')} &quot;{$EDIT_TEMPLATE.name}&quot;
                                    {/if}
                                {else}
                                    {vtranslate('LBL_NEW_TEMPLATE','PDFMaker')}
                                {/if}
                            </h4>
                            <ul class="nav nav-tabs layoutTabs massEditTabs">
                                <li class="active detailviewTab">
                                    <a data-toggle="tab" href="#pContentEdit">{vtranslate('LBL_PROPERTIES_TAB','PDFMaker')}</a>
                                </li>
                                <li class="detailviewTab">
                                    <a data-toggle="tab" href="#pContentLabels">{vtranslate('LBL_LABELS','PDFMaker')}</a>
                                </li>
                            </ul>

                            <div class="tab-content layoutContent themeTableColor overflowVisible">
                                {********************************************* PROPERTIES DIV*************************************************}
                                <div class="tab-pane active" id="pContentEdit">

                                    <table class="table no-border">
                                        <tbody>
                                        <tr>
                                            <td class="fieldLabel alignMiddle" style="width: 30%"><label class="muted pull-right">{vtranslate('LBL_PDF_NAME','PDFMaker')}: <span class="redColor">*</span></label></td>
                                            <td class="fieldValue"><input name="template_name" id="template_name" type="text" value="{if $MODE neq "duplicate"}{$EDIT_TEMPLATE.name}{/if}" class="inputElement" tabindex="1" data-rule-required="true"></td>
                                        </tr>
                                        <tr>
                                            <td class="fieldLabel alignMiddle"><label class="muted pull-right">{vtranslate('LBL_ARTICLE','PDFMaker')}:</label></td>
                                            <td class="fieldValue">
                                                <select name="articelvar" id="articelvar" class="select2 col-sm-9">
                                                    {html_options  options=$ARTICLE_STRINGS}
                                                </select>
                                                <button type="button" class="btn btn-success small create" onclick="InsertIntoTemplate('articelvar');"><i class="fa fa-usd"></i></button>
                                            </td>
                                        </tr>
                                        {* insert products & services fields into text *}
                                        <tr>
                                            <td class="fieldLabel alignMiddle"><label class="muted pull-right">*{vtranslate('LBL_PRODUCTS_AVLBL','PDFMaker')}:</label></td>
                                            <td class="fieldValue">
                                                <select name="psfields" id="psfields" class="select2 col-sm-9">
                                                    {html_options  options=$SELECT_PRODUCT_FIELD}
                                                </select>
                                                <button type="button" class="btn btn-success small create" onclick="InsertIntoTemplate('psfields');"><i class="fa fa-usd"></i></button>
                                            </td>
                                        </tr>
                                        {* products fields *}
                                        <tr>
                                            <td class="fieldLabel alignMiddle"><label class="muted pull-right">*{vtranslate('LBL_PRODUCTS_FIELDS','PDFMaker')}:</label></td>
                                            <td class="fieldValue">
                                                <select name="productfields" id="productfields" class="select2 col-sm-9">
                                                    {html_options  options=$PRODUCTS_FIELDS}
                                                </select>
                                                <button type="button" class="btn btn-success small create" onclick="InsertIntoTemplate('productfields');"><i class="fa fa-usd"></i></button>
                                            </td>
                                        </tr>
                                        {* services fields *}
                                        <tr>
                                            <td class="fieldLabel alignMiddle"><label class="muted pull-right">*{vtranslate('LBL_SERVICES_FIELDS','PDFMaker')}:</label></td>
                                            <td class="fieldValue">
                                                <select name="servicesfields" id="servicesfields" class="select2 col-sm-9">
                                                    {html_options  options=$SERVICES_FIELDS}
                                                </select>
                                                <button type="button" class="btn btn-success small create" onclick="InsertIntoTemplate('servicesfields');"><i class="fa fa-usd"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2"><small>{vtranslate('LBL_PRODUCT_FIELD_INFO','PDFMaker')}</small></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                {********************************************* Labels DIV *************************************************}
                                <div class="tab-pane" id="pContentLabels">
                                    <table class="table no-border">
                                        <tr>
                                            <td class="fieldLabel alignMiddle" style="width: 30%"><label class="muted pull-right">{vtranslate('LBL_GLOBAL_LANG','PDFMaker')}:</label></td>
                                            <td class="fieldValue">
                                                <select name="global_lang" id="global_lang" class="select2 col-sm-9">
                                                    {html_options  options=$GLOBAL_LANG_LABELS}
                                                </select>
                                                <button type="button" class="btn btn-success small create" onclick="InsertIntoTemplate('global_lang');"><i class="fa fa-usd"></i></button>
                                            </td>
                                        </tr>
                                        {if $VERSION_TYPE eq 'professional'}
                                            <tr>
                                                <td class="fieldLabel alignMiddle"><label class="muted pull-right">{vtranslate('LBL_CUSTOM_LABELS','PDFMaker')}:</label></td>
                                                <td class="fieldValue">
                                                    <select name="custom_lang" id="custom_lang" class="select2 col-sm-9">
                                                        {html_options  options=$CUSTOM_LANG_LABELS}
                                                    </select>
                                                    <button type="button" class="btn btn-success small create" onclick="InsertIntoTemplate('custom_lang');"><i class="fa fa-usd"></i></button>
                                                </td>
                                            </tr>
                                        {/if}
                                    </table>
                                </div>
                                {************************************** END OF TABS BLOCK *************************************}
                            </div>
                        </div>
                    </div>
                    {*********************************************BODY DIV*************************************************}
                    <div class="col-lg-8">
                        <textarea name="body" id="body" style="width:90%;height:700px" class=small tabindex="5">{$EDIT_TEMPLATE.body}</textarea>
                    </div>
                </div>
            </div>
            <div class="modal-overlay-footer row-fluid">
                <div class="textAlignCenter ">
                    <button class="btn btn-success" type="submit" onclick="if(PDFMaker_EditJs.savePDF()) this.form.submit();"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
                    {if $smarty.request.applied eq 'true'}
                        <a class="cancelLink" type="reset" onclick="window.location.href = 'index.php?action=DetailViewPDFTemplate&module=PDFMaker&templateid={$SAVETEMPLATEID}&parenttab=Tools';">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                    {else}
                        <a class="cancelLink" type="reset" onclick="javascript:window.history.back();">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                    {/if}
                </div>
            </div>
        </form>
        <script type="text/javascript">
            {literal} jQuery(document).ready(function () {
                CKEDITOR.replace('body', {height: '70vh'});
                var PDFMakerProductBlocksJs = PDFMaker_ProductBlocks_Js.getInstance();
                PDFMakerProductBlocksJs.registerEvents();
            });

            var selectedTab = "properties";

            function InsertIntoTemplate(elementType) {

                var insert_value = "";
                var selectField = document.getElementById(elementType).value;
                var oEditor = CKEDITOR.instances.body;

                if (elementType == "articelvar" || selectField == "LISTVIEWBLOCK_START" || selectField == "LISTVIEWBLOCK_END") {
                    insert_value = '#' + selectField + '#';
                } else if (elementType == "relatedmodulefields") {
                    insert_value = '$R_' + selectField + '$';
                } else if (elementType == "productbloctpl" || elementType == "productbloctpl2") {
                    insert_value = selectField;
                } else if (elementType == "global_lang") {
                    insert_value = '%G_' + selectField + '%';
                } else if (elementType == "custom_lang") {
                    insert_value = '%' + selectField + '%';
                } else {
                    insert_value = '$' + selectField + '$';
                }
                oEditor.insertHtml(insert_value);
            }
            {/literal}
        </script>
    </div>
{/strip}