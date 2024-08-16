{*<!--
/*********************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
-->*}
<!-- BEGIN: main -->
{literal}
<style type="text/css">
    .deactiveWf2Tab {
        background-color:#eeeeee;border-bottom:1px solid #cccccc;cursor:pointer;
    }
</style>
{/literal}
<form action="index.php?module=Workflow2&action=Importer" method="post" enctype="multipart/form-data" id="execWorkflow" name="execWorkflow">
<input name='execute_mode' id="execute_mode" type='hidden' value='execute'>
<input name='record_ids' id="WFLV_record_ids" type='hidden' value='{$record_ids}'>
<input name='return_module' id="WFLV_return_module" type='hidden' value='{$return_module}'>
<div id="roleLayWorkflow2" style="z-index:12;display:block;width:400px;" class="layerPopup">
	<table border=0 cellspacing=0 cellpadding=5 width=100% class=layerHeadingULine>
		<tr>
			<td width="90%" align="left" class="genHeaderSmall" id="Workflow2ViewDivHandle" style="cursor:move;">{$MOD.SELECT_WORKFLOW}&nbsp;
			</td>
			<td width="10%" align="right">
				<a href="javascript:fninvsh('roleLayWorkflow2');"><img title="{$APP.LBL_CLOSE}" alt="{$APP.LBL_CLOSE}" src="{'close.gif'|@vtiger_imageurl:$THEME}" border="0"  align="absmiddle" /></a>
			</td>
		</tr>
	</table>

	<table border=0 cellspacing=0 cellpadding=5 width=95% align=center>
		<tr><td class="small">
			<table border=0 cellspacing=0 cellpadding=5 width=100% align=center bgcolor=white>
				<tr>
					<td align="left">
                     	<table class="small" border="0" cellpadding="3" cellspacing="0" width="100%">
    					<tbody>
                        <tr>
    					    <td colspan=2 nowrap id="wf2popup_wf_execute_TAB" class="dvtSelectedCell" onclick="showWf2PopupContent('#wf2popup_wf_execute');">
    							<b>{$MOD.LBL_WORKFLOW2_EXECUTE}</b>
    						</td>
    					    <td nowrap class="dvtSelectedCell deactiveWf2Tab" id="wf2popup_wf_importer_TAB"  onclick="showWf2PopupContent('#wf2popup_wf_importer');">
    							<b>{$MOD.LBL_IMPORTER}</b>
    						</td>
    				    </tr>
                        </table>
                    <div id="wf2popup_wf_execute">
                    <table border=0 cellspacing=0 cellpadding=5 width=100% align=center bgcolor=white>
                        <tr>
                            <td class="dvtCellLabel" colspan="3" style="padding:10px;">
                                <select name="exec_this_workflow" id="exec_this_workflow" class="detailedViewTextBox" style="width:90%;">
                                  {html_options  options=$workflow_options}
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td  class="dvtCellLabel" colspan="1" style="padding:10px;">
                                <input type="checkbox" name="exec_workflow_parallel" id="exec_workflow_parallel">
                            </td>
                            <td class="dvtCellLabel"  colspan="2" alt="{$MOD.LBL_ALLOW_PARALLEL_EXECUTION}" title="{$MOD.LBL_ALLOW_PARALLEL_EXECUTION}">
                                {$MOD.LBL_PARALLEL_ALLOWED}
                            </td>
                        </tr>
    					</tbody>
    					</table>
                    </div>
                    <div id="wf2popup_wf_importer" style="display:none;">
                    <table border=0 cellspacing=0 cellpadding=5 width=100% align=center bgcolor=white>
                        <tr>
                            <td class="dvtCellLabel" colspan="3" style="padding:10px;">
                                CSV File:
                                <input type="file" name="importfile">
                            </td>
                        </tr>
                        <tr>
                            <td class="dvtCellLabel" colspan="3" style="padding:10px;">
                                Delimiter:
                                <input type="text" name="import_delimiter" value=",">
                            </td>
                        </tr>
                        <tr>
                            <td class="dvtCellLabel" colspan="3" style="padding:10px;">
                                <select name="exec_this_workflow" id="exec_this_import_workflow" class="detailedViewTextBox" style="width:90%;">
                                  {html_options  options=$import_workflow_options}
                                </select>
                            </td>
                        </tr>
    					</tbody>
    					</table>
                    </div>
					</td>
				</tr>
                <tr>
                    <td colspan="3">
                        <div style='display:none;' id='executionProgress'>{$MOD.LBL_PROGRESS}: <span id='executionProgress_Value'></span></div>
                    </td>
                </tr>
			</table>
		</td></tr>
	</table>
	<table border=0 cellspacing=0 cellpadding=5 width=100% class="layerPopupTransport">
		<tr><td align=center class="small">
			<input type="button" name="{$APP.LBL_CANCEL_BUTTON_LABEL}" value=" {$APP.LBL_CANCEL_BUTTON_LABEL} " class="crmbutton small cancel" onclick="fninvsh('roleLayWorkflow2');" />&nbsp;&nbsp;
            <input type="submit" name="{$APP.LBL_SELECT_BUTTON_LABEL}" value=" {$APP.LBL_SELECT_BUTTON_LABEL} " class="crmbutton small create" onClick="return executeLVWorkflow();"/>
		</td></tr>
	</table>
</div>
</form>