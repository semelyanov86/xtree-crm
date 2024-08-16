<?php

namespace Workflow;

abstract class AssistentBase
{
    protected $Name = 'Unnamed Assistent';

    protected $Description = '';

    private $_Key = '';

    public function __construct($key)
    {
        $this->_Key = $key;
    }

    public function getName()
    {
        return $this->Name;
    }

    public function getDescription()
    {
        return $this->Description;
    }

    final public function showConfiguration()
    {
        $viewer = \Vtiger_Viewer::getInstance();

        $this->_beforeShowConfiguration($viewer);

        $viewer->fetch(MODULE_ROOTPATH . DS . 'extends' . DS . 'assistent' . DS . $this->_Key . DS . 'configuration.tpl');
    }

    public function execute($configuration)
    {
        // Execute the Assistant
    }

    protected function _beforeShowConfiguration(\Vtiger_Viewer $viewer)
    {
        // Implement your custom functions before show Configuration
    }
}
