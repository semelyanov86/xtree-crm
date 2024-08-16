{strip}
    <input type="hidden" id="filters" name="filters" value='{Vtiger_Util_Helper::toSafeHTML($FILTERS)}'>
    <input type="hidden" id="filter_values" name="filter_values" value='{Vtiger_Util_Helper::toSafeHTML($FILTER_VALUES)}'>
    <input type="hidden" id="relatedModuleFields" name="relatedModuleFields" value='{Vtiger_Util_Helper::toSafeHTML($RELATED_MODULE_FIELDS)}'>
    <input type="hidden" id="relatedModuleActions" name="relatedModuleActions" value='{Vtiger_Util_Helper::toSafeHTML($RELATED_MODULE_ACTIONS)}'>
    <div class="container-fluid WidgetsManage">
        <input type="hidden" name="tabid" value="{$SOURCE}">
        <div class="widget_header row">
            <div class="col-sm-6"><h3><label>{vtranslate($MODULE, $QUALIFIED_MODULE)}</label></h3>{*{vtranslate('LBL_MODULE_DESC', $QUALIFIED_MODULE)}*}</div>

            <div class="col-sm-6 ">
                <div class="col-sm-4 textAlignCenter" style="padding-top: 8px;">Select Module</div>
                <div class="col-sm-6">
                    <select class="select2 col-sm-12" name="ModulesList">
                        {foreach from=$MODULE_MODEL->getModulesList() item=item key=key}
                            <option value="{$key}"
                                    {if $SOURCE eq $key}selected{/if}>{vtranslate($item['tablabel'], $item['name'])}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
        </div>
        <hr/>

        <div class="widget_header row">
            <div class="form-group col-sm-8">
                <h4>{*<span ><input class="pull-left defaultWidget" type="checkbox" name="all_widget" value="1" {if $DEFAULT_WIDGETS['all_widget'] == '1'}checked{/if}></span>*}
                    <strong class="pull-left translatedBlockLabel">{vtranslate('Hide Default Widgets', $QUALIFIED_MODULE)}: {vtranslate($SOURCEMODULE, $SOURCEMODULE)}</strong></h4>
            </div>
        </div>
        <div class="col-sm-12 padding1per row">
            <div class="row-fluid marginBottom10px">
                <span class="span2">
                    <input class="pull-left defaultWidget" type="checkbox" name="comments_widget" value="1" {if $DEFAULT_WIDGETS['comments_widget'] == '1'}checked{/if} > &nbsp;
                    <div class="pull-left marginRight10px">&nbsp;{vtranslate('ModComments', $SOURCEMODULE)}&nbsp;&nbsp; </div>
                </span>

                <span class="span2">
                    <input class="pull-left defaultWidget" type="checkbox" name="activities_widget" value="1" {if $DEFAULT_WIDGETS['activities_widget'] == 1}checked{/if} > &nbsp;
                    <div class="pull-left marginRight10px">&nbsp;{vtranslate('Activities', $SOURCEMODULE)}&nbsp;&nbsp;   </div>
                </span>
                <span class="span2">
                    <input class="pull-left defaultWidget" type="checkbox" name="document_widget" value="1" {if $DEFAULT_WIDGETS['document_widget'] == '1'}checked{/if} > &nbsp;
                    <div class="pull-left marginRight10px">&nbsp;{vtranslate('Documents', $SOURCEMODULE)} </div>
                </span>
            </div>
            {if $SOURCEMODULE=='Potentials'}
                <div class="row-fluid marginBottom10px">
                    {*<span class="span2">
                        <input class="pull-left defaultWidget" type="checkbox" name="document_widget" value="1" {if $DEFAULT_WIDGETS['document_widget'] == '1'}checked{/if} > &nbsp;
                            <div class="pull-left marginRight10px">&nbsp;{vtranslate('Documents', $SOURCEMODULE)}</div>
                    </span>*}
                    <span class="span2">
                        <input class="pull-left defaultWidget" type="checkbox" name="contact_widget" value="1" {if $DEFAULT_WIDGETS['contact_widget'] == '1'}checked{/if} > &nbsp;
                        <div class="pull-left marginRight10px">&nbsp;{vtranslate('LBL_RELATED_CONTACTS', $SOURCEMODULE)}</div>
                    </span>
                    <span class="span2">
                        <input class="pull-left defaultWidget" type="checkbox" name="product_widget" value="1" {if $DEFAULT_WIDGETS['product_widget'] == '1'}checked{/if} > &nbsp;
                        <div class="pull-left marginRight10px">&nbsp;{vtranslate('LBL_RELATED_PRODUCTS', $SOURCEMODULE)}</div>
                    </span>
                </div>
            {/if}
            {if $SOURCEMODULE=='Project'}
                <div class="row-fluid marginBottom10px">
                    {*<span class="span2">
                        <input class="pull-left defaultWidget" type="checkbox" name="document_widget" value="1" {if $DEFAULT_WIDGETS['document_widget'] == '1'}checked{/if} > &nbsp;
                        <div class="pull-left marginRight10px">&nbsp;{vtranslate('Documents', $SOURCEMODULE)} &nbsp;&nbsp;     </div>
                    </span>*}
                    <span class="span2">
                        <input class="pull-left defaultWidget" type="checkbox" name="helpdesk_widget" value="1" {if $DEFAULT_WIDGETS['helpdesk_widget'] == '1'}checked{/if} > &nbsp;
                        <div class="pull-left marginRight10px">&nbsp;{vtranslate('HelpDesk', $SOURCEMODULE)} &nbsp;&nbsp; </div>
                    </span>
                    <span class="span2">
                        <input class="pull-left defaultWidget" type="checkbox" name="milestones_widget" value="1" {if $DEFAULT_WIDGETS['milestones_widget'] == '1'}checked{/if} > &nbsp;
                        <div class="pull-left marginRight10px">&nbsp;{vtranslate('LBL_MILESTONES', $SOURCEMODULE)} </div>
                    </span>
                    <span class="span2">
                        <input class="pull-left defaultWidget" type="checkbox" name="tasks_widget" value="1" {if $DEFAULT_WIDGETS['tasks_widget'] == '1'}checked{/if} > &nbsp;
                        <div class="pull-left marginRight10px">&nbsp;{vtranslate('LBL_TASKS', $SOURCEMODULE)} &nbsp;&nbsp; </div>
                    </span>
                </div>
            {/if}
        </div>
        <div class="col-sm-12 padding1per row" >
            <h5 class="vt-callout-header" ><span class="fa fa-info-circle" style="color: #2b9cbd;"></span>&nbsp;{vtranslate('Select widgets to be hidden', $QUALIFIED_MODULE)}</h5>
        </div>
        <div class="clearfix"></div>
        <div class="paddingTop20" style="padding:1% 0 0">
            <h4> <strong class="pull-left translatedBlockLabel">
                {vtranslate('List of widgets for the module', $QUALIFIED_MODULE)}
                : {vtranslate($SOURCEMODULE, $SOURCEMODULE)}
            </strong></h4>
            <span class="pull-right">
			    <button class="btn btn-default addButton addWidget" type="button"><i class="fa fa-plus"></i>&nbsp;<strong>{vtranslate('Add widget', $QUALIFIED_MODULE)}</strong></button>
		    </span>
            <div class="clearfix"></div>
        </div>
        <div class="clearfix">&nbsp;</div>
        <div class="editFieldsTable block_9 marginBottom10px border1px  blockSortable ui-sortable-handle">
            <div class="blockFieldsList  blockFieldsSortable  row">
                {assign var=WIDGETCOL_COUNT value=count($WIDGETS)}
                {foreach from=$WIDGETS item=WIDGETCOL key=column}
                    <ul class="connectedSortable {if $WIDGETCOL_COUNT ==3}col-sm-4{else}col-sm-6{/if} ui-sortable" data-column="{$column}">
                        <div class="blocksSortable span4" data-column="{$column}">
                            {foreach from=$WIDGETCOL item=WIDGET key=key}
                                <div class="blockSortable" data-id="{$key}">
                                    <div class="border1px" style="margin: 10px 5px;">
                                        <div class="row" style="padding: 10px;">
                                            <div class="col-sm-5">
                                                <img class="alignMiddle" src="{vimage_path('drag.png')}"/>
                                                &nbsp;&nbsp;{vtranslate($WIDGET['type'], $QUALIFIED_MODULE)}
                                            </div>
                                            <div class="col-sm-5">
                                                {vtranslate($WIDGET['label'], $SOURCEMODULE)}&nbsp;
                                            </div>
                                            <div class="col-sm-2">
                                                <span class="pull-right">
                                                    <i class="fa fa-pencil editWidget" title="{vtranslate('Edit', $QUALIFIED_MODULE)}"></i>
                                                    &nbsp;&nbsp;<i class="fa fa-trash removeWidget" title="{vtranslate('Remove', $QUALIFIED_MODULE)}"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            {/foreach}
                        </div>
                    </ul>
                {/foreach}
            </div>
        </div>
        <div class="col-sm-12 padding1per row">
            <h5 class="vt-callout-header" ><span class="fa fa-info-circle" style="color: #2b9cbd;"></span>&nbsp;{vtranslate('Drag and drop widget box', $QUALIFIED_MODULE)}</h5>
        </div>
        <div class="clearfix"></div>
    </div>
{/strip}