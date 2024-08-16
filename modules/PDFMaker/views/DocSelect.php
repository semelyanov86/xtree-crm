<?php
/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class PDFMaker_DocSelect_View extends Vtiger_IndexAjax_View
{
    public $convertAttributes = [
        'pdftemplateid' => 'template_ids',
    ];

    public $templateAttributes = [
    ];

    public $urlAttributes = [
        'forview',
        'language',
        'pdftemplateid',
    ];

    public $formAttributes = [];

    public function checkPermission(Vtiger_Request $request)
    {
        $moduleName = 'Documents';

        if (!Users_Privileges_Model::isPermitted($moduleName, 'EditView')) {
            throw new AppException('LBL_PERMISSION_DENIED');
        }
    }

    /**
     * @return array
     */
    public function getRecordsFromRequest(Vtiger_Request $request)
    {
        return (!$request->isEmpty('record')) ? [$request->get('record')] : $request->get('selected_ids');
    }

    public function process(Vtiger_Request $request)
    {
        $records = $this->getRecordsFromRequest($request);
        $moduleName = $request->getModule();
        $recordModel = Vtiger_Record_Model::getCleanInstance('Documents');
        /** @var Documents_Module_Model $moduleModel */
        $moduleModel = $recordModel->getModule();
        $fieldList = $moduleModel->getFields();
        $requestFieldList = array_intersect_key($request->getAll(), $fieldList);

        foreach ($requestFieldList as $requestFieldName => $requestFieldValue) {
            if (array_key_exists($requestFieldName, $fieldList)) {
                $moduleFieldModel = $fieldList[$requestFieldName];
                $recordModel->set($requestFieldName, $moduleFieldModel->getDBInsertValue($requestFieldValue));
            }
        }

        $fieldsInfo = [];

        foreach ($fieldList as $name => $model) {
            $fieldsInfo[$name] = $model->getFieldInfo();

            if ($name === 'notes_title') {
                $mpdf = '';
                $model->set('fieldvalue', (new PDFMaker_PDFMaker_Model())->GetPreparedMPDF($mpdf, $records, [$request->get('pdftemplateid')], $request->get('formodule'), $request->get('language')));
            }
        }

        $viewer = $this->getViewer($request);
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('FIELDS_INFO', json_encode($fieldsInfo));
        $viewer->assign('FIELD_MODELS', $fieldList);
        $viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('PMODULE', $request->get('return_module'));
        $viewer->assign('PID', $request->get('return_id'));

        if ($request->has('edit_and_export') && !$request->isEmpty('edit_and_export')) {
            $this->setEditAndExportAttributes($request);
        }

        $viewer->assign('FORM_ATTRIBUTES', $this->getFormAttributes($request));
        $viewer->assign('TEMPLATE_ATTRIBUTES', $this->templateAttributes);

        $viewer->view('CreateDocument.tpl', $moduleName);
    }

    public function getFormAttributes(Vtiger_Request $request)
    {
        foreach ($this->urlAttributes as $attrType) {
            if ($request->has($attrType) && !$request->isEmpty($attrType)) {
                $attrVal = $request->get($attrType);

                if (is_array($attrVal)) {
                    $attrVal = json_encode($attrVal);
                }

                if (isset($this->convertAttributes[$attrType])) {
                    $attrType = $this->convertAttributes[$attrType];
                }

                $this->formAttributes[$attrType] = $attrVal;
            }
        }

        return $this->formAttributes;
    }

    public function setEditAndExportAttributes(Vtiger_Request $request)
    {
        $request->set('mode', 'edit');
        $requestData = $request->getAll();
        $templates = explode(';', $requestData['edit_and_export']);

        foreach ($templates as $template) {
            foreach (['header', 'body', 'footer'] as $type) {
                $name = $type . $template;
                $this->templateAttributes[$name] = $request->get($name);
            }
        }

        array_push($this->urlAttributes, 'mode', 'edit_and_export');
    }
}
