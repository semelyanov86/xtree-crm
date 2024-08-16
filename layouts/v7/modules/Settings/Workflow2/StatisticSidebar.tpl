<div style="overflow:hidden;padding:5px;">
    <input type='button' class="btn" onclick="window.location.href='index.php?module=Workflow2&view=Index&parent=Settings';" style="float:right;margin-top:-4px;" value='{vtranslate("Back", "Settings:Workflow2")}'>
    <strong>{vtranslate("Workflow2", "Workflow2")}</strong>
</div>
<div class="quickWidgetContainer accordion" id="WFconfigQuickWidgetContainer">
    <div class="quickWidget">
        <div class="accordion-heading accordion-toggle quickWidgetHeader" data-parent="#settingsQuickWidgetContainer" data-target="#Settings_sideBar_Settings"
            data-toggle="collapse" data-parent="#quickWidgets">
            <span class="pull-left"><img class="imageElement" data-rightimage="{vimage_path('rightArrowWhite.png')}" data-downimage="{vimage_path('downArrowWhite.png')}" src="{vimage_path('downArrowWhite.png')}" /></span>
            <h5 class="title paddingLeft10px widgetTextOverflowEllipsis" title="{vtranslate("Settings", "Settings:Workflow2")}">{vtranslate("Settings", "Settings:Workflow2")}</h5>
            <div class="clearfix"></div>
        </div>
        <div style="overflow:visible;" class="widgetContainer accordion-body in collapse" id="Settings_sideBar_Settings">
            <div style="padding:5px;background-color:#ffffff;" id='settingsCont' class="clearfix">
                <form method="POST" action="#" {if $workflowData.module_name neq ""}onsubmit='return false'{/if}>
                    <input type="hidden" name="workflow[id]" value="{$workflowID}">
                        <div>{vtranslate("Main module", "Settings:Workflow2")}</div>
                        <div style="text-align:right;font-weight:bold;">{vtranslate($workflowData.module_name, $workflowData.module_name)}</div>

                </form>
            </div>
        </div>
        <div class="accordion-heading accordion-toggle quickWidgetHeader" data-parent="#settingsQuickWidgetContainer" data-target="#Settings_sideBar_Statistic"
            data-toggle="collapse" data-parent="#quickWidgets">
            <span class="pull-left"><img class="imageElement" data-rightimage="{vimage_path('rightArrowWhite.png')}" data-downimage="{vimage_path('downArrowWhite.png')}" src="{vimage_path('downArrowWhite.png')}" /></span>
            <h5 class="title paddingLeft10px widgetTextOverflowEllipsis" title="{vtranslate("Statistics", "Settings:Workflow2")}">{vtranslate("Statistics", "Settings:Workflow2")}</h5>
            <div class="clearfix"></div>
        </div>
        <div style="padding:5px;overflow:visible;" class="widgetContainer accordion-body in collapse" id="Settings_sideBar_Statistic">
            <label style="overflow:hidden;">
                <input type='text' class="pull-right" style="width: 100px;"  name='statistik_from' id="statistik_from" value="{$STATISTIC_FROM}">
                {vtranslate('statistics_from','Settings:Workflow2')}
            </label>
            <label style="overflow:hidden;">
                <input type='text' class="pull-right" style="width: 100px;" name='statistik_to' id="statistik_to" value="{$STATISTIC_TO}">
                {vtranslate('statistics_to','Settings:Workflow2')}
            </label>
            <label style="overflow:hidden;">
                <input type='text' class="pull-right" style="width: 100px;" name='statistik_record' id="statistik_record" value="">
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

        <div style="display: none;" class="statUsageShow accordion-heading accordion-toggle quickWidgetHeader" data-parent="#settingsQuickWidgetContainer" data-target="#statUsageDetails"
            data-toggle="collapse" data-parent="#quickWidgets">
            <span class="pull-left"><img class="imageElement" data-rightimage="{vimage_path('rightArrowWhite.png')}" data-downimage="{vimage_path('downArrowWhite.png')}" src="{vimage_path('downArrowWhite.png')}" /></span>
            <h5 class="title paddingLeft10px widgetTextOverflowEllipsis" title="{vtranslate("connection usage", "Settings:Workflow2")}">{vtranslate("connection usage", "Settings:Workflow2")}</h5>
            <div class="clearfix"></div>
        </div>
        <div style="padding:5px;display:none;" class="statUsageShow widgetContainer accordion-body in collapse" id="statUsageDetails">
            <div id="jqPlotContainer"></div>
        </div>

        <div class="accordion-heading accordion-toggle quickWidgetHeader" data-parent="#settingsQuickWidgetContainer" data-target="#statConnDetails"
            data-toggle="collapse" data-parent="#quickWidgets">
            <span class="pull-left"><img class="imageElement" data-rightimage="{vimage_path('rightArrowWhite.png')}" data-downimage="{vimage_path('downArrowWhite.png')}" src="{vimage_path('downArrowWhite.png')}" /></span>
            <h5 class="title paddingLeft10px widgetTextOverflowEllipsis" title="{vtranslate("CONNECTION_DETAILS", "Settings:Workflow2")}">{vtranslate("CONNECTION_DETAILS", "Settings:Workflow2")}</h5>
            <div class="clearfix"></div>
        </div>

        <div style="padding:5px;" class="widgetContainer accordion-body in collapse" id="statConnDetails">
            <em>{vtranslate("TXT_CLICK_ON_PATH", "Settings:Workflow2")}</em>
        </div>
    </div>
    <div class='WF2footerSidebar'>
        Workflow Designer {$VERSION}<br/>
        Translation by <a href="{vtranslate('TRANSLATION_AUTHOR_URL', 'Workflow2')}">{vtranslate('TRANSLATION_AUTHOR_NAME', 'Workflow2')}</a>
    </div>

</div>
