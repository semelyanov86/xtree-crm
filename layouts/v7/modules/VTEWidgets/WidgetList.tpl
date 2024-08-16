<div class="modal-dialog createFieldModal modelContainer ">
    <div class="modal-content">
        <form class="form-horizontal createCustomFieldForm form-modalAddWidget">
            <div class="modal-header">
                <div class="clearfix">
                    <div class="pull-right ">
                        <button type="button" class="close" aria-label="Close" data-dismiss="modal"><span
                                    aria-hidden="true" class="fa fa-close"></span></button>
                    </div>
                    <h4 class="pull-left">{vtranslate('Add widget', $QUALIFIED_MODULE)}</h4>
                </div>
            </div>
            <div class="modal-body ">
                <div class="form-group">
                    <label class="control-label fieldLabel col-sm-5">{vtranslate('LBL_WIDGET_TYPE', $QUALIFIED_MODULE)}:</label>
                    <div class="controls col-sm-7">
                        <select name="type" class="fieldTypesList col-sm-9 select2-chosen">
                            {foreach from=$MODULE_MODEL->getType() item=item key=key}
                                <option value="{$key}">{vtranslate($item, $QUALIFIED_MODULE)}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer ">
                <center>
                    <button name='saveButton' class="btn btn-success saveButton" data-dismiss="modal" aria-hidden="true">{vtranslate('Continue', $QUALIFIED_MODULE)}</button>
                </center>
            </div>
        </form>
    </div>
</div>