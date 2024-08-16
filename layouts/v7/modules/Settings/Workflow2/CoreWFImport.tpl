<div class="container-fluid" id="moduleManagerContents">

    <div class="widget_header row-fluid">
        <div class="span12">
            <h3>
                <b>
                    <a href="index.php?module=Workflow2&view=Index&parent=Settings">{vtranslate('Workflow2', 'Workflow2')}</a> &raquo;
                    {vtranslate('Core Workflow Importer', 'Settings:Workflow2')}
                </b>
            </h3>
        </div>
    </div>
    <hr>
    <div>
        <table class="table table-condensed">
        {foreach from=$workflows key=moduleName item=workflowlist}
            <tr>
                <th colspan="3">{vtranslate($moduleName, $moduleName)}</th>
            </tr>
            {foreach from=$workflowlist item=workflow}
                <tr>
                    <td><input type="checkbox" name="import[]" value="{$workflow.workflow_id}" /></td>
                    <td>{$workflow.summary}</td>
                    <td>
                        {if $workflow.execution_condition eq '1'}
                            only on first save
                        {/if}
                        {if $workflow.execution_condition eq '2'}
                            until the first time condition is true
                        {/if}
                        {if $workflow.execution_condition eq '3'}
                            every time the record is saved
                        {/if}
                        {if $workflow.execution_condition eq '4'}
                            every time the record is modified
                        {/if}
                        {if $workflow.execution_condition eq '6'}
                            Schedule
                        {/if}
                    </td>
                </tr>
            {/foreach}
        {/foreach}
        </table>
    </div>
</div>