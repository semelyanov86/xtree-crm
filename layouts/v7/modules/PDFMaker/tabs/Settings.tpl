<div class="tab-pane" id="editTabSettings">
    <div id="settings_div" class="edit-template-content">
        <div class="form-group">
            <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                {vtranslate('LBL_FILENAME',$MODULE)}:
            </label>
            <div class="controls col-sm-9">
                <input type="text" name="nameOfFile" value="{$NAME_OF_FILE}" id="nameOfFile" class="inputElement getPopupUi">
            </div>
        </div>
        <div class="form-group hide">
            <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
            </label>
            <div class="controls col-sm-9">
                <select name="filename_fields" id="filename_fields" class="select2 form-control" onchange="PDFMaker_EditJs.insertFieldIntoFilename(this.value);">
                    <option value="">{vtranslate('LBL_SELECT_MODULE_FIELD',$MODULE)}</option>
                    <optgroup label="{vtranslate('LBL_COMMON_FILEINFO',$MODULE)}">
                        {html_options  options=$FILENAME_FIELDS}
                    </optgroup>
                    {if $TEMPLATEID neq "" || $SELECTMODULE neq ""}
                        {html_options  options=$SELECT_MODULE_FIELD_FILENAME}
                    {/if}
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                {vtranslate('LBL_PDF_PASSWORD',$MODULE)}:
            </label>
            <div class="controls col-sm-9">
                <input type="text" name="PDFPassword" value="{$PDF_PASSWORD}" id="PDFPassword" class="getPopupUi inputElement">
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

        {* ignored picklist values settings *}
        <div class="form-group">
            <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                {vtranslate('LBL_IGNORE_PICKLIST_VALUES',$MODULE)}:
            </label>
            <div class="controls col-sm-9">
                <input type="text" name="ignore_picklist_values" value="{$IGNORE_PICKLIST_VALUES}" class="inputElement"/>
            </div>
        </div>

        {* status settings *}
        <div class="form-group">
            <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                {vtranslate('LBL_STATUS',$MODULE)}:
            </label>
            <div class="controls col-sm-9">
                <select name="is_active" id="is_active" class="select2 col-sm-12" onchange="PDFMaker_EditJs.templateActiveChanged(this);">
                    {html_options options=$STATUS selected=$IS_ACTIVE}
                </select>
            </div>
        </div>
        {* is default settings *}
        <div class="form-group">
            <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                {vtranslate('LBL_SETASDEFAULT',$MODULE)}:
            </label>
            <div class="controls col-sm-9">
                {vtranslate('LBL_FOR_DV',$MODULE)} <input {if $IS_LISTVIEW_CHECKED eq "yes"}disabled="true"{/if} type="checkbox" id="is_default_dv" name="is_default_dv" {$IS_DEFAULT_DV_CHECKED}/>
                &nbsp;&nbsp;
                {vtranslate('LBL_FOR_LV',$MODULE)}&nbsp;&nbsp;<input type="checkbox" id="is_default_lv" name="is_default_lv" {$IS_DEFAULT_LV_CHECKED}/>
                {* hidden variable for template order settings *}
                <input type="hidden" name="tmpl_order" value="{$ORDER}" />
            </div>
        </div>
        <div class="form-group">
            <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                {vtranslate('LBL_DEFAULT_PRODUCT_IMAGE',$MODULE)}:
            </label>
            <div class="controls col-sm-9">
                <table class="table table-bordered">
                    <tr>
                        <td align="right">{vtranslate('LBL_WIDTH_PX', $MODULE)}</td>
                        <td>
                            <input type="text" name="product_image_width" class="inputElement" value="{$PDF_TEMPLATE_RESULT.product_image_width}">
                        </td>
                    </tr>
                    <tr>
                        <td align="right">{vtranslate('LBL_HEIGHT_PX', $MODULE)}</td>
                        <td>
                            <input type="text" name="product_image_height" class="inputElement" value="{$PDF_TEMPLATE_RESULT.product_image_height}">
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                {vtranslate('LBL_DISABLE_EXPORT_EDIT',$MODULE)}:
            </label>
            <div class="controls col-sm-9">
                <input type="checkbox" name="disable_export_edit" class="inputElement" value="1" {if $PDF_TEMPLATE_RESULT.disable_export_edit}checked{/if}>
            </div>
        </div>
    </div>
</div>