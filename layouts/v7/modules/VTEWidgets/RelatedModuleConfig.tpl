<style type="text/css">
    .qtip {
        z-index: 15005 !important;
    }
</style>
<div class="modal-dialog createFieldModal modelContainer ">
    <div class="modal-content">
        <form class="form-horizontal createCustomFieldForm form-modalAddWidget" >
            <input type="hidden" name="wid" value="{$WID}">
            <input type="hidden" name="type" value="{$TYPE}">
            <div class="modal-header">
                <div class="clearfix">
                    <div class="pull-right ">
                        <button type="button" class="close" aria-label="Close" data-dismiss="modal">
                            <span aria-hidden="true" class="fa fa-close"></span>
                        </button>
                    </div>
                    <h4 class="pull-left">{vtranslate('Add widget', $QUALIFIED_MODULE)}</h4>
                </div>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="control-label fieldLabel col-sm-5">{vtranslate('Type widget', $QUALIFIED_MODULE)}:</label>
                    <label class="control-label fieldLabel col-sm-5" style="text-align: left;">
                        {vtranslate($TYPE, $QUALIFIED_MODULE)}
                    </label>
                </div>
                <div class="form-group">
                    <label class="control-label fieldLabel col-sm-5">{vtranslate('Label', $QUALIFIED_MODULE)}:</label>
                    <div class="controls col-sm-7">
                        <input name="label" class="inputElement col-sm-9" style="width: 75%" type="text" value="{$WIDGETINFO['label']}" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label fieldLabel col-sm-5">{vtranslate('Limit entries', $QUALIFIED_MODULE)}:</label>
                    <div class="controls col-sm-7">
                        <input name="limit" class="inputElement col-sm-9" style="width: 75%" type="text" value="{$WIDGETINFO['data']['limit']}"/>
                        <a href="#" class="HelpInfoPopover pull-right" title="" data-placement="top" data-content="{vtranslate('Limit entries info', $QUALIFIED_MODULE)}" data-original-title="{vtranslate('Limit entries', $QUALIFIED_MODULE)}"><i class="icon-info-sign"></i></a>
                    </div>
                </div>

                <div class="form-group"">
                    <label class="control-label fieldLabel col-sm-5">{vtranslate('Related module', $QUALIFIED_MODULE)} <span class="redColor">*</span>:</label>
                    <div class="controls col-sm-7">
                        <select name="relatedmodule" class="fieldTypesList col-sm-9 select2-chosen">
                            <option value="">Select an option</option>
                            {foreach from=$RELATEDMODULES item=item key=key}
                                <option value="{$item['related_tabid']}" {if $WIDGETINFO['data']['relatedmodule'] == $item['related_tabid']}selected{/if} >{vtranslate($item['label'], $item['name'])}</option>
                            {/foreach}
                        </select>
                        <a href="#" class="HelpInfoPopover pull-right" title="" data-placement="top" data-content="{vtranslate('Related module info', $QUALIFIED_MODULE)}" data-original-title="{vtranslate('Related module', $QUALIFIED_MODULE)}"><i class="icon-info-sign"></i></a>
                    </div>
                </div>
                {if $WIDGETINFO['data']['relatedmodule'] == ''}
                    {assign var=RELATEDMODULE value="{$DEFAULT_MODULE}"}
                {else}
                    {assign var=RELATEDMODULE value=$WIDGETINFO['data']['relatedmodule']}
                    {if $WIDGETINFO['data']['relatedmodule']==9}
                        {if $WIDGETINFO['data']['activitytypes']=='Events'}
                            {assign var=RELATEDMODULE value=16}
                        {/if}
                    {/if}
                {/if}

                <div id="activity_types" class="form-group {if $WIDGETINFO['data']['relatedmodule']!=9}hide{/if}">
                    <label class="control-label fieldLabel col-sm-5">{vtranslate('Activity Types', $QUALIFIED_MODULE)}</label>
                    <div class="controls col-sm-7" >
                        <select name="activitytypes" class="fieldTypesList col-sm-9 select2-chosen">
                            <option value="All" {if $WIDGETINFO['data']['activitytypes']=="All"}selected{/if}>{vtranslate('Tasks & Events', $QUALIFIED_MODULE)}</option>
                            <option value="Tasks" {if $WIDGETINFO['data']['activitytypes']=="Tasks"}selected{/if}>{vtranslate('Tasks', $QUALIFIED_MODULE)}</option>
                            <option value="Events" {if $WIDGETINFO['data']['activitytypes']=="Events"}selected{/if}>{vtranslate('Events', $QUALIFIED_MODULE)}</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label fieldLabel col-sm-5">{vtranslate('Columns', $QUALIFIED_MODULE)}:</label>
                    <div class="controls col-sm-7">
                        <select data-placeholder="Select columns" multiple class="select2 columnsSelect select2-offscreen" id="viewColumnsSelect" style="width:210px;height:30px;">
                            {foreach item=FIELD_NAME from=$WIDGETINFO['data']['fieldList']}
                                {assign var=CUR_FIELD_LABEL value={$MODULE_MODEL->getFieldLabel({$RELATED_MODULENAME},{$FIELD_NAME})}}
                                {if $RELATED_MODULENAME=='Calendar' && !$CUR_FIELD_LABEL}
                                    {assign var=CUR_FIELD_LABEL value={$MODULE_MODEL->getFieldLabel('Events',{$FIELD_NAME})}}
                                {/if}
                                <option value="{$FIELD_NAME}" data-field-name="{$FIELD_NAME}" selected>{$CUR_FIELD_LABEL}</option>
                            {/foreach}

                            {foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$MODULE_MODEL->getRecordStructure($RELATEDMODULE)}
                                <optgroup label='{vtranslate($BLOCK_LABEL, $RELATED_MODULENAME)}'>
                                    {foreach key=FIELD_NAME item=FIELD_LABEL from=$BLOCK_FIELDS}
                                        {assign var=FIELD_MODEL value=Vtiger_Field_Model::getInstance($FIELD_NAME, Vtiger_Module_Model::getInstance($RELATED_MODULENAME))}
                                        {if $FIELD_MODEL}
                                            {if $FIELD_MODEL->getDisplayType() == '6'}
                                                {continue}
                                            {/if}
                                            {if $WIDGETINFO['data']['relatedmodule']==9 && !in_array($FIELD_MODEL->getDisplayType(),array('1','2'))}
                                                {continue}
                                            {/if}
                                        {/if}
                                        <option value="{$FIELD_NAME}" data-field-name="{$FIELD_NAME}"
                                                {if is_array($WIDGETINFO['data']['fieldList']) && in_array($FIELD_NAME, $WIDGETINFO['data']['fieldList'])}
                                                    selected
                                                {/if}
                                        >{vtranslate($FIELD_LABEL, $WIDGETINFO['data']['relatedmodule'])}
                                        </option>
                                    {/foreach}
                                </optgroup>
                            {/foreach}
                            {if $WIDGETINFO['data']['relatedmodule']==9 && $WIDGETINFO['data']['activitytypes']=='All'}
                                {foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$MODULE_MODEL->getRecordStructure(16)}
                                    <optgroup label='{vtranslate($BLOCK_LABEL, 'Events')}'>
                                        {foreach key=FIELD_NAME item=FIELD_LABEL from=$BLOCK_FIELDS}
                                            {assign var=FIELD_MODEL value=Vtiger_Field_Model::getInstance($FIELD_NAME, Vtiger_Module_Model::getInstance('Events'))}
                                            {if $FIELD_MODEL}
                                                {if $FIELD_MODEL->getDisplayType() == '6'|| !in_array($FIELD_MODEL->getDisplayType(),array('1','2'))}
                                                    {continue}
                                                {/if}
                                            {/if}
                                            <option value="{$FIELD_NAME}" data-field-name="{$FIELD_NAME}"
                                                    {if is_array($WIDGETINFO['data']['fieldList']) && in_array($FIELD_NAME, $WIDGETINFO['data']['fieldList'])}
                                                        selected
                                                    {/if}
                                            >{vtranslate($FIELD_LABEL, $WIDGETINFO['data']['relatedmodule'])}
                                            </option>
                                        {/foreach}
                                    </optgroup>
                                {/foreach}
                            {/if}

                        </select>
                        {if $WIDGETINFO['data']['fieldList'] !=''}
                            <input type="hidden" name="selected_fields" value="{$FIELDLIST}">
                        {/if}
                    </div>
                </div>
                <div class="form-group vte-preview-email {if $WIDGETINFO['data']['relatedmodule'] != $EMAIL_TABID || empty( $WIDGETINFO['data']['relatedmodule'])}hide{/if}">
                    <label class="control-label fieldLabel col-sm-5">{vtranslate('Preview Email', $QUALIFIED_MODULE)}:</label>
                    <div class="controls col-sm-7" style="padding-top: 5px;">
                        <input name="emailtabid" class="span3" type="hidden" value="{$EMAIL_TABID}"/>
                        <input name="preview_email" class="span3" type="checkbox" value="1" {if $WIDGETINFO['preview_email']==1} checked{/if}/></div>
                </div>
                <div class="form-group">
                    <label class="control-label fieldLabel col-sm-5">{vtranslate('Sort By', $QUALIFIED_MODULE)}:</label>
                    <div class="controls col-sm-7">
                        <select name="sortby" data-placeholder="Select columns" class="fieldTypesList col-sm-6 select2-chosen" id="sortby">
                            <option value="-1">{vtranslate('LBL_NONE',$QUALIFIED_MODULE)}</option>
                            {foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$MODULE_MODEL->getRecordStructure($RELATEDMODULE)}
                                <optgroup label='{vtranslate($BLOCK_LABEL, $RELATED_MODULENAME)}'>
                                    {foreach key=FIELD_NAME item=FIELD_LABEL from=$BLOCK_FIELDS}
                                        {assign var=FIELD_MODEL value=Vtiger_Field_Model::getInstance($FIELD_NAME, Vtiger_Module_Model::getInstance($RELATED_MODULENAME))}
                                        {if $FIELD_MODEL}
                                            {if $FIELD_MODEL->getDisplayType() == '6'}
                                                {continue}
                                            {/if}
                                            {if $WIDGETINFO['data']['relatedmodule']==9 && !in_array($FIELD_MODEL->getDisplayType(),array('1','2'))}
                                                {continue}
                                            {/if}
                                        {/if}
                                        <option value="{$FIELD_NAME}"
                                                {if $WIDGETINFO['data']['sortby'] != -1 AND  $FIELD_NAME == $WIDGETINFO['data']['sortby']}
                                        selected
                                                {/if}>
                                            {vtranslate($FIELD_LABEL, $WIDGETINFO['data']['relatedmodule'])}
                                        </option>
                                    {/foreach}
                                </optgroup>
                            {/foreach}
                            {if $WIDGETINFO['data']['relatedmodule']==9 && $WIDGETINFO['data']['activitytypes']=='All'}
                                {foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$MODULE_MODEL->getRecordStructure(16)}
                                    <optgroup label='{vtranslate($BLOCK_LABEL, 'Events')}'>
                                        {foreach key=FIELD_NAME item=FIELD_LABEL from=$BLOCK_FIELDS}
                                            {assign var=FIELD_MODEL value=Vtiger_Field_Model::getInstance($FIELD_NAME, Vtiger_Module_Model::getInstance('Events'))}
                                            {if $FIELD_MODEL}
                                                {if $FIELD_MODEL->getDisplayType() == '6'|| !in_array($FIELD_MODEL->getDisplayType(),array('1','2'))}
                                                    {continue}
                                                {/if}
                                            {/if}
                                            <option value="{$FIELD_NAME}"
                                                    {if $WIDGETINFO['data']['sortby'] != -1 AND  $FIELD_NAME == $WIDGETINFO['data']['sortby']}
                                            selected
                                                    {/if}>
                                                {vtranslate($FIELD_LABEL, $WIDGETINFO['data']['relatedmodule'])}
                                            </option>
                                        {/foreach}
                                    </optgroup>
                                {/foreach}
                            {/if}
                        </select>
                        <select name="sorttype" class="fieldTypesList select2-chosen" id="sorttype">
                            <option value="ASC" {if $WIDGETINFO['data']['sorttype'] eq 'ASC'}selected{/if}>ASC</option>
                            <option value="DESC" {if $WIDGETINFO['data']['sorttype'] eq 'DESC'}selected{/if}>DESC</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <div id="add_action_btn" {if $WIDGETINFO['data']['activitytypes']=='Events'}class="hide"{/if}>
                        <label class="control-label fieldLabel col-sm-5">{vtranslate('Add Button', $QUALIFIED_MODULE)}</label>
                        <div class="controls col-sm-2" style=" padding-top: 5px;">
                            <input name="action" class="span3" type="checkbox" value="1" {if $ISADD == 0}disabled="disabled" {/if} {if $WIDGETINFO['data']['action'] == 1}checked{/if}/>
                            <span class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="{vtranslate('Add button info', $QUALIFIED_MODULE)}"></span>
                            {*<a href="#" class="HelpInfoPopover pull-right" title="" data-placement="top" data-content="{vtranslate('Add button info', $QUALIFIED_MODULE)}" data-original-title="{vtranslate('Add button', $QUALIFIED_MODULE)}"><i class="icon-info-sign"></i></a>*}
                        </div>
                    </div>
                    <div id="add_event_btn" {if $WIDGETINFO['data']['relatedmodule']!=9 || $WIDGETINFO['data']['activitytypes']=='Tasks'}class="hide"{/if}>
                        <label class="control-label fieldLabel {if $WIDGETINFO['data']['activitytypes']=='Events'}col-sm-5{else}col-sm-3{/if}">{vtranslate('Add event button', $QUALIFIED_MODULE)}</label>
                        <div class="controls col-sm-2" style="padding-top: 5px;">
                            <input name="action_event" class="span3" type="checkbox" value="1" {if $ISADD == 0}disabled="disabled" {/if} {if $WIDGETINFO['data']['action_event'] == 1}checked{/if}/>
                            <span class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="Enable button event to add record in widget record view"></span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label fieldLabel col-sm-5">{vtranslate('Select button', $QUALIFIED_MODULE)}:</label>
                    <div class="controls col-sm-7" style="padding-top: 5px;">
                        <input name="select" class="span3" type="checkbox" value="1" {if $ISSELECT == 0}disabled="disabled" {/if} {if $WIDGETINFO['data']['select'] == 1}checked{/if}/>
                        <span class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="{vtranslate('Select button info', $QUALIFIED_MODULE)}"></span>
                        {*<a href="#" class="HelpInfoPopover pull-right" title="" data-placement="top" data-content="{vtranslate('Select button info', $QUALIFIED_MODULE)}" data-original-title="{vtranslate('Select button', $QUALIFIED_MODULE)}"><i class="icon-info-sign"></i></a>*}
                    </div>
                </div>
                <div class="form-group"">
                    <label class="control-label fieldLabel col-sm-5">{vtranslate('Filter', $QUALIFIED_MODULE)}:</label>
                    <div class="controls col-sm-7">
                        <input type="hidden" name="filter_selected" value="{$WIDGETINFO['data']['filter']}">
                        <select name="filter" class="fieldTypesList col-sm-9 select2-chosen">
                            <option value="-">{vtranslate('None', $QUALIFIED_MODULE)}</option>
                        </select>
                        <span style="padding: 8px;" class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="{vtranslate('Filter info', $QUALIFIED_MODULE)}"></span>
                        {*<a href="#" class="HelpInfoPopover pull-right" title="" data-placement="top" data-content="{vtranslate('Filter info', $QUALIFIED_MODULE)}" data-original-title="{vtranslate('Filter', $QUALIFIED_MODULE)}"><i class="icon-info-sign"></i></a>*}
                    </div>
                </div>
                <div class="form-group"">
                    <label class="control-label fieldLabel col-sm-5">{vtranslate('Default Filter Value', $QUALIFIED_MODULE)}:</label>
                    <div class="controls col-sm-7">
                        <input type="hidden" name="default_filter_selected" value="{$WIDGETINFO['data']['default_filter_value']}">
                        <select name="default_filter_value" class="fieldTypesList col-sm-9 select2-chosen">
                            <option value="-">{vtranslate('None', $QUALIFIED_MODULE)}</option>
                        </select>
                        <span style="padding: 8px;" class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="{vtranslate('Default Filter Value info', $QUALIFIED_MODULE)}"></span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label fieldLabel col-sm-5">{vtranslate('Active', $QUALIFIED_MODULE)}:</label>
                    <div class="controls col-sm-7" style="padding-top: 5px;">
                        <input name="isactive" class="span3" type="checkbox" value="1"{if $WIDGETINFO['isactive']==''}checked {elseif $WIDGETINFO['isactive']==1} checked{/if}/></div>
                </div>
                <div class="form-group">
                    <label class="control-label fieldLabel col-sm-5">{vtranslate('Advanced Query Box', $QUALIFIED_MODULE)}:</label>
                    <div class="controls col-sm-7" style="padding-top: 5px;">
                        <textarea name="advanced_query" row="5" style="width: 75%;height: 100px;">{$WIDGETINFO['advanced_query']}</textarea></div>
                </div>
            </div>
            <div class="modal-footer">
                <button name='saveButton' class="btn btn-success saveButton"  aria-hidden="true" >{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</button>
            </div>
        </form>
    </div>
</div>