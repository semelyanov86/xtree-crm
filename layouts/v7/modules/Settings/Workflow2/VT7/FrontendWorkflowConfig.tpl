<div class="container-fluid" id="moduleManagerContents">
    <div class="editViewHeader">
        <h4>
            <div class="pull-right">
                <select class="select2" id="addWorkflow" style="width:400px;" data-placeholder="{vtranslate('choose a Workflow','Settings:Workflow2')}">
                    <option value=""></option>
                    {foreach from=$workflows item=workflowList key=moduleName}
                        <optgroup label="{$moduleName}">
                            {foreach from=$workflowList item=workflow}
                                <option value="{$workflow.id}">{$workflow.title}</option>
                            {/foreach}
                        </optgroup>
                    {/foreach}
                </select>
                <button type="submit" id="addWorkflowButton" class="btn btn-primary" style="margin-top:0;vertical-align:top;">{vtranslate('add Workflow', 'Settings:Workflow2')}</button>
            </div>

            <a href="index.php?module=Workflow2&view=Index&parent=Settings">{vtranslate('Workflow Designer', 'Workflow2')}</a> &raquo;
            {vtranslate('EditView Workflow Configurations', 'Settings:Workflow2')}
        </h4>
    </div>
    <hr/>
    <br>
    <div class="listViewActionsDiv">

        <div style="background-color:#ffffff;">
        {foreach from=$configWFs item=linkArray key=moduleName}
            <div class="FrontendWorkflowBlock Toggleable Invisible" data-panelid="mod_{$moduleName}">
                <div style="font-size:16px;overflow:hidden;font-weight:bold;text-transform:uppercase;color:#0b3161;" class="ToggleHandler">
                    <i class="fa fa-chevron-right"></i>
                    <i class="fa fa-chevron-down"></i>

                    <b>&nbsp;{$moduleName}</b>
                </div>
                <div class="ModuleFrontendWorkflows ToggleContent">
                    {foreach from=$linkArray item=workflow}
                            <div class="WorkflowFrontendContainerIntern Toggleable Invisible"data-panelid="wf_{$workflow.id}" >
                                <div class="WorkflowFrontainerHead ToggleHandler">
                                    <span class="pull-right">{if $workflow.active eq '1'}{vtranslate('LBL_ACTIVE', 'Settings:Workflow2')}{else}{vtranslate('LBL_INACTIVE', 'Settings:Workflow2')}{/if}</span>
                                    <i class="fa fa-chevron-right"></i>
                                    <i class="fa fa-chevron-down"></i>

                                    <strong>{$workflow.title}</strong>
                                </div>

                                <div class="WorkflowFrontendContentIntern ToggleContent" data-id="{$workflow.id}" data-workflowid="{$workflow.workflow_id}">
                                    <table class="table">
                                        <tr>
                                            <th style="width:40%;max-width:300px;">{vtranslate('LBL_ACTIVE', 'Settings:Workflow2')}</th>
                                            <td><input type="checkbox" class="ActivateToggle" {if $workflow.active eq '1'}checked="checked"{/if} name="active" value="1" /></td>
                                        </tr>
                                        <tr>
                                            <th>{vtranslate('LBL_CONFIG_EDITOR', 'Settings:Workflow2')}</th>
                                            <td>
                                                <button type="button" class="pull-right deleteConfig btn btn-danger">{vtranslate('LBL_REMOVE_RECORD', 'Settings:Workflow2')}</button>
                                                <button type="button" class="editConfig btn btn-primary">{vtranslate('open Editor', 'Settings:Workflow2')}</button>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </tr>
                    {/foreach}
                    </table>
                </div>
            </div>
        {/foreach}
        </div>
    </div>
</div>
<script type="text/javascript">
    var configurations = {$configurations|json_encode};
</script>
