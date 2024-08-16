<!DOCTYPE html><html><head></head>
<body>
<div style="width:100%;height:50px; background-color:#eeeeee;padding:5px 10px;">
    <button type="button" class="btn btn-info SVGExportBtn">SVG Export</button>
    <button type="button" class="btn btn-info BPMNExportBtn">Download BPMN</button>
    <button type="button" class="btn btn-danger closeProcessViewBtn">Fenster schlie&szlig;en</button>
</div>
<div id="diagram" style="width:100%;top:50px;bottom:0;position:absolute;"></div>

<!-- required modeler styles -->
<link rel="stylesheet" href="https://unpkg.com/bpmn-js@3.2.2/dist/assets/diagram-js.css">
<link rel="stylesheet" href="https://unpkg.com/bpmn-js@3.2.2/dist/assets/bpmn-font/css/bpmn.css">

<!-- modeler distro -->
{if $READONLY == true}
    <script src="https://unpkg.com/bpmn-js@3.2.2/dist/bpmn-viewer.development.js"></script>
{else}
    <script src="https://unpkg.com/bpmn-js@3.2.2/dist/bpmn-modeler.development.js"></script>
{/if}


<!-- jquery (required for example only) -->
<script src="https://unpkg.com/jquery@3.3.1/dist/jquery.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.min.js"></script>
<style type="text/css">
    div#page {
        padding-top:0;
    }
    .app-footer {
        display:none;
    }
</style>
<!-- app -->
<script type="text/javascript">
    var READONLY = true;


    $('.closeProcessViewBtn').on('click', function() {
        window.close();
    });

    jQuery(function() {
        jQuery('#diagram').css('height', ($(window).height() - 51) + 'px');
        // jQuery.blockUI();
        var viewer = new BpmnJS({
            container: '#diagram'
        });

//        viewer.on('commandStack.changed', exportArtifacts);

        function showDiagram(diagramXML) {
            viewer.importXML(diagramXML, function() {
                viewer.get('canvas').zoom('fit-viewport');
                jQuery('.bpmn-icon-hand-tool').trigger('click');
                // jQuery.unblockUI();
            });
        }


        {if strlen($BPMNContent) > 5}
        showDiagram(jQuery('#bpmnTemplate').html());
        {/if}

        jQuery('.BPMNExportBtn').on('click', function() {
            var procId = $('#procId').val();

            var link = document.createElement("a");
            link.href = 'data:application/bpmn20-xml;charset=UTF-8,' + encodeURIComponent(jQuery('#bpmnTemplate').html());
            link.download = "download.bpmn";
            link.target = '_blank';
            link.click();

        });

        jQuery('.SVGExportBtn').on('click', function() {
            var procId = $('#procId').val();

            viewer.saveSVG({ format: true }, function(err, xml) {
                if(err) {
                    console.error(err);
                    alert(err.message);
                    return;
                }

                var link = document.createElement("a");
                link.href = 'data:application/bpmn20-xml;charset=UTF-8,' + encodeURIComponent(xml);
                link.download = "download.svg";
                link.target = '_blank';
                link.click();

            });
        });



    });

    // load + show diagram

</script>
<script type="text/bpmn" id="bpmnTemplate">
{$BPMNContent}
</script>
</body></html>
