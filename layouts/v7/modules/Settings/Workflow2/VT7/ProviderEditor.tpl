{if $requireOAuth eq true}
    <script type="text/javascript" src="modules/Settings/Workflow2/views/resources/OAuthHandler.js?v=<?php $workflowObj->getVersion() ?>"></script>
{/if}

<div class="modal-dialog modelContainer" style="width:600px;">
    {assign var=HEADER_TITLE value={vtranslate("Provider Editor","Settings:Workflow2")}}
    {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}

    <form method="POST" name="form{rand(10000,99999)}" id="WorkflowProviderEditor"
          autocomplete="off" action="index.php?module=Workflow2&parent=Settings&action=ProviderSave">

            <div class="modal-content">
                <div id="resultContainer"></div>
                <input type="hidden" name="connectionId" id="connectionId" value="{$connectionId}"/>
                <input type="hidden" name="type" id="type" value="{$connectionType}"/>
                <div style="overflow:hidden;padding: 10px;">
                    <table class="table table-condensed">
                        <tr>
                            <th style="width:30%;">Type</th>
                            <td><strong>{$providerTitle}</strong></td>
                        </tr>
                        <tr>
                            <th>Title</th>
                            <td><input type="text" style="width:90%;padding-bottom:0;" class="form-control" name='title' value="{$connectionTitle|htmlentities}"></td>
                        </tr>
                        {if $requireOAuth eq true}
                            <tr>
                                <th>Permission</th>
                                <td>
                                    {include file="VT7/ConfigGenerator.tpl"|vtemplate_path:'Settings/Workflow2' VALUE="oauth" config=$OAUTHConfig}
                                </td>
                            </tr>
                        {/if}
                        {$configFieldHTML}
                    </table>
                    {if $createMode eq false}
                    <div style="display:none;" id="providerTestError"></div>
                        <div class="pull-left">
                            <span class="pull-right" style="display:none;" id="providerTestResult"></span>
                            <input type="button" class="btn btn-info testSettingsBtn" name="test_settings" value="Test settings" />
                        </div>
                    {/if}
                </div>

            {include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
            </div>
    </form>
</div>
