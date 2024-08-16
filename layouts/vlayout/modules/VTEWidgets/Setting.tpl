
{strip}
    <input type="hidden" id="filters" name="filters" value='{Vtiger_Util_Helper::toSafeHTML($FILTERS)}'>
    <input type="hidden" id="relatedModuleFields" name="relatedModuleFields" value='{Vtiger_Util_Helper::toSafeHTML($RELATED_MODULE_FIELDS)}'>
    <input type="hidden" id="relatedModuleActions" name="relatedModuleActions" value='{Vtiger_Util_Helper::toSafeHTML($RELATED_MODULE_ACTIONS)}'>
    <div class="container-fluid WidgetsManage">
<input type="hidden" name="tabid" value="{$SOURCE}">
<div class="widget_header row-fluid">
    <div class="span8"><h3>{vtranslate($MODULE, $QUALIFIED_MODULE)}</h3>{vtranslate('LBL_MODULE_DESC', $QUALIFIED_MODULE)}</div>
    <div class="span4">
        <div class="pull-right">
            <select class="select2 span3" name="ModulesList">
                {foreach from=$MODULE_MODEL->getModulesList() item=item key=key}
                    <option value="{$key}" {if $SOURCE eq $key}selected{/if}>{vtranslate($item['tablabel'], $item['name'])}</option>
                {/foreach}
            </select>
        </div>
    </div>
</div>
<hr>

<div class="row-fluid marginBottom10px">
    <span class="pull-left"> <input class="pull-left defaultWidget" type="checkbox" name="all_widget" value="1"{if $DEFAULT_WIDGETS['all_widget'] == '1'}checked{/if}></span>
    <span class="span3" style="margin-left: 5px"><h4>{vtranslate('Hide Default Widgets', $QUALIFIED_MODULE)}: {vtranslate($SOURCEMODULE, $SOURCEMODULE)}</h4> </span>
</div>
<div class="blocks-content padding1per">
    <div class="row-fluid marginBottom10px">
        <span class="span2"><input class="pull-left defaultWidget" type="checkbox" name="comments_widget" value="1"{if $DEFAULT_WIDGETS['comments_widget'] == '1'}checked{/if}  > &nbsp;
          <div class="pull-left marginRight10px">&nbsp;{vtranslate('ModComments', $SOURCEMODULE)}&nbsp;&nbsp; </div>

        </span>

        <span class="span2">
         <input class="pull-left defaultWidget" type="checkbox" name="activities_widget" value="1" {if $DEFAULT_WIDGETS['activities_widget'] == 1}checked{/if}  > &nbsp;
          <div class="pull-left marginRight10px">&nbsp;{vtranslate('Activities', $SOURCEMODULE)}&nbsp;&nbsp;   </div>
        </span>

        <span class="span2">
       <input class="pull-left defaultWidget" type="checkbox" name="update_widget" value="1" {if $DEFAULT_WIDGETS['update_widget'] == 1}checked{/if}  > &nbsp;
      <div class="pull-left marginRight10px">&nbsp;{vtranslate('LBL_UPDATES', $SOURCEMODULE)} &nbsp;&nbsp;</div>
        </span>
        {if $SOURCEMODULE=='HelpDesk'}
            <span class="span2">
                <input class="pull-left defaultWidget" type="checkbox" name="document_widget" value="1"{if $DEFAULT_WIDGETS['document_widget'] == '1'}checked{/if}  > &nbsp;
                <div class="pull-left marginRight10px">&nbsp;{vtranslate('Documents', $SOURCEMODULE)} &nbsp;&nbsp;</div>
                </span>
        {/if}
    </div>
    {if $SOURCEMODULE=='Potentials'}
        <div class="row-fluid marginBottom10px">
         <span class="span2">
                    <input class="pull-left defaultWidget" type="checkbox" name="document_widget" value="1"{if $DEFAULT_WIDGETS['document_widget'] == '1'}checked{/if}  > &nbsp;
                            <div class="pull-left marginRight10px">&nbsp;{vtranslate('Documents', $SOURCEMODULE)} &nbsp;&nbsp;
                            </div>
        </span>
          <span class="span2">
           <input class="pull-left defaultWidget" type="checkbox" name="contact_widget" value="1"{if $DEFAULT_WIDGETS['contact_widget'] == '1'}checked{/if}  > &nbsp;
              <div class="pull-left marginRight10px">&nbsp;{vtranslate('LBL_RELATED_CONTACTS', $SOURCEMODULE)} &nbsp;&nbsp;
              </div>
        </span>
          <span class="span2">
           <input class="pull-left defaultWidget" type="checkbox" name="product_widget" value="1"{if $DEFAULT_WIDGETS['product_widget'] == '1'}checked{/if}  > &nbsp;
              <div class="pull-left marginRight10px">&nbsp;{vtranslate('LBL_RELATED_PRODUCTS', $SOURCEMODULE)} &nbsp;&nbsp;
              </div>
        </span>
        </div>
    {/if}
    {if $SOURCEMODULE=='Project'}
        <div class="row-fluid marginBottom10px">
         <span class="span2">
          <input class="pull-left defaultWidget" type="checkbox" name="document_widget" value="1"{if $DEFAULT_WIDGETS['document_widget'] == '1'}checked{/if}  > &nbsp;
          <div class="pull-left marginRight10px">&nbsp;{vtranslate('Documents', $SOURCEMODULE)} &nbsp;&nbsp;     </div>

        </span>
          <span class="span2">
            <input class="pull-left defaultWidget" type="checkbox" name="helpdesk_widget" value="1"{if $DEFAULT_WIDGETS['helpdesk_widget'] == '1'}checked{/if}  > &nbsp;
             <div class="pull-left marginRight10px">&nbsp;{vtranslate('HelpDesk', $SOURCEMODULE)} &nbsp;&nbsp; </div>
        </span>
          <span class="span2">
           <input class="pull-left defaultWidget" type="checkbox" name="milestones_widget" value="1"{if $DEFAULT_WIDGETS['milestones_widget'] == '1'}checked{/if}  > &nbsp;
             <div class="pull-left marginRight10px">&nbsp;{vtranslate('LBL_MILESTONES', $SOURCEMODULE)} &nbsp;&nbsp; </div>
        </span>
         <span class="span2">
           <input class="pull-left defaultWidget" type="checkbox" name="tasks_widget" value="1"{if $DEFAULT_WIDGETS['tasks_widget'] == '1'}checked{/if}  > &nbsp;
            <div class="pull-left marginRight10px">&nbsp;{vtranslate('LBL_TASKS', $SOURCEMODULE)} &nbsp;&nbsp; </div>
        </span>
        </div>
    {/if}
