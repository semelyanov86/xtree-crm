<div class="modal-dialog modelContainer" style="width:1024px;">
    {assign var=HEADER_TITLE value={vtranslate("EditView Workflow Configuration","Settings:Workflow2")}}
    {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}

    <form class="form-horizontal" name="FrontendWorkflowEditor" method="post" action="index.php">
        <input type="hidden" name="parent" value="Settings">
        <input type="hidden" name="module" value="Workflow2">
        <input type="hidden" name="action" value="FrontendWorkflowConfigSave">

        <input type="hidden" name="editRecordId" id="editRecordId" value="{$config.id}" />

        <div class="modal-content">
            <div  style="padding:20px;">
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="title">Trigger is Active</label>
                    <div class="col-sm-9">
                        <input type="checkbox" class="form-control"name="active" {if $config.active eq '1'}checked="checked"{/if} value="1" />
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label" for="title">Check condition on Pageload</label>
                    <div class="col-sm-9">
                        <input type="checkbox" class="form-control" name="pageload" {if $config.pageload eq '1'}checked="checked"{/if} value="1" />
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">{vtranslate('After this fields have changed, check Condition', 'Settings:Worklfows')}</label>
                    <div class="col-sm-9">
                        <select name="fields[]" class="select2 form-control" multiple="multiple">
                            {foreach from=$fields key=blockLabel item=fieldList}
                                <optgroup label="{$blockLabel}">
                                    {foreach from=$fieldList item=field}
                                        <option value="{$field->name}" {if in_array($field->name, $config.fields)}selected="selected"{/if}>{$field->label}</option>
                                    {/foreach}
                                </optgroup>
                            {/foreach}

                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label" for="title" style="text-align:left;display:block;margin-left:10px;">{vtranslate('Condition on Client', 'Settings:Workflow2')}</label>
                    <div class="controls" style="margin:10px;">
                        {$conditionalContent}
                    </div>
                </div>
            <div style="float:left;line-height:28px;padding:10px 20px;"  id="errorMessages">&nbsp;</div>
            </div>
            {include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
        </div>
    </form>
</div>
    <script type="text/javascript">
        {$javascript}
    </script>
