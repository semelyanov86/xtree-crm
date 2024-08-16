<?php

use Workflow\Importer;

global $root_directory;
require_once $root_directory . '/modules/Workflow2/autoload_wf.php';

class Workflow2_ImportSetOptions_Action extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request) {}

    public function process(Vtiger_Request $request)
    {
        global $current_user;

        $ImportHash = $request->get('ImportHash');
        $options = $request->get('import');

        $objImporter = Importer::getInstance($ImportHash);

        if (empty($options['skipfirst'])) {
            $options['skipfirst'] = 0;
        }

        $objImporter->set('importParams', $options);
        $objImporter->refreshTotalRows();
    }

    public function validateRequest(Vtiger_Request $request)
    {
        $request->validateReadAccess();
    }
}
