
<div class="modelContainer" style="width:550px;">
    <div class="modal-header contentsBackground">
        <button class="close" aria-hidden="true" data-dismiss="modal" type="button" title="{vtranslate('LBL_CLOSE')}">x</button>
        <h3>{vtranslate("Last done mails","Settings:Workflow2")}</h3>
    </div>
    <form class="form-horizontal">
        <div style="padding: 10px;">
            {include file="MailscannerHistoryContent.tpl"}
        </div>
    </form>
    <div class="modal-footer quickCreateActions">
        <a class="cancelLink cancelLinkContainer pull-right" type="reset" data-dismiss="modal">{vtranslate('LBL_CLOSE', "Settings:Workflow2")}</a>
        <button class="btn btn-success" type="submit" id="modalSubmitButton" ><strong>{vtranslate('create Workflow', "Settings:Workflow2")}</strong></button>
    </div>
</div>


