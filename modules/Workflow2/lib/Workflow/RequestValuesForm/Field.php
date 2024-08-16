<?php
/**
 * @copyright 2016-2017 Redoo Networks GmbH
 * @see https://redoo-networks.com/
 * This file is part of a vTigerCRM module, implemented by Redoo Networks GmbH and must not used without permission.
 */

namespace Workflow\RequestValuesForm;

use Workflow\Fieldtype;
use Workflow\Task;
use Workflow\VTEntity;

class Field
{
    /**
     * @var null|Row
     */
    private $_Row;

    private $_ColIndex = 0;

    private $_Type;

    private $_Data = [];

    private $_Config = [];

    private $_FieldName = '';

    private $_Label = '';

    private $_Value = '';

    private $_HTML = '';

    private $_JS = '';

    /**
     * Field constructor.
     */
    public function __construct(Row $row)
    {
        $this->_Row = $row;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->_Type = $type;
    }

    public function getType()
    {
        return $this->_Type;
    }

    public function setFieldname($fieldname)
    {
        $this->_FieldName = $fieldname;
    }

    public function getFieldname()
    {
        return $this->_FieldName;
    }

    public function getValue($value, $fieldName, $completeData, VTEntity $context, Task $task)
    {
        $type = Fieldtype::getType($this->_Type);

        return $type->getValue($value, $fieldName, $this->_Type, $context, $completeData, $this->_Data, $task);
    }

    public function setConfig($config)
    {
        $this->_Config = $config;
    }

    public function setConfigValue($key, $value)
    {
        $this->_Config[$key] = $value;
    }

    public function setLabel($label)
    {
        $this->_Label = $label;
    }

    public function setValue($value)
    {
        $this->_Value = $value;
    }

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->_Data = $data;
    }

    /**
     * @return string
     */
    public function render(VTEntity $context)
    {
        $this->_HTML = '';

        if (!empty($this->_FieldName)) {
            $this->_Data['name'] = $this->_FieldName;
        }
        $this->_Data['type'] = $this->_Type;

        $this->_Data['label'] = $this->_Label;
        $this->_Data['config'] = $this->_Config;

        if (!empty($this->_Value)) {
            $this->_Data['config']['default'] = $this->_Value;
        }

        $type = Fieldtype::getType($this->_Type, 2);

        if ($type === false) {
            throw new \Exception('Type ' . $this->_Type . ' not found');
        }

        if ($type->decorated($this->_Data) == true) {
            $this->_HTML .= '<div class="group materialstyle">';
            $result =  $type->renderFrontendV2($this->_Data, $context);

            $this->_HTML .= $result['html'];
            $this->_JS = $result['js'];

            $this->_HTML .= '<label>' . $this->_Label . '</label>';
            $this->_HTML .= '</div>';
        } else {
            $result = $type->renderFrontendV2($this->_Data, $context);

            $this->_HTML .= $result['html'];
            $this->_JS = $result['js'];
        }
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getHTML()
    {
        if (empty($this->_HTML)) {
            throw new \Exception('Execute render Function before get HTML');
        }

        return $this->_HTML;
    }

    /**
     * @return string
     */
    public function getJS()
    {
        return $this->_JS;
    }
}
