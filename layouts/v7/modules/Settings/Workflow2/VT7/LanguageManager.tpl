<div class="container-fluid" id="moduleManagerContents">
    <div class="editViewHeader">
        <h4>
            <a href="index.php?module=Workflow2&view=Index&parent=Settings">{vtranslate('Workflow Designer', 'Workflow2')}</a> &raquo;
            {vtranslate('Language Downloader','Settings:Workflow2')}
        </h4>
    </div>
    <hr/>
    <br>
    <div class="listViewActionsDiv">

    <table class="table table-bordered  table-condensed" style="width:700px;">
        <thead>
        <tr>
            <th>Language</th>
            <th>Last update</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$languages item=lang}
            <tr>
                <td>{$lang['code']}</td>
                <td>{$lang['updated_at']}</td>
                <td>{if $lang['update'] eq true}
                        <a href="index.php?module=Workflow2&view=LanguageManager&parent=Settings&download={$lang['code']}" class="btn btn-info"  name="button">{vtranslate('Download', 'Settings:Workflow2')}</a>
                    {else}
                       {vtranslate('latest version', 'Settings:Workflow2')}
                    {/if}
                </td>
            </tr>
        {/foreach}
        </tbody>
    </table>
    </div>
</div>