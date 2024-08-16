{*/* ********************************************************************************
* The content of this file is subject to the Field Autofill ("License");
* You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is VTExperts.com
* Portions created by VTExperts.com. are Copyright(C) VTExperts.com.
* All Rights Reserved.
* ****************************************************************************** */*}

<div class="container-fluid">
    <div class="widget_header row-fluid">
        <h3>{vtranslate('FieldAutofill', 'FieldAutofill')}</h3>
    </div>
    <hr>
    <div class="clearfix"></div>
    <div class="summaryWidgetContainer">
        <form action="index.php" method="post" id="FieldAutoFillSettings" class="form-horizontal">
            <input type="hidden" name="module" value="FieldAutofill">
            <input type="hidden" name="action" value="SaveSettings">
            <div class="form-group">
                <label for="modulesList"><b>{vtranslate('LBL_SELECT_MODULE', 'FieldAutofill')}</b></label>

            </div>

            <table class="table table-bordered blockContainer showInlineTable equalSplit" style="width: 500px;">
                <tr>
                    <th colspan="3" class="blockHeader">
                        <div style="margin-left:100px">
                            <select class="chzn-select" id="modulesList" name="modules" style="width: 300px;">
                                <option value="">{vtranslate('LBL_SELECT_OPTION', 'FieldAutofill')}</option>
                                {foreach key=PRIMODULE item=RELATED_MODULES from=$AVAILABLE_MODULES}
                                    <optgroup label="{vtranslate($PRIMODULE, $PRIMODULE)}">
                                        {foreach item=REL_MODULE from=$RELATED_MODULES}
                                            {assign var=MAPPING_NAME value=$PRIMODULE|cat:'_'|cat:$REL_MODULE}
                                            <option value="{$MAPPING_NAME}" {if $SELECTED_VAL eq $MAPPING_NAME}selected="" {/if}>{vtranslate($PRIMODULE, $PRIMODULE)} > {vtranslate($REL_MODULE, $REL_MODULE)}</option>
                                        {/foreach}
                                    </optgroup>
                                {/foreach}
                            </select>
                        </div>
                    </th>
                </tr>
            </table>
            <div id="mapped_field">
                {include file="modules/FieldAutofill/MappingFields.tpl"}
            </div>
            <br />
        </form>
    </div>
</div>