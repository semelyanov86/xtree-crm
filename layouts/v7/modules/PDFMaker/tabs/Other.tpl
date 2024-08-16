<div class="tab-pane" id="pdfContentOther">
    <div class="edit-template-content">
        {if $IS_BLOCK neq true}
            <div class="form-group" id="listview_block_tpl_row">
                <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                    <input type="checkbox" name="is_listview" id="isListViewTmpl" {if $IS_LISTVIEW_CHECKED eq "yes"}checked="checked"{/if} onclick="PDFMaker_EditJs.isLvTmplClicked();" title="{vtranslate('LBL_LISTVIEW_TEMPLATE',$MODULE)}" />&nbsp;{vtranslate('LBL_LISTVIEWBLOCK',$MODULE)}:
                </label>
                <div class="controls col-sm-9">
                    <div class="input-group">
                        <select name="listviewblocktpl" id="listviewblocktpl" class="select2 form-control" {if $IS_LISTVIEW_CHECKED neq "yes"}disabled{/if}>
                            {html_options  options=$LISTVIEW_BLOCK_TPL}
                        </select>
                        <div class="input-group-btn">
                            <button type="button" id="listviewblocktpl_butt" class="btn btn-success InsertIntoTemplate" data-type="listviewblocktpl" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}" {if $IS_LISTVIEW_CHECKED neq "yes"}disabled{/if}><i class="fa fa-usd"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        {/if}
        <div class="form-group">
            <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                {vtranslate('TERMS_AND_CONDITIONS',$MODULE)}:
            </label>
            <div class="controls col-sm-9">
                <div class="input-group">
                    <select name="invterandcon" id="invterandcon" class="select2 form-control">
                        {html_options  options=$INVENTORYTERMSANDCONDITIONS}
                    </select>
                    <div class="input-group-btn">
                        <button type="button" class="btn btn-success InsertIntoTemplate" data-type="invterandcon" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                {vtranslate('LBL_CURRENT_DATE',$MODULE)}:
            </label>
            <div class="controls col-sm-9">
                <div class="input-group">
                    <select name="dateval" id="dateval" class="select2 form-control">
                        {html_options  options=$DATE_VARS}
                    </select>
                    <div class="input-group-btn">
                        <button type="button" class="btn btn-success InsertIntoTemplate" data-type="dateval" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                    </div>
                </div>
            </div>
        </div>
        {***** BARCODES *****}
        <div class="form-group">
            <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                {vtranslate('LBL_BARCODES',$MODULE)}:
            </label>
            <div class="controls col-sm-9">
                <div class="input-group">
                    <select name="barcodeval" id="barcodeval" class="select2 form-control">
                        <optgroup label="{vtranslate('LBL_BARCODES_TYPE1',$MODULE)}">
                            <option value="EAN13">EAN13</option>
                            <option value="ISBN">ISBN</option>
                            <option value="ISSN">ISSN</option>
                        </optgroup>

                        <optgroup label="{vtranslate('LBL_BARCODES_TYPE2',$MODULE)}">
                            <option value="UPCA">UPCA</option>
                            <option value="UPCE">UPCE</option>
                            <option value="EAN8">EAN8</option>
                        </optgroup>

                        <optgroup label="{vtranslate('LBL_BARCODES_TYPE3',$MODULE)}">
                            <option value="EAN2">EAN2</option>
                            <option value="EAN5">EAN5</option>
                            <option value="EAN13P2">EAN13P2</option>
                            <option value="ISBNP2">ISBNP2</option>
                            <option value="ISSNP2">ISSNP2</option>
                            <option value="UPCAP2">UPCAP2</option>
                            <option value="UPCEP2">UPCEP2</option>
                            <option value="EAN8P2">EAN8P2</option>
                            <option value="EAN13P5">EAN13P5</option>
                            <option value="ISBNP5">ISBNP5</option>
                            <option value="ISSNP5">ISSNP5</option>
                            <option value="UPCAP5">UPCAP5</option>
                            <option value="UPCEP5">UPCEP5</option>
                            <option value="EAN8P5">EAN8P5</option>
                        </optgroup>

                        <optgroup label="{vtranslate('LBL_BARCODES_TYPE4',$MODULE)}">
                            <option value="IMB">IMB</option>
                            <option value="RM4SCC">RM4SCC</option>
                            <option value="KIX">KIX</option>
                            <option value="POSTNET">POSTNET</option>
                            <option value="PLANET">PLANET</option>
                        </optgroup>

                        <optgroup label="{vtranslate('LBL_BARCODES_TYPE5',$MODULE)}">
                            <option value="C128A">C128A</option>
                            <option value="C128B">C128B</option>
                            <option value="C128C">C128C</option>
                            <option value="EAN128C">EAN128C</option>
                            <option value="C39">C39</option>
                            <option value="C39+">C39+</option>
                            <option value="C39E">C39E</option>
                            <option value="C39E+">C39E+</option>
                            <option value="S25">S25</option>
                            <option value="S25+">S25+</option>
                            <option value="I25">I25</option>
                            <option value="I25+">I25+</option>
                            <option value="I25B">I25B</option>
                            <option value="I25B+">I25B+</option>
                            <option value="C93">C93</option>
                            <option value="MSI">MSI</option>
                            <option value="MSI+">MSI+</option>
                            <option value="CODABAR">CODABAR</option>
                            <option value="CODE11">CODE11</option>
                        </optgroup>

                        <optgroup label="{vtranslate('LBL_QRCODE',$MODULE)}">
                            <option value="QR">QR</option>
                        </optgroup>

                        <optgroup label="{vtranslate('LBL_BARCODES_CUSTOM',$MODULE)}">
                            <option value="TYPE">{vtranslate('LBL_CUSTOM_BARCODE',$MODULE)}</option>
                        </optgroup>
                    </select>
                    <div class="input-group-btn">
                        <button type="button" class="btn btn-success InsertIntoTemplate" data-type="barcodeval" title="{vtranslate('LBL_INSERT_BARCODE_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>&nbsp;&nbsp;
                        <div class="dropdown displayInlineBlock">
                            <button type="button" class="btn" data-toggle="dropdown">
                                <i class="fa fa-info"></i>
                            </button>
                            <div class="dropdown-menu padding15px">
                                <h5><b>{vtranslate('LBL_BARCODES_CUSTOM',$MODULE)}</b></h5>
                                <hr>
                                <p>{vtranslate('LBL_EXAMPLE',$MODULE)}: <b>[BARCODE|TYPE=YOURCODE|BARCODE]</b></p>
                                <p>{vtranslate('LBL_BARCODES_DESC1',$MODULE)}</p>
                                <p>
                                    <a class="btn-link" href="https://mpdf.github.io/reference/html-control-tags/barcode.html" target="_new"><i class="fa fa-link"></i> {vtranslate('LBL_MPDF_SUPPORTED_BARCODES',$MODULE)}</a>
                                </p>
                                <p>{vtranslate('LBL_BARCODES_DESC2',$MODULE)}</p>
                                <p>
                                    <a class="btn-link" href="index.php?module=PDFMaker&view=IndexAjax&mode=showBarcodes" target="_new"><i class="fa fa-link"></i> {vtranslate('LBL_BARCODES_INFO',$MODULE)}</a>
                                </p>
                                <hr>
                                <p>{vtranslate('LBL_EXAMPLE',$MODULE)}: <b>[BARCODE|TYPE=YOURCODE|size=1|height=1|text=1|BARCODE]</b></p>
                                <p>{vtranslate('LBL_BARCODES_DESC3',$MODULE)}</p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        {if $VERSION_TYPE eq 'professional'}
            <div class="form-group">
                <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                    {vtranslate('CUSTOM_FUNCTIONS',$MODULE)}:
                </label>
                <div class="controls col-sm-9">
                    <div class="input-group">
                        <select name="customfunction" id="customfunction" class="select2 form-control">
                            {html_options options=$CUSTOM_FUNCTIONS}
                        </select>
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-success InsertIntoTemplate" data-type="customfunction" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}"><i class="fa fa-usd"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        {/if}
        <div class="form-group">
            <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                {vtranslate('LBL_FONT_AWESOME',$MODULE)}:
            </label>
            <div class="controls col-sm-9">
                <div class="input-group">
                    <select name="fontawesomeicons" id="fontawesomeicons" class="select2 form-control">
                        {foreach item=FONTAWESOMEDATA from=$FONTAWESOMEICONS}
                            {if $SELECTEDFONTAWESOMEICON eq ""}{assign var=SELECTEDFONTAWESOMEICON value=$FONTAWESOMEDATA.name}{/if}
                            <option value="{$FONTAWESOMEDATA.code}" data-classname="{$FONTAWESOMEDATA.name}" {if $SELECTEDFONTAWESOMEICON eq $FONTAWESOMEDATA.name}selected="selected"{/if}>{$FONTAWESOMEDATA.name}</option>
                        {/foreach}

                    </select>
                    <div class="input-group-btn">
                        <button type="button" class="btn btn-warning InsertIconIntoTemplate" data-type="awesomeicon" title="{vtranslate('LBL_INSERT_TO_TEXT',$MODULE)}"><i id="fontawesomepreview" class="fa {$SELECTEDFONTAWESOMEICON}"></i></button><a href="index.php?module=PDFMaker&view=IndexAjax&mode=getAwesomeInfoPDF" target="_new"><button type="button" class="btn"><i class="fa fa-info"></i></button></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>