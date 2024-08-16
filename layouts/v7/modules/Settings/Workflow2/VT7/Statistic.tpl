<link rel="stylesheet" href="{$smarty.const.PATH_CONTEXTMENU}/src/jquery.contextMenu.css" type="text/css" media="all" />
<link rel="stylesheet" type="text/css" href="{$smarty.const.PATH_JQPLOT}/jquery.jqplot.min.css" />

<script type="text/javascript" src="{$smarty.const.PATH_CONTEXTMENU}/src/jquery.contextMenu.js"></script>

<script type="text/javascript" src="{$smarty.const.FILE_JSPLUMB}"></script>

<script src="{$smarty.const.PATH_CKEDITOR}/ckeditor.js"></script>
<script type="text/javascript">
    CKEDITOR.disableAutoInline = true;
</script>

<div style="height:1024px;width:100%;position:relative;" id="mainWfContainer" class="noselect">

			<div style="position:absolute;width:100%;" id="WFTaskContainer" class="statisticContainer">
            <div id='rangeDisplayContainer' style=""></div>

        </div>

            {if $workflowData.module_name eq ""}
            <div style="position:absolute;left: 50%;margin-left:-200px;top:200px;font-size:20px; color:#bbb;font-weight:bold;width:400px;">{vtranslate("Please choose a Main module!", "Settings:Workflow2")}</div>
            {/if}

			<div id="workflowDesignContainer" class="workflowDesignContainer noselect contentsDiv" style="{if $workflowData.module_name eq ""}display:none;{/if}">
                <div id='mainModelWindow' style='position:absolute;top:0;left:0;width:100%;height:100%;background-image:url(modules/Workflow2/icons/modal_white.png);z-index:270;'>
                    <div style="margin:150px auto;text-align:center;font-weight:bold;color:#aaa;font-size:18px;"><?php echo getTranslatedString("LOADING_INDICATOR", "Workflow2"); ?></div>
                </div>

                <div style="position:absolute;width:100%;z-index:1000;" id='workflowObjectsContainer' class="noselect">
                    {$WorkflowObjectHTML}
                    {$html}
                </div>

			</div>
		</div>
</div>

<script type="text/javascript">
    var workflow_data = {$workflowData|@json_encode};
    var workflow_id = {$workflowID};
    var maxConnections = {$maxConnections};
</script>

<div id='modalWindow' style='display:none;'></div>

<script type="text/javascript" src="{$smarty.const.PATH_JQPLOT}/jquery.jqplot.min.js"></script>
<script type="text/javascript" src="{$smarty.const.PATH_JQPLOT}/plugins/jqplot.highlighter.min.js"></script>
<script type="text/javascript" src="{$smarty.const.PATH_JQPLOT}/plugins/jqplot.cursor.min.js"></script>
<script type="text/javascript" src="{$smarty.const.PATH_JQPLOT}/plugins/jqplot.dateAxisRenderer.min.js"></script>

<script type="text/javascript" src="{$smarty.const.PATH_JQPLOT}/plugins/jqplot.logAxisRenderer.min.js"></script>

<script type="text/javascript" src="{$smarty.const.PATH_JQPLOT}/plugins/jqplot.canvasTextRenderer.min.js"></script>
<script type="text/javascript" src="{$smarty.const.PATH_JQPLOT}/plugins/jqplot.canvasAxisTickRenderer.min.js"></script>
<script type="text/javascript" src="{$smarty.const.PATH_JQPLOT}/plugins/jqplot.canvasAxisLabelRenderer.min.js"></script>

