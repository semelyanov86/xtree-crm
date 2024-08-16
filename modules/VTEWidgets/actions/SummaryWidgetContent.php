<?php

class VTEWidgets_SummaryWidgetContent_Action extends Vtiger_BasicAjax_Action
{
    public function process(Vtiger_Request $request)
    {
        $mode = $request->getMode();
        $response = new Vtiger_Response();
        $result = $this->getCustomWidgets($request);
        $response->setResult($result);
        $response->emit();
    }

    public function getCustomWidgets(Vtiger_Request $request)
    {
        global $vtiger_current_version;
        if (version_compare($vtiger_current_version, '7.0.0', '<')) {
            return $this->getCustomWidgetsV6($request);
        }

        return $this->getCustomWidgetsV7($request);
    }

    public function getCustomWidgetsV6(Vtiger_Request $request)
    {
        $moduleName = $request->get('sourcemodule');
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $Record = $request->get('record');
        $html_viewwidgets = [];
        $html_span7 = '';
        $html_span5 = '';
        $ModelWidgets = VTEWidgets_Module_Model::getWidgets($moduleName, $Record);
        $index = 0;
        foreach ($ModelWidgets as $widgetCol) {
            foreach ($widgetCol as $widget) {
                $widgetName = 'VTEWidgets_' . $widget['type'] . '_Handler';
                if (class_exists($widgetName)) {
                    $widgetInstance = new $widgetName($moduleName, $moduleModel, $Record, $widget);
                    $WIDGET = $widgetInstance->getWidget();
                    if (count($WIDGET) > 0) {
                        if ($WIDGET['isactive'] == '0') {
                            continue;
                        }
                        $RELATED_MODULE_NAME = Vtiger_Functions::getModuleName($WIDGET['data']['relatedmodule']);
                        if ($RELATED_MODULE_NAME != '') {
                            $RELATED_MODULE_MODEL = Vtiger_Module_Model::getInstance($RELATED_MODULE_NAME);
                            if ($WIDGET['field_name'] != '') {
                                $FIELD_MODEL = $RELATED_MODULE_MODEL->getField($WIDGET['field_name']);
                                $FIELD_INFO = Zend_Json::encode($FIELD_MODEL->getFieldInfo());
                                if ($WIDGET['column_name'] == 'taxtype') {
                                    $PICKLIST_VALUES = [];
                                    $PICKLIST_VALUES['individual'] = vtranslate('LBL_INDIVIDUAL', $RELATED_MODULE_NAME);
                                    $PICKLIST_VALUES['group'] = vtranslate('LBL_GROUP', $RELATED_MODULE_NAME);
                                } else {
                                    $PICKLIST_VALUES = $FIELD_MODEL->getPicklistValues();
                                }
                                $SPECIAL_VALIDATOR = $FIELD_MODEL->getValidator();
                            }
                            if ($WIDGET['data']['fieldList'] != '') {
                                $fieldlist = ZEND_JSON::encode($WIDGET['data']['fieldList']);
                            }
                        }
                        if ($WIDGET['wcol'] == '1') {
                            $class = 'customwidgetContainer_';
                            $filter = $WIDGET['data']['filter'];
                            $url = $WIDGET['url'] . '&sourcemodule=' . $moduleName;
                            $html_span7 .= "<div class=\"summaryWidgetContainer\">\n                       <div class=\"" . $class . ' widgetContentBlock" data-url="' . $url . '" data-name="' . $WIDGET['label'] . "\">\n                            <div class=\"widget_header row-fluid\">";
                            if ($RELATED_MODULE_NAME == 'Events') {
                                $html_span7 .= '<input type="hidden" class="relatedModuleName" name="relatedModule" value="Calendar" />';
                            } else {
                                $html_span7 .= '<input type="hidden" class="relatedModuleName" name="relatedModule" value="' . $RELATED_MODULE_NAME . '" />';
                            }
                            if ($widget['type'] == 'RelatedModule') {
                                $html_span7 .= ' <input type="hidden" name="columnslist" value="' . Vtiger_Util_Helper::toSafeHTML($fieldlist) . '" />';
                                $html_span7 .= ' <input type="hidden" name="sortby" value="' . $WIDGET['data']['sortby'] . '" />';
                                $html_span7 .= ' <input type="hidden" name="sorttype" value="' . $WIDGET['data']['sorttype'] . '" />';
                            }
                            $html_span7 .= "<span class=\"span11 margin0px\">\n                                <div class=\"row-fluid\">\n                                    <span class=\"pull-left\"><h4 class=\"textOverflowEllipsis\" style=\"width:10em;\">" . vtranslate($WIDGET['label'], $moduleName) . '</h4></span>';
                            if ($filter != '' && $filter != '-') {
                                $html_span7 .= "\n                                        <input type=\"hidden\"  name=\"filter_data\" value=\"" . $filter . "\" />\n                                        <span class=\"span2 alignCenter\" style=\"margin-left:30px\">\n                                         <select class=\"chzn-select filterField\" style=\"max-width:200px;\" name=\"" . $FIELD_MODEL->get('name') . '" data-validation-engine="validate[';
                                if ($FIELD_MODEL->isMandatory() == true) {
                                    $html_span7 .= ' required,';
                                }
                                $html_span7 .= 'funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"';
                                $html_span7 .= ' data-fieldinfo="' . Vtiger_Util_Helper::toSafeHTML($FIELD_INFO) . '" ';
                                if (!empty($SPECIAL_VALIDATOR)) {
                                    $html_span7 .= ' data-validator="' . Zend_Json::encode($SPECIAL_VALIDATOR) . '"';
                                }
                                $html_span7 .= ' data-fieldlable="' . vtranslate($FIELD_MODEL->get('label'), $RELATED_MODULE_NAME) . '"';
                                $html_span7 .= ' data-filter="' . $FIELD_MODEL->get('table') . '.' . $WIDGET['column_name'] . '" data-urlparams="whereCondition">';
                                $html_span7 .= '<option>Select ' . vtranslate($FIELD_MODEL->get('label'), $RELATED_MODULE_NAME) . '</option>';
                                foreach ($PICKLIST_VALUES as $key => $value) {
                                    $html_span7 .= '   <option value="' . $key . '"';
                                    if ($WIDGET['data']['default_filter_value'] == $key) {
                                        $html_span7 .= 'selected';
                                    }
                                    $html_span7 .= '>' . $value . '</option>';
                                }
                                $html_span7 .= '</select> </span>';
                            }
                            if ($WIDGET['data']['action'] == '1' || $WIDGET['data']['select'] == '1') {
                                $html_span7 .= ' <span class="pull-right" style="margin-right: -40px" >';
                            }
                            if ($WIDGET['data']['action'] == '1') {
                                $VRM = Vtiger_Record_Model::getInstanceById($Record, $moduleName);
                                $VRMM = Vtiger_RelationListView_Model::getInstance($VRM, $RELATED_MODULE_NAME);
                                $RELATIONMODEL = $VRMM->getRelationModel();
                                $RELATION_FIELD = $RELATIONMODEL->getRelationField();
                                $html_span7 .= "<button class=\"btn addButton vteWidgetCreateButton\" type=\"button\" href=\"javascript:void(0)\"\n                                               data-url=\"" . $WIDGET['actionURL'] . '" data-name="' . $RELATED_MODULE_NAME . '"';
                                if ($RELATION_FIELD) {
                                    $html_span7 .= ' data-prf="' . $RELATION_FIELD->getName() . '"';
                                }
                                $html_span7 .= '><span class="fa fa-plus"></span>&nbsp;' . vtranslate('LBL_ADD', $RELATED_MODULE_NAME) . '</button>';
                            }
                            if ($WIDGET['data']['select'] == '1') {
                                $html_span7 .= "&nbsp;<button class=\"btn addButton selectRelationonWidget\" type=\"button\"\n                                                data-modulename=\"" . $RELATED_MODULE_NAME . '"';
                                if ($RELATION_FIELD) {
                                    $html_span7 .= ' data-prf="' . $RELATION_FIELD->getName() . '"';
                                }
                                $html_span7 .= '>' . vtranslate('LBL_SELECT', $RELATED_MODULE_NAME) . '</button>';
                            }
                            if ($WIDGET['data']['action'] == '1' || $WIDGET['data']['select'] == '1') {
                                $html_span7 .= ' </span> ';
                            }
                            $html_span7 .= " </div>\n                                        </span>";
                            $html_span7 .= "</div>\n                            <div class=\"widget_contents\"></div>\n                            </div> </div>";
                        } else {
                            $class = 'customwidgetContainer_';
                            $filter = $WIDGET['data']['filter'];
                            $url = $WIDGET['url'] . '&sourcemodule=' . $moduleName;
                            $html_span5 .= "<div class=\"summaryWidgetContainer\">\n                       <div class=\"" . $class . ' widgetContentBlock" data-url="' . $url . '" data-name="' . $WIDGET['label'] . "\">\n\t\t <div class=\"widget_header row-fluid\">";
                            if ($RELATED_MODULE_NAME == 'Events') {
                                $html_span5 .= '<input type="hidden" class="relatedModuleName" name="relatedModule" value="Calendar" />';
                            } else {
                                $html_span5 .= '<input type="hidden" class="relatedModuleName" name="relatedModule" value="' . $RELATED_MODULE_NAME . '" />';
                            }
                            if ($widget['type'] == 'RelatedModule') {
                                $html_span5 .= ' <input type="hidden" name="columnslist" value="' . Vtiger_Util_Helper::toSafeHTML($fieldlist) . '" />';
                                $html_span5 .= ' <input type="hidden" name="sortby" value="' . $WIDGET['data']['sortby'] . '" />';
                                $html_span5 .= ' <input type="hidden" name="sorttype" value="' . $WIDGET['data']['sorttype'] . '" />';
                            }
                            $html_span5 .= "<span class=\"span11 margin0px\">\n\t\t\t\t<div class=\"row-fluid\">\n\t\t\t\t\t<span class=\"pull-left\"><h4 class=\"textOverflowEllipsis\"  style=\"width:8em;\">" . vtranslate($WIDGET['label'], $moduleName) . '</h4></span>';
                            if ($filter != '' && $filter != '-') {
                                $html_span5 .= "\n\t\t\t\t\t\t<input type=\"hidden\"  name=\"filter_data\" value=\"" . $filter . "\" />\n                        <span class=\"span2 alignCenter\" style=\"margin-left: -3px\">\n                            <select class=\"chzn-select filterField\" style=\"max-width:150px;\" name=\"" . $FIELD_MODEL->get('name') . '" data-validation-engine="validate[';
                                if ($FIELD_MODEL->isMandatory() == true) {
                                    $html_span5 .= ' required,';
                                }
                                $html_span5 .= 'funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"';
                                $html_span5 .= ' data-fieldinfo="' . Vtiger_Util_Helper::toSafeHTML($FIELD_INFO) . '" ';
                                if (!empty($SPECIAL_VALIDATOR)) {
                                    $html_span5 .= ' data-validator="' . Zend_Json::encode($SPECIAL_VALIDATOR) . '"';
                                }
                                $html_span5 .= ' data-fieldlable="' . vtranslate($FIELD_MODEL->get('label'), $RELATED_MODULE_NAME) . '"';
                                $html_span5 .= ' data-filter="' . $FIELD_MODEL->get('table') . '.' . $WIDGET['column_name'] . '" data-urlparams="whereCondition">';
                                $html_span5 .= '<option>Select ' . vtranslate($FIELD_MODEL->get('label'), $RELATED_MODULE_NAME) . '</option>';
                                foreach ($PICKLIST_VALUES as $key => $value) {
                                    $html_span5 .= '   <option value="' . $key . '"';
                                    if ($WIDGET['data']['default_filter_value'] == $key) {
                                        $html_span5 .= 'selected';
                                    }
                                    $html_span5 .= '>' . $value . '</option>';
                                }
                                $html_span5 .= '</select> </span>';
                            }
                            if ($WIDGET['data']['action'] == '1' || $WIDGET['data']['select'] == '1') {
                                $html_span5 .= ' <span class="pull-right" style="margin-right: -30px" >';
                            }
                            if ($WIDGET['data']['action'] == '1') {
                                $VRM = Vtiger_Record_Model::getInstanceById($Record, $moduleName);
                                $VRMM = Vtiger_RelationListView_Model::getInstance($VRM, $RELATED_MODULE_NAME);
                                $RELATIONMODEL = $VRMM->getRelationModel();
                                $RELATION_FIELD = $RELATIONMODEL->getRelationField();
                                $html_span5 .= "\n\t\t\t\t\t\t            <button class=\"btn addButton vteWidgetCreateButton\" type=\"button\"\n                               data-url=\"" . $WIDGET['actionURL'] . '" data-name="' . $RELATED_MODULE_NAME . '"';
                                if ($RELATION_FIELD) {
                                    $html_span5 .= ' data-prf="' . $RELATION_FIELD->getName() . '"';
                                }
                                $html_span5 .= '><span class="fa fa-plus"></span>&nbsp;' . vtranslate('LBL_ADD', $RELATED_MODULE_NAME) . '</button>';
                            }
                            if ($WIDGET['data']['select'] == '1') {
                                $html_span5 .= "&nbsp;<button class=\"btn addButton selectRelationonWidget\" type=\"button\"\n                                data-modulename=\"" . $RELATED_MODULE_NAME . '"';
                                if ($RELATION_FIELD) {
                                    $html_span5 .= ' data-prf="' . $RELATION_FIELD->getName() . '"';
                                }
                                $html_span5 .= '>' . vtranslate('LBL_SELECT', $RELATED_MODULE_NAME) . '</button>';
                            }
                            if ($WIDGET['data']['action'] == '1' || $WIDGET['data']['select'] == '1') {
                                $html_span5 .= ' </span> ';
                            }
                            $html_span5 .= " </div>\n\t\t\t</span>";
                            $html_span5 .= "</div>\n\t\t    <div class=\"widget_contents\"></div>\n\t\t</div> </div>";
                        }
                        ++$index;
                    }
                }
            }
        }
        $html_viewwidgets['span5'] = $html_span5;
        $html_viewwidgets['span7'] = $html_span7;

        return $html_viewwidgets;
    }

