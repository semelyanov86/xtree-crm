{*/* ********************************************************************************
* The content of this file is subject to the Table Block ("License");
* You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is VTExperts.com
* Portions created by VTExperts.com. are Copyright(C) VTExperts.com.
* All Rights Reserved.
* ****************************************************************************** */*}
<table>
    <tr>
        <td>
            <div id="formula_field_cb">
                    <label class="muted control-label">
                        &nbsp;<strong>{vtranslate('LBL_BLOCK',$QUALIFIED_MODULE)}</strong>
                    </label>
                    <div class="controls row-fluid">
                        <select class="select2" id="block" name="block"  style="width: 225px;">
                            {foreach from=$BLOCKS key=FIELD_LBL item=BLOCK}
                                <option value="{$BLOCK ->get("id")}" {if {$BLOCK ->get("id")} eq $BLOCK_DATA['block']}selected{/if}>
                                                            {vtranslate($BLOCK ->get("label"),$SELECTED_MODULE_NAME)}</option>
                            {/foreach}
                        </select>
                    </div>
            </div>  
        </td>
    </tr>
</table>    