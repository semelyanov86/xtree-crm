/**
 * Created by PHPStorm
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 2013-09-17
 * You must not use this file without permission.
 */



function addBlock(block, styleClass, duplicateId) {
    if(duplicateId === undefined) duplicateId = 0;

    jQuery.post("index.php?module=Workflow2&action=Workflow2Ajax&file=addblock", { workflow:workflow_id, duplicateId: duplicateId, blockid:block, left:350, top:80 }, function(response) {
        if(response == false) {
            return;
        }
        var styleClass = response.styleClass;
        var element_id = response.element_id;
        var topPos = response.topPos;
        var leftPos = response.leftPos;
        var styleExtra = response.styleExtra;
        var typeText = response.typeText;

        var blockText = (response["blockText"].length > 2 ?'<br><span style="font-weight:bold;">' + response["blockText"] + '</span>':'');
        var html = '<div class="context-wfBlock wfBlock ' + styleClass + '" id="' + element_id+ '" style="display:none;top:' + topPos + 'px;left:' + leftPos + 'px;' + styleExtra + '"><span class="blockDescription">' + typeText + blockText + '</span><div data-color="" class="colorLayer">&nbsp;</div><img class="settingsIcon" src="modules/Workflow2/icons/settings.png"></div>';

        jQuery("#workflowDesignContainer").append(html);

        jQuery("#" + element_id).fadeIn("fast");

        endpoints[element_id + "__input"] = jsPlumb.addEndpoint(element_id, inputPointOptions, jQuery.extend(getInput('modules/Workflow2/icons/input.png', "flowChart", false, true, false), {parameters:{ "in":element_id + '__input' }}));

        for(var i = 0; i < response.personPoints.length; i++) {
            var pointKey = response.personPoints[i][0];
            endpoints[element_id + "__" + pointKey] = jsPlumb.addEndpoint(element_id, { anchor:topAnchor[response.personPoints.length][i], maxConnections:maxConnections, overlays:getOverlay(response.personPoints[i][1], 'personLabel')  }, jQuery.extend(getInput('modules/Workflow2/icons/peopleInput.png', "person", false, true, true), {parameters:{ "in":element_id + '__' + pointKey }}));
            _listeners(endpoints[element_id + "__" + pointKey]);
        }
        for(var i = 0; i < response.outputPoints.length; i++) {
            var pointKey = response.outputPoints[i][0];
            endpoints[element_id + "__" + pointKey] = jsPlumb.addEndpoint(element_id, { anchor:rightAnchor[response.outputPoints.length][i], maxConnections:maxConnections, overlays:getOverlay(response.outputPoints[i][1]) }, jQuery.extend(getInput('modules/Workflow2/icons/output.png', "flowChart", true, false, false), {parameters:{ out:element_id + '__' + pointKey }}));

            _listeners(endpoints[element_id + "__" + pointKey]);
        }

//        jsPlumb.draggable(jsPlumb.getSelector("#" + element_id));
        jQuery("#" + element_id).bind( "dblclick", onDblClickBlock);

        jQuery("#" + element_id + ' .colorLayer').bind( "dblclick", function(event) { jQuery(event.target).parent().trigger("dblclick"); });

        jQuery("#" + element_id).bind( "dragstop", onDragStopBlock);
    }, 'json');
}

function addRecord(module_name) {
    jQuery.post("index.php?module=Workflow2&action=Workflow2Ajax&file=addrecord", { workflow:workflow_id, module_name:module_name, left:400, top:110 }, function(response) {
        var element_id = response.element_id;
        var topPos = response.topPos;
        var leftPos = response.leftPos;

        var html = '<div class="wfBlock wfPerson" id="' + element_id + '" style="top:' + topPos + 'px;left:' + leftPos + 'px;">Not connected<img src="modules/Workflow2/icons/cross-button.png" class="removePersonIcon" onclick="removePerson(\'' + element_id + '\');"></div>';

        jQuery("#workflowDesignContainer").append(html);

        endpoints[element_id + "__person"] = jsPlumb.addEndpoint(element_id, { anchor:bottomAnchor, maxConnections:maxConnections }, jQuery.extend(getInput('modules/Workflow2/icons/peopleOutput.png', "person", true, false, true), {parameters:{ out:element_id + '__person' }}));

//        jsPlumb.draggable(jsPlumb.getSelector("#" + element_id));
        jQuery("#" + element_id).bind( "dblclick", onDblClickBlock);

        jQuery("#" + element_id).bind( "dragstop", onDragStopBlock);
    }, 'json');
}

function getOverlay(label, cls) {
   if(cls === undefined) cls = "";

   return [
        [ "Label", { cssClass:"labelClass " + cls, label:label, id:"lbl" } ]
    ];
}

