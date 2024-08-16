<?php

class VTEWidgets_Basic_Handler
{
    public $Module = false;

    public $Record = false;

    public $Config = [];

    public $moduleModel = false;

    public $dbParams = [];

    public function __construct($Module = false, $moduleModel = false, $Record = false, $widget = [])
    {
        $this->Module = $Module;
        $this->Record = $Record;
        $this->Config = $widget;
        $this->Config['tpl'] = 'Basic.tpl';
        $this->Data = $widget['data'];
        $this->moduleModel = $moduleModel;
    }

    public function getConfigTplName()
    {
        return 'BasicConfig';
    }

    public function getWidget()
    {
        return $this->Config;
    }
}
