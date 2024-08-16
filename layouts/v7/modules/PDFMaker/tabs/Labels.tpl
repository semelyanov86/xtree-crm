<div class="tab-pane" id="pdfContentLabels">
    <div class="edit-template-content">
        {********************************************* Labels *************************************************}
        <div class="form-group" id="labels_div">
            <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                {vtranslate('LBL_GLOBAL_LANG',$MODULE)}:
            </label>
            <div class="controls col-sm-9">
                <div class="input-group">
                    <select name="global_lang" id="global_lang" class="select2 form-control" data-width="100%">
                        <option value="">{vtranslate('LBL_SELECT_LABEL', $QUALIFIED_MODULE)}</option>
                        {html_options  options=$GLOBAL_LANG_LABELS}
                    </select>
                    <span class="input-group-btn">
                        <button type="button" class="btn btn-warning InsertIntoTemplate" data-type="global_lang" title="{vtranslate('LBL_INSERT_LABEL_TO_TEXT',$MODULE)}"><i class="fa fa-text-width"></i></button>
                    </span>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                {vtranslate('LBL_MODULE_LANG',$MODULE)}:
            </label>
            <div class="controls col-sm-9">
                <div class="input-group">
                    <select name="module_lang" id="module_lang" class="select2 form-control" data-width="100%">
                        <option value="">{vtranslate('LBL_SELECT_LABEL', $QUALIFIED_MODULE)}</option>
                        {html_options options=$MODULE_LANG_LABELS}
                    </select>
                    <span class="input-group-btn">
                        <button type="button" class="btn btn-warning InsertIntoTemplate" data-type="module_lang" title="{vtranslate('LBL_INSERT_LABEL_TO_TEXT',$MODULE)}"><i class="fa fa-text-width"></i></button>
                    </span>
                </div>
            </div>
        </div>
        {if $VERSION_TYPE eq 'professional'}
            <div class="form-group">
                <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                    {vtranslate('LBL_CUSTOM_LABELS',$MODULE)}:
                </label>
                <div class="controls col-sm-9">
                    <div class="input-group">
                        <select name="custom_lang" id="custom_lang" class="select2 form-control" data-width="100%">
                            <option value="">{vtranslate('LBL_SELECT_LABEL', $QUALIFIED_MODULE)}</option>
                            {html_options  options=$CUSTOM_LANG_LABELS}
                        </select>
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-warning InsertIntoTemplate" data-type="custom_lang" title="{vtranslate('LBL_INSERT_LABEL_TO_TEXT',$MODULE)}"><i class="fa fa-text-width"></i></button>
                        </span>
                    </div>
                </div>
            </div>
        {/if}
    </div>
</div>