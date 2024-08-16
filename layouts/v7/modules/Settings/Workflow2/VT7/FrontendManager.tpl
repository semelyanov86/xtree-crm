<div class="container-fluid" id="moduleManagerContents">
    <div class="editViewHeader">
        <h4>
            <div class="pull-right">
                <select class="select2" id="addWorkflow" style="width:400px;" data-placeholder="{vtranslate('choose a Workflow','Settings:Workflow2')}">
                    <option value=""></option>
                    {foreach from=$workflows item=workflowList key=moduleName}
                        <optgroup label="{$moduleName}">
                            {foreach from=$workflowList item=workflow}
                                <option value="{$workflow.id}">{$workflow.title}</option>
                            {/foreach}
                        </optgroup>
                    {/foreach}
                </select>
                <button type="submit" id="addWorkflowButton" class="btn btn-primary" style="margin-top:0;vertical-align:top;">{vtranslate('add Workflow', 'Settings:Workflow2')}</button>
            </div>

            <a href="index.php?module=Workflow2&view=Index&parent=Settings">{vtranslate('Workflow Designer', 'Workflow2')}</a> &raquo;
            {vtranslate('Frontend Manager', 'Settings:Workflow2')}
        </h4>
    </div>

    <div class="detailViewContainer ">

        {foreach from=$links item=linkArray key=moduleName}
            <div class="block" data-target="{$linkArray[0]['module_name']}">
                <h5 class="modHead">
                    <img src="modules/Workflow2/icons/toggle_minus.png" class="toggleImageCollapse toggleImage" style="display: none;" />
                    <img src="modules/Workflow2/icons/toggle_plus.png" class="toggleImageExpand toggleImage"/>

                    <strong>{$moduleName} ({count($linkArray)})</strong>
                </h5>
            </div>

        <table class="table frontendManagerTable" cellspacing="0" cellpadding="4" style="border-collapse:collapse;display:none;" data-module="{$linkArray[0]['module_name']}" id="workflowList{$linkArray[0]['module_name']}" >
            <thead>
            <tr style="background-color: #eee;">
                <th align="left"></th>
                <th align="left" style="width:250px;">{vtranslate('Workflow', 'Settings:Workflow2')}</th>
                <th align="left" style="width:250px;">{vtranslate('Label', 'Settings:Workflow2')}</th>
                <th align="left" style="width:250px;">{vtranslate('Include Type', 'Settings:Workflow2')}</th>
                <th align="left">Configuration</th>
                <!--<th align="left">{vtranslate('visible in Listview', 'Settings:Workflow2')}</th>
                <th align="left">{vtranslate('Button Color', 'Settings:Workflow2')}</th>-->
            </tr>
            </thead>
            <tbody>
            {foreach from=$linkArray item=link}
            <tr class="hoverTR" data-index="{$link.id}">
                <td>
                    <img src="modules/Workflow2/icons/delete.png" data-id="{$link.id}" class="removeFrontendManagerOnClick" width="16" />
                </td>
                <td>
                    {if $link.color neq 'separator'}
                        {$link.title}
                    {else}
                        -- {vtranslate('Separator', 'Settings:Workflow2')} --
                    {/if}
                </td>
                <td><input type="text" class="defaultTextfield saveOnBlur" data-field="label" data-id="{$link.id}" value="{$link.label}" style="margin-bottom:0;width:100%;" /></td>
                <td>
                    <select data-field="position" data-id="{$link.id}" class="saveOnBlur select2 FrontendType" style="width:100%;">
                        <option value="none" {if $link.position eq 'none'}selected="selected"{/if}>hidden</option>
                        <optgroup label="{vtranslate('Workflow Designer', "Settings:Workflow2")}">
                            <option value="sidebar" {if $link.position eq 'sidebar'}selected="selected"{/if}>{vtranslate('Dedicated Button in popup', 'Settings:Workflow2')}</option>
                            <option value="headerbtn" {if $link.position eq 'headerbtn'}selected="selected"{/if}>{vtranslate('Button in Headerrow', 'Settings:Workflow2')}</option>
                            <option value="listviewbtn" {if $link.position eq 'listviewbtn'}selected="selected"{/if}>{vtranslate('Listview Top Button', 'Settings:Workflow2')}</option>
                            <option value="detailbtn" {if $link.position eq 'detailbtn'}selected="selected"{/if}>{vtranslate('Detailview Top Button', 'Settings:Workflow2')}</option>
                            <option value="relatedbtn" {if $link.position eq 'relatedbtn'}selected="selected"{/if}>{vtranslate('Campaign Relations Button', 'Settings:Workflow2')}</option>
                            <option value="morebtn" {if $link.position eq 'morebtn'}selected="selected"{/if}>{vtranslate('More Action Button', 'Settings:Workflow2')}</option>
                            <option value="fieldbtn" {if $link.position eq 'fieldbtn'}selected="selected"{/if}>{vtranslate('Button in Field', 'Settings:Workflow2')}</option>
                        {*<option value="more" {if $link.position eq 'more'}selected="selected"{/if}>More Menu</option>*}
                        </optgroup>
                        <optgroup label="{vtranslate('Custom Types', 'Settings:Workfow2')}">
                            {foreach from=$FrontendTypes item=Type}
                                <option value="{$Type.key}" {if $link.position eq $Type.key}selected="selected"{/if}>{$Type.title}</option>
                            {/foreach}
                        </optgroup>
                    </select>
                </td>
                <td style="text-align: left;"">
                    <div class="ConfigContainer" data-types="sidebar">
                        <label>
                            <span style="display:inline-block;width:140px;">Show in ListView:</span>
                            <input type="checkbox" id="config_{$link.id}_listview"  name="listview" data-field="listview" value="1" data-id="{$link.id}" class="saveOnBlur" {if $link.listview eq '1'}checked="checked"{/if} />
                        </label>
                    </div>
                    <div class="ConfigContainer" data-types="sidebar,listviewbtn,morebtn,detailbtn,relatedbtn,fieldbtn,headerbtn">
                        <span style="display:inline-block;width:140px;">Button Color:</span>
                        <input type="text" style="margin-bottom:0;" id="config_{$link.id}_color"  class="defaultTextfield saveOnBlur color {ldelim}hash:true}" data-field="color" data-id="{$link.id}" value="{if $link.color eq ''}#3D57FF{else}{$link.color}{/if}" />
                        <label>
                            <input type="checkbox" value="1" class="saveOnBlur" data-field="config-defaultlayout" data-id="{$link.id}" {if !empty($link.config.defaultlayout)}checked="checked"{/if} />
                            No Backgroundcolor, but default Button Layout
                        </label>
                    </div>
                    <div class="ConfigContainer" data-types="fieldbtn" data-content="fieldlist">
                        <span style="display:inline-block;width:140px;">Select Fields:</span>
                        <div style="display:inline;" class="FieldListing" data-value="{}"></div>
                    </div>
                    <div class="ConfigContainer" data-types="fieldbtn">
                        <label>
                            <span style="display:inline-block;width:140px;">Use Dropdown:</span>
                            <input type="checkbox" id="config_{$link.id}_dropdown"  name="config-dropdown" data-field="config-dropdown" value="1" data-id="{$link.id}" class="saveOnBlur" {if $link.config.dropdown eq '1'}checked="checked"{/if} />
                        </label>
                    </div>
                    {foreach from=$FrontendTypes item=Type}
                    <div class="ConfigContainer" data-types="{$Type.key}">
                        {foreach from=$Type.options key=Field item=FieldData}
                            <div>
                                <label>
                                    <span style="display:inline-block;width:140px;">{vtranslate($FieldData.label, $Type.langmodule)}:</span>

                                    {if $FieldData.type eq Workflow2_FrontendType_Model::TYPE_COLORPICKER}
                                        {if $Field neq 'color'}
                                            <input type="text" style="margin-bottom:0;" id="config_{$link.id}_{$Field}"  class="defaultTextfield saveOnBlur color {ldelim}hash:true}" data-field="config-{$Field}" data-id="{$link.id}" value="{if empty($link.config[$Field])}{$FieldData['default']}{else}{$link.config[$Field]}{/if}" />
                                        {else}
                                            <input type="text" style="margin-bottom:0;" id="config_{$link.id}_{$Field}"  class="defaultTextfield saveOnBlur color {ldelim}hash:true}" data-field="{$Field}" data-id="{$link.id}" value="{if empty($link[$Field])}{$FieldData['default']}{else}{$link[$Field]}{/if}" />
                                        {/if}
                                    {/if}
                                    {if $FieldData.type eq Workflow2_FrontendType_Model::TYPE_CHECKBOX}
                                        <input type="checkbox" id="config_{$link.id}_{$Field}"  name="config-{$Field}" data-field="config-{$Field}" value="1" data-id="{$link.id}" class="saveOnBlur" {if (isset($link.config[$Field]) && $link.config[$Field] eq '1') || (!isset($link.config[$Field]) && !empty($FieldData['default']))}checked="checked"{/if} />
                                    {/if}
                                    {if $FieldData.type eq Workflow2_FrontendType_Model::TYPE_FIELDDSELECT}
                                        <div style="display:inline;" data-field="{$Field}" class="FieldListing" data-value="{}"></div>
                                    {/if}
                                    {if $FieldData.type eq Workflow2_FrontendType_Model::TYPE_ICON}
                                        <select class="select2 saveOnBlur"  data-id="{$link.id}" id="config_{$link.id}_{$Field}" style="width:300px;" data-field="config-{$Field}">
                                            {foreach from=$FA_ICONS item=ICON}
                                                <option value="{$ICON}" {if $link.config[$Field] eq $ICON}selected="selected"{/if}>{$ICON}</option>
                                            {/foreach}
                                        </select>
                                    {/if}
                                </label>
                            </div>
                        {/foreach}
                    </div>
                    {/foreach}
                </td>
                <td>
                    {if $link.color neq 'separator'}

                    {/if}
                </td>
            </tr>
            {/foreach}
            </tbody>
            <tfoot>
            <tr style="background-color: #eee;">
                <td></td>
                <td colspan="5"">
                    {vtranslate('disable complete Workflow List in Sidebar', 'Settings:Workflow2')}:&nbsp;&nbsp;&nbsp;<input type="checkbox" class="SaveConfigOnBlur" data-field="hide_listview" data-module="{$linkArray[0]['module_name']}" {if isset($frontendConfig[$linkArray[0]['module_name']]) && $frontendConfig[$linkArray[0]['module_name']]['hide_listview'] eq '1'}checked="checked"{/if} />
                    &nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{vtranslate('add other Object', 'Settings:Workflow2')}:&nbsp;&nbsp;&nbsp;<select class="separatorChooser"><option value="separator">Separator</option></select><button class="btn btn-primary addSpecialObjectButton" type="button" name="">{vtranslate('add', 'Settings:Workflow2')}</button>
                </td>
            </tr>
            </tfoot>
        </table>
        {/foreach}

    </div>
</div>
<script type="text/javascript">
    var configurations = {$configurations|json_encode};

    var frontendTypes = {$FrontendTypes|json_encode};
</script>
