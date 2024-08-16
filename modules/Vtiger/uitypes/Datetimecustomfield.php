<?php

class Vtiger_DateTimeCustomField_UIType extends Vtiger_Date_UIType
{
    public function getTemplateName()
    {
        return 'uitypes/DateTimeCustomField.tpl';
    }

    public function getDisplayValue($value, $record = false, $recordInstance = false)
    {
        if ($recordInstance) {
            $fieldModel = $this->get('field');
            $fieldDateName = $fieldModel->getName();
            $fieldTimeName = $fieldDateName . '_time';
            $dateTimeValue = $value . ' ' . $recordInstance->get($fieldTimeName);
            $value = $this->getDisplayDateTimeValue($dateTimeValue);
            [$startDate, $startTime] = explode(' ', $value);
            $currentUser = Users_Record_Model::getCurrentUserModel();
            if ($currentUser->get('hour_format') == '12') {
                $startTime = Vtiger_Time_UIType::getTimeValueInAMorPM($startTime);
            }

            return $startDate . ' ' . $startTime;
        }

        return parent::getDisplayValue($value, $record, $recordInstance);
    }
}
