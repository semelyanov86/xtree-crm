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
<div style="text-align:left;position:relative;padding:5px;" id="WorkflowDesignerWidgetContainer">
    <div id="WorkflowDesignerErrorLoaded" style="text-align:center;border:2px solid #cc2222;background-color:#dfa9a9;padding:3px 0;">Workflow Designer JS-File not loaded.<br/>Please run "DB Check".</div>
    <input type="hidden" id="WFD_CURRENT_MODULE" value="{$source_module}" />
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
            <div id="executionProgress_Value" style="text-align:center;font-weight:bold;display:none;"></div>
            <button class="btn btn-success"  onclick="runListViewSidebarWorkflow();"name='runWorkfow' >{vtranslate('execute','Settings:Workflow2')}</button>
        {else}
            <span style="color:#777;font-style:italic;">{vtranslate('LBL_NO_WORKFLOWS','Workflow2')}</span>
        {/if}
    {/if}

    <div class="WorkflowDesignerButtons">
    {foreach from=$buttons item=button}
        {if $button.color neq 'separator'}
            <button type="button" data-crmid="{$crmid}" data-withoutrecord="{$button.withoutrecord}"  data-collection="{$button.collection_process}" class="btn" onclick="runListViewWorkflow({$button.workflow_id}, jQuery(this).data('withoutrecord') == '1');" alt="execute this workflow"  title="execute this workflow" style="text-shadow:none;color:{$button.textcolor}; {if !empty($button.color)}background-color: {$button.color}{/if};margin-top:2px;width:100%;">{$button.label}</button><br/>
        {else}
            {if empty($button.label)}
                <hr/>
            {else}
                <div class="WFdivider"><span>{$button.label}</span></div>
            {/if}

        {/if}
    {/foreach}
    </div>

    <div id="startfieldsContainer" style="position:relative;"></div>
    {if $hide_importer neq true}
    <hr>
    <button class="btn btn-info" style="width:100%;" onclick="WorkflowHandler.startImport();">{vtranslate('Import Prozess starten', 'Settings:Workflow2')}</button>
    {/if}
</div>
<script type="text/javascript">jQuery(window).trigger('workflow.list.sidebar.ready');</script>
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