{*/* ********************************************************************************
* The content of this file is subject to the Table Block ("License");
* You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is VTExperts.com
* Portions created by VTExperts.com. are Copyright(C) VTExperts.com.
* All Rights Reserved.
* ****************************************************************************** */*}

<div class="container-fluid">
    <div class="widget_header row-fluid">
        <h3>{vtranslate($QUALIFIED_MODULE, $QUALIFIED_MODULE)}</h3>
    </div>
    <hr>
    <div class="row-fluid">
        <span class="span8 btn-toolbar">
            <button class="btn addButton" data-url="index.php?module=AdvancedCustomFields&view=EditAjax&mode=getEditForm">
                <i class="icon-plus"></i>&nbsp;<strong>{vtranslate('LBL_ADD', $QUALIFIED_MODULE)} {vtranslate($QUALIFIED_MODULE, $QUALIFIED_MODULE)}</strong>
            </button>
        </span>
        <span class="span4">
            <div class="pull-right">
                <select class="select2 span3" id="tableBlockModules">
                    <option value="All">All</option>
                    {foreach item=MODULE_NAME from=$SUPPORTED_MODULES}
                        <option value="{$MODULE_NAME}" {if $MODULE_NAME eq $SELECTED_MODULE_NAME} selected {/if}>{vtranslate($MODULE_NAME, $MODULE_NAME)}</option>
                    {/foreach}
                </select>
            </div>
        </span>
    </div>
    <div class="listViewContentDiv" id="listViewContents">
        <br>{include file='ListViewContents.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
    </div>
</div>