{*<!--
/*********************************************************************************
* The content of this file is subject to the PDF Maker license.
* ("License"); You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
* Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
* All Rights Reserved.
********************************************************************************/
-->*}
{strip}
    <div class="quickWidgetContainer accordion">
        {assign var=val value=1}
        {foreach item=SIDEBARWIDGET key=index from=$QUICK_LINKS['SIDEBARWIDGET']}
            <div class="quickWidget">
                <div class="accordion-heading accordion-toggle quickWidgetHeader" data-target="#{$MODULE}_sideBar_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($SIDEBARWIDGET->getLabel())}"
                     data-toggle="collapse" data-parent="#quickWidgets" data-label="{$SIDEBARWIDGET->getLabel()}"
                     data-widget-url="{$SIDEBARWIDGET->getUrl()}" >
                    <span class="pull-left"><img class="imageElement" data-rightimage="{vimage_path('rightArrowWhite.png')}" data-downimage="{vimage_path('downArrowWhite.png')}" src="{vimage_path('rightArrowWhite.png')}" /></span>
                    <h5 class="title widgetTextOverflowEllipsis pull-right" title="{vtranslate($SIDEBARWIDGET->getLabel(), $MODULE)}">{vtranslate($SIDEBARWIDGET->getLabel(), $MODULE)}</h5>
                    <div class="loadingImg hide pull-right">
                        <div class="loadingWidgetMsg"><strong>{vtranslate('LBL_LOADING_WIDGET', $MODULE)}</strong></div>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="widgetContainer accordion-body collapse" id="{$MODULE}_sideBar_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($SIDEBARWIDGET->getLabel())}" data-url="{$SIDEBARWIDGET->getUrl()}">
                </div>
            </div>
        {/foreach}
        {if $smarty.request.view eq 'List' || $smarty.request.view eq 'Detail'}
        <div class="quickWidget">
            <div class="accordion-heading accordion-toggle quickWidgetHeader" data-target="#{$MODULE}_sideBar_Tools"
                 data-toggle="collapse" data-parent="#quickWidgets" data-label="{vtranslate('Tools', $MODULE)}"
                 data-widget-url="module=PDFMaker&view=TemplateTools&templateid={$smarty.request.templateid}&from_view={$smarty.request.view}&from_templateid={$smarty.request.templateid}" >
                <span class="pull-left"><img class="imageElement" data-rightimage="{vimage_path('rightArrowWhite.png')}" data-downimage="{vimage_path('downArrowWhite.png')}" src="{vimage_path('rightArrowWhite.png')}" /></span>
                <h5 class="title widgetTextOverflowEllipsis pull-right" title="{vtranslate('Tools', $MODULE)}">{vtranslate('Tools', $MODULE)}</h5>
                <div class="loadingImg hide pull-right">
                    <div class="loadingWidgetMsg"><strong>{vtranslate('LBL_LOADING_WIDGET', $MODULE)}</strong></div>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="widgetContainer accordion-body collapse" id="{$MODULE}_sideBar_Tools" data-url="module=PDFMaker&view=TemplateTools&templateid={$smarty.request.templateid}&from_view={$smarty.request.view}&from_templateid={$smarty.request.templateid}">
            </div>
        </div>
        {/if}
    </div>
{/strip}