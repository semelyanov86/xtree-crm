<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 01.03.14 17:57
 * You must not use this file without permission.
 */

namespace Workflow\Preset;

use Workflow\Preset;
use Workflow\SimpleConfigFields;
use Workflow\VTEntity;
use Workflow\VTTemplate;

class SimpleConfig extends Preset
{
    /**
     * Preset related definition of related JS Files of preet.
     * @var array
     */
    protected $_JSFiles = [
        'SimpleConfig.js',
    ];

    /**
     * How much columns to show.
     * @var int
     */
    private $_columns = 2;

    /**
     * All fields in SimpleConfig.
     * @var array
     */
    private $_fields = [];

    private $_currentRow;

    /**
     * Store configured data.
     * @var null
     */
    private $_data;

    /**
     * Store current Context.
     * @var null
     */
    private $_context;

    /**
     * @var \Vtiger_Viewer
     */
    private $viewer;

    /**
     * @internal
     */
    public function setViewer($viewer)
    {
        $this->viewer = $viewer;
    }

    /**
     * Determine how much columns are shown in configuration.
     * @return int
     */
    public function getColumnCount()
    {
        return $this->_columns;
    }

    /**
     * Define how much columns are shown in configuration.
     * @param int $columnCount
     */
    public function setColumnCount($columnCount = 2)
    {
        $this->_columns = $columnCount;
    }

    /**
     * Get all values from configuration, otionallz as RAW values.
     * @param bool $raw
     * @return array
     */
    public function getAll($raw = false)
    {
        $result = [];

        foreach ($this->_data as $key => $value) {
            $result[$key] = $this->get($key, $row == true);
        }

        return $result;
    }

    /**
     * @internal
     */
    public function changeContext(VTEntity $context)
    {
        $this->_context = $context;
    }

    /**
     * Check if configuration have a non empty value for a variablename.
     * @return bool
     */
    public function notEmpty($key)
    {
        $value = $this->get($key);

        return empty($value) === false;
    }

    /**
     * Get all values parsed through template engine.
     *
     * @return array
     */
    public function getAllParsed()
    {
        $result = [];

        if (empty($this->_context)) {
            $context = $this->workflow->getContext();
        } else {
            $context = $this->_context;
        }

        foreach ($this->_data as $key => $value) {
            $result[$key] = VTTemplate::parse($this->get($key, true), $context);
        }

        return $result;
    }

    /**
     * Check if configuration have values for a given variablename.
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        $value = $this->get($key, true);

        if (!empty($value) && $value != -1) {
            return true;
        }

        return false;
    }

    /**
     * Function to get Data from SimpleConfig configuration
     * Returns -1, when key is not found
     * Per default the value, will parsed by template engine. You can prevent this, bz providing a second parameter.
     *
     * @param $key string Variable to return
     * @param $raw boolean Do you want RAW Data, before sending to template engine?
     */
    public function get($key, $raw = false)
    {
        if (empty($this->_task)) {
            return -1;
        }

        if ($this->_data === null) {
            $this->_data = $this->_task->get($this->field);
        }

        if (empty($this->_context)) {
            $context = $this->workflow->getContext();
        } else {
            $context = $this->_context;
        }

        if (isset($this->_data[$key])) {
            if (isset($this->_data[$key]['mode']) && $this->_data[$key]['mode'] == 'custom' && $raw === false) {
                // $this->_data[$key]['mode'] = 'default';
                return VTTemplate::parse($this->_data[$key]['value'], $context);
            }
            if ($raw === false && !empty($this->_data[$key]['value'])) {
                return VTTemplate::parse($this->_data[$key]['value'], $context);
            }

            return $this->_data[$key]['value'];
        }

        return -1;
    }

    /**
     * Add Picklist field to configuration.
     *
     * @param string $name variablename
     * @param string $label Field Label
     * @param array $options Available options
     * @param array $args additional Arguments
     */
    public function addPicklist($name, $label, $options, $args = [])
    {
        $args['options'] = $options;

        $this->addField($name, $label, 'select', $args);
    }

    /**
     * @internal
     * Add field with variable type to configuration
     *
     * @see https://wiki.redoo-networks.com/display/MODDEV/Use+SimpleConfig#UseSimpleConfig-Availablefieldtypes
     * @param string $name variablename
     * @param string $label Field Label
     * @param string $type type of field
     * @param array $args additional Arguments
     */
    public function addField($name, $label, $type = 'template', $args = [])
    {
        $this->addFields($name, $label, $type, $args);
    }

