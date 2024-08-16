<div class="tab-pane" id="pdfContentHeaderFooter">
    <div class="edit-template-content">
        {********************************************* Header/Footer *************************************************}
        <div id="headerfooter_div">
            {if $IS_BLOCK neq true}
                {* pdf format settings *}
                {foreach from=$BLOCK_TYPES key=BLOCKID item=BLOCK_TYPE}
                    <div class="form-group">
                        <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                            {$BLOCK_TYPE["name"]}:
                        </label>
                        <div class="controls col-sm-9">
                            <div class="blocktypeselect">
                                <select name="blocktype{$BLOCKID}_val" id="blocktype{$BLOCKID}_val" data-type="{$BLOCKID}" class="select2 col-sm-12">
                                    {html_options  options=$BLOCK_TYPE["types"] selected=$BLOCK_TYPE["selected"]}
                                </select>
                            </div>
                            <div id="blocktype{$BLOCKID}" class="{if $BLOCK_TYPE["selected"] eq "custom"}hide{/if}">
                                <select name="blocktype{$BLOCKID}_list" id="blocktype{$BLOCKID}_list" class="select2 col-sm-12">
                                    {foreach  item=BLOCK_TYPE_DATA from=$BLOCK_TYPE["list"]}
                                        <option value="{$BLOCK_TYPE_DATA["templateid"]}" {if $BLOCK_TYPE_DATA["templateid"] eq $BLOCK_TYPE["selectedid"]}selected{/if}>{$BLOCK_TYPE_DATA["name"]}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                    </div>
                {/foreach}
            {/if}


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
            {* don't display header on first page *}
            <div class="form-group">
                <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                    {vtranslate('LBL_DISPLAY_HEADER',$MODULE)}:
                </label>
                <div class="controls col-sm-9">
                    <b>{vtranslate('LBL_ALL_PAGES',$MODULE)}</b>&nbsp;<input type="checkbox" id="dh_allid" name="dh_all" onclick="PDFMaker_EditJs.hf_checkboxes_changed(this, 'header');" {$DH_ALL}/>
                    &nbsp;&nbsp;
                    {vtranslate('LBL_FIRST_PAGE',$MODULE)}&nbsp;<input type="checkbox" id="dh_firstid" name="dh_first" onclick="PDFMaker_EditJs.hf_checkboxes_changed(this, 'header');" {$DH_FIRST}/>
                    &nbsp;&nbsp;
                    {vtranslate('LBL_OTHER_PAGES',$MODULE)}&nbsp;<input type="checkbox" id="dh_otherid" name="dh_other" onclick="PDFMaker_EditJs.hf_checkboxes_changed(this, 'header');" {$DH_OTHER}/></div>
            </div>
            <div class="form-group">
                <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                    {vtranslate('LBL_DISPLAY_FOOTER',$MODULE)}:
                </label>
                <div class="controls col-sm-9">
                    <b>{vtranslate('LBL_ALL_PAGES',$MODULE)}</b>&nbsp;<input type="checkbox" id="df_allid" name="df_all" onclick="PDFMaker_EditJs.hf_checkboxes_changed(this, 'footer');" {$DF_ALL}/>
                    &nbsp;&nbsp;
                    {vtranslate('LBL_FIRST_PAGE',$MODULE)}&nbsp;<input type="checkbox" id="df_firstid" name="df_first" onclick="PDFMaker_EditJs.hf_checkboxes_changed(this, 'footer');" {$DF_FIRST}/>
                    &nbsp;&nbsp;
                    {vtranslate('LBL_OTHER_PAGES',$MODULE)}&nbsp;<input type="checkbox" id="df_otherid" name="df_other" onclick="PDFMaker_EditJs.hf_checkboxes_changed(this, 'footer');" {$DF_OTHER}/>
                    &nbsp;&nbsp;
                    {vtranslate('LBL_LAST_PAGE',$MODULE)}&nbsp;<input type="checkbox" id="df_lastid" name="df_last" onclick="PDFMaker_EditJs.hf_checkboxes_changed(this, 'footer');" {$DF_LAST}/>
                </div>
            </div>
        </div>
    </div>
</div>