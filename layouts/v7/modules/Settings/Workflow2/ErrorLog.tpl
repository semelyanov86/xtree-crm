<div id="popupPageContainer" class="popupBackgroundColor">
	<div id="popupContents" class="paddingLeftRight10px">
        <table class="tableHeading"  width="100%" border="0" cellspacing="0" cellpadding="5">
        	<tr>
        		<td class="big" nowrap="nowrap">
        			<strong>{vtranslate('HEAD_ERRORS_FOR_WORKFLOW','Settings:Workflow2')} {$workflow_id}</strong>
        		</td>
        		<td class="small" align="right">
        			<input type="button" id="edittask_cancel_button" onclick="window.close();" class="crmbutton small cancel" value="{$MOD.close}">
        		</td>
        	</tr>
        </table>
         <table style="background-color: #fff;">
             <tr>
                 <th style="color: #000;" width=80>BlockID</th>
                 <th style="color: #000;" >Error Message</th>
                 <th style="color: #000;" width=150 align="right">Error Date</th>
             </tr>
         {foreach item=error from=$errors}
             <tr>
                 <td>{$error.block_id}</td>
                 <td>{$error.text}</td>
                 <td align="right">{$error.datum_eintrag}</td>
             </tr>
         {/foreach}
         </table>

	</div>
</div>
</div>

