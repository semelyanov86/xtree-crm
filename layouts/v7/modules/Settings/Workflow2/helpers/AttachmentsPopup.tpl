<div class="modal-dialog modelContainer" style="width:600px;">
    {assign var=HEADER_TITLE value={vtranslate("select File","Settings:Workflow2")}}
    {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}

    <div class="modal-content">

        <form method="POST" id="PopupAttachmentsForm">
            {foreach from=$attachmentsJAVASCRIPT item=script}<script type="text/javascript">{$script}</script>{/foreach}
            <div style="margin:10px; min-height:300px;">
                {$attachmentsHTML}
            </div>

            <div class="modal-footer quickCreateActions">
                <a class="cancelLink cancelLinkContainer pull-right" type="reset" data-dismiss="modal">{vtranslate('LBL_CLOSE', $MODULE)}</a>
                <button class="btn btn-success" data-type="" style="display:none;" type="submit" id="submitAttachmentsPopup" ><strong>{vtranslate('LBL_SELECT', $MODULE)}</strong></button>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
    {$javascript}
</script>
