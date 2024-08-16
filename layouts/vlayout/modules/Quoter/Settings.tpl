{*/* * *******************************************************************************
* The content of this file is subject to the Quoter ("License");
* You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is VTExperts.com
* Portions created by VTExperts.com. are Copyright(C)VTExperts.com.
* All Rights Reserved.
* ****************************************************************************** */*}
{strip}
<div class="container-fluid">
    <div class="widget_header row-fluid">
        <h3>{vtranslate('Item Details Customizer (Advanced)', $QUALIFIED_MODULE)}</h3>
    </div>
    <hr>
    <div class="clearfix"></div>
    <form action="index.php" class="frmItemDetailSettings" name="frmItemDetailSettings">
        <input type="hidden" name="module" value="{$QUALIFIED_MODULE}"/>
        <input type="hidden" name="action" value="SaveAjax"/>
        <div class="summaryWidgetContainer">
            <ul class="nav nav-tabs massEditTabs">
                {foreach from=$SETTINGS name=SETTING_NAME  key=MODULE item=SETTING}
                    <li  class="{if $smarty.foreach.SETTING_NAME.first}active{/if} moduleTab_{$MODULE}"  >
                        <a href="#module_{$MODULE}" data-toggle="tab">
                            <strong>{vtranslate($MODULE, $MODULE)}</strong>
                        </a>
                    </li>
                {/foreach}
            </ul>

            <div class="tab-content massEditContent">
                {foreach from=$SETTINGS key=MODULE name=SETTING_NAME item=MODULE_SETTING}
                    {assign var = "TOTAL_SETTING" value=$TOTAL_SETTINGS.$MODULE}
                    {assign var = "SECTION_VALUES" value=$SECTIONS_SETTINGS.$MODULE}
                    <div class="tab-pane moduleTab  {if $smarty.foreach.SETTING_NAME.first}active{/if}" id="module_{$MODULE}">
                        <div class="row-fluid">
                            <div class="span6">
                                <input type="hidden" name="module_name" value="{$MODULE}">
                                <ul class="nav nav-pills " style="display:  block; margin-top: 10px; margin-bottom:0;">
                                    <li class='active'><a href="#ItemField_{$MODULE}"  data-toggle = "pill"><strong>{vtranslate('LBL_ITEMS', $QUALIFIED_MODULE)}</strong></a></li>
                                    <li><a href="#totalsTab_{$MODULE}"  data-toggle = "pill"><strong>{vtranslate('LBL_TOTALS', $QUALIFIED_MODULE)}</strong></a></li>
                                    <li id="activeSection{$MODULE}"><a href="#sectionTab_{$MODULE}"  data-toggle = "pill"><strong>{vtranslate('LBL_SECTIONS', $QUALIFIED_MODULE)}</strong></a></li>
                                </ul>
                            </div>

                            {**********List All Field************}
                            <div class="span6 select_field_container">
                                <span class="display_field_name"></span>
                                <span class="copy_icon"><img src="layouts\vlayout\modules\Quoter\images\copy-icon.png" alt=""/></span>
                                <select class="select2 select_field_name" style="width: 220px">
                                    <option value="0" selected>{vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}</option>
                                    {foreach from=$MODULE_SETTING item = SETTING key=COLUMN}
                                        {if in_array($SETTING->columnName,$COLUMN_DEFAULT)}
                                            <option value="{$SETTING->columnName}">{vtranslate($SETTING->columnName,$QUALIFIED_MODULE)}</option>
                                        {else}
                                            {if $SETTING->customHeader}
                                                <option value="{$SETTING->columnName}">{$SETTING->customHeader}</option>
                                            {/if}
                                        {/if}
                                    {/foreach}
                                    {foreach item=FIELD_VALUE key=FIELD_NAME from=$TOTAL_SETTING}
                                        <option value="{$FIELD_NAME}">{vtranslate($FIELD_VALUE.fieldLabel,'Quoter')}</option>
                                    {/foreach}
                                    {foreach from=$MODULE_SETTING['all_field'] item=FIELD_MODEL}
                                        <option value="{$FIELD_MODEL->get('name')}">{vtranslate($FIELD_MODEL->get('label'),$MODULE)}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="tab-content">

                            {********COLUMNS ITEMS*********}
                            <div class="tab-pane itemTab active" id="ItemField_{$MODULE}">
                                <div class="row-fluid colContainer " >
                                    {foreach from=$MODULE_SETTING item = SETTING key=COLUMN}
                                        {if is_numeric($COLUMN)}
                                            {include file="ColumnsDetails.tpl"|@vtemplate_path:$QUALIFIED_MODULE SETTING = $SETTING MODULE = $MODULE COLUMN_DEFAULT = $COLUMN_DEFAULT MAPPED_COLUMN=$MAPPED_COLUMNS[$MODULE][$COLUMN] }
                                        {/if}
                                    {/foreach}

                                </div>
                                <div class="base_column">
                                    {include file="ColumnsDetails.tpl"|@vtemplate_path:$QUALIFIED_MODULE SETTING = $MODULE_SETTING['base_column'] MODULE = $MODULE COLUMN_DEFAULT = $COLUMN_DEFAULT BASE = 'hide' MAPPED_COLUMN = $MAPPED_COLUMNS[$MODULE]['base_column'] }
                                </div>
                                <div style="margin-top: 20px;">
                                    <span class="pull-left">
                                        <button class="btn btn-success btnSaveSettings" type="button" >{vtranslate('LBL_SAVE')}</button>
                                    </span>
                                    <span class="pull-right">
                                        <button class="btn btnAddNewColumn" type="button" >
                                            <i class="icon-plus"></i> &nbsp; <strong>{vtranslate('LBL_ADD_NEW_COLUMN',$QUALIFIED_MODULE)}</strong>
                                        </button>
                                    </span>
                                    <span class="clearfix"></span>
                                </div>
                            </div>

                            {*********TOTAL FIELDS*********}
                            <div class="tab-pane totalTab" id="totalsTab_{$MODULE}">
                                <div class="row-fluid fieldTotalContainer" style="width: 80%; padding: 10px;" >
                                    <table class="table table-bordered blockContainer tblTotalFieldsContainer">
                                        <tbody>
                                            <tr>
                                                <th>{vtranslate('LBL_TOOLS',$QUALIFIED_MODULE)}</th>
                                                <th class="blockHeader">{vtranslate('LBL_LABEL_FIELD',$QUALIFIED_MODULE)}</th>
                                                <th class="blockHeader">{vtranslate('LBL_FORMULA',$QUALIFIED_MODULE)}</th>
                                                <th class="blockHeader">{vtranslate('LBL_DATA_ENTRY',$QUALIFIED_MODULE)}</th>
                                                <th class="blockHeader">{vtranslate('LBL_RUNNING_SUBTOTAL',$QUALIFIED_MODULE)}</th>
                                                <th>{vtranslate('LBL_SECTION_TOTAL',$QUALIFIED_MODULE)}</th>
                                            </tr>
                                            {if empty($TOTAL_SETTING)}
                                                {include file="TotalField.tpl"|@vtemplate_path:$QUALIFIED_MODULE FIELD_VALUE = array() FIELD_NAME='' }
                                            {else}
                                                {foreach item=FIELD_VALUE key=FIELD_NAME from=$TOTAL_SETTING}
                                                    {include file="TotalField.tpl"|@vtemplate_path:$QUALIFIED_MODULE FIELD_VALUE =  $FIELD_VALUE FIELD_NAME=$FIELD_NAME}
                                                {/foreach}
                                            {/if}

                                        </tbody>
                                    </table>
                                    <table class="hide fieldBasic">
                                        {include file="TotalField.tpl"|@vtemplate_path:$QUALIFIED_MODULE FIELD_VALUE = array() FIELD_NAME='' }
                                    </table>
                                </div>
                                <div style="margin-top: 20px;">
                                        <span class="pull-left">
                                            <button class="btn btn-success btnSaveTotalsSettingField" type="button" id="">{vtranslate('LBL_SAVE')}</button>
                                        </span>
                                        <span class="pull-right">
                                            <button class="btn addNewTotalField" type="button" >
                                                <i class="icon-plus"></i> &nbsp; <strong>{vtranslate('LBL_ADD_NEW_FIELD',$QUALIFIED_MODULE)}</strong>
                                            </button>
                                        </span>
                                    <span class="clearfix"></span>
                                </div>
                            </div>

                            {********SECTION********}
                            <div class="tab-pane sectionTab" id="sectionTab_{$MODULE}">
                                <div class="row-fluid sectionsContainer" style="width: 70%; padding: 10px;" >
                                    <table class="table table-bordered blockContainer tblSectionsContainer">
                                        <tbody>
                                            <tr>
                                                <th width="9%">{vtranslate('LBL_TOOLS',$QUALIFIED_MODULE)}</th>
                                                <th>{vtranslate('LBL_SECTIONS',$QUALIFIED_MODULE)}</th>
                                            </tr>
                                            {if empty($SECTION_VALUES)}
                                                <tr>
                                                    <td  style="vertical-align: middle; text-align: center;">
                                                        <img src="layouts/vlayout/skins/images/drag.png" class="moveIcon" border="0" title="Drag" style="cursor: move;">&nbsp;
                                                        <i class="icon-trash deleteSection cursorPointer" title="{vtranslate('LBL_DELETE',$MODULE)}"></i>
                                                    </td>
                                                    <td><input type="text" class="sectionValue" data-validation-engine="validate[required]" value=""></td>
                                                </tr>
                                            {else}
                                                {foreach item = SECTION_VALUE from=$SECTION_VALUES}
                                                    <tr>
                                                        <td style="vertical-align: middle; text-align: center;">
                                                            <img src="layouts/vlayout/skins/images/drag.png" class="moveIcon" border="0" title="Drag" style="cursor: move;">
                                                            &nbsp;
                                                            <i class="icon-trash deleteSection cursorPointer" title="{vtranslate('LBL_DELETE',$MODULE)}"></i>
                                                            &nbsp;&nbsp;
                                                            {*<span class="dropdown">*}
                                                                {*<a class="dropdown-toggle fieldInfo" data-toggle="dropdown" href="#" title="Show column name"><span class="icon-info-sign"></span></a>*}
                                                                {*<ul class="dropdown-menu _tooltip">*}
                                                                    {*<span style="color: #000">${$MODULE}_section_{$SECTION_VALUE}$</span>*}
                                                                {*</ul>*}
                                                            {*</span>*}

                                                        </td>
                                                        <td>
                                                            <input type="hidden" class="sectionOldValue" value="{$SECTION_VALUE}">
                                                            <input type="text" class="sectionValue" data-validation-engine="validate[required]" value="{$SECTION_VALUE}">
                                                        </td>
                                                    </tr>
                                                {/foreach}
                                            {/if}

                                        </tbody>
                                    </table>
                                    <table class="hide fieldBasic">
                                        <tr>
                                            <td style="vertical-align: middle; text-align: center;">
                                                <img src="layouts/vlayout/skins/images/drag.png" class="moveIcon" border="0" title="Drag" style="cursor: move;">
                                                &nbsp;
                                                <i class="icon-trash deleteSection cursorPointer" title="{vtranslate('LBL_DELETE',$MODULE)}"></i>
                                            </td>
                                            <td><input type="text" class="sectionValue" data-validation-engine="validate[required]" value=""></td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="margin-top: 20px;">
                                        <span class="pull-left">
                                            <button class="btn btn-success btnSaveSectionsValue" type="button" id="">{vtranslate('LBL_SAVE')}</button>
                                        </span>
                                        <span class="pull-right">
                                            <button class="btn addNewSection" type="button" >
                                                <i class="icon-plus"></i> &nbsp; <strong>{vtranslate('LBL_ADD_NEW_VALUE',$QUALIFIED_MODULE)}</strong>
                                            </button>
                                        </span>
                                    <span class="clearfix"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                {/foreach}
            </div>
        </div>
    </form>
</div>
{/strip}