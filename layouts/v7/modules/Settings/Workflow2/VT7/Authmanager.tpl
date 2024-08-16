<link rel="stylesheet" href="modules/Workflow2/adminStyle.css" type="text/css" media="all" />
<div class="container-fluid" id="moduleManagerContents">
    <div class="editViewHeader">
        <h4>
            <a href="index.php?module=Workflow2&view=Index&parent=Settings">{vtranslate('Workflow2', 'Workflow2')}</a> &raquo;
            {vtranslate('BTN_AUTH_MANAGEMENT', 'Settings:Workflow2')} &raquo
            {$settings.title}
        </h4>
    </div>
    <hr>
    <div class="listViewActionsDiv">

<form method="POST" action="#">
    <input type="hidden" name="save_auth" value="1"/>
    <table cellspacing="0" cellpadding="0" border="0" align="center" width="98%">
    <tr>
            <td width="100%" valign="top" class="showPanelBg">
                <br>
                <div class="settingsUI" style="width:95%;margin-left:10px;">
                    <div style="font-weight:bold;font-size:14px;"></div>

                    <p style="text-align:right;float:right;">
                        <button type="submit" class='btn btn-success'>{vtranslate('BTN_LBL_SAVE_PERMISSION','Settings:Workflow2')}</button>
                    </p>
                    <p style="text-align:left;">
                        <button type="button" onclick="window.open('https://support.stefanwarnat.de/en:extensions:workflowdesigner:workflowauth');" class='btn btn-warning'>{vtranslate('LBL_HELP','Settings:Workflow2')}</button>
                    </p>
                    <p style="padding:10px;border:1px solid #ccc;background-color:#fff;">
                        <input type="checkbox" name="authmanagement" value="1" onclick="if(jQuery(this).prop('checked')) enableAuthmanagement() else disableAuthmanagement();" value="10" {if $enabledAuth eq true}checked='checked'{/if} />
                        {vtranslate('LBL_AUTHMANAGEMENT_INDIVIDUAL','Settings:Workflow2')}
                    </p>
                    <div id='authmanagement'>
                        <table class="tableHeading newTable" border="0"  width="100%" cellspacing="0" cellpadding="5">
                            <tr>
                                <td class="big" nowrap="nowrap">
                                      <strong>{vtranslate('LBL_ROLES','Settings:Workflow2')}</strong>
                                </td>
                            </tr>
                            <table border="0" cellpadding="5" cellspacing="0" width="100%" class="rolePermissions newTable" style="background-color:#ffffff;">
                                {foreach from=$roles key=roleid item=role}

                                    <tr>
                                        <td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{$role[0]}</td>
                                        <td class='dvtCellInfo' width=25>
                                            <select name='auth[roles][{$roleid}]' class="select2" style="width:250px;">
                                                <option value='3' {if $authData["role{$roleid}"] eq '3'}selected='selected'{/if}>{vtranslate('LBL_AUTH_SELECT_EDIT','Settings:Workflow2')}</option>
                                                <option value='2' {if $authData["role{$roleid}"] eq '2'}selected='selected'{/if}>{vtranslate('LBL_AUTH_SELECT_VIEW','Settings:Workflow2')}</option>
                                                <option value='1' {if $authData["role{$roleid}"] eq '1'}selected='selected'{/if}>{vtranslate('LBL_AUTH_SELECT_EXEC','Settings:Workflow2')}</option>
                                                <option value='0' {if $authData["role{$roleid}"] eq '0'}selected='selected'{/if}>{vtranslate('LBL_AUTH_SELECT_NONE','Settings:Workflow2')}</option>
                                            </select>
                                        </td>
                                    </tr>
                                {/foreach}
                            </table>

                        <table class="tableHeading" border="0"  width="100%" cellspacing="0" cellpadding="5">
                                <tr>
                                    <td class="big" nowrap="nowrap">
                                          <strong>{vtranslate('LBL_USER')}</strong>
                                    </td>
                                </tr>
                            </table>

                        <table border="0" cellpadding="5" cellspacing="0" width="100%" class="userPermissions newTable" style="background-color:#ffffff;">
                            {foreach from=$availUser.user item=user}
                                {assign var="userId" value=$user.id}
                                <tr>
                                    <td class='dvtCellLabel' align="right" width=15% nowrap="nowrap">{$user.first_name} {$user.last_name}</td>
                                    <td class='dvtCellInfo' width=25>
                                        <select name='auth[users][{$userId}]' class="select2" style="width:250px;">
                                            <option value='-1'>{vtranslate('LBL_AUTH_SELECT_INHERIT','Settings:Workflow2')}</option>
                                            <option value='3' {if $authData["user{$userId}"] eq '3'}selected='selected'{/if}>{vtranslate('LBL_AUTH_SELECT_EDIT','Settings:Workflow2')}</option>
                                            <option value='2' {if $authData["user{$userId}"] eq '2'}selected='selected'{/if}>{vtranslate('LBL_AUTH_SELECT_VIEW','Settings:Workflow2')}</option>
                                            <option value='1' {if $authData["user{$userId}"] eq '1'}selected='selected'{/if}>{vtranslate('LBL_AUTH_SELECT_EXEC','Settings:Workflow2')}</option>
                                            <option value='0' {if $authData["user{$userId}"] eq '0'}selected='selected'{/if}>{vtranslate('LBL_AUTH_SELECT_NONE','Settings:Workflow2')}</option>
                                        </select>
                                    </td>
                                </tr>
                            {/foreach}
                        </table>
                    </div>
                    <div style="border: 1px solid #777777; padding: 5px; margin: 20px 0 10px 10px;">
                        {vtranslate('LBL_SET_ALL_ROLE_PERMISSIONS','Settings:Workflow2')}
                        <select name='defaultSet' id="defaultSetRole" class="select2" style="width:250px">
                            <option value='3'>{vtranslate('LBL_AUTH_SELECT_EDIT','Settings:Workflow2')}</option>
                            <option value='2'>{vtranslate('LBL_AUTH_SELECT_VIEW','Settings:Workflow2')}</option>
                            <option value='1'>{vtranslate('LBL_AUTH_SELECT_EXEC','Settings:Workflow2')}</option>
                            <option value='0'>{vtranslate('LBL_AUTH_SELECT_NONE','Settings:Workflow2')}</option>
                        </select>
                        <button type="button" class="btn btn-info" onclick="setAllPermissionsTo('rolePermissions', jQuery('#defaultSetRole').val())">{vtranslate('LBL_SET', 'Settings:Workflow2')}</button>
                        <br/>
                        <br/>
                        {vtranslate('LBL_SET_ALL_USER_PERMISSIONS','Settings:Workflow2')}
                        <select name='defaultSet' id="defaultSetUser" class="select2" style="width:250px">
                            <option value='-1'>{vtranslate('LBL_AUTH_SELECT_INHERIT','Settings:Workflow2')}</option>
                            <option value='3'>{vtranslate('LBL_AUTH_SELECT_EDIT','Settings:Workflow2')}</option>
                            <option value='2'>{vtranslate('LBL_AUTH_SELECT_VIEW','Settings:Workflow2')}</option>
                            <option value='1'>{vtranslate('LBL_AUTH_SELECT_EXEC','Settings:Workflow2')}</option>
                            <option value='0'>{vtranslate('LBL_AUTH_SELECT_NONE','Settings:Workflow2')}</option>
                        </select>
                        <button type="button" class="btn btn-info"  onclick="setAllPermissionsTo('userPermissions', jQuery('#defaultSetUser').val())">{vtranslate('LBL_SET', 'Settings:Workflow2')}</button>
                    </div>

                </div>
    </td></tr>
    </table>
</form>
</div>
</div>