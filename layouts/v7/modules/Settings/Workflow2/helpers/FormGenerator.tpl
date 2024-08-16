<input type="hidden" name="task[{$field}_version]" value="2" />
<div id="formgenerator"></div>

<script type="text/javascript">
    var FGDAT = {};
    var fieldTypes = {$fieldTypes|@json_encode};

    jQuery(function() {
        initFormGenerator('formgenerator', '{$field}', {$formFields|@json_encode});
    });
</script>

{foreach from=$fields item=fieldConfig}
    <script type="text/template" id="fieldtemplate_{$fieldConfig.id|@strtolower}">
            {foreach from=$fieldConfig.config key=variable item=config}
                <div style="clear:both;">
                    {if $config.type == 'checkbox'}
                        <div style="float:left;padding-right:10px;">
                            <input class="configField rcSwitch doInit" type="checkbox" data-type="{$config.type}" data-variable="{$variable}" name="task[{$field}][##FIELDNAME##][config][{$variable}]"  data-id="task_{$field}_##FIELDNAME##_config_{$variable}" id="task_{$field}_##FIELDNAME##_config_{$variable}" value="{$config.value}">
                        </div>
                    {/if}

                    {if $config.type != 'condition'}
                        <div style='padding:0;padding-right:5px;line-height:{if $config.type != 'checkbox'}20{else}30{/if}px;font-size:11px;{if $config.type != 'label'}font-weight:bold;{else}font-style:italic;{/if}'>{vtranslate($config.label, 'Settings:Workflow2')}</div>
                    {/if}
                    {if $config.type != 'label'}
                        <div style="{if $variable eq 'mandatory'}display:inline;{/if}">
                            {if $config.type eq 'templatefield'}
                                <div class="configField insertTextfield" data-style="width:{$width}px;" data-type="{$config.type}" data-name="task[{$field}][##FIELDNAME##][config][{$variable}]" data-id="task_{$field}_##FIELDNAME##_config_{$variable}" data-placeholder="{$config.placeholder}"></div>
                            {elseif $config.type eq 'templatearea'}
                                <div class="configField insertTextarea" data-type="{$config.type}" data-name="task[{$field}][##FIELDNAME##][config][{$variable}]" data-placeholder="{$config.placeholder}" data-id="task_{$field}_##FIELDNAME##_config_{$variable}" data-options='{ldelim}"height":"100px"{rdelim}'>{$value}</div>
                            {elseif $config.type eq 'picklist'}
                                <select name="task[{$field}][##FIELDNAME##][config][{$variable}]" data-nomodify="{$config.nomodify}" data-variable="{$variable}" data-id="task_{$field}_##FIELDNAME##_config_{$variable}" id="task_{$field}_##FIELDNAME##_config_{$variable}" data-type="{$config.type}" style="width:100%;height:30px;" class="configField MakeSelect2" >
                                    {html_options options=$config.options}
                                </select>
                            {elseif $config.type eq 'condition'}
                                <input type="hidden" class="configField" name="task[{$field}][##FIELDNAME##][config][{$variable}]" data-variable="{$variable}" data-id="task_{$field}_##FIELDNAME##_config_{$variable}" id="task_{$field}_##FIELDNAME##_config_{$variable}" data-type="hidden" />
                                <button class="btn btn-primary" type="button" onclick="ConditionPopup.open('#task_{$field}_##FIELDNAME##_config_{$variable}', '#task_{$field}_##FIELDNAME##_config_{$config.moduleField}', 'LBL_FILTER_RECORDS_2_SELECT');">{vtranslate($config.label, 'Settings:Workflow2')}</button>
                            {/if}
                        </div>
                    {/if}
                </div>
            {/foreach}
    </script>
{/foreach}