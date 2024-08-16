
<form class="form-modalAddWidget" style="width: 450px;">
    <input type="hidden" name="wid" value="{$WID}">
    <input type="hidden" name="type" value="{$TYPE}">
    <div class="modal-header contentsBackground">
        <button type="button" data-dismiss="modal" class="close" title="Zamknij">×</button>
        <h3 id="massEditHeader">{vtranslate('Add widget', $QUALIFIED_MODULE)}</h3>
    </div>
    <div class="modal-body">
        <div class="modal-Fields">
            <div class="row-fluid">
                <div class="span5 marginLeftZero">{vtranslate('Type widget', $QUALIFIED_MODULE)}:</div>
                <div class="span7">
                    {vtranslate($TYPE, $QUALIFIED_MODULE)}
                </div>
                <div class="span5 marginLeftZero"><label class="">{vtranslate('Label', $QUALIFIED_MODULE)}:</label></div>
                <div class="span7"><input name="label" class="span3" type="text" value="{$WIDGETINFO['label']}" /></div>

                <div class="span5 marginLeftZero"><label class="">{vtranslate('Limit entries', $QUALIFIED_MODULE)}:</label></div>
                <div class="span7">
                    <input name="limit" class="span3" type="text" value="{$WIDGETINFO['data']['limit']}"/>
                    <a href="#" class="HelpInfoPopover pull-right" title="" data-placement="top" data-content="{vtranslate('Limit entries info', $QUALIFIED_MODULE)}" data-original-title="{vtranslate('Limit entries', $QUALIFIED_MODULE)}"><i class="icon-info-sign"></i></a>
                </div>

                <div class="span5 marginLeftZero"><label class="">{vtranslate('Sort By', $QUALIFIED_MODULE)}:</label></div>
                <div class="span7">
                    <div class="row-fluid">
                        {if $WIDGETINFO['data']['relatedmodule'] == ''}
                            {assign var=RELATEDMODULE value="{$DEFAULT_MODULE}"}
                        {else}
                            {assign var=RELATEDMODULE value=$WIDGETINFO['data']['relatedmodule']}
                        {/if}
                        <select name="sortby" data-placeholder="Select columns" class="select2 marginLeftZero span7" id="sortby">
                            {foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$MODULE_MODEL->getRecordStructure($RELATEDMODULE)}
                                <option value="-1">{vtranslate('LBL_NONE',$QUALIFIED_MODULE)}</option>
                                <optgroup label='{vtranslate($BLOCK_LABEL, $RELATED_MODULENAME)}'>
                                    {foreach key=FIELD_NAME item=FIELD_LABEL from=$BLOCK_FIELDS}
                                        <option value="{$FIELD_NAME}"
                                            {if $WIDGETINFO['data']['sortby'] != -1 AND  $FIELD_NAME == $WIDGETINFO['data']['sortby']}
                                                selected
                                            {/if}>
                                            {vtranslate($FIELD_LABEL, $WIDGETINFO['data']['relatedmodule'])}
                                        </option>
                                    {/foreach}
                                </optgroup>
                            {/foreach}
                        </select>
                        <select name="sorttype" class="select2 marginLeftZero span4" id="sorttype">
                            <option value="ASC" {if $WIDGETINFO['data']['sorttype'] eq 'ASC'}selected{/if}>ASC</option>
                            <option value="DESC" {if $WIDGETINFO['data']['sorttype'] eq 'DESC'}selected{/if}>DESC</option>
                        </select>
                    </div>
                </div>

                <div class="span5 marginLeftZero">{vtranslate('Related module', $QUALIFIED_MODULE)}:</div>
                <div class="span7">
                    <select name="relatedmodule" class="select2 span3 marginLeftZero">
                        {foreach from=$RELATEDMODULES item=item key=key}
                            <option value="{$item['related_tabid']}" {if $WIDGETINFO['data']['relatedmodule'] == $item['related_tabid']}selected{/if} >{vtranslate($item['label'], $item['name'])}</option>
                        {/foreach}
                    </select>
                    <a href="#" class="HelpInfoPopover pull-right" title="" data-placement="top" data-content="{vtranslate('Related module info', $QUALIFIED_MODULE)}" data-original-title="{vtranslate('Related module', $QUALIFIED_MODULE)}"><i class="icon-info-sign"></i></a>
                </div>

                <div class="span5 marginLeftZero">{vtranslate('Columns', $QUALIFIED_MODULE)}:</div>
                <div class="span7 columnsSelectDiv">
                    <select data-placeholder="Select columns" multiple class="select2-container columnsSelect" id="viewColumnsSelect">
                        {foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$MODULE_MODEL->getRecordStructure($RELATEDMODULE)}
                            <optgroup label='{vtranslate($BLOCK_LABEL, $RELATED_MODULENAME)}'>
                                {foreach key=FIELD_NAME item=FIELD_LABEL from=$BLOCK_FIELDS}
                                    <option value="{$FIELD_NAME}" data-field-name="{$FIELD_NAME}"
                                            {if in_array($FIELD_NAME, $WIDGETINFO['data']['fieldList'])}
                                                selected
                                            {/if}
                                            >{vtranslate($FIELD_LABEL, $WIDGETINFO['data']['relatedmodule'])}
                                    </option>
                                {/foreach}
                            </optgroup>
                        {/foreach}

                    </select>
                    {if $WIDGETINFO['data']['fieldList'] !=''}
                        <input type="hidden" name="selected_fields" value="{$FIELDLIST}">
                    {/if}
                </div>

                <div class="span5 marginLeftZero"><label class="">{vtranslate('Add button', $QUALIFIED_MODULE)}:</label></div>
                <div class="span7">
                    <input name="action" class="span3" type="checkbox" value="1" {if $ISADD == 0}disabled="disabled" {/if} {if $WIDGETINFO['data']['action'] == 1}checked{/if}/>
                    <a href="#" class="HelpInfoPopover pull-right" title="" data-placement="top" data-content="{vtranslate('Add button info', $QUALIFIED_MODULE)}" data-original-title="{vtranslate('Add button', $QUALIFIED_MODULE)}"><i class="icon-info-sign"></i></a>
                </div>

                <div class="span5 marginLeftZero"><label class="">{vtranslate('Select button', $QUALIFIED_MODULE)}:</label></div>
                <div class="span7">
                    <input name="select" class="span3" type="checkbox" value="1" {if $ISSELECT == 0}disabled="disabled" {/if} {if $WIDGETINFO['data']['select'] == 1}checked{/if}/>
                    <a href="#" class="HelpInfoPopover pull-right" title="" data-placement="top" data-content="{vtranslate('Select button info', $QUALIFIED_MODULE)}" data-original-title="{vtranslate('Select button', $QUALIFIED_MODULE)}"><i class="icon-info-sign"></i></a>
                </div>

                <div class="span5 marginLeftZero">{vtranslate('Filter', $QUALIFIED_MODULE)}:</div>
                <div class="span7">
                    <input type="hidden" name="filter_selected" value="{$WIDGETINFO['data']['filter']}">
                    <select name="filter" class="select2 span3 marginLeftZero">
                        <option value="-">{vtranslate('None', $QUALIFIED_MODULE)}</option>
                    </select>
                    <a href="#" class="HelpInfoPopover pull-right" title="" data-placement="top" data-content="{vtranslate('Filter info', $QUALIFIED_MODULE)}" data-original-title="{vtranslate('Filter', $QUALIFIED_MODULE)}"><i class="icon-info-sign"></i></a>
                </div>
                <div class="span5 marginLeftZero"><label class="">{vtranslate('Active', $QUALIFIED_MODULE)}:</label></div>
                <div class="span7"><input name="isactive" class="span3" type="checkbox" value="1" {if $WIDGETINFO['isactive']==1} checked{/if}/></div>

            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn btn-success saveButton" data-dismiss="modal" aria-hidden="true" >{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</button>
    </div>
</form>