<div class="modal-dialog modelContainer" style="width:600px;">
    {assign var=HEADER_TITLE value={vtranslate("Select Workflow to execute","Settings:Workflow2")}}
    {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}

    <div class="modal-content">
        <input type="hidden" class="WorkflowPopupCRMID" value="{$crmid}" />

        <div class="row" style="padding:10px;">
            <div class="col-lg-6">
                {if $show_listview eq true}
                    {if count($workflows) gt 0}
                        <select name="workflow2_workflowid" id="workflow2_workflowid" size=7 class="detailedViewTextBox" style="width:100%;">
                            <!--<option value='0'><?php echo getTranslatedString("LBL_CHOOSE", "Workflow2"); ?></option>-->
                            {foreach from=$workflows item=workflow}
                                <option value='{$workflow.id}' data-withoutrecord="{$workflow.withoutrecord}" data-collection="{$workflow.collection_process}">{$workflow.title}</option>
                            {/foreach}
                        </select>
                        {*<button class="btn btn-success"  onclick="runSidebarWorkflow('{$crmid}');"name='runWorkfow' >{vtranslate('execute','Workflow2')}</button>*}
                    {else}
                        <span style="color:#777;font-style:italic;">{vtranslate('LBL_NO_WORKFLOWS','Workflow2')}</span>
                    {/if}
                {/if}

                {if $isAdmin eq true}
                    <a class="pull-left" href="#" onclick="showEntityData('{$crmid}');return false;" name='showEntityData'>{vtranslate('BTN_SHOW_ENTITYDATA','Workflow2')}</a>
                {/if}

            </div>
            <div class="col-lg-6">
                {if $hide_importer neq true}
                    <button class="btn btn-info" style="width:100%;" onclick="WorkflowHandler.startImport('{$source_module}');">{vtranslate('Import Prozess starten', 'Settings:Workflow2')}</button>
                    <hr/>
                {/if}

                <div class="WorkflowDesignerButtons">
                    {foreach from=$buttons item=button}
                        {if $button.color neq 'separator'}
                            <button type="button" data-crmid="{$crmid}" class="btn"  data-collection="{$button.collection_process}" onclick="var workflow = new Workflow();workflow.execute({$button.workflow_id}, {$crmid});" alt="execute this workflow"  title="execute this workflow" style="text-shadow:none;color:{$button.textcolor}; background-color: {$button.color};margin-top:2px;width:100%;">{$button.label}</button><br/>
                        {else}
                            {if empty($button.label)}
                                <hr/>
                            {else}
                                <div class="WFdivider"><span>{$button.label}</span></div>
                            {/if}

                        {/if}
                    {/foreach}
                </div>
                {if count($waiting) gt 0}
                    <p><strong>{vtranslate("running Workflows with this record","Workflow2")}:</strong></p>
                    <table width='238' cellspacing=0  style="font-size:10px;">
                        {foreach from=$waiting item=workflow}
                            <tr>
                                {if $isAdmin eq true}
                                    <td style='border-top:1px solid #ccc;' colspan=2><a href='index.php?module=Workflow2&view=Config&parent=Settings&workflow={$workflow.workflow_id}'>{$workflow.title}</a></td>
                                {else}
                                    <td style='border-top:1px solid #ccc;' colspan=2>{$workflow.title}</td>
                                {/if}
                            </tr>
                            <tr>
                                <td colspan=2><strong>{$workflow.text}</strong></td>
                            </tr>
                            <tr>
                                <td style='border-bottom:1px solid #ccc;'>
                                    <a href='#' onclick='return stopWorkflow("{$workflow.execid}","{$workflow.crmid}","{$workflow.block_id}");return false;'>del</a> |
                                    <a href='#' onclick='return continueWorkflow("{$workflow.execid}","{$workflow.crmid}","{$workflow.block_id}");return false;'>continue</a>
                                </td>
                                <td style='text-align:right;border-bottom:1px solid #ccc;'>{DateTimeField::convertToUserFormat(VtUtils::convertToUserTZ($workflow.nextsteptime))}</td>
                            </tr>
                        {/foreach}
                    </table>
                {/if}
            </div>
        </div>

        <div class="modal-footer ">
            <center>
                <button {if $BUTTON_ID neq null} id="{$BUTTON_ID}" {/if} class="btn btn-success" type="submit" name="saveButton"><strong>{vtranslate('LBL_WORKFLOW2_EXECUTE', 'Workflow2')}</strong></button>
                <a href="#" class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CLOSE', 'Workflow2')}</a>
            </center>
        </div>
    </div>
</div>



