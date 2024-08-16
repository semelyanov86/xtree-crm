<div class="WFDSidebar settingsgroup">
    <h5>
        {vtranslate("Settings", "Settings:Workflow2")}
    </h5>
    <div class="panel">
        <input type="hidden" name="workflow[id]" value="{$workflowID}">
        <div>{vtranslate("Main module", "Settings:Workflow2")}</div>
        <div style="text-align:right;font-weight:bold;">{vtranslate($workflowData.module_name, $workflowData.module_name)}</div>
    </div>
    <h5>
        {vtranslate("Statistics", "Settings:Workflow2")}
    </h5>
    <div class="panel">
        <label style="overflow:hidden;width:100%;">
            <input type='text' class="defaultTextfield pull-right" style="width: 100px;"  name='statistik_from' id="statistik_from" value="{$STATISTIC_FROM}">
            {vtranslate('statistics_from','Settings:Workflow2')}
        </label>
        <label style="overflow:hidden;width:100%;">
            <input type='text' class="defaultTextfield pull-right" style="width: 100px;" name='statistik_to' id="statistik_to" value="{$STATISTIC_TO}">
            {vtranslate('statistics_to','Settings:Workflow2')}
        </label>
        <label style="overflow:hidden;width:100%;">
            <input type='text' class="defaultTextfield pull-right" style="width: 100px;" name='statistik_record' id="statistik_record" value="">
            {vtranslate('statistics_record','Settings:Workflow2')}
        </label>

        <!--<input type='hidden' name='statistik_to' id="statistik_to" value="{$STATISTIC_TO}">-->
        <br>
        <input type='checkbox' name='show_percents' checked="checked" id="show_percents" onclick="readConnectionStatistik();" value="1"> {vtranslate("LBL_SHOW_VALUES", "Settings:Workflow2")}<br/>
        <input type='checkbox' name='show_removed' id="show_removed" onclick="readConnectionStatistik();" value="1"> {vtranslate("LBL_SHOW_REMOVED", "Settings:Workflow2")}<br/>
        <input type='checkbox' name='show_inactive' id="show_inactive" onclick="showInactiveBlocks();" value="1"> {vtranslate("LBL_SHOW_INACTIVE", "Settings:Workflow2")}
        <br/>
        <br/>
        <input type="button" onclick="refreshData();" class="btn btn-primary"  name="load_data" value="{vtranslate("BTN_LOAD_STATS", "Settings:Workflow2")}">
    </div>

    <div class="ConnectionStat">
        <h5>
            {vtranslate("CONNECTION_DETAILS", "Settings:Workflow2")}
        </h5>
        <div class="panel" id="statConnDetails">
            <em>{vtranslate("TXT_CLICK_ON_PATH", "Settings:Workflow2")}</em>
        </div>
    </div>
</div>
<script type="text/javascript">
    var TranslationString = {};
    TranslationString['loadData'] = "{vtranslate('load Data', 'Settings:Workflow2')}";
</script>