<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 20.09.14 23:15
 * You must not use this file without permission.
 */

namespace Workflow\Plugins\Mailattachments;

use Workflow\Attachment;
use Workflow\VTTemplate;
use Workflow\VtUtils;

class Reports extends Attachment
{
    public function isActive($moduleName)
    {
        return vtlib_isModuleActive('Reports');
    }

    public function getConfigurations($moduleName)
    {
        $sql = 'SELECT * FROM vtiger_report ORDER BY reportname';
        $result = VtUtils::query($sql);
        $reports = [];

        $options = [];

        while ($row = VtUtils::fetchByAssoc($result)) {
            $options[] = '<option value="' . $row['reportid'] . '" data-title="' . htmlentities($row['reportname']) . '">' . $row['reportname'] . '</option>';
        }

        $configuration = [
            'html' => '<a href="#" class="attachmentsConfigLink" data-type="reports">Attach Report from Reports</a>
                <div class="attachmentsConfig" data-type="reports" id="redooreportAttachmentContainer" style="display:none;">
                    Add this Report<br/>
                    <select id="reportid" data-name="reportid" class="select2" style="width:100%;"><option value=""></option>' . implode('', $options) . '</select>
                    <br><br>Filetype to download: </em><br/>
                    <select id="flexreportfiletype" data-name="filetype" class="select2" style="width:100%;"><option value="csv">CSV</option><option value="xls">XLS</option></select>
                    <br><br>Filename of this file:<br/><em>(leave empty to use a generic one)</em><br/>
                    <div class="insertTextfield" style="display:inline;" data-name="filename" data-style="width:250px;" data-id="filename"></div>
                </div>',
            'script' => "
Attachments.registerCallback('reports', function() {
//    jQuery('#redooreportAttachmentContainer').hide();

    var reportID = jQuery('select[data-name=\"reportid\"]').val();
    var ReportLabel = jQuery('select[data-name=\"reportid\"] option:selected').data('title');
    var filetype = jQuery('select[data-name=\"filetype\"]').val();
    var filename = jQuery('#filename').val();

    return [
        {
            'id'        : 's#reports#' + reportID + '#' + filetype,
            'label'     : filetype.toUpperCase() + ' - ' + ReportLabel,
            'filename'  : '',
            'options'   : {
                'filename'  : filename,
                'reportid'  : reportID,
                'filetype'  : filetype
            }
        }
    ];
});", ];

        $return  = [$configuration];

        return $return;
    }

    /**
     * @param $context \Workflow\VTEntity
     * @return array|void
     */
    public function generateAttachments($key, $value, $context)
    {
        global $current_user;
        $adb = \PearDatabase::getInstance();

        $config = $value[2];

        $reportId = $config['reportid'];

        $reportModel = \Reports_Record_Model::getInstanceById($reportId);

        $reportRun = \ReportRun::getInstance($reportModel->getId());
        $advanceFilterSql = $reportModel->getAdvancedFilterSQL();
        $rootDirectory = vglobal('root_directory');
        $tmpDir = vglobal('tmp_dir');

        $filename = $value[2]['filename'];
        $filename = VTTemplate::parse($filename, $context);

        if ($config['filetype'] == 'xls') {
            $tmpfile = tempnam($rootDirectory . $tmpDir, 'xls');
            $reportRun->writeReportToExcelFile($tmpfile, $advanceFilterSql);

            if (empty($filename)) {
                $filename = decode_html($reportModel->getName());
            }
        }
        if ($config['filetype'] == 'csv') {
            $tmpfile = tempnam($rootDirectory . $tmpDir, 'xls');
            $reportRun->writeReportToCSVFile($tmpfile, $advanceFilterSql);

            if (empty($filename)) {
                $filename = decode_html($reportModel->getName());
            }
        }

        if (strpos($filename, '.' . $config['filetype']) === false) {
            $filename .= '.' . $config['filetype'];
        }

        $filename = preg_replace('/[^A-Za-z0-9-_.]/', '_', $filename);

        if ($this->_mode === self::MODE_NOT_ADD_NEW_ATTACHMENTS) {
            $this->addAttachmentRecord('PATH', $tmpfile, $filename);

            return;
        }

        $upload_file_path = decideFilePath();

        $next_id = $adb->getUniqueID('vtiger_crmentity');

        rename($tmpfile, $upload_file_path . $next_id . '_' . $filename);

        $filesize = filesize($upload_file_path . $next_id . '_' . $filename);
        $filetype = 'application/octet-stream';

        $sql1 = 'insert into vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) values(?, ?, ?, ?, ?, ?, ?)';
        $params1 = [$next_id, $current_user->id, $current_user->id, 'Documents Attachment', 'Documents Attachment', date('Y-m-d H:i:s'), date('Y-m-d H:i:s')];

        $adb->pquery($sql1, $params1);

        $sql2 = 'insert into vtiger_attachments(attachmentsid, name, description, type, path) values(?, ?, ?, ?, ?)';
        $params2 = [$next_id, $filename, '', $filetype, $upload_file_path];
        $adb->pquery($sql2, $params2);

        $this->addAttachmentRecord('ID', $next_id);
    }
}

Attachment::register('reports', '\Workflow\Plugins\Mailattachments\Reports');
