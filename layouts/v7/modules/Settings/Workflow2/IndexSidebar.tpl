<div class="sidebarTitleBlock">
	<h3 class="titlePadding themeTextColor unSelectedQuickLink cursorPointer"><a href="index.php?module=Vtiger&parent=Settings&view=Index">{vtranslate('LBL_SETTINGS', $QUALIFIED_MODULE)}</a></h3>
</div>
<div id="pageOverlay" onclick="closePageOverlay();" style='cursor:url("modules/Workflow2/icons/cross-button.png"), auto;position:fixed;z-index:20000;top:0;left:0;display:none;height:100%;width:100%;background-image:url("modules/Workflow2/icons/modal.png");'><div id='pageOverlayContent' style='position:fixed;cursor:default;top:100px;margin:auto;left:50%;padding:10px;background-color:#ffffff;'>&nbsp;</div></div>
<div class="quickWidgetContainer accordion" id="settingsQuickWidgetContainer">
    <div class="quickWidget">
        {foreach from=$MENUITEMS key=CATLABEL item=ITEMS}
            <div class="accordion-heading quickWidgetHeader">
                <h5 class="title paddingLeft10px widgetTextOverflowEllipsis" title="{vtranslate($CATLABEL, 'Settings:Workflow2')}">{vtranslate($CATLABEL, 'Settings:Workflow2')}</h5>
                <div class="clearfix"></div>
            </div>

            <div  style="border-bottom: 1px solid black;" class="widgetContainer accordion-body">
                {foreach from=$ITEMS item=ITEM}
                    <div class="{if $VIEW eq $ITEM['view'] && (empty($PAGE) || $PAGE eq $ITEM['page'])} selectedMenuItem selectedListItem{/if}" style='padding-left:10px;border-top:0px;padding-bottom: 5px'>
                        <div class="row-fluid menuItem">
                            <a href="{$ITEM['url']}" data-id="{$ITEM['label']}" class="span9 menuItemLabel" data-menu-item="true" >{vtranslate($ITEM['label'], $ITEM['module'])}</a>
                            {if !empty($ITEM['errors'])}<span class="SidebarErrors">{$ITEM['errors']}</span>{/if}
                            <div class="clearfix"></div>
                        </div>
                    </div>
                {/foreach}
            </div>

        {/foreach}
    </div>
    <div class='WF2footerSidebar'>
        Workflow Designer {$VERSION}<br/>
        Translation by <a href="{vtranslate('TRANSLATION_AUTHOR_URL', 'Workflow2')}">{vtranslate('TRANSLATION_AUTHOR_NAME', 'Workflow2')}</a>
    </div>
</div>