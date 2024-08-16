<div class="container-fluid" id="moduleManagerContents">
    <div class="editViewHeader">
        <h4>
            <div class="pull-right">
                <select  id="addConnection" style="width:400px;" data-placeholder="{vtranslate('add new Connection','Settings:Workflow2')}">
                    <option value=""></option>
                    {foreach from=$providers item=label key=type}
                        {* is array if existing sub provider *}
                        {if !is_array($label)}
                            <option value="{$type}">{$label}</option>
                        {else}
                            <optgroup label="{$label.label}">
                                {foreach from=$label.provider item=sublabel key=subtype}
                                    <option value="{$subtype}">{$sublabel}</option>
                                {/foreach}
                            </optgroup>
                        {/if}

                    {/foreach}
                </select>
                <button type="submit" id="addConnectionBtn" class="btn btn-primary" style="margin-top:0;vertical-align:top;">{vtranslate('add Connection', 'Settings:Workflow2')}</button>
            </div>

            <a href="index.php?module=Workflow2&view=Index&parent=Settings">{vtranslate('Workflow Designer', 'Workflow2')}</a> &raquo;
            {vtranslate('Provider Manager', 'Workflow2')}
        </h4>
    </div>

    <div class="listViewActionsDiv">
        <table class="table table-condensed">
            {foreach from=$connections item=items key=type}
                <tr>
                    <th colspan="3"><strong>{$providerLabel[$type]}</strong></th>
                </tr>
                {foreach from=$items item=connection}
                <tr>
                    <td></td>
                    <td>{$connection.title}</td>
                    <td>
                        <input type="button" class="btn btn-success editProviderBtn" data-id="{$connection.id}" data-type="{$connection.type}" value="{vtranslate('Edit','Settings:Workflow2')}" />
                        <input type="button" class="btn btn-danger delProviderBtn" data-id="{$connection.id}" data-type="{$connection.type}" value="{vtranslate('delete','Settings:Workflow2')}" />
                    </td>
                </tr>
                {/foreach}
            {/foreach}
        </table>
    </div>

    <br/>
    <br/>
    <a href="https://support.stefanwarnat.de/en:extensions:workflowdesigner:providermanager" target="_blank"><strong>{vtranslate('read documentation for more information', 'Settings:Workflow2')}</strong></a>
</div>
