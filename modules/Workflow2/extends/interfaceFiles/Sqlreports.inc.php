<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 01.06.14 12:04
 * You must not use this file without permission.
 */

namespace Workflow\Plugins\InterfaceFiles;

use Workflow\InterfaceFiles;

class SQLReports extends InterfaceFiles
{
    protected $title = 'SQL Reports';

    protected $key = 'sqlreport';

    public function __construct()
    {
        if (!$this->isModuleActive()) {
            return;
        }
    }

    public function isModuleActive()
    {
        return getTabid('SQLReports') && vtlib_isModuleActive('SQLReports');
    }

    protected function _getFile($id, $moduleName, $crmid)
    {
        if (!$this->isModuleActive()) {
            return false;
        }
        $adb = \PearDatabase::getInstance();
        $parts = explode('#', $id);

        $tmpFilename = $this->_getTmpFilename();

        if ($parts[0] == 'pdf') {
        }

        $sql = 'SELECT * FROM vtiger_sqlreports WHERE sqlreportsid = ' . $parts[1];
        $result = $adb->query($sql);
        $row = $adb->fetchByAssoc($result);

        switch ($parts[0]) {
            case 'pdf':
                $file_name = 'Report_' . preg_replace('/[^A-Za-z0-9-_]/', '_', $row['reportname']) . '.pdf';
                $this->_createPDF($tmpFilename, $parts[1]);
                $type = 'application/pdf';
                break;
            case 'xls':
                $file_name = 'Report_' . preg_replace('/[^A-Za-z0-9-_]/', '_', $row['reportname']) . '.xls';
                $this->_createXLS($tmpFilename, $parts[1]);
                $type = 'application/x-msexcel';
                break;
            case 'csv':
                $file_name = 'Report_' . preg_replace('/[^A-Za-z0-9-_]/', '_', $row['reportname']) . '.csv';
                $this->_createCSV($tmpFilename, $parts[1]);
                $type = 'application/csv';
                break;
        }

        return [
            'path' => $tmpFilename,
            'type' => $type,
            'name' => $file_name,
        ];
    }

    protected function _getAvailableFiles($moduleName)
    {
        $return = [];
        if (!$this->isModuleActive()) {
            return $return;
        }

        $adb = \PearDatabase::getInstance();
        $sql = 'SELECT * FROM vtiger_sqlreports ORDER BY reportname';
        $result = $adb->query($sql);
        $reports = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $return['pdf#' . $row['sqlreportsid']] = 'SQLReport - ' . $row['reportname'] . ' PDF';
            $return['xls#' . $row['sqlreportsid']] = 'SQLReport - ' . $row['reportname'] . ' XLS';
            $return['csv#' . $row['sqlreportsid']] = 'SQLReport - ' . $row['reportname'] . ' CSV';
        }

        return $return;
    }

    private function _createPDF($tmpFile, $reportId)
    {
        $reportModel = \SQLReports_Record_Model::getInstanceById($reportId);
        $pdf = $reportModel->getReportPDF();

        $pdf->Output($tmpFile, 'F');
    }

    private function _createCSV($tmpFile, $reportId)
    {
        vimport('~~/modules/SQLReports/ReportRunSQL.php');
        $reportRun = \ReportRunSQL::getInstance($reportId);
        $reportRun->writeReportToCSVFile($tmpFile, false);
    }

    private function _createXLS($tmpFile, $reportId)
    {
        vimport('~~/modules/SQLReports/ReportRunSQL.php');
        /**
         * @var \ReportRunSQL $reportRun
         */
        $reportRun = \ReportRunSQL::getInstance($reportId);
        $reportRun->writeReportToExcelFile($tmpFile, false);
    }
}

InterfaceFiles::register('sqlreport', '\Workflow\Plugins\InterfaceFiles\SQLReports');