    public function getCustomWidgetsV7(Vtiger_Request $request)
    {
        $moduleName = $request->get('sourcemodule');
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $Record = $request->get('record');
        if ($moduleName == '' || $Record == '') {
            return null;
        }
        $html_viewwidgets = [];
        $html_span7 = '';
        $html_span5 = '';
        $html_span3 = '';
        $button_event = '';
        $ModelWidgets = VTEWidgets_Module_Model::getWidgets($moduleName, $Record);
        $index = 0;
        foreach ($ModelWidgets as $widgetCol) {
            foreach ($widgetCol as $widget) {
                $button_event = '';
                $widgetName = 'VTEWidgets_' . $widget['type'] . '_Handler';
                if (class_exists($widgetName)) {
                    $widgetInstance = new $widgetName($moduleName, $moduleModel, $Record, $widget);
                    $WIDGET = $widgetInstance->getWidget();
                    if (count($WIDGET) > 0) {
                        if ($WIDGET['isactive'] == '0') {
                            continue;
                        }
                        $RELATED_MODULE_NAME = Vtiger_Functions::getModuleName($WIDGET['data']['relatedmodule']);
                        if ($WIDGET['data']['action_event'] == '1') {
                            $VRM = Vtiger_Record_Model::getInstanceById($Record, $moduleName);
                            $VRMM = Vtiger_RelationListView_Model::getInstance($VRM, $RELATED_MODULE_NAME);
                            $RELATIONMODEL = $VRMM->getRelationModel();
                            $RELATION_FIELD = $RELATIONMODEL->getRelationField();
                            if ($RELATED_MODULE_NAME == 'Calendar') {
                                $button_event = '<button style="padding:5px 12px;margin-left:5px" class="btn addButton btn-event btn-sm btn-default vteWidgetCreateButton textOverflowEllipsis max-width-100" title="' . vtranslate('LBL_ADD_EVENT', $moduleName) . "\" data-name=\"Events\"\n                                                    data-url=\"index.php?module=Events&view=QuickCreateAjax\" href=\"javascript:void(0)\" type=\"button\">\n                                                <i class=\"fa fa-plus\"></i>&nbsp;&nbsp;" . vtranslate('LBL_ADD_EVENT', $moduleName) . "\n                                                </button>";
                            }
                        }
                        if ($RELATED_MODULE_NAME != '') {
                            $RELATED_MODULE_MODEL = Vtiger_Module_Model::getInstance($RELATED_MODULE_NAME);
                            if ($WIDGET['field_name'] != '') {
                                $FIELD_MODEL = $RELATED_MODULE_MODEL->getField($WIDGET['field_name']);
                                $FIELD_INFO = Zend_Json::encode($FIELD_MODEL->getFieldInfo());
                                if ($WIDGET['column_name'] == 'taxtype') {
                                    $PICKLIST_VALUES = [];
                                    $PICKLIST_VALUES['individual'] = vtranslate('LBL_INDIVIDUAL', $RELATED_MODULE_NAME);
                                    $PICKLIST_VALUES['group'] = vtranslate('LBL_GROUP', $RELATED_MODULE_NAME);
                                } else {
                                    $PICKLIST_VALUES = $FIELD_MODEL->getPicklistValues();
                                }
                                $SPECIAL_VALIDATOR = $FIELD_MODEL->getValidator();
                            }
                            if ($WIDGET['data']['fieldList'] != '') {
                                $fieldlist = ZEND_JSON::encode($WIDGET['data']['fieldList']);
                            }
                        }
                        if ($WIDGET['wcol'] == '1') {
                            $class = 'customwidgetContainer_';
                            $filter = $WIDGET['data']['filter'];
                            $url = $WIDGET['url'] . '&sourcemodule=' . $moduleName;
                            $html_span7 .= "<div class=\"summaryWidgetContainer\">\n                                            <div class=\"" . $class . ' widgetContentBlock" data-url="' . $url . '" data-name="' . $WIDGET['label'] . '" data-type="' . $widget['type'] . "\">\n\t\t                                        <div class=\"widget_header row-fluid\">\n\t\t\t                                        <input type=\"hidden\" class=\"relatedlimit\" name=\"relatedlimit\" value=\"" . $WIDGET['data']['limit'] . '" />';
                            if ($RELATED_MODULE_NAME == 'Events') {
                                $html_span7 .= '<input type="hidden" class="relatedModuleName" name="relatedModule" value="Calendar" />';
                            } else {
                                $html_span7 .= '<input type="hidden" class="relatedModuleName" name="relatedModule" value="' . $RELATED_MODULE_NAME . '" />';
                            }
                            if ($widget['type'] == 'RelatedModule') {
                                $html_span7 .= ' <input type="hidden" name="columnslist" value="' . Vtiger_Util_Helper::toSafeHTML($fieldlist) . '" />';
                                $html_span7 .= ' <input type="hidden" name="sortby" value="' . $WIDGET['data']['sortby'] . '" />';
                                $html_span7 .= ' <input type="hidden" name="sorttype" value="' . $WIDGET['data']['sorttype'] . '" />';
                            }
                            $html_span7 .= "<span class=\"span11 margin0px\">\n                                            <div class=\"row-fluid\"><h4 class=\"display-inline-block\" style=\"width:10em;\">" . vtranslate($WIDGET['label'], $moduleName) . '</h4>';
                            if ($filter != '' && $filter != '-') {
                                $html_span7 .= '<input type="hidden"  name="filter_data" value="' . $filter . "\" />\n                                                <span class=\"span2 alignCenter\" style=\"margin-left:30px\">\n                                                    <select class=\"chzn-select filterField\" style=\"max-width:200px;\" name=\"" . $FIELD_MODEL->get('name') . '" data-validation-engine="validate[';
                                if ($FIELD_MODEL->isMandatory() == true) {
                                    $html_span7 .= ' required,';
                                }
                                $html_span7 .= 'funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"';
                                $html_span7 .= ' data-fieldinfo="' . Vtiger_Util_Helper::toSafeHTML($FIELD_INFO) . '" ';
                                if (!empty($SPECIAL_VALIDATOR)) {
                                    $html_span7 .= ' data-validator="' . Zend_Json::encode($SPECIAL_VALIDATOR) . '"';
                                }
                                $html_span7 .= ' data-fieldlable="' . vtranslate($FIELD_MODEL->get('label'), $RELATED_MODULE_NAME) . '"';
                                $html_span7 .= ' data-filter="' . $FIELD_MODEL->get('table') . '.' . $WIDGET['column_name'] . '" data-urlparams="whereCondition">';
                                $html_span7 .= '<option>Select ' . vtranslate($FIELD_MODEL->get('label'), $RELATED_MODULE_NAME) . '</option>';
                                foreach ($PICKLIST_VALUES as $key => $value) {
                                    $html_span7 .= '   <option value="' . $key . '"';
                                    if ($WIDGET['data']['default_filter_value'] == $key) {
                                        $html_span7 .= 'selected';
                                    }
                                    $html_span7 .= '>' . $value . '</option>';
                                }
                                $html_span7 .= '</select> </span>';
                            }
                            if ($WIDGET['data']['action'] == '1' || $WIDGET['data']['select'] == '1') {
                                $html_span7 .= ' <span class="pull-right"  >';
                            }
                            if ($WIDGET['data']['action'] == '1') {
                                $VRM = Vtiger_Record_Model::getInstanceById($Record, $moduleName);
                                $VRMM = Vtiger_RelationListView_Model::getInstance($VRM, $RELATED_MODULE_NAME);
                                $RELATIONMODEL = $VRMM->getRelationModel();
                                $RELATION_FIELD = $RELATIONMODEL->getRelationField();
                                if ($RELATED_MODULE_NAME == 'Documents') {
                                    $html_span7 .= "<div class=\"dropdown\" style=\"float: left\">\n\t\t\t\t\t\t\t\t\t<button type=\"button\" class=\"btn btn-default dropdown-toggle\" data-toggle=\"dropdown\">\n\t\t\t\t\t\t\t\t\t\t<span class=\"fa fa-plus\" title=\"" . vtranslate('LBL_NEW_DOCUMENT', 'Documents') . '"></span>&nbsp;' . vtranslate('LBL_NEW_DOCUMENT', 'Documents') . "&nbsp; <span class=\"caret\"></span>\n\t\t\t\t\t\t\t\t\t</button>\n\t\t\t\t\t\t\t\t\t<ul class=\"dropdown-menu\">\n\t\t\t\t\t\t\t\t\t\t<li class=\"dropdown-header\"><i class=\"fa fa-upload\"></i>" . vtranslate('LBL_FILE_UPLOAD', 'Documents') . "</li>\n\t\t\t\t\t\t\t\t\t\t<li id=\"VtigerAction\">\n\t\t\t\t\t\t\t\t\t\t\t<a href=\"javascript:Documents_Index_Js.uploadTo('Vtiger','" . $Record . "','" . $moduleName . "')\">\n\t\t\t\t\t\t\t\t\t\t\t\t<img style=\"  margin-top: -3px;margin-right: 4%;\" title=\"Vtiger\" alt=\"Vtiger\" src=\"layouts/v7/skins//images/Vtiger.png\">\n\t\t\t\t\t\t\t\t\t\t\t\t" . vtranslate('LBL_TO_SERVICE', 'Documents', vtranslate('LBL_VTIGER', 'Documents')) . "\n\t\t\t\t\t\t\t\t\t\t\t</a>\n\t\t\t\t\t\t\t\t\t\t</li>\n\t\t\t\t\t\t\t\t\t\t<li role=\"separator\" class=\"divider\"></li>\n\t\t\t\t\t\t\t\t\t\t<li id=\"shareDocument\"><a href=\"javascript:Documents_Index_Js.createDocument('E','" . $Record . "','" . $moduleName . "')\">&nbsp;<i class=\"fa fa-external-link\"></i>&nbsp;&nbsp; " . vtranslate('LBL_FROM_SERVICE', 'Documents', vtranslate('LBL_FILE_URL', 'Documents')) . "</a></li>\n\t\t\t\t\t\t\t\t\t\t<li role=\"separator\" class=\"divider\"></li>\n\t\t\t\t\t\t\t\t\t\t<li id=\"createDocument\"><a href=\"javascript:Documents_Index_Js.createDocument('W','" . $Record . "','" . $moduleName . "')\"><i class=\"fa fa-file-text\"></i> " . vtranslate('LBL_CREATE_NEW', 'Documents', vtranslate('SINGLE_Documents', 'Documents')) . "</a></li>\n\t\t\t\t\t\t\t\t\t</ul>\n\t\t\t\t\t\t\t\t</div>";
                                } else {
                                    if ($RELATED_MODULE_NAME == 'Documents') {
                                        $html_span7 .= "<div class=\"dropdown\" style=\"float: left\">\n                                            <button type=\"button\" class=\"btn btn-default dropdown-toggle\" data-toggle=\"dropdown\">\n                                                <span class=\"fa fa-plus\" title=\"" . vtranslate('LBL_NEW_DOCUMENT', 'Documents') . '"></span>&nbsp;' . vtranslate('LBL_NEW_DOCUMENT', 'Documents') . "&nbsp; <span class=\"caret\"></span>\n                                            </button>\n                                            <ul class=\"dropdown-menu\">\n                                                <li class=\"dropdown-header\"><i class=\"fa fa-upload\"></i>" . vtranslate('LBL_FILE_UPLOAD', 'Documents') . "</li>\n                                                <li id=\"VtigerAction\">\n                                                    <a href=\"javascript:Documents_Index_Js.uploadTo('Vtiger','" . $Record . "','" . $moduleName . "')\">\n                                                        <img style=\"  margin-top: -3px;margin-right: 4%;\" title=\"Vtiger\" alt=\"Vtiger\" src=\"layouts/v7/skins//images/Vtiger.png\">\n                                                        " . vtranslate('LBL_TO_SERVICE', 'Documents', vtranslate('LBL_VTIGER', 'Documents')) . "\n                                                    </a>\n                                                </li>\n                                                <li role=\"separator\" class=\"divider\"></li>\n                                                <li id=\"shareDocument\"><a href=\"javascript:Documents_Index_Js.createDocument('E','" . $Record . "','" . $moduleName . "')\">&nbsp;<i class=\"fa fa-external-link\"></i>&nbsp;&nbsp; " . vtranslate('LBL_FROM_SERVICE', 'Documents', vtranslate('LBL_FILE_URL', 'Documents')) . "</a></li>\n                                                <li role=\"separator\" class=\"divider\"></li>\n                                                <li id=\"createDocument\"><a href=\"javascript:Documents_Index_Js.createDocument('W','" . $Record . "','" . $moduleName . "')\"><i class=\"fa fa-file-text\"></i> " . vtranslate('LBL_CREATE_NEW', 'Documents', vtranslate('SINGLE_Documents', 'Documents')) . "</a></li>\n                                            </ul>\n                                        </div>";
                                    } else {
                                        if ($RELATED_MODULE_NAME != 'Emails') {
                                            $html_span7 .= "<button style=\"padding:5px 12px;margin-left:5px\" class=\"btn addButton btn-sm btn-default  vteWidgetCreateButton\" type=\"button\" href=\"javascript:void(0)\"\n                                                data-url=\"" . $WIDGET['actionURL'] . '" data-name="' . $RELATED_MODULE_NAME . '"';
                                            if ($RELATION_FIELD) {
                                                $html_span7 .= ' data-prf="' . $RELATION_FIELD->getName() . '"';
                                            }
                                            $html_span7 .= '><span class="fa fa-plus"></span>&nbsp;' . vtranslate('LBL_ADD', $RELATED_MODULE_NAME) . '</button> &nbsp;&nbsp;';
                                            if ($RELATED_MODULE_NAME != 'Calendar') {
                                            }
                                        } else {
                                            $html_span7 .= "<button class=\"btn btn-default vteWidgetSendMailButton\" onclick=\"javascript:Vtiger_Detail_Js.triggerSendEmail('index.php?module=" . $moduleName . "&view=MassActionAjax&mode=showComposeEmailForm&step=step1','Emails');\" type=\"button\" href=\"javascript:void(0)\"\n                                                data-url=\"" . $WIDGET['actionURL'] . '" data-name="' . $RELATED_MODULE_NAME . '"';
                                            if ($RELATION_FIELD) {
                                                $html_span7 .= ' data-prf="' . $RELATION_FIELD->getName() . '"';
                                            }
                                            $html_span7 .= '><span class="fa fa-plus"></span>&nbsp;' . vtranslate('LBL_ADD', $RELATED_MODULE_NAME) . '</button>';
                                        }
                                    }
                                }
                            }
                            $html_span7 .= $button_event;
                            if ($WIDGET['data']['select'] == '1') {
                                $html_span7 .= "&nbsp;<button class=\"btn btn-default selectRelationonWidget\" type=\"button\"\n                                                data-modulename=\"" . $RELATED_MODULE_NAME . '"';
                                if ($RELATION_FIELD) {
                                    $html_span7 .= ' data-prf="' . $RELATION_FIELD->getName() . '"';
                                }
                                $html_span7 .= '>' . vtranslate('LBL_SELECT', $RELATED_MODULE_NAME) . '</button>';
                            }
                            if ($WIDGET['data']['action'] == '1' || $WIDGET['data']['select'] == '1') {
                                $html_span7 .= ' </span> ';
                            }
                            $html_span7 .= ' </div></span>';
                            $html_span7 .= "</div>\n                             \t\t      <div class=\"widget_contents\"></div>\n\t\t                                </div> </div>";
                        } else {
                            if ($WIDGET['wcol'] == '2') {
                                $class = 'customwidgetContainer_';
                                $filter = $WIDGET['data']['filter'];
                                $url = $WIDGET['url'] . '&sourcemodule=' . $moduleName;
                                $html_span5 .= "<div class=\"summaryWidgetContainer\">\n                                            <div class=\"" . $class . ' widgetContentBlock" data-url="' . $url . '" data-name="' . $WIDGET['label'] . '" data-type="' . $widget['type'] . "\">\n\t\t                                        <div class=\"widget_header row-fluid\">\n\t\t                                            <input type=\"hidden\" class=\"relatedlimit\" name=\"relatedlimit\" value=\"" . $WIDGET['data']['limit'] . '" />';
                                if ($RELATED_MODULE_NAME == 'Events') {
                                    $html_span5 .= '<input type="hidden" class="relatedModuleName" name="relatedModule" value="Calendar" />';
                                } else {
                                    $html_span5 .= '<input type="hidden" class="relatedModuleName" name="relatedModule" value="' . $RELATED_MODULE_NAME . '" />';
                                }
                                if ($widget['type'] == 'RelatedModule') {
                                    $html_span5 .= ' <input type="hidden" name="columnslist" value="' . Vtiger_Util_Helper::toSafeHTML($fieldlist) . '" />';
                                    $html_span5 .= ' <input type="hidden" name="sortby" value="' . $WIDGET['data']['sortby'] . '" />';
                                    $html_span5 .= ' <input type="hidden" name="sorttype" value="' . $WIDGET['data']['sorttype'] . '" />';
                                }
                                $html_span5 .= "<span class=\"span11 margin0px\">\n\t\t\t\t                          <div class=\"row-fluid\"><h4 class=\"display-inline-block\"  style=\"width:8em;\">" . vtranslate($WIDGET['label'], $moduleName) . '</h4>';
                                if ($filter != '' && $filter != '-') {
                                    $html_span5 .= '<input type="hidden"  name="filter_data" value="' . $filter . "\" />\n                                                <span class=\"span2 alignCenter\" style=\"margin-left: 30px\">\n                                                    <select class=\"chzn-select filterField\" style=\"max-width:150px;\" name=\"" . $FIELD_MODEL->get('name') . '" data-validation-engine="validate[';
                                    if ($FIELD_MODEL->isMandatory() == true) {
                                        $html_span5 .= ' required,';
                                    }
                                    $html_span5 .= 'funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"';
                                    $html_span5 .= ' data-fieldinfo="' . Vtiger_Util_Helper::toSafeHTML($FIELD_INFO) . '" ';
                                    if (!empty($SPECIAL_VALIDATOR)) {
                                        $html_span5 .= ' data-validator="' . Zend_Json::encode($SPECIAL_VALIDATOR) . '"';
                                    }
                                    $html_span5 .= ' data-fieldlable="' . vtranslate($FIELD_MODEL->get('label'), $RELATED_MODULE_NAME) . '"';
                                    $html_span5 .= ' data-filter="' . $FIELD_MODEL->get('table') . '.' . $WIDGET['column_name'] . '" data-urlparams="whereCondition">';
                                    $html_span5 .= '<option>Select ' . vtranslate($FIELD_MODEL->get('label'), $RELATED_MODULE_NAME) . '</option>';
                                    foreach ($PICKLIST_VALUES as $key => $value) {
                                        $html_span5 .= '   <option value="' . $key . '"';
                                        if ($WIDGET['data']['default_filter_value'] == $key) {
                                            $html_span5 .= 'selected';
                                        }
                                        $html_span5 .= '>' . $value . '</option>';
                                    }
                                    $html_span5 .= '</select> </span>';
                                }
                                if ($WIDGET['data']['action'] == '1' || $WIDGET['data']['select'] == '1') {
                                    $html_span5 .= ' <span class="pull-right" >';
                                }
                                if ($WIDGET['data']['action'] == '1') {
                                    $VRM = Vtiger_Record_Model::getInstanceById($Record, $moduleName);
                                    $VRMM = Vtiger_RelationListView_Model::getInstance($VRM, $RELATED_MODULE_NAME);
                                    $RELATIONMODEL = $VRMM->getRelationModel();
                                    $RELATION_FIELD = $RELATIONMODEL->getRelationField();
                                    if ($RELATED_MODULE_NAME == 'Documents') {
                                        $html_span5 .= "<div class=\"dropdown\" style=\"float: left\">\n                                            <button type=\"button\" class=\"btn btn-default dropdown-toggle\" data-toggle=\"dropdown\">\n                                                <span class=\"fa fa-plus\" title=\"" . vtranslate('LBL_NEW_DOCUMENT', 'Documents') . '"></span>&nbsp;' . vtranslate('LBL_NEW_DOCUMENT', 'Documents') . "&nbsp; <span class=\"caret\"></span>\n                                            </button>\n                                            <ul class=\"dropdown-menu\">\n                                                <li class=\"dropdown-header\"><i class=\"fa fa-upload\"></i>" . vtranslate('LBL_FILE_UPLOAD', 'Documents') . "</li>\n                                                <li id=\"VtigerAction\">\n                                                    <a href=\"javascript:Documents_Index_Js.uploadTo('Vtiger','" . $Record . "','" . $moduleName . "')\">\n                                                        <img style=\"  margin-top: -3px;margin-right: 4%;\" title=\"Vtiger\" alt=\"Vtiger\" src=\"layouts/v7/skins//images/Vtiger.png\">\n                                                        " . vtranslate('LBL_TO_SERVICE', 'Documents', vtranslate('LBL_VTIGER', 'Documents')) . "\n                                                    </a>\n                                                </li>\n                                                <li role=\"separator\" class=\"divider\"></li>\n                                                <li id=\"shareDocument\"><a href=\"javascript:Documents_Index_Js.createDocument('E','" . $Record . "','" . $moduleName . "')\">&nbsp;<i class=\"fa fa-external-link\"></i>&nbsp;&nbsp; " . vtranslate('LBL_FROM_SERVICE', 'Documents', vtranslate('LBL_FILE_URL', 'Documents')) . "</a></li>\n                                                <li role=\"separator\" class=\"divider\"></li>\n                                                <li id=\"createDocument\"><a href=\"javascript:Documents_Index_Js.createDocument('W','" . $Record . "','" . $moduleName . "')\"><i class=\"fa fa-file-text\"></i> " . vtranslate('LBL_CREATE_NEW', 'Documents', vtranslate('SINGLE_Documents', 'Documents')) . "</a></li>\n                                            </ul>\n                                        </div>";
                                    } else {
                                        if ($RELATED_MODULE_NAME != 'Emails') {
                                            $html_span5 .= "<button class=\"btn btn-default vteWidgetCreateButton\" type=\"button\"\n                                                data-url=\"" . $WIDGET['actionURL'] . '" data-name="' . $RELATED_MODULE_NAME . '"';
                                            if ($RELATION_FIELD) {
                                                $html_span5 .= ' data-prf="' . $RELATION_FIELD->getName() . '"';
                                            }
                                            $html_span5 .= '><span class="fa fa-plus"></span>&nbsp;' . vtranslate('LBL_ADD', $RELATED_MODULE_NAME) . '</button>';
                                        } else {
                                            $html_span5 .= "<button class=\"btn btn-default vteWidgetSendMailButton\" onclick=\"javascript:Vtiger_Detail_Js.triggerSendEmail('index.php?module=" . $moduleName . "&view=MassActionAjax&mode=showComposeEmailForm&step=step1','Emails');\" type=\"button\"\n                                                data-url=\"" . $WIDGET['actionURL'] . '" data-name="' . $RELATED_MODULE_NAME . '"';
                                            if ($RELATION_FIELD) {
                                                $html_span5 .= ' data-prf="' . $RELATION_FIELD->getName() . '"';
                                            }
                                            $html_span5 .= '><span class="fa fa-plus"></span>&nbsp;' . vtranslate('LBL_ADD', $RELATED_MODULE_NAME) . '</button>';
                                        }
                                    }
                                }
                                $html_span5 .= $button_event;
                                if ($WIDGET['data']['select'] == '1') {
                                    $html_span5 .= "&nbsp;<button class=\"btn btn-default selectRelationonWidget\" type=\"button\"\n                                data-modulename=\"" . $RELATED_MODULE_NAME . '"';
                                    if ($RELATION_FIELD) {
                                        $html_span5 .= ' data-prf="' . $RELATION_FIELD->getName() . '"';
                                    }
                                    $html_span5 .= '>' . vtranslate('LBL_SELECT', $RELATED_MODULE_NAME) . '</button>';
                                }
                                if ($WIDGET['data']['action'] == '1' || $WIDGET['data']['select'] == '1') {
                                    $html_span5 .= ' </span> ';
                                }
                                $html_span5 .= ' </div></span>';
                                $html_span5 .= "</div>\n                                        <div class=\"widget_contents\"></div>\n                                    </div> </div>";
                            } else {
                                if ($WIDGET['wcol'] == '3') {
                                    $class = 'customwidgetContainer_';
                                    $filter = $WIDGET['data']['filter'];
                                    $url = $WIDGET['url'] . '&sourcemodule=' . $moduleName;
                                    $html_span3 .= "<div class=\"summaryWidgetContainer\">\n                                            <div class=\"" . $class . ' widgetContentBlock" data-url="' . $url . '" data-name="' . $WIDGET['label'] . '" data-type="' . $widget['type'] . "\">\n\t\t                                        <div class=\"widget_header row-fluid\">\n\t\t                                            <input type=\"hidden\" class=\"relatedlimit\" name=\"relatedlimit\" value=\"" . $WIDGET['data']['limit'] . '" />';
                                    if ($RELATED_MODULE_NAME == 'Events') {
                                        $html_span3 .= '<input type="hidden" class="relatedModuleName" name="relatedModule" value="Calendar" />';
                                    } else {
                                        $html_span3 .= '<input type="hidden" class="relatedModuleName" name="relatedModule" value="' . $RELATED_MODULE_NAME . '" />';
                                    }
                                    if ($widget['type'] == 'RelatedModule') {
                                        $html_span3 .= '<input type="hidden" name="columnslist" value="' . Vtiger_Util_Helper::toSafeHTML($fieldlist) . '" />';
                                        $html_span3 .= ' <input type="hidden" name="sortby" value="' . $WIDGET['data']['sortby'] . '" />';
                                        $html_span3 .= ' <input type="hidden" name="sorttype" value="' . $WIDGET['data']['sorttype'] . '" />';
                                    }
                                    $html_span3 .= "<span class=\"span11 margin0px\">\n                                            <div class=\"row-fluid\"><h4 class=\"display-inline-block\" style=\"width:10em;\">" . vtranslate($WIDGET['label'], $moduleName) . '</h4>';
                                    if ($filter != '' && $filter != '-') {
                                        $html_span3 .= '<input type="hidden"  name="filter_data" value="' . $filter . "\" />\n                                                <span class=\"span2 alignCenter\" style=\"margin-left:30px\">\n                                                    <select class=\"chzn-select filterField\" style=\"max-width:200px;\" name=\"" . $FIELD_MODEL->get('name') . '" data-validation-engine="validate[';
                                        if ($FIELD_MODEL->isMandatory() == true) {
                                            $html_span3 .= ' required,';
                                        }
                                        $html_span3 .= 'funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"';
                                        $html_span3 .= ' data-fieldinfo="' . Vtiger_Util_Helper::toSafeHTML($FIELD_INFO) . '" ';
                                        if (!empty($SPECIAL_VALIDATOR)) {
                                            $html_span3 .= ' data-validator="' . Zend_Json::encode($SPECIAL_VALIDATOR) . '"';
                                        }
                                        $html_span3 .= ' data-fieldlable="' . vtranslate($FIELD_MODEL->get('label'), $RELATED_MODULE_NAME) . '"';
                                        $html_span3 .= ' data-filter="' . $FIELD_MODEL->get('table') . '.' . $WIDGET['column_name'] . '" data-urlparams="whereCondition">';
                                        $html_span3 .= '<option>Select ' . vtranslate($FIELD_MODEL->get('label'), $RELATED_MODULE_NAME) . '</option>';
                                        foreach ($PICKLIST_VALUES as $key => $value) {
                                            $html_span3 .= '   <option value="' . $key . '"';
                                            if ($FIELD_MODEL->get('fieldvalue') == $key) {
                                                $html_span3 .= 'selected';
                                            }
                                            if ($WIDGET['data']['default_filter_value'] == $key) {
                                                $html_span3 .= 'selected';
                                            }
                                            $html_span3 .= '>' . $value . '</option>';
                                        }
                                        $html_span3 .= '</select> </span>';
                                    }
                                    if ($WIDGET['data']['action'] == '1' || $WIDGET['data']['select'] == '1') {
                                        $html_span3 .= ' <span class="pull-right"  >';
                                    }
                                    if ($WIDGET['data']['action'] == '1') {
                                        $VRM = Vtiger_Record_Model::getInstanceById($Record, $moduleName);
                                        $VRMM = Vtiger_RelationListView_Model::getInstance($VRM, $RELATED_MODULE_NAME);
                                        $RELATIONMODEL = $VRMM->getRelationModel();
                                        $RELATION_FIELD = $RELATIONMODEL->getRelationField();
                                        if ($RELATED_MODULE_NAME == 'Documents') {
                                            $html_span3 .= "<div class=\"dropdown\" style=\"float: left\">\n                                            <button type=\"button\" class=\"btn btn-default dropdown-toggle\" data-toggle=\"dropdown\">\n                                                <span class=\"fa fa-plus\" title=\"" . vtranslate('LBL_NEW_DOCUMENT', 'Documents') . '"></span>&nbsp;' . vtranslate('LBL_NEW_DOCUMENT', 'Documents') . "&nbsp; <span class=\"caret\"></span>\n                                            </button>\n                                            <ul class=\"dropdown-menu\">\n                                                <li class=\"dropdown-header\"><i class=\"fa fa-upload\"></i>" . vtranslate('LBL_FILE_UPLOAD', 'Documents') . "</li>\n                                                <li id=\"VtigerAction\">\n                                                    <a href=\"javascript:Documents_Index_Js.uploadTo('Vtiger','" . $Record . "','" . $moduleName . "')\">\n                                                        <img style=\"  margin-top: -3px;margin-right: 4%;\" title=\"Vtiger\" alt=\"Vtiger\" src=\"layouts/v7/skins//images/Vtiger.png\">\n                                                        " . vtranslate('LBL_TO_SERVICE', 'Documents', vtranslate('LBL_VTIGER', 'Documents')) . "\n                                                    </a>\n                                                </li>\n                                                <li role=\"separator\" class=\"divider\"></li>\n                                                <li id=\"shareDocument\"><a href=\"javascript:Documents_Index_Js.createDocument('E','" . $Record . "','" . $moduleName . "')\">&nbsp;<i class=\"fa fa-external-link\"></i>&nbsp;&nbsp; " . vtranslate('LBL_FROM_SERVICE', 'Documents', vtranslate('LBL_FILE_URL', 'Documents')) . "</a></li>\n                                                <li role=\"separator\" class=\"divider\"></li>\n                                                <li id=\"createDocument\"><a href=\"javascript:Documents_Index_Js.createDocument('W','" . $Record . "','" . $moduleName . "')\"><i class=\"fa fa-file-text\"></i> " . vtranslate('LBL_CREATE_NEW', 'Documents', vtranslate('SINGLE_Documents', 'Documents')) . "</a></li>\n                                            </ul>\n                                        </div>";
                                        } else {
                                            if ($RELATED_MODULE_NAME != 'Emails') {
                                                $html_span3 .= "<button class=\"btn btn-default vteWidgetCreateButton\" type=\"button\" href=\"javascript:void(0)\"\n                                                data-url=\"" . $WIDGET['actionURL'] . '" data-name="' . $RELATED_MODULE_NAME . '"';
                                                if ($RELATION_FIELD) {
                                                    $html_span3 .= ' data-prf="' . $RELATION_FIELD->getName() . '"';
                                                }
                                                $html_span3 .= '><span class="fa fa-plus"></span>&nbsp;' . vtranslate('LBL_ADD', $RELATED_MODULE_NAME) . '</button>';
                                            } else {
                                                $html_span3 .= "<button class=\"btn btn-default vteWidgetSendMailButton\" onclick=\"javascript:Vtiger_Detail_Js.triggerSendEmail('index.php?module=" . $moduleName . "&view=MassActionAjax&mode=showComposeEmailForm&step=step1','Emails');\" type=\"button\" href=\"javascript:void(0)\"\n                                                data-url=\"" . $WIDGET['actionURL'] . '" data-name="' . $RELATED_MODULE_NAME . '"';
                                                if ($RELATION_FIELD) {
                                                    $html_span3 .= ' data-prf="' . $RELATION_FIELD->getName() . '"';
                                                }
                                                $html_span3 .= '><span class="fa fa-plus"></span>&nbsp;' . vtranslate('LBL_ADD', $RELATED_MODULE_NAME) . '</button>';
                                            }
                                        }
                                    }
                                    $html_span3 .= $button_event;
                                    if ($WIDGET['data']['select'] == '1') {
                                        $html_span3 .= "&nbsp;<button class=\"btn btn-default selectRelationonWidget\" type=\"button\"\n                                                data-modulename=\"" . $RELATED_MODULE_NAME . '"';
                                        if ($RELATION_FIELD) {
                                            $html_span3 .= ' data-prf="' . $RELATION_FIELD->getName() . '"';
                                        }
                                        $html_span3 .= '>' . vtranslate('LBL_SELECT', $RELATED_MODULE_NAME) . '</button>';
                                    }
                                    if ($WIDGET['data']['action'] == '1' || $WIDGET['data']['select'] == '1') {
                                        $html_span3 .= ' </span> ';
                                    }
                                    $html_span3 .= ' </div></span>';
                                    $html_span3 .= "</div>\n                             \t\t      <div class=\"widget_contents\"></div>\n\t\t                                </div> </div>";
                                }
                            }
                        }
                        ++$index;
                    }
                }
            }
        }
        $html_viewwidgets['span5'] = $html_span5;
        $html_viewwidgets['span7'] = $html_span7;
        $html_viewwidgets['span3'] = $html_span3;

        return $html_viewwidgets;
    }
}
