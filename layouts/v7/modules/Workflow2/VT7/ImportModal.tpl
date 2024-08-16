<script type="text/javascript" src="modules/Workflow2/views/resources/js/jquery.form.min.js?v=3.51.0"></script>
<div class='fc-overlay-modal overlayDetail'>
    <input type="hidden" id="ImportHash" value="{$ImportHash}" />

    <div class = "modal-content" style="width:100%;">
            <div class="overlayDetailHeader col-lg-12 col-md-12 col-sm-12" style="width:100%;z-index:1;">
                <div class="col-lg-10 col-md-10 col-sm-10" style = "padding-left:0px;">
                    {vtranslate('LBL_IMPORTER', 'Settings:Workflow2')}
                </div>
                <div class = "col-lg-2 col-md-2 col-sm-2">
                    <div class="clearfix">
                        <div class="pull-right" >
                            <button type="button" class="close" aria-label="Close" data-dismiss="modal">
                                <span aria-hidden="true" class='fa fa-close'></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class='modal-body'>
                {if $SHOW_WARNING === true}
                <p class="alert alert-danger">{vtranslate('Please make sure, the test folder of your vTigerCRM installation is writable with PHP. Then come back to Import.', 'Settings:Workflow2')}</p>
                {else}
                    <div class = "detailViewContainer">

                        <div class="panel panel-default HideOnImport">
                            <div class="panel-body">
                                <div class="col-lg-1 col-md-1 col-sm-1" style = "padding-left:0px;">
                                    {vtranslate('LBL_SELECT_FILE', 'Settings:Workflow2')}
                                </div>
                                <div class="col-lg-11 col-md-11 col-sm-11" style = "padding-left:0px;color:#aaaaaa;font-weight:bold;">
                                    <form method="POST" enctype="multipart/form-data" id="ImportFileUpload">
                                        <input type="hidden" name="ImportHash" value="{$ImportHash}" />
                                        <input type="file" name="file" value="" style="width:300px;" />
                                        <br/>
                                        <button type="button" class="btn btn-info UploadFile" style="width:300px;">{vtranslate('Upload this file', 'Settings:Workflow2')}</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="panel panel-default ImportStep2 HideOnImport" style="display:none;">
                            <div class="panel-body">
                                <div class="col-lg-1 col-md-1 col-sm-1" style = "padding-left:0px;">
                                    {vtranslate('Import options', 'Settings:Workflow2')}
                                </div>
                                <div class="col-lg-11 col-md-11 col-sm-11" style="padding-left:0px;">
                                    <form method="POST" id="ImportSetOptions">
                                        <input type="hidden" name="ImportHash" value="{$ImportHash}" />

                                        <div class="form-group" style="margin-bottom:5px;overflow:hidden;">
                                            <label for="inputEmail3" class="col-sm-2 control-label">{vtranslate('Workflow for Import', 'Settings:Workflow2')}</label>
                                            <div class="col-sm-10">
                                                <select name="import[workflowid]" id="ImportWorkflowSelection" class="select2" style="width:350px;">
                                                    {foreach from=$Workflows item=workflow}
                                                        {if $workflow.invisible eq false}
                                                            <option value="{$workflow.id}">{$workflow.title}</option>
                                                        {/if}
                                                    {/foreach}
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group" style="margin-bottom:5px;overflow:hidden;">
                                            <label for="inputEmail3" class="col-sm-2 control-label align-left">{vtranslate('Import delimiter', 'Settings:Workflow2')}</label>
                                            <div class="col-sm-10">
                                                <input type="text" name="import[delimiter]" class="form-control" style="width:150px;line-height:20px;font-size:18px;text-align:center;" value="{$DefaultSettings.default_delimiter}" />
                                            </div>
                                        </div>

                                        {if $ShowEncoding eq true}
                                        <div class="form-group">
                                            <label for="inputEmail3" class="col-sm-2 control-label">{vtranslate('File encoding', 'Settings:Workflow2')}</label>
                                            <div class="col-sm-10">
                                                <select name="import[encoding]" class="select2" style="width:150px;">
                                                    <option value="UTF-8" {if $DefaultSettings.default_encoding eq 'UTF-8'}selected="selected"{/if}>UTF-8</option>
                                                    <option value="ISO-8859-1" {if $DefaultSettings.default_encoding eq 'ISO-8859-1'}selected="selected"{/if}>ISO-8859-1</option>
                                                </select>
                                            </div>
                                        </div>
                                        <br/>
                                        {else}
                                            <input type="hidden" name="import[encoding]" value="{$DefaultSettings.default_encoding}" />
                                        {/if}
                                        <br/>
                                        <div class="form-group">
                                            <label for="inputEmail3" class="col-sm-2 control-label"></label>

                                            <div class="col-sm-10">
                                                <label>
                                                    <input type="checkbox" name="import[skipfirst]" style="line-height:20px;font-size:18px;text-align:center;" value="1" {if $DefaultSettings.default_skip_first_row}checked="checked"{/if} />
                                                    {vtranslate('Skip first row', 'Settings:Workflow2')}
                                                </label>
                                            </div>
                                        </div>

                                        <button type="button" class="btn btn-info SetImportOptions" style="width:300px;">{vtranslate('Set Import Options', 'Settings:Workflow2')}</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="panel panel-default ImportStep3" style="display:none;">
                            <div class="panel-body">
                                <button type="button" class="btn btn-primary StartImportBtn">{vtranslate('Start Import process', 'Settings:Workflow2')}</button>
                            </div>
                        </div>
                        <div class="panel panel-info ImportStep2  HideOnImport" style="display:none;">
                            <div class="panel-heading">
                                {vtranslate('HINT_FILE_IMPORT_PREVIEW', 'Settings:Workflow2')}
                            </div>
                            <div class="panel-body">
                                <div>{vtranslate('HINT_FILE_IMPORT_PREVIEW_DESCR', 'Settings:Workflow2')}</div>

                                <div id="ImportPreview"></div>
                            </div>
                        </div>
                        <div class="panel panel-info  ShowOnImport" style="display:none;">
                            <div class="panel-heading">
                                {vtranslate('Import progress', 'Settings:Workflow2')}
                            </div>
                            <div class="panel-body" id="ProgressPanel">

                            </div>
                        </div>
                    </div>
                {/if}
            </div>
    </div>
</div>
<script type="text/javascript">
    var ImportWorkflows = {$Workflows|json_encode};
</script>