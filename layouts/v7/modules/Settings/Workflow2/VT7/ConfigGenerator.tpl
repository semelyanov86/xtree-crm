{if empty($fieldname) && !empty($config.name)}
    {assign var="fieldname" value=$config.name}
{/if}

{if $config.type eq 'text'}
<input type="text" name="{$fieldname}" class="form-control" autocomplete="off" value="{$value}" style="width:90%;" />
{/if}
{if $config.type eq 'checkbox'}
<input type="checkbox" name="{$fieldname}" autocomplete="off" value="1" {if $value eq '1'}checked="checked"{/if}  />
{/if}
{if $config.type eq 'templatefield' || $config.type eq 'template'}
    <div class="insertTextfield" data-name="{$fieldname}" data-placeholder="{$config.placeholder}" data-id="id{md5(microtime())}" data-options='' style="width:90%;">{$value}</div>
{/if}
{if $config.type eq 'templatearea'}
    <div class="insertTextarea" data-name="{$fieldname}" data-placeholder="{$config.placeholder}" data-id="id2{md5(microtime())}" data-options='' style="width:90%;">{$value}</div>
{/if}
{if $config.type eq 'textarea'}
    <textarea style="width:90%;" name="{$fieldname}">{$value}</textarea>
{/if}
{if $config.type eq 'password'}
<input type="password" name="{$fieldname}" class="form-control"  autocomplete="off" value="{$value}" style="width:90%;" />
{/if}
{if $config.type eq 'oauth'}
    {if Handler_OAuth::isDone($config.oauth_key)}
        <strong>{vtranslate('Done', 'Settings:Workflow2')}</strong>
    {else}
        <div id="oauthbtn_{$config.oauth_key}">
            <button type="button" class="btn btn-primary" onclick="OAuthHandler.start('{$config.oauth_key}');">{vtranslate('Authorize Workflow Designer Link', 'Settings:Workflow2')}</button> (<a target="_blank" href="https://support.redoo-networks.com/documentation/oauth-handling/">Info about Request</a>)<br/>
        </div>
        <div id="oauth_{$config.oauth_key}" data-text1="{vtranslate('Check authorization', 'Settings:Workflow2')}"  data-text2="{vtranslate('Authorization done successfully!', 'Settings:Workflow2')}" style="font-weight:bold;display:none;"></div>
    {/if}
    <input type="hidden" name="{$fieldname}" value="{$config.oauth_key}" />
{/if}
{if $config.type eq 'picklist' || $config.type eq 'select'}
<select name="{$fieldname}" style="width:90%;" class="select2">
    {foreach from=$config.options key=key item=label}
        <option value="{$key}" {if !empty($value) && ($key eq $value || in_array($key, $value))}selected="selected"{/if}>{$label}</option>
    {/foreach}
</select>
{/if}
{if $config.type eq 'related_picklist'}
<select name="{$fieldname}{if !empty($config.multiple)}[]{/if}" data-name="{$fieldname}" style="width:90%;" class="select2" {if !empty($config.multiple)}multiple="multiple"{/if}></select>
<script type="text/javascript">jQuery(function() { WFBackendUtils.fillSelectWithPicklistvalues('[name="{str_replace('##FIELDNAME##', $config.src, $fieldname_template)}"]', '[data-name="{$fieldname}"]', {$value|json_encode}); });</script>
{/if}
{if $config.type eq 'simpleselect'}
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <select name="{$fieldname}" style="width:90%;" class="select2">
        {foreach from=$config.options key=key item=label}
            <option value="{$label}" {if !empty($value) && $label == $value}selected="selected"{/if}>
                {if empty($config.optionsLabel)}
                    {$label}
                {else}
                    {$config.optionsLabel|replace:"[value]":$label|htmlentities}
                {/if}
            </option>
        {/foreach}
    </select>
{/if}
{if $config.type eq 'multipicklist'}
<select name="{$fieldname}[]" style="width:90%;" class="select2" multiple="multiple">
    {foreach from=$config.options key=key item=label}
        <option value="{$key}" {if !empty($value) && in_array($key, $value)}selected="selected"{/if}>{$label}</option>
    {/foreach}
</select>
{/if}
{if $config.type eq 'field'}
    {assign var="fieldid" value="`$fieldname|md5`"}
    <select name="{$fieldname}" id="{$fieldid}" style="width:90%;" class="select2 AsyncLoaded"></select>
    <script type="text/javascript">jQuery(function() { RedooUtils('Workflow2').fillFieldSelect("{$fieldid}", "{$value}", undefined, {if !empty($config.fieldtype)}"{$config.fieldtype}"{else}false{/if}); });</script>
{/if}
{if $config.type eq 'fields'}
    {assign var="fieldid" value="`$fieldname|md5`"}
    <select multiple="multiple" name="{$fieldname}[]" id="{$fieldid}" style="width:90%;" class="select2"></select>
    <script type="text/javascript">jQuery(function() { RedooUtils('Workflow2').fillFieldSelect("{$fieldid}", {$value|json_encode}); });</script>
{/if}
{if $config.type eq 'colorpicker'}
    <script src="modules/Workflow2/views/resources/js/jscolor/jscolor.js" type="text/javascript"></script>
    <input type="text" name="{$fieldname}" value="{$value}" class="color {ldelim}hash:true{rdelim}">
{/if}

