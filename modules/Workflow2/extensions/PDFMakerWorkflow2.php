<?php

use Workflow\ExpressionParser;
use Workflow\Main;
use Workflow\VTEntity;

if (!function_exists('pdfmaker_recordlist')) {
    function pdfmaker_recordlist($environmentId)
    {
        require_once 'modules/Workflow2/autoload_wf.php';
        $html = '';

        if (class_exists('\Workflow\ExpressionParser') && isset(ExpressionParser::$INSTANCE)) {
            $context = Main::$INSTANCE->getContext();
            $env = $context->getEnvironment($environmentId);
            $html = $env['html'];
        }

        return $html;
    }
}
if (!function_exists('record_env')) {
    function record_env($crmid, $envKey)
    {
        require_once 'modules/Workflow2/autoload_wf.php';
        $context = VTEntity::getForId($crmid);

        return $context->getEnvironment($envKey);
    }
}
