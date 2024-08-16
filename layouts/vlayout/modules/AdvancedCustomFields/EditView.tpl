{*/* ********************************************************************************
* The content of this file is subject to the Table Block ("License");
* You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is VTExperts.com
* Portions created by VTExperts.com. are Copyright(C) VTExperts.com.
* All Rights Reserved.
* ****************************************************************************** */*}

{strip}
    <div id="massEditContainer">
        <div id="massEdit">
            <div class="modal-header contentsBackground">
                <button type="button" class="close " data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 id="massEditHeader">{if $RECORD}{vtranslate('LBL_EDIT')}{else}{vtranslate('LBL_ADD')}{/if} {vtranslate('AdvancedCustomFields', 'AdvancedCustomFields')}</h3>
            </div>
            <form class="form-horizontal" action="index.php" id="tableblocks_form">
                <input type="hidden" name="record" value="{$RECORD}" />

                <div name='massEditContent' class="row-fluid">
                    <div class="modal-body" style="margin-left: -38px;">
                        <div class="control-group">
                            <label class="muted control-label">
                                &nbsp;<strong>{vtranslate('LBL_MODULE', 'AdvancedCustomFields')}</strong>
                            </label>
                            <div class="controls row-fluid">
                                <select class="select2 span4" name="select_module" id="select_module" data-validation-engine='validate[required]]'>
                                    {foreach item=MODULE from=$SUPPORTED_MODULES name=moduleIterator}
                                        <option value="{$MODULE}" {if $MODULE eq $BLOCK_DATA['module']}selected{/if}>
                                            {vtranslate($MODULE, $MODULE)}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
						<div class="control-group" id="div_blocks">
                            {include file='InsertBlock.tpl'|@vtemplate_path:$QUALIFIED_MODULE} 
                        </div>
                        <div class="control-group">
                            <label class="muted control-label">
                                &nbsp;<strong>{vtranslate('LBL_FIELD_TYPE', 'AdvancedCustomFields')}</strong>
                            </label>
                            <div class="controls row-fluid">
                                <select class="select2 span4" name="select_type" id="select_type" data-validation-engine='validate[required]]'>
                                    {foreach item=FIELD_TYPE from=$FIELD_TYPES}
                                        <option value="{$FIELD_TYPE['id']}" {if $FIELD_TYPE['id'] eq $BLOCK_DATA['uitype']}selected{/if}>
                                            {vtranslate($FIELD_TYPE['value'], 'AdvancedCustomFields')}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="muted control-label">
                                &nbsp;<strong>{vtranslate('LBL_NAME', 'AdvancedCustomFields')}</strong>
                            </label>
                            <div class="controls row-fluid">
                                  <input type="text" id = "name" name="name"  value="{$BLOCK_DATA['name']}" class="span3"   style="width: 216px;" />
                            </div>
                        </div>        
                        <div class="control-group">
                            <label class="muted control-label">
                                &nbsp;<strong>{vtranslate('LBL_LABEL', 'AdvancedCustomFields')}</strong>
                            </label>
                            <div class="controls row-fluid">
                                  <input type="text" name="label" value="{$BLOCK_DATA['label']}" class="span3"   style="width: 216px;" />
                            </div>
                        </div>  
                        
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="pull-right cancelLinkContainer" style="margin-top:0px;">
                        <a class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                    </div>
                    <button class="btn btn-success" type="submit" name="saveButton"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
                </div>
            </form>
        </div>
    </div>
{/strip}