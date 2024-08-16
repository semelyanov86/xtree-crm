<div class="tab-pane" id="editTabProperties">
    <div id="properties_div" class="edit-template-content">
        {* pdf format settings *}
        <div class="form-group">
            <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                {vtranslate('LBL_PDF_FORMAT',$MODULE)}:
            </label>
            <div class="controls col-sm-9">
                <select name="pdf_format" id="pdf_format" class="select2 col-sm-12" onchange="PDFMaker_EditJs.CustomFormat();">
                    {html_options  options=$FORMATS selected=$SELECT_FORMAT}
                </select>
                <table class="table showInlineTable" id="custom_format_table" {if $SELECT_FORMAT neq 'Custom'}style="display:none"{/if}>
                    <tr>
                        <td align="right" nowrap>{vtranslate('LBL_WIDTH',$MODULE)}</td>
                        <td>
                            <input type="text" name="pdf_format_width" id="pdf_format_width" class="inputElement" value="{$CUSTOM_FORMAT.width}" style="width:50px">
                        </td>
                        <td align="right" nowrap>{vtranslate('LBL_HEIGHT',$MODULE)}</td>
                        <td>
                            <input type="text" name="pdf_format_height" id="pdf_format_height" class="inputElement" value="{$CUSTOM_FORMAT.height}" style="width:50px">
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        {* pdf orientation settings *}
        <div class="form-group">
            <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                {vtranslate('LBL_PDF_ORIENTATION',$MODULE)}:
            </label>
            <div class="controls col-sm-9">
                <select name="pdf_orientation" id="pdf_orientation" class="select2 col-sm-12">
                    {html_options  options=$ORIENTATIONS selected=$SELECT_ORIENTATION}
                </select>
            </div>
        </div>
        {* pdf margin settings *}
        {assign var=margin_input_width value='50px'}
        {assign var=margin_label_width value='50px'}
        <div class="form-group">
            <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                {vtranslate('LBL_MARGINS',$MODULE)}:
            </label>
            <div class="controls col-sm-9">
                <table class="table table-bordered">
                    <tr>
                        <td align="right" nowrap>{vtranslate('LBL_TOP',$MODULE)}</td>
                        <td>
                            <input type="text" name="margin_top" id="margin_top" class="inputElement" value="{$MARGINS.top}" style="width:{$margin_input_width}" onKeyUp="PDFMaker_EditJs.ControlNumber('margin_top', false);">
                        </td>
                    </tr>
                    <tr>
                        <td align="right" nowrap>{vtranslate('LBL_BOTTOM',$MODULE)}</td>
                        <td>
                            <input type="text" name="margin_bottom" id="margin_bottom" class="inputElement" value="{$MARGINS.bottom}" style="width:{$margin_input_width}" onKeyUp="PDFMaker_EditJs.ControlNumber('margin_bottom', false);">
                        </td>
                    </tr>
                    <tr>
                        <td align="right" nowrap>{vtranslate('LBL_LEFT',$MODULE)}</td>
                        <td>
                            <input type="text" name="margin_left"  id="margin_left" class="inputElement" value="{$MARGINS.left}" style="width:{$margin_input_width}" onKeyUp="PDFMaker_EditJs.ControlNumber('margin_left', false);">
                        </td>
                    </tr>
                    <tr>
                        <td align="right" nowrap>{vtranslate('LBL_RIGHT',$MODULE)}</td>
                        <td>
                            <input type="text" name="margin_right" id="margin_right" class="inputElement" value="{$MARGINS.right}" style="width:{$margin_input_width}" onKeyUp="PDFMaker_EditJs.ControlNumber('margin_right', false);">
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        {* decimal settings *}
        <div class="form-group">
            <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                {vtranslate('LBL_DECIMALS',$MODULE)}:
            </label>
            <div class="controls col-sm-9">
                <table class="table table-bordered">
                    <tr>
                        <td align="right" nowrap>{vtranslate('LBL_DEC_POINT',$MODULE)}</td>
                        <td><input type="text" maxlength="2" name="dec_point" class="inputElement" value="{$DECIMALS.point}" style="width:{$margin_input_width}"/></td>
                    </tr>
                    <tr>
                        <td align="right" nowrap>{vtranslate('LBL_DEC_DECIMALS',$MODULE)}</td>
                        <td><input type="text" maxlength="2" name="dec_decimals" class="inputElement" value="{$DECIMALS.decimals}" style="width:{$margin_input_width}"/></td>
                    </tr>
                    <tr>
                        <td align="right" nowrap>{vtranslate('LBL_DEC_THOUSANDS',$MODULE)}</td>
                        <td><input type="text" maxlength="2" name="dec_thousands" class="inputElement" value="{$DECIMALS.thousands}" style="width:{$margin_input_width}"/></td>
                    </tr>
                    <tr>
                        <td align="right" nowrap>{vtranslate('Truncate Trailing Zeros',$MODULE)}</td>
                        <td><input type="checkbox" value="1" name="dec_truncate_zero" class="inputElement" {if $PDF_TEMPLATE_RESULT.truncate_zero}checked{/if} /></td>
                    </tr>
                </table>
            </div>
        </div>
        {* currency settings *}
        <div class="form-group">
            <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                {vtranslate('LBL_CURRENCY_FORMAT',$MODULE)}:
            </label>
            <div class="controls col-sm-9">
                <table class="table table-bordered">
                    <tr>
                        <td align="right" nowrap>{vtranslate('LBL_CURRENCY_ACTIVE',$MODULE)}</td>
                        <td><input type="checkbox" maxlength="2" name="is_currency" {if $PDF_TEMPLATE_RESULT.is_currency}checked{/if}></td>
                    </tr>
                    <tr>
                        <td align="right" nowrap>{vtranslate('LBL_DEC_POINT',$MODULE)}</td>
                        <td><input type="text" maxlength="2" name="currency_point" class="inputElement" value="{$PDF_TEMPLATE_RESULT.currency_point}" style="width:{$margin_input_width}"/></td>
                    </tr>
                    <tr>
                        <td align="right" nowrap>{vtranslate('LBL_DEC_DECIMALS',$MODULE)}</td>
                        <td><input type="text" maxlength="2" name="currency" class="inputElement" value="{$PDF_TEMPLATE_RESULT.currency}" style="width:{$margin_input_width}"/></td>
                    </tr>
                    <tr>
                        <td align="right" nowrap>{vtranslate('LBL_DEC_THOUSANDS',$MODULE)}</td>
                        <td><input type="text" maxlength="2" name="currency_thousands" class="inputElement" value="{$PDF_TEMPLATE_RESULT.currency_thousands}" style="width:{$margin_input_width}"/></td>
                    </tr>
                </table>
            </div>
        </div>
        {* watemark settings *}
        <div class="form-group">
            <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                {vtranslate('Watermark',$MODULE)}:
            </label>
            <div class="controls col-sm-9">
                <table class="table table-bordered">
                    <tr>
                        <td align="right" nowrap width="20%">{vtranslate('Type',$MODULE)}</td>
                        <td>
                            <select name="watermark_type" id="watermark_type" class="select2 col-sm-12">
                                {html_options options=$WATERMARK.types selected=$WATERMARK.type}
                            </select>
                        </td>
                    </tr>
                    <tr id="watermark_image_tr" {if $WATERMARK.type neq "image"}class="hide"{/if}>
                        <td align="right" nowrap >{vtranslate('Image',$MODULE)}</td>
                        <td>
                            <input type="hidden" name="watermark_img_id" class="inputElement" value="{$WATERMARK.image_id}"/>
                            <div id="uploadedWatermarkFileImage" {if $WATERMARK.image_name neq ""}class="hide"{/if}>
                                <input type="file" name="watermark_image" class="inputElement"/>
                                <div class="uploadedFileDetails">
                                    <div class="uploadedFileSize"></div>
                                    <div class="uploadFileSizeLimit redColor">
                                        {vtranslate('LBL_MAX_UPLOAD_SIZE',$MODULE)}&nbsp;<span class="maxUploadSize" data-value="{$MAX_UPLOAD_LIMIT_BYTES}">{$MAX_UPLOAD_LIMIT_MB}{vtranslate('MB',$MODULE)}</span>
                                    </div>
                                </div>
                            </div>
                            <div id="uploadedWatermarkFileName" {if $WATERMARK.image_name eq ""}class="hide"{/if}>
                                <a href="{$WATERMARK.image_url}">{$WATERMARK.image_name}</a>
                                <span class="deleteWatermarkFile cursorPointer col-lg-1">
                                                                        <i class="alignMiddle fa fa-trash"></i>
                                                                    </span>
                            </div>
                        </td>
                    </tr>
                    <tr id="watermark_text_tr" {if $WATERMARK.type neq "text"}class="hide"{/if}>
                        <td align="right" nowrap>{vtranslate('Text',$MODULE)}</td>
                        <td><input type="text" name="watermark_text" class="inputElement getPopupUi" value="{$WATERMARK.text}"/></td>
                    </tr>
                    <tr id="watermark_alpha_tr" {if $WATERMARK.type eq "none"}class="hide"{/if}>
                        <td align="right" nowrap>{vtranslate('Alpha',$MODULE)}</td>
                        <td><input type="text" name="watermark_alpha" class="inputElement" {if $WATERMARK.alpha eq ""}placeholder="0.1"{/if} value="{$WATERMARK.alpha}"/></td>
                    </tr>

                </table>
            </div>
        </div>
    </div>
</div>