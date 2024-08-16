<div class="modal-dialog modelContainer" style="width:600px;">
    {assign var=HEADER_TITLE value={vtranslate("Import Block by Text","Settings:Workflow2")}}
    {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}

    <div class="modal-content">

        <form method="POST" id="blockImportPopupForm"
              action="index.php?module=Workflow2&parent=Settings&action=BlockTextImport"
              enctype="multipart/form-data">
            <input type="hidden" name="workflow_id" value="{$workflowID}"/>

            <input type="hidden" name="position[x]" value="{$position.x}"/>
            <input type="hidden" name="position[y]" value="{$position.y}"/>

            <div style="padding: 10px;">{* Content Start *}
                <p>{vtranslate('Paste the text you exported in another vtigercrm system and click Save to import the blocks.', 'Settings:Workflow2')}</p>
                <p class="alert alert-info"><strong>{vtranslate('Pay Attention', 'Settings:Workflow2')}
                        :</strong> {vtranslate("If the other system would have different fieldnames, the configurations, which use fields probably will not work in this system.","Settings:Workflow2")}
                </p>
                <textarea name="data" style="font-family:'Courier New'; padding:10px; width:100%;height:200px;" id="BlockData">{$data}</textarea>
            </div> {* Content Ende *}
            <div class="modal-footer quickCreateActions">
                <a class="cancelLink cancelLinkContainer pull-right" type="reset"
                   data-dismiss="modal">{vtranslate('LBL_CLOSE', $MODULE)}</a>
                <button class="btn btn-success importBlocksBtn" type="submit" id="modalSubmitButton">
                    <strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
            </div>
        </form>
    </div>
</div>
</div>



