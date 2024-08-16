<div class="WFDSidebar settingsgroup">
    <h5 style="overflow:hidden;padding-right:1px;">
        <a href="index.php?module=Workflow2&view=Index&parent=Settings" class="btn btn-default pull-right"><i class="fa fa-arrow-circle-left" aria-hidden="true"></i>&nbsp;&nbsp;{vtranslate("Back", "Settings:Workflow2")}</a>
        {vtranslate("Settings", "Settings:Workflow2")}
    </h5>
    <div class="panel">
        <form method="POST" action="#" {if $workflowData.module_name neq ""}onsubmit='return false'{/if} role="form">
            <input type="hidden" name="workflow[id]" value="{$workflowID}">
            {if $workflowData.module_name eq ""}
                <div>{vtranslate("Main Module", "Settings:Workflow2")}</div>
                <div><select class="chzn-select" style="float:right;height:25px;width:199px;" name="workflow[mainmodule]">
                        {foreach from=$module key=key item=moduleName}
                            <option value="{$key}">{$moduleName}</option>
                        {/foreach}
                    </select></div>
            {else}
                <div style="font-size:14px;text-transform:uppercase;font-weight:bold;margin:7px 0 10px 0;color:#53697e;">{vtranslate($workflowData.module_name, $workflowData.module_name)}</div>

                <div class="form-group" style="overflow:hidden;">
                    <label for="workflow_title">{vtranslate("Title", "Settings:Workflow2")}</label>
                    <br/>
                    <input type="text" class="form-control" style="float:right;width:100%;" onblur="saveWorkflowTitle();" id="workflow_title" name="workflow[title]" value="{$workflowData.title}">
                </div>
                <div class="form-group" style="overflow:hidden;">
                    <label style="float:left;line-height: 22px;">{vtranslate("LBL_WORKFLOW_IS_ACTIVE", "Settings:Workflow2")}</label>
                    <div class="switch">
                        <input id="workflowActiveSwitch" class="cmn-toggle cmn-toggle-round" style=" position:absolute !important;" type="checkbox" {if $workflowData.active eq '1'}checked="checked"{/if} value='1'>
                        <label for="workflowActiveSwitch" style="margin-right:5px;"></label>
                    </div>
                </div>
            {/if}
            <br>
            <div class="buttonbar center" style="clear:both;margin-bottom:10px;">
                {if $workflowData.module_name eq ""}
                    <input type="submit"  style=";" onclick="saveSetings({$workflowID});"  name="submitSettings" class="btn btn-primary" value="{vtranslate("Save Settings", "Settings:Workflow2")}">
                {else}
                    <a href="index.php?module=Workflow2&view=Statistic&parent=Settings&workflow={$workflowID}" class="btn btn-info" target="_blank">{vtranslate("Statistics", "Settings:Workflow2")}</a>
                {/if}
            </div>

            {if $workflowData.module_name neq ""}
                <div class="center" style="margin-top:5px;padding-top:10px;color:#999999;height:40px;text-align:center;">
                    <div style='text-align:center;width:48%;float:left;' alt='<?php echo getTranslatedString("LBL_CURRENTLY_RUNNING_DESCR", "Workflow2"); ?>' title='<?php echo getTranslatedString("LBL_CURRENTLY_RUNNING_DESCR", "Workflow2"); ?>'>
                        <span class='overviewStatisticNumber'>{$runningCounter}</span>
                        <br>
                        {vtranslate("LBL_CURRENTLY_RUNNING", "Settings:Workflow2")}
                    </div>
                    <div onclick='{if $errorCounter > 0}window.open("index.php?module=Workflow2&view=ErrorLog&parent=Settings&workflow_id={$workflowID}", "", "width=700,height=800");{/if}' style='float:left;cursor:pointer;text-align:center;width:48%;' alt='{vtranslate("LBL_LAST_ERRORS_DESCR", "Settings:Workflow2")}' title='{vtranslate("LBL_LAST_ERRORS_DESCR", "Settings:Workflow2")}'>
                        <span class='overviewStatisticNumber'>{$errorCounter}</span>
                        <br>
                        {vtranslate("LBL_LAST_ERRORS", "Settings:Workflow2")}
                    </div>
                </div>

                <div id="runningWarning" style="display:{if $runningCounter > 0 && $workflowData.active neq '1'}block{else}none{/if};">
                    <br/>
                    <p>{vtranslate('You have deactivate the workflow. But already running instances will be executed nevertheless.', 'Settings:Workflow2')}</p>
                    <button type="button" id="stopAllRunningInstances" class="btb btn-warning">{vtranslate('stop all running instances','Settings:Workflow2')}</button>
                </div>

                <!-- if(!empty($_SESSION["mWFB"])) {
                    echo "<div style='background-color:#eeeeee;border:1px solid #777777;text-align:center;padding:5px 0;'>Your license only allows ".$_SESSION["mWFB"]." Blocks</div>";
                } -->
                <br><a href='#' onclick='showOptionsContainer();return false;'>+ {vtranslate("LBL_OPTIONEN", "Settings:Workflow2")}</a><br>
                <div id='optionsContainer' style="display:none;">
                    <p>
                        <label>
                            <input class="pull-left" type="checkbox" onclick="refreshBlockIDs();" name="optionShowBlockId" id="optionShowBlockId">&nbsp;&nbsp;&nbsp;{vtranslate('show BlockIDs', 'Settings:Workflow2')}
                        </label>
                    </p>
                </div>
            {/if}

        </form>
    </div>

    <div id="pageOverlay" onclick="closePageOverlay();" style='cursor:url("modules/Workflow2/icons/cross-button.png"), auto;position:fixed;z-index:20000;top:0;left:0;display:none;height:100%;width:100%;background-image:url("modules/Workflow2/icons/modal.png");'><div id='pageOverlayContent' style='position:fixed;cursor:default;top:100px;margin:auto;left:50%;padding:10px;background-color:#ffffff;'>&nbsp;</div></div>

    <div class="WFDTaskList" id="settingsQuickWidgetContainer">
        <div class="">

            {if $workflowData.module_name neq ""}
                <input type="text" class="typeSearchBox" style="width:100%;box-sizing: border-box;height:30px;" placeholder="{vtranslate('search in available types', 'Settings:Workflow2')}"/>
                <input type="hidden" id="IsInventory" value="{if $IsInventory eq true}1{else}0{/if}" />
                <input type="hidden" id="WorkflowTrigger" value="{$workflowData.trigger}" />
                <input type="hidden" id="WorkflowModule" value="{$workflowData.module_name}" />
            {/if}

            {foreach from=$typesCat key=blockKey item=typekey}
                <div class="settingsgroup-panel panel-default taskWidgetContainer" style="display:none;">
                    <div id="{$blockKey}_accordion" class="app-nav" role="tab">
                        <div class="app-settings-accordion">
                            <div class="settingsgroup-accordion">
                                <a data-toggle="collapse" data-parent="#accordion" class='collapsed' aria-expanded="false" href="#Settings_sideBar_{$blockKey|replace:' ':'_'}">
                                    <i class="indicator fa fa-chevron-right"></i>
                                    &nbsp;<span>{$blockKey}</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="panel-collapse settingsgroup ulBlock typeContainer collapse" id="Settings_sideBar_{$blockKey|replace:' ':'_'}" data-block="{$typekey.0.0}">
                        <ul class="list-group widgetContainer">
                            {foreach from=$typekey item=typeVal key=blockType}
                                {assign var=typeVal value=$typeVal.1}
                                {assign var=type value=$types.$typeVal}

                                <li class="WorkflowTypeContainer" data-singlemodule="{$type->get("singleModule")|@implode:','|@strtolower}" data-search="{$typeVal}{$type->get("text")|@mb_strtolower|@htmlentities}" style='padding-left:10px;border-top:0px;padding-bottom: 5px'  onclick="addBlock('{$typeVal}');return false;" data-type="{$typeVal}" data-default="b">
                                    <a href="#" class="menuItemLabel">
                                        {$type->get("text")}
                                    </a>
                                </li>
                            {/foreach}
                        </ul>
                    </div>
                </div>
            {/foreach}
        </div>
        <div class='WF2footerSidebar'>
            Workflow Designer {$VERSION}<br/>
            Translation by <a href="{vtranslate('TRANSLATION_AUTHOR_URL', 'Workflow2')}">{vtranslate('TRANSLATION_AUTHOR_NAME', 'Workflow2')}</a>
        </div>
    </div>
</div>
