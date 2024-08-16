<div class="modal-dialog createFieldModal modelContainer ">
    <div class="modal-content">
        <form class="form-horizontal createCustomFieldForm form-modalAddWidget">
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
                        <input name="label" class="inputElement col-sm-9" style="width: 75%" type="text" value="{$WIDGETINFO['label']}"/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label fieldLabel col-sm-5">{vtranslate('Limit entries', $QUALIFIED_MODULE)}:</label>
                    <div class="controls col-sm-7">
                        <input name="limit" class="inputElement col-sm-9" style="width: 75%" type="text" value="{$WIDGETINFO['data']['limit']}"/>
                        <span style="padding: 8px;" class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="{vtranslate('Limit entries info', $QUALIFIED_MODULE)}"></span>
                        {*<a href="#" class="HelpInfoPopover pull-right" title="" data-placement="top"
                           data-content="{vtranslate('Limit entries info', $QUALIFIED_MODULE)}"
                           data-original-title="{vtranslate('Limit entries', $QUALIFIED_MODULE)}"><i
                                    class="icon-info-sign"></i></a>*}
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label fieldLabel col-sm-5">{vtranslate('Active', $QUALIFIED_MODULE)}:</label>
                    <div class="controls col-sm-7" style="padding-top: 5px;">
                        <input name="isactive" class="span3" type="checkbox" value="1" {if $WIDGETINFO['isactive']==''}checked {elseif $WIDGETINFO['isactive']==1} checked{/if}/></div>
                </div>
            </div>
            <div class="modal-footer">
                <center> <button name='saveButton' class="btn btn-success saveButton" data-dismiss="modal" aria-hidden="true">{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</button></center>
            </div>
        </form>
    </div>
</div>
