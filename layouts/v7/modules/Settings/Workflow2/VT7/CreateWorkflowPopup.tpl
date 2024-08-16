<div class="modal-dialog modelContainer" style="width:600px;">
    {assign var=HEADER_TITLE value={vtranslate("Create new workflow","Settings:Workflow2")}}
    {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}

    <div class="modal-content">

    <form class="form-horizontal newWorkflowPopup">
        <div style="padding: 10px;">
            <div class="control-group">
                <label class="control-label" for="inputEmail">{vtranslate("Main Module", "Settings:Workflow2")}</label>
                <div class="controls">
                    <select name="new_workflow_module" style="width:100%;" class="select2" id="new_workflow_module">
                        {foreach from=$modules item=label key=tabid}
                            <option value="{$tabid}" {if $targetModule eq $tabid}selected="selected"{/if}>{$label[1]}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
            <div class="alert alert-danger ShowUsers" style="display:none;">
                {vtranslate('Users module <strong>ONLY</strong> support Workflows, triggered in Frontend!', 'Settings:Workflow2')}
                <script type="text/javascript">
                    var UsersModID = {getTabId('Users')};
                </script>
            </div>
            <div class="control-group">
                <label class="control-label" for="inputEmail">{vtranslate("LBL_START_CONDITION", "Settings:Workflow2")}</label>
                <div class="controls">
                    <select name="runtime" id="workflow_trigger" class="select2" style="width:100%;">
                         {html_options options=$trigger selected=$task.runtime}
                    </select><br/>
                    <span><em>{vtranslate('The trigger define the situation, when this Workflow is executed.', 'Settings:Workflow2')}</em></span>
                </div>
            </div>

            {if $folderView eq true}
            <div class="control-group">
                <label class="control-label" for="inputEmail">{vtranslate("Folder name", "Settings:Workflow2")}</label>
                <div class="controls">
                    <input type="text" name="WorkflowFolderName" value="{$presetFolderName}" style="width:100%;box-sizing: border-box; height:30px;"/>
                </div>
            </div>
            {/if}
        </div>
    </form>
    {*<div class="modal-footer quickCreateActions">*}
            {*<a class="cancelLink cancelLinkContainer pull-right" type="reset" data-dismiss="modal">{vtranslate('LBL_CLOSE', "Settings:Workflow2")}</a>*}
        {*<button class="btn btn-success" type="submit" id="modalSubmitButton" ><strong>{vtranslate('create Workflow', "Settings:Workflow2")}</strong></button>*}
   	{*</div>*}
        {include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
    </div>
</div>



