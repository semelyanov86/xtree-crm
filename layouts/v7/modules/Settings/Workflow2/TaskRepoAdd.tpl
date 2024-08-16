<div class="modelContainer" style="width:750px;">
    <form method="POST" id="popupForm" action="index.php?module=Workflow2&parent=Settings&action=TaskRepoSave" enctype="multipart/form-data">
        <input type="hidden" name="_nonce" value="" />
        <div class="modal-header contentsBackground">
            <button class="close" aria-hidden="true" data-dismiss="modal" type="button" title="{vtranslate('LBL_CLOSE')}">x</button>
            <h3>{vtranslate('LBL_ADD_REPOSITORY', 'Settings:Workflow2')}</h3>
        </div>
        <div style="padding: 10px;">{* Content Start *}
            <table cellpadding="0" cellspacing="0">
                <tr>
                    <td class='dvtCellLabel' style="width:200px;">{vtranslate('LBL_REPO_URL','Settings:Workflow2')}</td>
                    <td class='dvtCellInfo'><input type="text" name="repo_url" id="repo_url" value="" style="width:400px;"></td>
                </tr>
                <tr height="35">
                    <td class='dvtCellLabel' style="width:200px;">{vtranslate('LBL_REPO_TITLE','Settings:Workflow2')}</td>
                    <td class='dvtCellInfo' id="repoTitle" data-placeholder="Please enter URL">Please enter URL</td>
                </tr>
                <tr style="display:none;" id="licenseColumn">
                    <td class='dvtCellLabel' style="width:200px;">{vtranslate('LBL_REPO_SYSTEMKEY','Settings:Workflow2')}</td>
                    <td class='dvtCellInfo'><input type="text" name="repo_license" value="" style="width:300px;"><br/>
                    <div id="Autolicense" style="display:none;">{vtranslate('A licensekey was generated automatically. You are free to insert your own one.', 'Settings:Workflow2')}</div>
                    </td>
                </tr>
            </table>
        </div> {* Content Ende *}
        <div class="modal-footer quickCreateActions">
                <a class="cancelLink cancelLinkContainer pull-right" type="reset" data-dismiss="modal">{vtranslate('LBL_CLOSE', $MODULE)}</a>
            <button class="btn btn-success" type="submit" disabled="disabled" id="modalSubmitButton" ><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
        </div>
    </form>
</div>



