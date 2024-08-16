<?php
/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class PDFMaker_CreatePDFFromTemplate_Action extends Vtiger_Action_Controller
{
    public function __construct()
    {
        $this->exposeMethod('CreatePDF');
        $this->exposeMethod('CreateZip');
    }

    public function checkPermission(Vtiger_Request $request) {}

    /**
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $mode = $request->getMode();
        if (!empty($mode) && $this->isMethodExposed($mode)) {
            $this->invokeExposedMethod($mode, $request);

            return;
        }

        $this->CreatePDF($request);
    }

    /**
     * @throws Exception
     */
    public function CreateZip(Vtiger_Request $request)
    {
        $selectedIds = $request->get('selected_ids');
        $zipFile = decideFilePath() . $request->get('source_module') . '_' . time() . '.zip';

        if (!class_exists('ZipArchive')) {
            throw new AppException('Required to install PHP extension ZipArchive');
        }

        $zip = new ZipArchive();

        if ($zip->open($zipFile, ZipArchive::CREATE) !== true) {
            throw new AppException('Cannot open <' . $zipFile . '>');
        }

        PDFMaker_Debugger_Model::GetInstance()->Init();

        foreach ($selectedIds as $selectedId) {
            $request->set('selected_ids', [$selectedId]);
            $checkGenerate = new PDFMaker_checkGenerate_Model();
            $checkGenerate->set('onlyname', true);
            $checkGenerate->set('export_file', true);
            $result = $checkGenerate->generate($request);

            $zip->addFile($result['file_path'], $result['filename']);
        }

        $zip->close();
        $this->forceDownloadZip($zipFile);
    }

    /**
     * @param string $zipFile
     */
    public function forceDownloadZip($zipFile)
    {
        $disk_file_size = filesize($zipFile);
        $fileContent = fread(fopen($zipFile, 'rb'), $disk_file_size);

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename=' . basename($zipFile) . ';');
        header('Content-Length: ' . $disk_file_size);

        echo $fileContent;
    }

    /**
     * @throws Exception
     */
    public function CreatePDF(Vtiger_Request $request)
    {
        PDFMaker_Debugger_Model::GetInstance()->Init();
        $checkGenerate = new PDFMaker_checkGenerate_Model();
        $checkGenerate->generate($request);
    }
}
