
<form class="form-modalAddWidget" style="width: 400px;">
    <div class="modal-header contentsBackground">
        <button type="button" data-dismiss="modal" class="close" title="Close">×</button>
        <h3 id="massEditHeader">{vtranslate('Add widget', $QUALIFIED_MODULE)}</h3>
    </div>
    <div class="modal-body">
        <div class="modal-Fields">
            <div class="row-fluid">
                <div class="span4">{vtranslate('LBL_WIDGET_TYPE', $QUALIFIED_MODULE)}:</div>
                <div class="span8">
                    <select name="type" class="select2 span3 marginLeftZero">
                        {foreach from=$MODULE_MODEL->getType() item=item key=key}
                            <option value="{$key}" >{vtranslate($item, $QUALIFIED_MODULE)}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn btn-success saveButton" data-dismiss="modal" aria-hidden="true" >{vtranslate('LBL_SELECT', $QUALIFIED_MODULE)}</button>
    </div>
</form>