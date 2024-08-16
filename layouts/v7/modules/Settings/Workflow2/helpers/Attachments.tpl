<input type="hidden" id="task-attachments" data-id="asd" name="task[{$attachmentsField}]" value="">
<div id='mail_files' style="margin-top:5px;"></div>
<input type="button" class="btn btn-primary openAttachmentsPopupBtn" value="{vtranslate('add Attachment', 'Settings:Workflow2')}" />

<script type="text/javascript">
    var SetAttachmentList = {$SetAttachmentList};

    jQuery(function() {
        AttachmentsList.init(SetAttachmentList, '{$SetAttachmentsModule}');
    });
</script>