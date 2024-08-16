<div class="tab-pane" id="editTabSignature">
    <div id="signature_div" class="edit-template-content">
        <div class="form-group">
            <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                {vtranslate('LBL_IS_SIGNATURE',$MODULE)}:
            </label>
            <div class="controls col-sm-9">
                <div class="input-group">
                    <select name="signaturevalues" id="signaturevalues" class="select2 form-control">
                        <option value="PDF_SIGNATURE">{vtranslate('PDF_SIGNATURE',$MODULE)}</option>
                    </select>
                    <div class="input-group-btn">
                        <button type="button" class="btn btn-success InsertIntoTemplate" data-type="signaturevalues" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}">
                            <i class="fa fa-usd"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        {* accept signature variables *}
        <div class="form-group">
            <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                {vtranslate('LBL_SIGNATURE_ACCEPT_VARIABLES',$MODULE)}:
            </label>
            <div class="controls col-sm-9">
                <div class="input-group">
                    <select name="signatureacceptvalues" id="signatureacceptvalues" class="select2 form-control">
                        {foreach from=$SIGNATURE_RECORDS item=SIGNATURE_RECORD}
                            <option value="{$SIGNATURE_RECORD->getVariableName()}">{vtranslate($SIGNATURE_RECORD->getName(),$MODULE)}</option>
                        {/foreach}
                    </select>
                    <div class="input-group-btn">
                        <button type="button" class="btn btn-success InsertIntoTemplate" data-type="signatureacceptvalues" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}">
                            <i class="fa fa-usd"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                {vtranslate('LBL_SIGNATURE_ACCEPT_USER',$MODULE)}:
            </label>
            <div class="controls col-sm-9">
                <select name="signature_accept_user" id="signature_accept_user" class="select2 form-control">
                    <option value="">{vtranslate('LBL_ACCEPT_ASSIGNED_TO_USER', $MODULE)}</option>
                    <optgroup label="{vtranslate('LBL_USERS', $MODULE)}">
                        {assign var=CURRENT_USER value=Users_Record_Model::getCurrentUserModel()}
                        {foreach from=$CURRENT_USER->getAccessibleUsers('', $MODULE) key=ACCESSIBLE_USER_ID item=ACCESSIBLE_USER_NAME}
                            <option value="{$ACCESSIBLE_USER_ID}" {if $SIGNATURE_ACCEPT_USER eq $ACCESSIBLE_USER_ID}selected="selected"{/if}>{$ACCESSIBLE_USER_NAME}</option>
                        {/foreach}
                    </optgroup>
                </select>
            </div>
        </div>
        {* signature settings *}
        <div class="form-group">
            <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                {vtranslate('LBL_SIGNATURE',$MODULE)}:
            </label>
            <div class="controls col-sm-9">
                <table class="table table-bordered">
                    <tr>
                        <td align="right">{vtranslate('LBL_WIDTH_PX', $MODULE)}</td>
                        <td>
                            <input type="text" class="inputElement" name="signature_width" value="{$SIGNATURE_WIDTH}">
                        </td>
                    </tr>
                    <tr>
                        <td align="right">{vtranslate('LBL_HEIGHT_PX', $MODULE)}</td>
                        <td>
                            <input type="text" class="inputElement" name="signature_height" value="{$SIGNATURE_HEIGHT}">
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>