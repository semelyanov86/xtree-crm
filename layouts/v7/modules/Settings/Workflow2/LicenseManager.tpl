<div class="container-fluid" id="moduleManagerContents">

    <div class="widget_header row-fluid">
        <div class="span12">
            <h3>
                <b>
                    <a href="index.php?module=Workflow2&view=Index&parent=Settings">Workflow Designer</a> &raquo;
                {vtranslate('LBL_LICENSE_MANAGER','Settings:Workflow2')}
                </b>
            </h3>
        </div>
    </div>
    <hr>

    <table class="table table-bordered  table-condensed" style="width:500px;">
        <tr class="{if $ACTIVE_LICENSE eq true}success{else}error{/if}">
            <td width="200">
                {vtranslate('LBL_LICENSE_IS', 'Settings:Workflow2')}
            </td>
            <td>
                <strong>
                    {if $ACTIVE_LICENSE eq true}
                        {vtranslate('LBL_ACTIVE','Workflow2')}
                        {else}
                        {vtranslate('LBL_INACTIVE','Workflow2')}
                    {/if}
                </strong>
            </td>
        </tr>
    </table>
    <br>
{if $ACTIVE_LICENSE eq true}
    <table class="table table-bordered  table-condensed" style="width:500px;">
        <tr>
            <td width="200">{vtranslate('LBL_LICENSE_STATE', 'Settings:Workflow2')}</td>
            <td>
                {if $STATE eq 'pro'}
                Professional
            {else}
                Basic
            {/if}
            </td>
        </tr>
        <tr>
            <td>{vtranslate('LBL_LICENSE_FOR', 'Settings:Workflow2')}</td>
            <td>{$LICENSE_FOR}</td>
        </tr>
    </table>
    <br>
    <button class="btn btn-primary" onclick="refreshLicense();">{vtranslate('LBL_REVALIDATE_LICENSE', 'Settings:Workflow2')}</button>
    <button class="btn btn-danger" onclick="removeLicense();">{vtranslate('LBL_REMOVE_LICENSE', 'Settings:Workflow2')}</button>
{else}
    {if $hasLicense}
        <button class="btn btn-danger" onclick="removeLicense();">{vtranslate('LBL_REMOVE_LICENSE', 'Settings:Workflow2')}</button>
    {/if}
    <button type="button" class="btn btn-success" onclick="setLicense();">Set License</button>
{/if}
