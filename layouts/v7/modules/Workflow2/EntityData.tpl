<div class="modelContainer" style="width:550px;">
    <form method="POST" id="WorkflowImportForm" action="index.php?module=Workflow2&parent=Settings&action=WorkflowImport" enctype="multipart/form-data">
<div class="modal-header contentsBackground">
	<button class="close" aria-hidden="true" data-dismiss="modal" type="button" title="{vtranslate('LBL_CLOSE')}">x</button>
    <h3>EntityData Viewer ID {$crmid}</h3>
</div>

<table style="width: 550px;">
{foreach from=$entityData item=data}
<tr style="background-color: #eeeeee;line-height: 28px;">
    <td width="350" style="font-weight: bold;padding-left:5px;"><img class="EntityDataDelete" data-id="{$data.dataid}" src="modules/Workflow2/icons/cross-button.png" style="margin-bottom:-3px;" />&nbsp;&nbsp;{$data.key}</td>
    <td width="150" style="text-align: right;padding-right:5px;" alt="{vtranslate('LBL_ENTITYDATA_MODIFYDATE', 'Workflow2')}" title="{vtranslate('LBL_ENTITYDATA_MODIFYDATE', 'Workflow2')}">{$data.modified}</td>
</tr>
    <tr>
        <td width="350" colspan="2" style="font-family: 'Courier New';margin-bottom: 10px;padding: 5px;">{$data.value}</td>
    </tr>
{/foreach}
</table>
        <div class="modal-footer quickCreateActions" style="margin-top: 10px;">
            <button class="btn btn-warning cancelLinkContainer" type="button" onclick="app.hideModalWindow();" style="padding: 5px 10px;"><strong>{vtranslate('LBL_CLOSE', $MODULE)}</strong></button>
       	</div>
        </form>
    </div>


