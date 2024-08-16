<?php

use Workflow\ComplexeCondition;
use Workflow\VtUtils;

global $root_directory;
require_once $root_directory . '/modules/Workflow2/autoload_wf.php';

class Workflow2_ComplexeCondition_View extends Vtiger_Index_View
{
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('ConditionPopup');
    }

    public function process(Vtiger_Request $request)
    {
        $mode = $request->getMode();
        if (!empty($mode)) {
            echo $this->invokeExposedMethod($mode, $request);

            return;
        }
    }

    public function ConditionPopup(Vtiger_Request $request)
    {
        $adb = PearDatabase::getInstance();

        $moduleName = $request->getModule();
        $qualifiedModuleName = $request->getModule(false);
        $viewer = $this->getViewer($request);

        $configuration = $request->get('configuration');

        if (!empty($configuration)) {
            $configuration = VtUtils::json_decode(base64_decode($request->get('configuration')));
        } else {
            $toModule = $request->get('toModule');
            $configuration = [
                'module' => $toModule,
                'condition' => [],
            ];
        }

        $preset = new ComplexeCondition('condition', [
            'fromModule' => $request->has('fromModule') ? $request->get('fromModule') : '',
            'toModule' => $configuration['module'],
            'enableHasChanged' => false,
            'container' => 'conditionalPopupContainer',
            'enableTemplateFields' => true,
            'references' => false,
            'variables' => false,
            'calculator' => $request->has('calculator') && $request->get('calculator') == true,
            //            'disableTemplateFields' => true,
            'disableConditionMode' => true,
        ]);

        $preset->setCondition($configuration['condition']);

        $preset->InitViewer(null, $viewer);

        $viewer->assign('ConditionScopeModule', $moduleName);

        $viewer->assign('toModule', $configuration['module']);
        $viewer->assign('title', getTranslatedString($request->get('title'), 'Settings:Workflow2'));

        $viewer->view('ConditionPopup.tpl', $qualifiedModuleName);
    }
}
