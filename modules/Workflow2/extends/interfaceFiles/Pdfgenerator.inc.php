<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 01.06.14 12:04
 * You must not use this file without permission.
 */

namespace Workflow\Plugins\InterfaceFiles;

use Workflow\InterfaceFiles;

class PDFGenerator extends InterfaceFiles
{
    protected $title = 'PDFGenerator Templates';

    protected $key = 'pdfgenerator';

    public function __construct()
    {
        if (!$this->isModuleActive()) {
            return;
        }
        // require_once('modules/PDFGenerator/PDFGenerator.php');
    }

    public function isModuleActive()
    {
        return getTabid('PDFGenerator') && vtlib_isModuleActive('PDFGenerator');
    }

    protected function _getFile($id, $moduleName, $crmid)
    {
        $current_user = \Users_Record_Model::getCurrentUserModel();

        $useUser = \Users::getActiveAdminUser();
        $oldUser = vglobal('current_user');
        vglobal('current_user', $useUser);

        $tmpFilename = $this->_getTmpFilename();

        $mpdf = '';

        $language = $current_user->language;
        if (empty($language)) {
            $language = \Vtiger_Language_Handler::getLanguage();
        }

        $recordIds = [$crmid];

        \Vtiger_Loader::autoLoad('PDFGenerator_Template_Model');

        try {
            $templateObj = new \PDFGenerator_Template_Model($id, $language);
        } catch (\Exception $exp) {
            echo $exp->getMessage();
        }
        $templateObj->exportToFile($recordIds, $tmpFilename);

        // $request = $_REQUEST;
        // $_REQUEST['search'] = true;
        // $_REQUEST['submode'] = true;

        vglobal('current_user', $oldUser);

        $filetype = 'application/pdf';

        return [
            'path' => $tmpFilename,
            'name' => $templateObj->generateFilename(),
            'type'  => $filetype,
        ];
    }

    protected function _getAvailableFiles($moduleName)
    {
        $return = [];
        if (!$this->isModuleActive()) {
            return $return;
        }
        /**
         * @var \PDFGenerator_PDFGenerator_Model $PDFMaker
         */
        \Vtiger_Loader::autoLoad('PDFGenerator_Module_Model');
        $PDFMaker = new \PDFGenerator_Module_Model();

        if (method_exists($PDFMaker, 'GetAvailableTemplates')) {
            $templates = $PDFMaker->GetAvailableTemplates(0, $moduleName, 'list');

            foreach ($templates as $index => $value) {
                $return[$value['id']] = 'PDFGenerator - ' . $value['name'];
            }
        }

        return $return;
    }
}

InterfaceFiles::register('pdfgenerator', '\Workflow\Plugins\InterfaceFiles\PDFGenerator');
