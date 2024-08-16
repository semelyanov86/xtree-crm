<div class="modal-dialog modelContainer" style="width:600px;">
    {assign var=HEADER_TITLE value={vtranslate("LBL_TASK_IMPORT_FILE","Settings:Workflow2")}}
    {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}

    <form method="POST" id="popupForm" action="index.php?module=Workflow2&parent=Settings&action=TaskImport" enctype="multipart/form-data">
    <div class="modal-content">
            <table cellpadding="0" cellspacing="0" class="newTable">
                <tr>
                    <td class='dvtCellLabel' style="width:300px;">{vtranslate('LBL_CHOOSE_TASKFILE','Settings:Workflow2')}</td>
                    <td class='dvtCellInfo'><input type="file" name="file"></td>
                </tr>
                <tr>
                    <td class='dvtCellLabel'>{vtranslate('LBL_UPGRADE_EXISTING','Settings:Workflow2')}</td>
                    <td class='dvtCellInfo'><input type="checkbox" name="enableUpgrade"></td>
                </tr>
                <tr>
                    <td class='dvtCellLabel'>{vtranslate('LBL_UPGRADE_EVEN_OLDER','Settings:Workflow2')}</td>
                    <td class='dvtCellInfo'><input type="checkbox" name="enableDowngrade"></td>
                </tr>
            </table>

            {include file='ModalFooter.tpl'|@vtemplate_path:$MODULE BUTTON_NAME=vtranslate('start import', $MODULE)}
        </div> {* Content Ende *}

    </form>
</div>
</div>


