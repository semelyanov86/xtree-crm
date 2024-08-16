<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 01.03.14 18:18
 * You must not use this file without permission.
 */

namespace Workflow;

class Preset
{
    /**
     * @var bool|Main
     */
    protected $workflow = false;

    protected $field = false;

    protected $parameter = false;

    protected $_inlineJS = '';

    protected $_JSFiles = [];

    protected $_CSSFiles = [];

    /**
     * @var Task
     */
    protected $_task;

    public function __construct($field, $workflow, $extraParameter = [], $task = null)
    {
        $this->workflow = $workflow;
        $this->field = $field;
        $this->parameter = $extraParameter;
        $this->_task = $task;

        $this->init();
    }

    /**
     * Set a value as parameter.
     */
    public function setParameter($key, $value)
    {
        if (is_array($this->parameter)) {
            $this->parameter = [];
        }

        $this->parameter[$key] = $value;
    }

    /**
     * @internal
     * @return string
     */
    public function getInlineJS()
    {
        return $this->_inlineJS;
    }

    /**
     * @internal
     * @return array
     */
    public function getJSFiles()
    {
        return $this->_JSFiles;
    }

    /**
     * @internal
     * @return array
     */
    public function getCSSFiles()
    {
        return $this->_CSSFiles;
    }

    /**
     * Add JS file to current Preset.
     */
    public function addInlineJS($script)
    {
        $this->_inlineJS .= $script;
    }

    public function afterSave() {}

    public function beforeSave($data)
    {
        return $data;
    }

    public function beforeGetTaskform($data)
    {
        return $data;
    }

    public function init() {}
}
