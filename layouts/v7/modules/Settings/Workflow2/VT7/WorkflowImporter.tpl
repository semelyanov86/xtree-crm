<div class="modal-dialog modelContainer" style="width:600px;">
    {assign var=HEADER_TITLE value={vtranslate("Workflow Import","Settings:Workflow2")}}
    {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}

    <div class="modal-content">


    <form method="POST" id="WorkflowImportForm" action="index.php?module=Workflow2&parent=Settings&action=WorkflowImport" enctype="multipart/form-data">
    <div style="padding: 10px;">
        <div style="margin-bottom:10px;">
            <label>{vtranslate("LBL_SELECT_FILE", "Settings:Workflow2")}:</label>
            <input type="file" name="import">
        </div>
        <div style="margin-bottom:10px;">
            <label>{vtranslate("LBL_IMPORT_PASSWORD", "Settings:Workflow2")}: (optional)</label>
            <input type="text" id="password" class="defaultTextfield" name="password">
        </div>

            <fieldset style="border: 1px solid #ccc;border-radius:5px;padding: 10px;">
                <div style="margin-bottom:10px;">
                    <label for="name">{vtranslate("new workflow name", "Settings:Workflow2")}:</label>
                    <input type="text" class="defaultTextfield" id="workflow_name" name="workflow_name">
                    <span>Leer lassen für Orignalnamen</span>
                </div>
                <label for="name">{vtranslate("LBL_IMPORT_OVERWRITE_MODULE", "Settings:Workflow2")}:</label>
                <select name="workflow_module" id="workflow_module" disabled="disabled" class="defaultTextfield">
                    <option value="">Original module</option>
                    {foreach from=$modules item=label key=tabid}
                        <option value="{$label[0]}">{$label[1]}</option>
                    {/foreach}
                </select>
                &nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="workflow_checkbox" value="1" onclick="if(jQuery(this).prop('checked')) { jQuery('#workflow_module').removeAttr('disabled'); } else {  jQuery('#workflow_module').attr('disabled','disabled');  } ">
            {vtranslate("LBL_IMPORT_OVERWRITE_MODULE_ACTIVATE", "Settings:Workflow2")}
            </fieldset>
    </div>
        {include file='ModalFooter.tpl'|@vtemplate_path:$MODULE BUTTON_NAME=vtranslate('start import', $MODULE)}
    </form>
    </div>
</div>