    /**
     * @internal
     * @param string $type
     * @param array $args
     */
    public function addRepeatField($name, $label, $type = 'template', $args = [])
    {
        $args['repeatable'] = true;
        // if(substr($name, -2) != '[]') { $name .= '[]'; }

        $this->addField($name, $label, $type, $args);
    }

    /**
     * Add field with variable type to configuration.
     *
     * @see https://wiki.redoo-networks.com/display/MODDEV/Use+SimpleConfig#UseSimpleConfig-Availablefieldtypes
     * @param string $name variablename
     * @param string $label Field Label
     * @param string $type type of field
     * @param array $args additional Arguments
     */
    public function addFields($name, $label, $type = 'template', $args = [])
    {
        if ($this->_currentRow === null) {
            $this->nextRow();
        }

        if (!is_array($args)) {
            $args = [];
        }

        $this->_fields[$this->_currentRow]['child'][] = [
            'type' => $type,
            'label' => $label,
            'name' => $name,
            'args' => $args,
        ];

        if (count($this->_fields[$this->_currentRow]['child']) == $this->_columns) {
            $this->nextRow();
        }
    }

    /**
     * Start new Row in configuration and skip open columns.
     */
    public function nextRow()
    {
        $this->_fields[] = [
            'type' => 'fields',
            'child' => [],
        ];

        $this->_currentRow = count($this->_fields) - 1;
    }

    /**
     * Insert Headline and continue in next row.
     * @param string $text headline to insert
     */
    public function addHeadline($text)
    {
        $this->_fields[] = [
            'type' => 'headline',
            'text' => $text,
        ];
        $this->nextRow();
    }

    /**
     * @internal
     */
    public function beforeGetTaskform($transferData)
    {
        if (empty($this->parameter['templatename'])) {
            $this->parameter['templatename'] = 'simpleconfig';
        }
        if ($this->_data === null) {
            $this->get('LoadDummy');
        }

        // $start = microtime(true);
        [$data, $viewer] = $transferData;
        // $this->_data = $data[$this->field];
        $this->setViewer($viewer);

        $html = '<table class="table table-condensed">';

        foreach ($this->_fields as $rowIndex => $row) {
            $html .= $this->_generateRow($row);
        }

        $html .= '</table>';
        $viewer->assign($this->parameter['templatename'], $html);

        return $transferData;
    }

    /**
     * @internal
     *
     * @return string
     */
    private function _generateRow($row)
    {
        $html = '<tr>';
        if ($row['type'] == 'headline') {
            $html .= '<th colspan="' . ($this->_columns * 2) . '">' . $row['text'] . '</th>';
        } else {
            foreach ($row['child'] as $field) {
                $html .= $this->_generateField($field);
            }
        }
        $html .= '</tr>';

        return $html;
    }

    /**
     * @internal
     */
    private function _generateField($field)
    {
        // $html = '<td>'.$field['label'].'</td><td>';

        $config = array_merge($field['args'], [
            'type' => $field['type'],
            'label' => $field['label'],
            'name' => 'task[' . $this->field . '][' . $field['name'] . ']',
        ]);

        if (isset($this->_data[$field['name']])) {
            $config['value'] = $this->_data[$field['name']];
        } else {
            $config['value'] = '';
        }
        if (!isset($this->_data[$field['name']]) && !empty($field['args']) && !empty($field['args']['default'])) {
            $config['value'] = $field['args']['default'];
        }

        if (!empty($field['args']['default']) && !empty($field['args']['default_on_empty']) && empty($config['value']['value'])) {
            $config['value'] = $field['args']['default'];
        }

        $html = SimpleConfigFields::render($config, $this);

        /*
                if(isset($this->_data[$field['name']])) {
                    $this->viewer->assign('value', $this->_data[$field['name']]);
                } else {
                    $this->viewer->assign('value', '');
                }
                $this->viewer->assign('config', $config);
                $html .= $this->viewer->view('ConfigGenerator.tpl', 'Settings:Workflow2', true);
                $html .= '</td>';
        */
        return $html;
    }
}