</div>

<div class="clearfix"></div>
<div class="btn-toolbar paddingTop20">
		<span class="pull-left">
			<h4>{vtranslate('List of widgets for the module', $QUALIFIED_MODULE)}: {vtranslate($SOURCEMODULE, $SOURCEMODULE)}</h4>
		</span>
		<span class="pull-right">
			<button class="btn addWidget" type="button"><i class="icon-plus"></i>&nbsp;<strong>{vtranslate('Add widget', $QUALIFIED_MODULE)}</strong></button>
		</span>
    <div class="clearfix"></div>
</div>
<div class="blocks-content padding1per">
    <div class="row-fluid">
        {foreach from=$WIDGETS item=WIDGETCOL key=column}
            <div class="blocksSortable span4" data-column="{$column}">
                {foreach from=$WIDGETCOL item=WIDGET key=key}
                    <div class="blockSortable" data-id="{$key}">
                        <div class="padding1per border1px">
                            <div class="row-fluid">
                                <div class="span5">
                                    <img class="alignMiddle" src="{vimage_path('drag.png')}" /> &nbsp;&nbsp;{vtranslate($WIDGET['type'], $QUALIFIED_MODULE)}
                                </div>
                                <div class="span5">
                                    {vtranslate($WIDGET['label'], $SOURCEMODULE)}&nbsp;
                                </div>
                                <div class="span2">
									<span class="pull-right">
										<i class="cursorPointer icon-pencil editWidget" title="{vtranslate('Edit', $QUALIFIED_MODULE)}"></i>
										&nbsp;&nbsp;<i class="cursorPointer icon-remove removeWidget" title="{vtranslate('Remove', $QUALIFIED_MODULE)}"></i>
									</span>
                                </div>
                            </div>
                        </div>
                    </div>
                {/foreach}
            </div>
        {/foreach}
    </div>
</div>
<div class="clearfix"></div>
{/strip}