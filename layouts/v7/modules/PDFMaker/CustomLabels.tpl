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
<div class="container-fluid" id="CustomLabelsContainer">
    <form name="custom_labels" action="index.php" method="post" class="form-horizontal">
        <input type="hidden" name="module" value="PDFMaker" />
        <input type="hidden" name="action" value="IndexAjax" />
        <input type="hidden" name="mode" value="DeleteCustomLabels" />
        <input type="hidden" name="newItems" value="" />
        <br>
        <label class="pull-left themeTextColor font-x-x-large">{vtranslate('LBL_CUSTOM_LABELS','PDFMaker')}</label>
        <br clear="all">{vtranslate('LBL_CUSTOM_LABELS_DESC','PDFMaker')}
        <hr>
        <br />
        <div class="row-fluid">
            <label class="fieldLabel"><strong>{vtranslate('LBL_DEFINE_CUSTOM_LABELS','PDFMaker')}:</strong></label><br>
            <div class="row-fluid clearfix">
                <div class="pull-right btn-group">
                    <button type="button" class="addCustomLabel btn addButton btn-default" data-url="?module=PDFMaker&view=IndexAjax&mode=editCustomLabel">
                        <i class="fa fa-plus"></i>&nbsp;
                        <span>{vtranslate('LBL_ADD')}</span>
                    </button>&nbsp;&nbsp;
                    <button type="reset" class="btn btn-default marginLeftZero" onClick="window.history.back();">{vtranslate('LBL_BACK')}</button>
                </div>
            </div>
            <br>
            <div class="pushDownHalfper">
                <table id="CustomLabelTable" class="table table-bordered table-condensed CustomLabelTable" style="padding:0px;margin:0px">
                <thead>
                    <tr class="blockHeader">
                        <th style="border-left: 1px solid #DDD !important;" width="30%">{vtranslate('LBL_KEY','PDFMaker')}</th>
                        <th style="border-left: 1px solid #DDD !important;" width="50%" colspan="2">{vtranslate('LBL_CURR_LANG_VALUE','PDFMaker')} ({$CURR_LANG.label})</th>
                        <th style="border-left: 1px solid #DDD !important;" width="16%" align="center">{vtranslate('LBL_OTHER_LANG_VALUES','PDFMaker')}</th>
                    </tr>
                </thead>
                <tbody>
                <script type="text/javascript" language="javascript">var existingKeys = new Array();</script>
                {assign var="lang_id" value=$CURR_LANG.id}
                {foreach key=label_id item=label_value from=$LABELS name=lbl_foreach}
                    <tr class="opacity">
                        <td>
                            <label class="CustomLabelKey textOverflowEllipsis">{$label_value.key}</label>
                        </td>
                        <td style="border-right: 0px;">
                            <label class="CustomLabelValue">{$label_value.lang_values.$lang_id}</label>
                        </td>
                        <td style="border-left: 0px;">
                            <div class="pull-right actions">
                                <a class="editCustomLabel cursorPointer" data-url="?module=PDFMaker&view=IndexAjax&mode=editCustomLabel&labelid={$label_id}&langid={$lang_id}">
                                    <i title="{vtranslate('LBL_EDIT_CUSTOM_LABEL','PDFMaker')}" class="fa fa-pencil"></i>
                                </a>&nbsp;&nbsp;
                                <a class="deleteCustomLabel cursorPointer" data-url="?module=PDFMaker&action=IndexAjax&mode=deleteCustomLabel&labelid={$label_id}">
                                    <i title="{vtranslate('LBL_DELETE','PDFMaker')}" class="fa fa-trash"></i>
                                </a>&nbsp;
                            </div>
                        </td>
                        <td>
                            <a class="showCustomLabelValues textOverflowEllipsis cursorPointer" data-url="?module=PDFMaker&view=IndexAjax&mode=showCustomLabelValues&labelid={$label_id}&langid={$lang_id}">{vtranslate('LBL_OTHER_VALS','PDFMaker')}</a>
                        </td>
                    </tr>
                {foreachelse}
                    <tr id="noItemFountTr">
                        <td colspan="3" class="cellText" align="center" style="padding:10px;"><strong>{vtranslate('LBL_NO_ITEM_FOUND','PDFMaker')}</strong></td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
            </div>
            <div id="otherLangsDiv" style="display:none; width:350px; position:absolute;" class="layerPopup"></div>
            <br>
            <div class="row-fluid pushDownHalfper clearfix">
                <div class="pull-right btn-group">
                    <button type="button" class="addCustomLabel btn addButton btn-default" data-url="?module=PDFMaker&view=IndexAjax&mode=editCustomLabel">
                        <i class="fa fa-plus"></i>&nbsp;
                        <span>{vtranslate('LBL_ADD')}</span>
                    </button>&nbsp;&nbsp;
                    <button type="reset" class="btn btn-default marginLeftZero" onClick="window.history.back();">{vtranslate('LBL_BACK')}</button>
                </div>
            </div>
        </div>
    </form>
</div>
{/strip}