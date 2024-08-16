<div class="settingsgroup">

    <div id="pageOverlay" onclick="closePageOverlay();" style='cursor:url("modules/Workflow2/icons/cross-button.png"), auto;position:fixed;z-index:20000;top:0;left:0;display:none;height:100%;width:100%;background-image:url("modules/Workflow2/icons/modal.png");'><div id='pageOverlayContent' style='position:fixed;cursor:default;top:100px;margin:auto;left:50%;padding:10px;background-color:#ffffff;'>&nbsp;</div></div>
    <div class="settingsgroup" id="settingsQuickWidgetContainer">
        <div class="panel-group">

            {foreach from=$MENUITEMS key=CATLABEL item=ITEMS}
            <div class="settingsgroup-panel panel panel-default instaSearch">
                <div id="{$BLOCK_NAME}_accordion" class="app-nav" role="tab">
                    <div class="app-settings-accordion">
                        <div class="settingsgroup-accordion">
                            <a data-toggle="collapse" data-parent="#accordion" class='collapsed' href="#{$BLOCK_NAME}">
                                <i class="indicator fa fa-chevron-right"></i>
                                &nbsp;<span>{vtranslate($CATLABEL, 'Settings:Workflow2')}</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div id="{$BLOCK_NAME}" class="panel-collapse ulBlock">
                    <ul class="list-group widgetContainer">
                        {foreach from=$ITEMS item=ITEM}
                            <li>
                                <a data-name="{$MENU}" href="{$ITEM['url']}" class="menuItemLabel {if $VIEW eq $ITEM['view'] && (empty($PAGE) || $PAGE eq $ITEM['page'])} selectedMenuItem selectedListItem{/if}">
                                    {if !empty($ITEM['errors'])}<span class="SidebarErrors">{$ITEM['errors']}</span>{/if}
                                    {vtranslate($ITEM['label'], $ITEM['module'])}
                                </a>
                            </li>
                        {/foreach}
                    </ul>
                </div>
            </div>
            {/foreach}
        </div>
        <div class='WF2footerSidebar' style="padding-bottom:10px;">
            Workflow Designer {$VERSION}<br/>
            Translation by <a href="{vtranslate('TRANSLATION_AUTHOR_URL', 'Workflow2')}">{vtranslate('TRANSLATION_AUTHOR_NAME', 'Workflow2')}</a>
        </div>
    </div>
</div>
