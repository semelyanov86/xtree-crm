<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 01.06.14 12:04
 * You must not use this file without permission.
 */

namespace Workflow\Plugins\InterfaceFiles;

use Workflow\InterfaceFiles;

class PDFMaker extends InterfaceFiles
{
    protected $title = 'PDFMaker Templates';

    protected $key = 'pdfmaker';

    public function __construct()
    {
        if (!$this->isModuleActive()) {
            return;
        }
        require_once 'modules/PDFMaker/PDFMaker.php';
    }

    public function isModuleActive()
    {
        return getTabid('PDFMaker') && vtlib_isModuleActive('PDFMaker');
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

        $Records = [$crmid];

        // $request = $_REQUEST;
        // $_REQUEST['search'] = true;
        // $_REQUEST['submode'] = true;

        \Vtiger_Loader::autoLoad('PDFMaker_PDFMaker_Model');
        $PDFMaker = new \PDFMaker_PDFMaker_Model();
        $name = $PDFMaker->GetPreparedMPDF($mpdf, $Records, [$id], $moduleName, $language);
        $name = $PDFMaker->generate_cool_uri($name);

        if ($name != '') {
            $name = $name . '.pdf';
        }

        $mpdf->Output($tmpFilename);

        vglobal('current_user', $oldUser);

        $filetype = 'application/pdf';

        // $_REQUEST = $request;

        return [
            'path' => $tmpFilename,
            'name' => $name,
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
         * @var PDFMaker_PDFMaker_Model $PDFMaker
         */
        \Vtiger_Loader::autoLoad('PDFMaker_PDFMaker_Model');
        $PDFMaker = new \PDFMaker_PDFMaker_Model();

        if (method_exists($PDFMaker, 'GetAvailableTemplates')) {
            $templates = $PDFMaker->GetAvailableTemplates($moduleName);
            foreach ($templates as $index => $value) {
                $return[$index] = 'PDFMaker - ' . $value['templatename'];
            }
        }

        return $return;
    }
}

InterfaceFiles::register('pdfmaker', '\Workflow\Plugins\InterfaceFiles\PDFMaker');
