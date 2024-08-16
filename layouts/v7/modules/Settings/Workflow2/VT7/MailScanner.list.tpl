<div class="container-fluid" id="moduleManagerContents">
    <div class="editViewHeader">
        <h4>
            <input type="button" name="createConfig" class="btn btn-primary pull-right createMailscannerConfig" value="{vtranslate('Create configuration', 'Settnigs:Workflow2')}" />
            <a href="index.php?module=Workflow2&view=Index&parent=Settings">{vtranslate('Workflow Designer', 'Workflow2')}</a> &raquo;
            {vtranslate(' Mailscanner configuration','Settings:Workflow2')}
        </h4>
    </div>
    <hr/>
    <br>
    <div class="listViewActionsDiv">

        {if !empty($scanner)}
        {foreach from=$scanner item=config}
            <div class="MailScannerConfiguration" onclick="window.location.href='index.php?module=Workflow2&view=MailscannerEditor&scanner={$config.id}&parent=Settings';" data-id="{$config.id}">
                <span class="MS_TITLE"><i class="icon-inbox" style="margin-top:3px;"></i> {$config.title}</span>
                <br/>
                {*<br/>*}
                {*<button type="button" class="ShowHistory btn btn-default"><i class="icon-tasks"></i> {vtranslate('Show history of done Mails', 'Settings:Workflow2')}</button>*}
            </div>
        {/foreach}
        {else}
        <h5>
            <em>{vtranslate('No MailScanner configured', 'Settings:Workflow2')}</em>
        </h5>
        {/if}

        {if !empty($LogFiles)}
        <div>
            <h3>{vtranslate('Execution log', 'Settings:Workflow2')}</h3>
            <table class="table table-condensed table-striped">
                <tr>
                    <th>Execution Time / Filename within <i>test/Workflow2/Mailscanner-Log/</i></th>
                </tr>
                {foreach from=$LogFiles key=File item=Filesize}
                    <tr>
                        <td><a href="{vglobal('site_URL')}/test/Workflow2/Mailscanner-Log/{$File}" target="_blank">{$File}</a></td>
                        <td>{$Filesize} Bytes</td>
                    </tr>
                {/foreach}
            </table>
        </div>
        {/if}
    </div>
</div>