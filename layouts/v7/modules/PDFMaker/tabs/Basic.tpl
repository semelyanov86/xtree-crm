<div class="tab-pane active" id="pdfContentEdit">
    <div class="edit-template-content">
        {********************************************* PROPERTIES DIV*************************************************}
        <div class="properties_div">
            {* pdf module name *}
            <div class="form-group">
                <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                    {vtranslate('LBL_PDF_NAME',$MODULE)}:&nbsp;<span class="redColor">*</span>
                </label>
                <div class="controls col-sm-9">
                    <input name="filename" id="filename" type="text" value="{$FILENAME}" data-rule-required="true" class="inputElement nameField" tabindex="1">
                </div>
            </div>
            {if $IS_BLOCK eq true}
                <div class="form-group">
                    <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                        {vtranslate('LBL_TYPE',$MODULE)}:
                    </label>
                    <div class="controls col-sm-9">
                        {if $SAVETEMPLATEID neq "" && $TEMPLATEBLOCKTYPE neq ""}
                            {$TEMPLATEBLOCKTYPEVAL}
                            <input type="hidden" name="blocktype" id="blocktype" value="{$TEMPLATEBLOCKTYPE}">
                        {else}
                            <select name="blocktype" id="blocktype" class="select2 form-control" data-rule-required="true">
                                <option value="header" {if $TEMPLATEBLOCKTYPE eq 'header'}selected{/if}>{vtranslate('Header',$MODULE)}</option>
                                <option value="footer" {if $TEMPLATEBLOCKTYPE eq 'footer'}selected{/if}>{vtranslate('Footer',$MODULE)}</option>
                            </select>
                        {/if}
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                        {vtranslate('LBL_DESCRIPTION',$MODULE)}:
                    </label>
                    <div class="controls col-sm-9">
                        <input name="description" type="text" value="{$DESCRIPTION}" class="inputElement" tabindex="2">
                    </div>
                </div>
                {* pdf header variables*}
                <div class="form-group" id="header_variables">
                    <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                        {vtranslate('LBL_HEADER_FOOTER_VARIABLES',$MODULE)}:
                    </label>
                    <div class="controls col-sm-9">
                        <div class="input-group">
                            <select name="header_var" id="header_var" class="select2 form-control">
                                {html_options  options=$HEAD_FOOT_VARS selected=""}
                            </select>
                            <div class="input-group-btn">
                                <button type="button" class="btn btn-success InsertIntoTemplate" data-type="header_var" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            {/if}
            {* pdf source module and its available fields *}
            <div class="form-group">
                <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                    {vtranslate('LBL_MODULENAMES',$MODULE)}:{if $TEMPLATEID eq "" && $IS_BLOCK neq true}&nbsp;<span class="redColor">*</span>&nbsp;{/if}
                </label>
                <div class="controls col-sm-9">
                    <select name="modulename" id="modulename" class="select2 form-control" {if $IS_BLOCK neq true}data-rule-required="true"{/if}>
                        {if $TEMPLATEID neq "" || $SELECTMODULE neq ""}
                            {html_options  options=$MODULENAMES selected=$SELECTMODULE}
                        {else}
                            {html_options  options=$MODULENAMES}
                        {/if}
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                </label>
                <div class="controls col-sm-9">
                    <div class="input-group">
                        <select name="modulefields" id="modulefields" class="select2 form-control">
                            {if $TEMPLATEID eq "" && $SELECTMODULE eq ""}
                                <option value="">{vtranslate('LBL_SELECT_MODULE_FIELD',$MODULE)}</option>
                            {else}
                                {html_options  options=$SELECT_MODULE_FIELD}
                            {/if}
                        </select>
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-success InsertIntoTemplate" data-type="modulefields" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                            <button type="button" class="btn btn-warning InsertLIntoTemplate" data-type="modulefields" title="{vtranslate('LBL_INSERT_LABEL_TO_TEXT',$MODULE)}"><i class="fa fa-text-width"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            {* related modules and its fields *}
            <div class="form-group">
                <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                    {vtranslate('LBL_RELATED_MODULES',$MODULE)}:
                </label>
                <div class="controls col-sm-9">
                    <select name="relatedmodulesorce" id="relatedmodulesorce" class="select2 form-control">
                        <option value="">{vtranslate('LBL_SELECT_MODULE',$MODULE)}</option>
                        {foreach item=RelMod from=$RELATED_MODULES}
                            <option value="{$RelMod.3}|{$RelMod.0}" data-module="{$RelMod.3}">{$RelMod.1} ({$RelMod.2})</option>
                        {/foreach}
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                </label>
                <div class="controls col-sm-9">
                    <div class="input-group">
                        <select name="relatedmodulefields" id="relatedmodulefields" class="select2 form-control">
                            <option value="">{vtranslate('LBL_SELECT_MODULE_FIELD',$MODULE)}</option>
                        </select>
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-success InsertIntoTemplate" data-type="relatedmodulefields" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                            <button type="button" class="btn btn-warning InsertLIntoTemplate" data-type="relatedmodulefields" title="{vtranslate('LBL_INSERT_LABEL_TO_TEXT',$MODULE)}"><i class="fa fa-text-width"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            {* related bloc tpl *}
            {if $IS_BLOCK neq true}
                <div class="form-group" id="related_block_tpl_row">
                    <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                        {vtranslate('LBL_RELATED_BLOCK_TPL',$MODULE)}:
                    </label>
                    <div class="controls col-sm-9">
                        <div class="input-group">
                            <select name="related_block" id="related_block" class="select2 form-control" >
                                {html_options options=$RELATED_BLOCKS}
                            </select>
                            <div class="input-group-btn">
                                <button type="button" class="btn btn-success marginLeftZero" onclick="PDFMaker_EditJs.InsertRelatedBlock();" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                <button type="button" class="btn addButton marginLeftZero" onclick="PDFMaker_EditJs.CreateRelatedBlock();" title="{vtranslate('LBL_CREATE')}"><i class="fa fa-plus"></i></button>
                                <button type="button" class="btn marginLeftZero" onclick="PDFMaker_EditJs.EditRelatedBlock();" title="{vtranslate('LBL_EDIT')}"><i class="fa fa-edit"></i></button>
                                <button type="button" class="btn btn-danger marginLeftZero" class="crmButton small delete" onclick="PDFMaker_EditJs.DeleteRelatedBlock();" title="{vtranslate('LBL_DELETE')}"><i class="fa fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            {/if}
            <div class="form-group">
                <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                    {vtranslate('LBL_COMPANY_INFO',$MODULE)}:
                </label>
                <div class="controls col-sm-9">
                    <div class="input-group">
                        <select name="acc_info" id="acc_info" class="select2 form-control">
                            {html_options  options=$ACCOUNTINFORMATIONS}
                        </select>
                        <div id="acc_info_div" class="input-group-btn">
                            <button type="button" class="btn btn-success InsertIntoTemplate" data-type="acc_info" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                            <button type="button" class="btn btn-warning InsertLIntoTemplate" data-type="acc_info" title="{vtranslate('LBL_INSERT_LABEL_TO_TEXT',$MODULE)}"><i class="fa fa-text-width"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                    {vtranslate('LBL_SELECT_USER_INFO',$MODULE)}:
                </label>
                <div class="controls col-sm-9">
                    <select name="acc_info_type" id="acc_info_type" class="select2 form-control" onChange="PDFMaker_EditJs.change_acc_info(this)">
                        {html_options  options=$CUI_BLOCKS}
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label fieldLabel col-sm-3" style="font-weight: normal"></label>
                <div class="controls col-sm-9">
                    <div id="user_info_div" class="au_info_div">
                        <div class="input-group">
                            <select name="user_info" id="user_info" class="select2 form-control">
                                {html_options  options=$USERINFORMATIONS['a']}
                            </select>
                            <div class="input-group-btn">
                                <button type="button" class="btn btn-success InsertIntoTemplate" data-type="user_info" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                <button type="button" class="btn btn-warning InsertLIntoTemplate" data-type="user_info" title="{vtranslate('LBL_INSERT_LABEL_TO_TEXT',$MODULE)}"><i class="fa fa-text-width"></i></button>
                            </div>
                        </div>
                    </div>
                    <div id="logged_user_info_div" class="au_info_div" style="display:none;">
                        <div class="input-group">
                            <select name="logged_user_info" id="logged_user_info" class="select2 form-control">
                                {html_options  options=$USERINFORMATIONS['l']}
                            </select>
                            <div class="input-group-btn">
                                <button type="button" class="btn btn-success InsertIntoTemplate" data-type="logged_user_info" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                <button type="button" class="btn btn-warning InsertLIntoTemplate" data-type="logged_user_info" title="{vtranslate('LBL_INSERT_LABEL_TO_TEXT',$MODULE)}"><i class="fa fa-text-width"></i></button>
                            </div>
                        </div>
                    </div>
                    <div id="modifiedby_user_info_div" class="au_info_div" style="display:none;">
                        <div class="input-group">
                            <select name="modifiedby_user_info" id="modifiedby_user_info" class="select2 form-control">
                                {html_options  options=$USERINFORMATIONS['m']}
                            </select>
                            <div class="input-group-btn">
                                <button type="button" class="btn btn-success InsertIntoTemplate" data-type="modifiedby_user_info" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                <button type="button" class="btn btn-warning InsertLIntoTemplate" data-type="modifiedby_user_info" title="{vtranslate('LBL_INSERT_LABEL_TO_TEXT',$MODULE)}"><i class="fa fa-text-width"></i></button>
                            </div>
                        </div>
                    </div>
                    <div id="smcreator_user_info_div" class="au_info_div" style="display:none;">
                        <div class="input-group">
                            <select name="smcreator_user_info" id="smcreator_user_info" class="select2 form-control">
                                {html_options  options=$USERINFORMATIONS['c']}
                            </select>
                            <div class="input-group-btn">
                                <button type="button" class="btn btn-success InsertIntoTemplate" data-type="smcreator_user_info" title="{vtranslate('LBL_INSERT_VARIABLE_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                                <button type="button" class="btn btn-warning InsertLIntoTemplate" data-type="smcreator_user_info" title="{vtranslate('LBL_INSERT_LABEL_TO_TEXT',$MODULE)}"><i class="fa fa-text-width"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>