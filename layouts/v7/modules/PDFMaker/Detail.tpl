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
<div class="detailview-content container-fluid">
    <div class="details row">
        <form id="detailView" method="post" action="index.php" name="etemplatedetailview" onsubmit="VtigerJS_DialogBox.block();">
            <input type="hidden" name="action" value="">
            <input type="hidden" name="view" value="">
            <input type="hidden" name="module" value="PDFMaker">
            <input type="hidden" name="retur_module" value="PDFMaker">
            <input type="hidden" name="return_action" value="PDFMaker">
            <input type="hidden" name="return_view" value="Detail">
            <input type="hidden" name="templateid" value="{$TEMPLATEID}">
            <input type="hidden" name="parenttab" value="{$PARENTTAB}">
            <input type="hidden" name="isDuplicate" value="false">
            <input type="hidden" name="subjectChanged" value="">
            <input id="recordId" value="{$TEMPLATEID}" type="hidden">
            <div class="col-lg-12">
                <div class="left-block col-lg-4">
                    <div class="summaryView">
                        <div class="summaryViewHeader">
                            <h4 class="display-inline-block">
                                {if $IS_BLOCK eq true}
                                    {vtranslate('LBL_HEADER_INFORMATIONS','PDFMaker')}
                                {else}
                                    {vtranslate('LBL_TEMPLATE_INFORMATIONS','PDFMaker')}
                                {/if}
                            </h4>
                        </div>
                        <div class="summaryViewFields">
                            <div class="recordDetails">
                                <table class="summary-table no-border">
                                    <tbody>
                                    <tr class="summaryViewEntries">
                                        <td class="fieldLabel"><label class="muted textOverflowEllipsis">{vtranslate('LBL_PDF_NAME','PDFMaker')}</label></td>
                                        <td class="fieldValue">{$FILENAME}</td>
                                    </tr>
                                    <tr class="summaryViewEntries">
                                        <td class="fieldLabel"><label class="muted textOverflowEllipsis">{vtranslate('LBL_DESCRIPTION','PDFMaker')}</label></td>
                                        <td class="fieldValue" valign=top>{$DESCRIPTION}</td>
                                    </tr>
                                    {if $MODULENAME neq ""}
                                        <tr class="summaryViewEntries">
                                            <td class="fieldLabel"><label class="muted textOverflowEllipsis">{vtranslate('LBL_MODULENAMES','PDFMaker')}</label></td>
                                            <td class="fieldValue" valign=top>{$MODULENAME}</td>
                                        </tr>
                                    {/if}
                                    {if $IS_BLOCK neq true}
                                        <tr class="summaryViewEntries">
                                            <td class="fieldLabel"><label class="muted textOverflowEllipsis">{vtranslate('Status')}</label></td>
                                            <td class="fieldValue" valign=top>{$IS_ACTIVE}</td>
                                        </tr>
                                        <tr class="summaryViewEntries">
                                            <td class="fieldLabel"><label class="muted textOverflowEllipsis">{vtranslate('LBL_SETASDEFAULT','PDFMaker')}</label></td>
                                            <td class="fieldValue" valign=top>{$IS_DEFAULT}</td>
                                        </tr>
                                    {/if}

                                    {if $WATERMARK.type neq "none"}
                                        <tr class="summaryViewEntries">
                                            <td class="fieldLabel"><label class="muted textOverflowEllipsis">{vtranslate('Watermark','PDFMaker')} ({$WATERMARK.type_label})</label></td>
                                            <td class="fieldValue" valign=top>{if $WATERMARK.type eq "image"}<a href="{$WATERMARK.image_url}">{$WATERMARK.image_name}</a>{else}{$WATERMARK.text}{/if}</td>
                                        </tr>
                                    {/if}

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <br>
                    {if $IS_BLOCK neq true}
                        {if $EDIT_PERMISSIONS}
                        <div class="summaryView">
                            <div class="summaryViewHeader">
                                <h4 class="display-inline-block">{vtranslate('LBL_DISPLAY_TAB',$MODULE)}</h4>
                                <div class="pull-right">
                                    <button type="button" class="btn btn-default editDisplayConditions" data-url="index.php?module=PDFMaker&view=EditDisplayConditions&templateid={$TEMPLATEID}">
                                        &nbsp;{vtranslate('LBL_EDIT',$MODULE)}&nbsp;{vtranslate('LBL_CONDITIONS',$MODULE)}
                                    </button>
                                </div>
                            </div>
                            <div class="summaryViewFields">
                                <div class="recordDetails">
                                    {include file='DetailDisplayConditions.tpl'|@vtemplate_path:$MODULE}
                                </div>
                            </div>
                        </div>
                        <br>
                        {if $ISSTYLESACTIVE eq "yes"}
                            <div class="summaryView">
                                <div class="summaryViewHeader">
                                    <h4 class="display-inline-block">{vtranslate('LBL_CSS_STYLE_TAB',$MODULE)}</h4>
                                    <div class="pull-right">
                                            <button type="button" class="btn btn-default addButton addStyleContentBtn" data-modulename="ITS4YouStyles">{vtranslate('LBL_ADD')}&nbsp;{vtranslate('SINGLE_ITS4YouStyles','ITS4YouStyles')}</button>&nbsp;&nbsp;
                                            <button type="button" class="btn btn-default addButton selectRelationStyle" data-modulename="ITS4YouStyles">&nbsp;{vtranslate('LBL_SELECT')}&nbsp;{vtranslate('SINGLE_ITS4YouStyles','ITS4YouStyles')}</button>
                                    </div>
                                </div>
                                <br>
                                <div class="summaryWidgetContainer noContent">
                                    {if $STYLES_LIST}
                                        <div id="table-content" class="table-container">
                                            <table id="listview-table" class="table listview-table">
                                                <thead>
                                                <tr class="listViewContentHeader">
                                                    <th style="width:55px;"></th>
                                                    <th nowrap>{vtranslate('Name','ITS4YouStyles')}</th>
                                                    <th nowrap>{vtranslate('Priority','ITS4YouStyles')}</th>
                                                </tr>
                                                </thead>
                                                <tbody class="overflow-y">
                                                {foreach item=style_data  from=$STYLES_LIST}
                                                    <tr class="" data-id="{$style_data.id}">
                                                        <td style="width:55px">
                                                            {if $style_data.iseditable eq "yes"}
                                                            <span class="actionImages">&nbsp;&nbsp;&nbsp;
                                                            <a name="styleEdit" data-url="index.php?module=ITS4YouStyles&view=Edit&record={$style_data.id}">
                                                                <i title="Edit" class="fa fa-pencil"></i></a> &nbsp;&nbsp;
                                                            <a class="relationDelete">
                                                                <i title="Unlink" class="vicon-linkopen"></i></a>
                                                        </span>
                                                        {/if}
                                                        </td>
                                                        <td class="listViewEntryValue textOverflowEllipsis " width="%" nowrap><a name="styleEdit" data-url="index.php?module=ITS4YouStyles&view=Detail&record={$style_data.id}">{$style_data.name}</a></td>
                                                        <td class="listViewEntryValue textOverflowEllipsis " width="%" nowrap>{$style_data.priority}</td>
                                                    </tr>
                                                {/foreach}
                                                </tbody>
                                            </table>
                                        </div>
                                    {else}
                                        <p class="textAlignCenter">{vtranslate('LBL_NO_RELATED',$MODULE)} {vtranslate('LBL_STYLES',$MODULE)}</p>
                                    {/if}
                                </div>
                        </div>
                        <br>
                        {/if}
                        {/if}
                    {/if}
                </div>
                <div class="middle-block col-lg-8">
                    {if $IS_BLOCK neq true}
                        <div id="ContentEditorTabs">
                            <ul class="nav nav-pills">
                                <li class="active" data-type="body">
                                    <a href="#body_div2" aria-expanded="false" style="margin-right: 5px" data-toggle="tab">{vtranslate('LBL_BODY',$MODULE)}</a>
                                </li>
                                <li data-type="header">
                                    <a href="#header_div2" aria-expanded="false" style="margin-right: 5px" data-toggle="tab">{vtranslate('LBL_HEADER_TAB',$MODULE)}</a>
                                </li>
                                <li data-type="footer">
                                    <a href="#footer_div2" aria-expanded="false" data-toggle="tab">{vtranslate('LBL_FOOTER_TAB',$MODULE)}</a>
                                </li>
                            </ul>
                        </div>
                    {/if}
                    {*********************************************BODY DIV*************************************************}
                    <div class="tab-content marginTop5px">
                        <div class="tab-pane active" id="body_div2">
                            <div id="previewcontent_body" class="hide">{$BODY}</div>
                            <iframe id="preview_body" style="width: 100%;height:1200px;"></iframe>
                        </div>
                        {if $IS_BLOCK neq true}
                            {*********************************************Header DIV*************************************************}
                            <div class="tab-pane" id="header_div2">
                                <div id="previewcontent_header" class="hide">{$HEADER}</div>
                                <iframe id="preview_header" style="width: 100%;height:500px;"></iframe>
                            </div>
                            {*********************************************Footer DIV*************************************************}
                            <div class="tab-pane" id="footer_div2">
                                <div id="previewcontent_footer" class="hide">{$FOOTER}</div>
                                <iframe id="preview_footer" style="width: 100%;height:500px;"></iframe>
                            </div>
                        {/if}
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function() {
        PDFMaker_Detail_Js.setPreviewContent('body');
        PDFMaker_Detail_Js.setPreviewContent('header');
        PDFMaker_Detail_Js.setPreviewContent('footer');
    });
</script>
{/strip}