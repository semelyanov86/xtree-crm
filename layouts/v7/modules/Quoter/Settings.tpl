{*/* * *******************************************************************************
* The content of this file is subject to the Quoter ("License");
* You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is VTExperts.com
* Portions created by VTExperts.com. are Copyright(C)VTExperts.com.
* All Rights Reserved.
* ****************************************************************************** */*}
{strip}
    <link type="text/css" rel="stylesheet" href="libraries/jquery/bootstrapswitch/css/bootstrap2/bootstrap-switch.min.css" media="screen">
    <script type="text/javascript" src="libraries/jquery/bootstrapswitch/js/bootstrap-switch.min.js"></script>
<div class="container-fluid">
    <h4>{vtranslate('Item Details Customizer (Advanced)', $QUALIFIED_MODULE)}</h4>
    <hr>
    <div class="clearfix"></div>
    <input type="hidden" name="module" value="{$QUALIFIED_MODULE}"/>
    <input type="hidden" name="action" value="SaveAjax"/>
    <div class="related-tabs summaryWidgetContainer ">
        <ul class="nav nav-tabs">
            {foreach from=$SETTINGS name=SETTING_NAME  key=MODULE item=SETTING}
                <li  class="tab-item {if $smarty.foreach.SETTING_NAME.first}active{/if} moduleTab_{$MODULE}" >
                    <a href="#module_{$MODULE}" data-toggle="tab" class="textOverflowEllipsis moduleTab">
                        <strong>{vtranslate($MODULE, $MODULE)}</strong>
                    </a>
                </li>
            {/foreach}
            <li class="pull-right">
                <a class="hide" target="_blank" href="https://www.vtexperts.com/vtiger-item-details-customizer-advanced-upgrading-vtiger-7/">{vtranslate('LBL_UPGRADING_FROM_VTIGER6',$QUALIFIED_MODULE)}</a>
            </li>
        </ul>
        <div class="tab-content col-lg-12 col-md-12" style="border: 0px;">
            {foreach from=$SETTINGS key=MODULE name=SETTING_NAME item=MODULE_SETTING}
                {assign var = "TOTAL_SETTING" value=$TOTAL_SETTINGS.$MODULE}
                {assign var = "SECTION_VALUES" value=$SECTIONS_SETTINGS.$MODULE}
                <div class="tab-pane moduleTab  {if $smarty.foreach.SETTING_NAME.first}active{/if}" id="module_{$MODULE}">
                    <div class="row" style="margin-top: 20px;">
                        <div class="col-lg-8 col-md-8">
                            <div class="row">
                                <div class="col-lg-6 col-md-6"></div>
                                <div class="col-lg-6 col-md-6">
                                    <input type="hidden" name="module_name" value="{$MODULE}">
                                    <ul class="nav nav-pills" style="display:  block; margin-top: 10px; margin-bottom:0;">
                                        <li role="presentation" class='active'><a style="width: 92px;text-align: center;" href="#ItemField_{$MODULE}"  data-toggle = "pill">{vtranslate('LBL_ITEMS', $QUALIFIED_MODULE)}</a></li>
                                        <li role="presentation"><a style="width: 92px;text-align: center;" href="#totalsTab_{$MODULE}"  data-toggle = "pill">{vtranslate('LBL_TOTALS', $QUALIFIED_MODULE)}</a></li>
                                        <li role="presentation" id="activeSection{$MODULE}"><a style="width: 92px;text-align: center;" href="#sectionTab_{$MODULE}"  data-toggle = "pill">{vtranslate('LBL_SECTIONS', $QUALIFIED_MODULE)}</a></li>
                                    </ul>
                                </div>
                            </div>

                        </div>

                        {**********List All Field************}
                        <div class="col-lg-4 col-md-4 select_field_container">
                            <span class="display_field_name"></span>
                            <span class="hide copy_icon"><img src="layouts\vlayout\modules\Quoter\images\copy-icon.png" alt=""/></span>
                            <select class="select2 select_field_name" style="width: 220px">
                                <option value="">{vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}</option>
                                {*
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
                                *}
                                {foreach from=$MODULE_SETTING['all_field'] item=FIELD_MODEL}
                                    <option value="{$FIELD_MODEL->get('name')}">({vtranslate($MODULE,$MODULE)}) {vtranslate($FIELD_MODEL->get('label'),$MODULE)}</option>
                                {/foreach}
                            </select>
                            &nbsp;<a class="info-icon" data-toggle="popover" data-placement="bottom" style="color: #000" data-content='This is a list of all available fields on this module. You can use these fields in formulas for totals/columns. Select the field and value will show up next to it. Next, copy that value and incorporate into your formula.' href="javascript:void(0)"><i class="fa fa fa-info-circle"></i></a>
                        </div>
                    </div>
                    <div class="tab-content fieldBlockContainer" style="border: 0px;">
                        {********COLUMNS ITEMS*********}
                        <div class="tab-pane itemTab active" id="ItemField_{$MODULE}">
                            <form name="frmColumnListType" class="frmColumnListType">
                                <div class="colContainer " style="padding: 20px 0 0px 0;">
                                    {include file="ItemFieldsListStyle.tpl"|@vtemplate_path:$QUALIFIED_MODULE MODULE_SETTING = $MODULE_SETTING}
                                </div>
                                <div style="margin-top: 5px; margin-bottom: 5px; text-align: left;">
                                    <button class="btn btn-default btnAddNewColumn" type="button" >
                                        <i class="fa fa-plus"></i> &nbsp; <strong>{vtranslate('LBL_ADD_NEW_COLUMN',$QUALIFIED_MODULE)}</strong>
                                    </button>
                                </div>
                                <div style="margin-top: 0px; margin-bottom: 10px;text-align: center;">
                                    <span class="">
                                        <button class="btn btn-success btnSaveSettings" type="submit" >{vtranslate('LBL_SAVE')}</button>
                                    </span>
                                    <span class="pull-right">

                                    </span>
                                    <span class="clearfix"></span>
                                </div>
                            </form>
                        </div>

                        {*********TOTAL FIELDS*********}
                        <div class="tab-pane totalTab" id="totalsTab_{$MODULE}">
                            <form name="frmTotal" class="frmTotal">
                                <div class="fieldTotalContainer" style="padding-top: 10px;" >
                                    <table class="table table-bordered tblTotalFieldsContainer">
                                        <tbody>
                                            <tr>
                                                <th>{vtranslate('LBL_TOOLS',$QUALIFIED_MODULE)}</th>
                                                <th>{vtranslate('LBL_LABEL_FIELD',$QUALIFIED_MODULE)}</th>
                                                <th>{vtranslate('LBL_FORMULA',$QUALIFIED_MODULE)}</th>
                                                <th>{vtranslate('LBL_DATA_ENTRY',$QUALIFIED_MODULE)}</th>
                                                <th>{vtranslate('LBL_RUNNING_SUBTOTAL',$QUALIFIED_MODULE)}</th>
                                                <th>{vtranslate('Sections',$QUALIFIED_MODULE)}</th>
                                                <th>{vtranslate('Active',$QUALIFIED_MODULE)}</th>
                                            </tr>
                                            {if empty($TOTAL_SETTING)}
                                                {include file="TotalField.tpl"|@vtemplate_path:$QUALIFIED_MODULE FIELD_VALUE = array() FIELD_NAME='' }
                                            {else}
                                                {foreach item=FIELD_VALUE key=FIELD_NAME from=$TOTAL_SETTING name = total_field}
                                                    {assign var = INDEX_TOTAL value=$smarty.foreach.total_field.iteration-1}
                                                    {include file="TotalField.tpl"|@vtemplate_path:$QUALIFIED_MODULE FIELD_VALUE =  $FIELD_VALUE FIELD_NAME=$FIELD_NAME}
                                                {/foreach}
                                            {/if}

                                        </tbody>
                                    </table>
                                    <table class="hide fieldBasic">
                                        {include file="TotalField.tpl"|@vtemplate_path:$QUALIFIED_MODULE FIELD_VALUE = array() FIELD_NAME='' }
                                    </table>
                                </div>
                                <div style="margin-top: 0px; margin-bottom: 10px; text-align: left;">
                                    <button class="btn btn-default addNewTotalField" type="button" >
                                        <i class="fa fa-plus"></i> &nbsp; <strong>{vtranslate('LBL_ADD_NEW_FIELD',$QUALIFIED_MODULE)}</strong>
                                    </button>
                                </div>
                                <div style="margin-top: 0px;text-align: center;">
                                        <span class="">
                                            <button class="btn btn-success btnSaveTotalsSettingField" type="submit" id="">{vtranslate('LBL_SAVE')}</button>
                                        </span>
                                        <span class="pull-right">

                                        </span>
                                    <span class="clearfix"></span>
                                </div>
                            </form>
                        </div>

                        {********SECTION********}
                        <div class="tab-pane sectionTab" id="sectionTab_{$MODULE}">
                            <form name="frmSection" class="frmSection">
                                <div class="row-fluid sectionsContainer" style="width: 70%; padding: 10px;" >
                                    <table class="table table-bordered blockContainer tblSectionsContainer">
                                        <tbody>
                                            <tr>
                                                <th width="9%">{vtranslate('LBL_TOOLS',$QUALIFIED_MODULE)}</th>
                                                <th>{vtranslate('LBL_SECTIONS',$QUALIFIED_MODULE)}</th>
                                            </tr>
                                            {if empty($SECTION_VALUES)}
                                                {include file="Section.tpl"|@vtemplate_path:$QUALIFIED_MODULE SECTION_VALUE = '' INDEX_SECTION = ''}
                                            {else}
                                                {foreach item = SECTION_VALUE key=INDEX_SECTION from=$SECTION_VALUES}
                                                    {include file="Section.tpl"|@vtemplate_path:$QUALIFIED_MODULE SECTION_VALUE = $SECTION_VALUE}
                                                {/foreach}
                                            {/if}

                                        </tbody>
                                    </table>
                                    <table class="hide fieldBasic">
                                        {include file="Section.tpl"|@vtemplate_path:$QUALIFIED_MODULE SECTION_VALUE = '' INDEX_SECTION = ''}
                                    </table>
                                </div>
                                <div style="margin-bottom: 10px; margin-top: 10px; text-align: left;">
                                    <button class="btn btn-default addNewSection" type="button" >
                                        <i class="fa fa-plus"></i> &nbsp; <strong>{vtranslate('LBL_ADD_NEW_VALUE',$QUALIFIED_MODULE)}</strong>
                                    </button>
                                </div>
                                <div style="margin-top: 0px;text-align: center;">
                                    <span class="">
                                        <button class="btn btn-success btnSaveSectionsValue" type="submit" >{vtranslate('LBL_SAVE')}</button>
                                    </span>
                                    <span class="pull-right">

                                    </span>
                                    <span class="clearfix"></span>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>
    </div>
</div>
{/strip}