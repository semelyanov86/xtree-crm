<?php
/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class PDFMaker_FilterRecordStructure_Model extends PDFMaker_RecordStructure_Model
{
    /**
     * Function to get the values in stuctured format.
     * @return <array> - values in structure array('block'=>array(fieldinfo));
     */
    public function getStructure()
    {
        if (!empty($this->structuredValues)) {
            return $this->structuredValues;
        }

        $recordModel = $this->getPDFMakerModel();
        $recordId = $recordModel->getId();

        $values = [];

        $baseModuleModel = $moduleModel = $this->getModule();
        $blockModelList = $moduleModel->getBlocks();

        foreach ($blockModelList as $blockLabel => $blockModel) {
            $fieldModelList = $blockModel->getFields();
            if (!empty($fieldModelList)) {
                $values[$blockLabel] = [];
                foreach ($fieldModelList as $fieldName => $fieldModel) {
                    if ($fieldModel->isViewableInFilterView()) {
                        if (in_array($moduleModel->getName(), ['Calendar', 'Events']) && $fieldModel->getDisplayType() == 3) {
                            /* Restricting the following fields(Event module fields) for "Calendar" module
                             * time_start, time_end, eventstatus, activitytype,	visibility, duration_hours,
                             * duration_minutes, reminder_time, recurringtype, notime
                             */
                            continue;
                        }
                        if (!empty($recordId)) {
                            // Set the fieldModel with the valuetype for the client side.
                            $fieldValueType = $recordModel->getFieldFilterValueType($fieldName);
                            $fieldInfo = $fieldModel->getFieldInfo();
                            $fieldInfo['pdfmaker_valuetype'] = $fieldValueType;
                            $fieldModel->setFieldInfo($fieldInfo);
                        }
                        // This will be used during editing task like email, sms etc
                        $fieldModel->set('pdfmaker_columnname', $fieldName)->set('pdfmaker_columnlabel', vtranslate($fieldModel->get('label'), $moduleModel->getName()));
                        // This is used to identify the field belongs to source module of pdfmaker
                        $fieldModel->set('pdfmaker_sourcemodule_field', true);
                        $values[$blockLabel][$fieldName] = clone $fieldModel;
                    }
                }
            }
        }

        if ($moduleModel->isCommentEnabled()) {
            $commentFieldModel = PDFMaker_Field_Model::getCommentFieldForFilterConditions($moduleModel);
            $commentFieldModelsList = [$commentFieldModel->getName() => $commentFieldModel];

            $labelName = vtranslate($moduleModel->getSingularLabelKey(), $moduleModel->getName()) . ' ' . vtranslate('LBL_COMMENTS', $moduleModel->getName());
            foreach ($commentFieldModelsList as $commentFieldName => $commentFieldModel) {
                $commentFieldModel->set('pdfmaker_columnname', $commentFieldName)
                    ->set('pdfmaker_columnlabel', vtranslate($commentFieldModel->get('label'), $moduleModel->getName()))
                    ->set('pdfmaker_sourcemodule_field', true);

                $values[$labelName][$commentFieldName] = $commentFieldModel;
            }
        }

        // All the reference fields should also be sent
        $fields = $moduleModel->getFieldsByType(['reference', 'multireference']);
        foreach ($fields as $parentFieldName => $field) {
            $referenceModules = $field->getReferenceList();
            foreach ($referenceModules as $refModule) {
                if ($refModule == 'Users') {
                    continue;
                }
                $moduleModel = Vtiger_Module_Model::getInstance($refModule);
                $blockModelList = $moduleModel->getBlocks();
                foreach ($blockModelList as $blockLabel => $blockModel) {
                    $fieldModelList = $blockModel->getFields();
                    if (!empty($fieldModelList)) {
                        if (PDFMaker_Utils_Helper::count($referenceModules) > 1) {
                            // block label format : reference field label (modulename) - block label. Eg: Related To (Organization) Address Details
                            $newblockLabel = vtranslate($field->get('label'), $baseModuleModel->getName()) . ' (' . vtranslate($refModule, $refModule) . ') - ' .
                                vtranslate($blockLabel, $refModule);
                        } else {
                            $newblockLabel = vtranslate($field->get('label'), $baseModuleModel->getName()) . '-' . vtranslate($blockLabel, $refModule);
                        }
                        $values[$newblockLabel] = [];
                        $fieldModel = $fieldName = null;
                        foreach ($fieldModelList as $fieldName => $fieldModel) {
                            if ($fieldModel->isViewableInFilterView()) {
                                $name = "({$parentFieldName} : ({$refModule}) {$fieldName})";
                                $label = vtranslate($field->get('label'), $baseModuleModel->getName()) . ' : (' . vtranslate($refModule, $refModule) . ') ' . vtranslate($fieldModel->get('label'), $refModule);
                                $fieldModel->set('pdfmaker_columnname', $name)->set('pdfmaker_columnlabel', $label);
                                if (!empty($recordId)) {
                                    $fieldValueType = $recordModel->getFieldFilterValueType($name);
                                    $fieldInfo = $fieldModel->getFieldInfo();
                                    $fieldInfo['pdfmaker_valuetype'] = $fieldValueType;
                                    $fieldModel->setFieldInfo($fieldInfo);
                                }
                                $newFieldModel = clone $fieldModel;
                                $label = vtranslate($field->get('label'), $baseModuleModel->getName()) . '-' . vtranslate($fieldModel->get('label'), $refModule);
                                $newFieldModel->set('label', $label);
                                $values[$newblockLabel][$name] = $newFieldModel;
                            }
                        }
                    }
                }

                $commentFieldModelsList = [];
            }
        }
        $this->structuredValues = $values;

        return $values;
    }
}
