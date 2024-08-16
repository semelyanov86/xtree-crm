<div style="width:100%;height:50px; background-color:#eeeeee;padding:5px 10px;">
    <button type="button" class="btn btn-info SVGExportBtn">SVG Export</button>
    <button type="button" class="btn btn-danger closeProcessViewBtn">Fenster schlie&szlig;en</button>
</div>
<div id="diagram" style="width:100%;height:calc(100% - 50px);"></div>

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

<!-- app -->
<script type="text/javascript">
    var WINDOWMODE = {if $WINDOWMODE == true}true{else}false{/if};
    var READONLY = {if $READONLY == true}true{else}false{/if};

    jQuery(function() {
        jQuery.blockUI();
        var viewer = new BpmnJS({
            container: '#diagram'
        });

//        viewer.on('commandStack.changed', exportArtifacts);

        function showDiagram(diagramXML) {
            viewer.importXML(diagramXML, function() {
                viewer.get('canvas').zoom('fit-viewport');
                jQuery('.bpmn-icon-hand-tool').trigger('click');
                jQuery.unblockUI();
            });
        }


        {if strlen($BPMNContent) > 5}
        showDiagram(jQuery('#bpmnTemplate').html());
        {else}
        jQuery.get('modules/RedooBusinessProcesses/resources/newDiagram.bpmn', showDiagram);
        {/if}

        jQuery('.saveProcessBtn').on('click', function() {
            var procId = $('#procId').val();

            viewer.saveXML({ format: true }, function(err, xml) {
                if(err) {
                    console.error(err);
                    alert(err.message);
                    return;
                }

                jQuery.post('index.php?module=RedooBusinessProcesses&action=BPMNSave', {
                    procId: procId,
                    title: $('#proc_title').val(),
                    xml: xml
                })

            });

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

        $('.closeProcessViewBtn').on('click', function() {
            var procId = $('#procId').val();
            if(READONLY === false) {
                bootbox.confirm('Editor wirklich schließen?', function(result) {
                    if(result === true) {
                        if(WINDOWMODE === true) {
                            window.close();
                        } else {
                            var current = window.parent.jQuery.featherlight.current();
                            current.close();
                        }

                    }
                });
                return;
            }

            if(WINDOWMODE === true) {
                window.close();
            } else {
                var current = window.parent.jQuery.featherlight.current();
                current.close();
            }
        });
        $('.activateProcessBtn').on('click', function() {
            var procId = $('#procId').val();
            if(READONLY === false) {
                bootbox.confirm('Bitte bestätigen!<br/><b>Vorherige Revisionen werden archiviert!</b>', function(result) {
                    if(result === true) {

                        jQuery.post('index.php?module=RedooBusinessProcesses&action=RevisionActivate', {
                            procId: procId
                        })

                    }
                });
                return;
            }

            if(WINDOWMODE === true) {
                window.close();
            } else {
                var current = window.parent.jQuery.featherlight.current();
                current.close();
            }
        });
        $('.createRevisionBtn').on('click', function() {
            var procId = $('#procId').val();
            if(READONLY === true) {
                bootbox.confirm('Bitte bestätigen!<br/><b>Es wird eine neue Revision als Entwurf erstellt!</b>', function(result) {
                    if(result === true) {

                        jQuery.post('index.php?module=RedooBusinessProcesses&action=RevisionCreate', {
                            procId: procId
                        }).then(function(response) {
                            if(WINDOWMODE) {
                                window.location.href = 'index.php?module=RedooBusinessProcesses&view=BPMNEditor&mode=edit&window=1&procid=' + response;
                            } else {
                                window.location.href = 'index.php?module=RedooBusinessProcesses&view=BPMNEditor&mode=edit&procid=' + response;
                            }
                        });

                    }
                });
            }

        });
    });

    // load + show diagram

</script>
<script type="text/bpmn" id="bpmnTemplate">
{$BPMNContent}
</script>
