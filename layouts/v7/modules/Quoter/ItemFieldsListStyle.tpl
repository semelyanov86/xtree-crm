{*/* * *******************************************************************************
* The content of this file is subject to the Quoter ("License");
* You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is VTExperts.com
* Portions created by VTExperts.com. are Copyright(C)VTExperts.com.
* All Rights Reserved.
* ****************************************************************************** */*}
{strip}
    {if in_array($SETTING->columnName,$COLUMN_DEFAULT)}
        {assign var = IS_DEFAULT value = 1}
    {else}
        {assign var = IS_DEFAULT value = 0}
    {/if}
    {if is_numeric($COLUMN)}
        {assign var = INDEX value = $COLUMN}
    {else}
        {assign var = INDEX value = ''}
    {/if}
    <table class="table table-bordered tblItemsFieldsContainer">
        <tbody>
        <tr>
            <th>Action</th>
            <th>Field Name</th>
            <th>
                <span class=" textAlignRight">
                    {vtranslate('LBL_PRODUCT',$QUALIFIED_MODULE)}&nbsp;
                    <a class="info-icon" data-toggle="popover" data-placement="top" style="color: #333" data-content="Select field to be prefilled from product. Optional" href="javascript:void(0)"><i class="fa fa fa-info-circle"></i></a>
                </span>
            </th>
            <th>
                <span class=" textAlignRight" >
                    {vtranslate('LBL_SERVICE',$QUALIFIED_MODULE)}&nbsp;
                    <a class="info-icon" data-toggle="popover" data-placement="top" style="color: #333" data-content="Select field to be prefilled from service. Optional" href="javascript:void(0)"><i class="fa fa fa-info-circle"></i></a>
                </span>
            </th>
            <th>
                <span class=" textAlignRight" >
                    {vtranslate('LBL_MANDATORY',$QUALIFIED_MODULE)}&nbsp;
                    <a class="info-icon" data-toggle="popover" data-placement="top" style="color: #333" data-content="If set to yes, the user will be required to enter a value in this column." href="javascript:void(0)"><i class="fa fa fa-info-circle"></i></a>
                </span>
            </th>
            <th>
                <span class=" textAlignRight" >{vtranslate('Active')}</span></th>
            <th>
                <span class=" textAlignRight" >
                    {vtranslate('Editable')}&nbsp;
                    <a class="info-icon" data-toggle="popover" data-placement="top" style="color: #333" data-content="If set to yes, the field will be read only for the user. The formulas will still apply, however user will not be able to change the value." href="javascript:void(0)"><i class="fa fa fa-info-circle"></i></a>
                </span>
            </th>
            <th>
                <span class=" textAlignRight">
                    {{vtranslate('LBL_WIDTH',$QUALIFIED_MODULE)}}&nbsp;
                    <a class="info-icon" data-toggle="popover" data-placement="top" style="color: #333" data-content='Default is 100%. You can also put it 20px, 40px, etc. Note, that all columns default to 100%, if you want to change the width of any column, you will need to update others as well, so it all adds up to 100%. Would be something like "Item Name=20%, Description=20%, Quantity, 7%, ListPrice=7%, etc...' href="javascript:void(0)"><i class="fa fa fa-info-circle"></i></a>
                </span>
            </th>
            <th>
                <div class="textAlignCenter" style="width: 100%;margin-bottom: 5px;" >
                    <strong style="font-size: 1em;">{{vtranslate('LBL_FORMULA',$QUALIFIED_MODULE)}}</strong>&nbsp;
                    <a class="info-icon" data-toggle="popover" data-placement="top" style="color: #333" data-content='Create your own formula utilizing any of the columns or fields from the record. Please see documentation for more details.' href="javascript:void(0)"><i class="fa fa fa-info-circle"></i></a>
                </div>
            </th>
        </tr>
        {foreach from=$MODULE_SETTING item = SETTING key=COLUMN}
            {if is_numeric($COLUMN)}
                {include file="ItemFieldDetailsListStyle.tpl"|@vtemplate_path:$QUALIFIED_MODULE SETTING = $SETTING MODULE = $MODULE COLUMN_DEFAULT = $COLUMN_DEFAULT MAPPED_COLUMN=$MAPPED_COLUMNS[$MODULE][$COLUMN] }
            {/if}
        {/foreach}
        <!-- base column -->
        {include file="ItemFieldDetailsListStyle.tpl"|@vtemplate_path:$QUALIFIED_MODULE SETTING = $MODULE_SETTING['base_column'] MODULE = $MODULE COLUMN_DEFAULT = $COLUMN_DEFAULT BASE = 'hide' BASE_CLASS = 'base_column' MAPPED_COLUMN = $MAPPED_COLUMNS[$MODULE]['base_column'] }
        </tbody>
    </table>
{/strip}