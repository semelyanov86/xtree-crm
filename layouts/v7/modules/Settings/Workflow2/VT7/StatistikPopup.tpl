<div class="modal-dialog modelContainer" style="width:600px;">
    {assign var=HEADER_TITLE value={vtranslate("Reporting for this block","Settings:Workflow2")}}
    {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}

    <div class="modal-content">

        <div style='height:160px;margin:auto;' id="durationBlock"></div>
        <script type="text/javascript">
          var durations = {$durations|@json_encode};
          var maxValue = {$maxValue};
        </script>
        <div style='height:160px;margin:auto;' id="extraLogInformation">
            {$LogInformation}
        </div>
    </div>
</div>



