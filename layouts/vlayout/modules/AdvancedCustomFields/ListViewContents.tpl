{*/* ********************************************************************************
* The content of this file is subject to the Table Block ("License");
* You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is VTExperts.com
* Portions created by VTExperts.com. are Copyright(C) VTExperts.com.
* All Rights Reserved.
* ****************************************************************************** */*}

{strip}
<div class="listViewEntriesDiv" style='overflow-x:auto;'>
        {foreach item=LISTVIEW_ENTRY key=RECORDID from=$LISTVIEW_ENTRIES}
        <div style="margin-bottom:20px;" class="blockSortable" data-id="{$RECORDID}" data-sequence="{$LISTVIEW_ENTRY['sequence']}">
            <table class="table table-bordered table-condensed listViewEntriesTable">
                <thead>
                    <tr class="listViewHeaders">
                        {*<th width="1%" class="medium"><img src="{vimage_path('drag.png')}" class="alignTop" title="{vtranslate('LBL_DRAG',$QUALIFIED_MODULE)}" /></th>*}
                        <th width="15%" class="medium">{vtranslate('LBL_MODULE',$QUALIFIED_MODULE)}</th>
                        <th width="15%" class="medium">{vtranslate('LBL_BLOCK',$QUALIFIED_MODULE)}</th>
                        <th width="15%" class="medium">{vtranslate('LBL_FIELD_TYPE',$QUALIFIED_MODULE)}</th>
                        <th width="15%" class="medium">{vtranslate('LBL_NAME',$QUALIFIED_MODULE)}</th>
                        <th width="15%" class="medium">{vtranslate('LBL_LABEL',$QUALIFIED_MODULE)}</th>
                        <th width="5%" class="medium">
                                <span class="btn-group actions">
                                    <a class="editBlockDetails" href='javascript: void(0);' data-url="index.php?module=AdvancedCustomFields&view=EditAjax&mode=getEditForm&record={$RECORDID}">
                                        <i title="Edit" class="icon-pencil alignMiddle"></i>
                                    </a>
                                    &nbsp;&nbsp;
                                    <a class="deleteBlock" href="javascript:void(0);" data-id="{$RECORDID}">
                                        <i title="Delete" class="icon-trash alignMiddle"></i>
                                    </a>
                                </span>
                        </th>
                    </tr>
                </thead>
                <tbody>                    
                        <tr class="listViewEntries">
                            <td width="15%" style="vertical-align:top !important;" nowrap class="medium">{vtranslate($LISTVIEW_ENTRY['module'], $LISTVIEW_ENTRY['module'])}</td>
                            <td width="15%" style="vertical-align:top !important;" nowrap class="medium">
                                {vtranslate($LISTVIEW_ENTRY['block'], $LISTVIEW_ENTRY['module'])}
                            </td>
                            <td width="15%" nowrap class="medium">
                                {$LISTVIEW_ENTRY['field_type']}
                            </td>
                            <td width="15%" style="vertical-align:top !important;" nowrap class="medium">
                                {vtranslate($LISTVIEW_ENTRY['name'], $LISTVIEW_ENTRY['module'])}
                            </td>
                            <td width="40%" nowrap class="medium" colspan="2">
                                {vtranslate($LISTVIEW_ENTRY['label'], $LISTVIEW_ENTRY['module'])}
                            </td>
                            
                        </tr>
                    
                </tbody>
            </table>
        </div>
       {/foreach}                          

	<!--added this div for Temporarily -->
	{if $LISTVIEW_ENTRIES_COUNT eq '0'}
	<table class="emptyRecordsDiv">
		<tbody>
			<tr>
				<td>
					{vtranslate('LBL_NO')} {vtranslate($QUALIFIED_MODULE, $QUALIFIED_MODULE)} {vtranslate('LBL_FOUND')}
				</td>
			</tr>
		</tbody>
	</table>
	{/if}
</div>
{/strip}