<style type="text/css">
    .WFdivider{
      display:table;
      text-align:center;
    }
    .WFdivider:before, .WFdivider:after{
      content:'';
      display:table-cell;
      background:#333;
      width:50%;
      -webkit-transform:scaleY(0.1);
      transform:scaleY(0.1);
    }
    .WFdivider > span { white-space:pre; padding:0 15px; }
</style>
<script type="text/javascript">
    var WorkflowDesignerProcessSettings = {$processSettings|@json_encode};

    if(typeof WorkflowWidgetLoaded !== 'undefined') {
        WorkflowWidgetLoaded();
    }
</script>
<div style="text-align:left;position:relative;padding:5px;" class="workflowDesignerSidebar"  id="WorkflowDesignerWidgetContainer">
    <div id="WorkflowDesignerErrorLoaded" style="text-align:center;border:2px solid #cc2222;background-color:#dfa9a9;padding:3px 0;">Workflow Designer JS-File not loaded.<br/>Please run "DB Check"</div>
    <div id='workflow_layer_executer' style='display:none;width:100%;height:100%;top:0px;left:0px;background-image:url(modules/Workflow2/icons/modal_white.png);font-size:12px;letter-spacing:1px;border:1px solid #777777;  position:absolute;text-align:center;'><br><img src='modules/Workflow2/icons/sending.gif'><br><br><strong>Executing Workflow ...</strong><br><a href='#' onclick='jQuery("#workflow_layer_executer").hide();return false;'>Close</a></a></div>
    {if $show_listview eq true}
        {if count($workflows) gt 0}
            {vtranslate('LBL_FORCE_EXECUTION','Workflow2')}
            <select name="workflow2_workflowid" id="workflow2_workflowid" size=7 class="detailedViewTextBox" style="width:100%;">
                <!--<option value='0'><?php echo getTranslatedString("LBL_CHOOSE", "Workflow2"); ?></option>-->
                {foreach from=$workflows item=workflow}
                    <option value='{$workflow.id}' data-withoutrecord="{$workflow.withoutrecord}" data-collection="{$workflow.collection_process}">{$workflow.title}</option>
                {/foreach}
            </select>
            <button class="btn btn-success"  onclick="runSidebarWorkflow('{$crmid}');"name='runWorkfow' >{vtranslate('execute','Workflow2')}</button>
        {else}
            <span style="color:#777;font-style:italic;">{vtranslate('LBL_NO_WORKFLOWS','Workflow2')}</span>
        {/if}
    {/if}

    {if $isAdmin eq true}
    <a class="pull-right" href="#" onclick="showEntityData('{$crmid}');return false;" name='showEntityData'>{vtranslate('BTN_SHOW_ENTITYDATA','Workflow2')}</a>
    {/if}
    <div id="startfieldsContainer" style="position:relative;"></div>

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
<script type="text/javascript">jQuery(window).trigger('workflow.detail.sidebar.ready');</script>
{*
        <?php foreach($workflows as $row) {
                if($row["trigger"] == "WF2_IMPORTER") continue;
            $objWorkflow = new Workflow_Main($row["id"]);

        if($row["authmanagement"] == "0" || $objWorkflow->checkAuth("view")) {
        ?>
        <?php }

        }
        ?>
*}