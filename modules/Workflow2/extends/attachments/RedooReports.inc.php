<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 20.09.14 23:15
 * You must not use this file without permission.
 */

namespace Workflow\Plugins\Mailattachments;

use RedooReports\Report;
use Workflow\Attachment;
use Workflow\VTTemplate;
use Workflow\VtUtils;

class RedooReports extends Attachment
{
    public function isActive($moduleName)
    {
        return vtlib_isModuleActive('RedooReports');
    }

    public function getConfigurations($moduleName)
    {
        $sql = 'SELECT * FROM vtiger_redooreport WHERE mainmodule != "" AND title != "" ORDER BY title';
        $result = VtUtils::query($sql);
        $reports = [];

        $options = [];

        while ($row = VtUtils::fetchByAssoc($result)) {
            $type = ucfirst(strtolower($row['type']));
            $options[] = '<option value="' . $row['id'] . '" data-type="' . $row['type'] . '" data-title="' . htmlentities($row['title']) . '">[' . $type . '] ' . vtranslate($row['mainmodule'], $row['mainmodule']) . ' - ' . $row['title'] . '</option>';
        }

        $configuration = [
            'html' => '<a href="#" class="attachmentsConfigLink" data-type="redooreport">Attach Report from Flex Reports</a>
                <div class="attachmentsConfig" data-type="redooreport" id="redooreportAttachmentContainer" style="display:none;">
                    Add this Report<br/>
                    <select id="flexreportid" data-name="reportid" class="select2" style="width:100%;"><option value=""></option>' . implode('', $options) . '</select>
                    <br><br>Filetype to download: </em><br/>
                    <select id="flexreportfiletype" data-name="filetype" class="select2" style="width:100%;"><option value="pdf">PDF</option><option value="png">PNG</option><option value="xls">XLS</option></select>
                    <br><br>Filename of this file:<br/><em>(leave empty to use a generic one)</em><br/>
                    <div class="insertTextfield" style="display:inline;" data-name="filename" data-style="width:250px;" data-id="filename"></div>
                </div>',
            'script' => "
jQuery('#flexreportid').on('change', function(e) {
    if(jQuery('option:selected', e.currentTarget).data('type') == 'grid' || jQuery('option:selected', e.currentTarget).data('type') == 'sql') {
        jQuery('#flexreportfiletype option[value=\"xls\"]').prop('disabled', false);
        jQuery('#flexreportfiletype option[value=\"png\"]').prop('disabled', true);
    } else {
        jQuery('#flexreportfiletype option[value=\"xls\"]').prop('disabled', true);
        jQuery('#flexreportfiletype option[value=\"png\"]').prop('disabled', false);
    }
});
Attachments.registerCallback('redooreport', function() {
    jQuery('#redooreportAttachmentContainer').hide();

    var reportID = jQuery('select[data-name=\"reportid\"]').val();
    var ReportLabel = jQuery('select[data-name=\"reportid\"] option:selected').data('title');
    var filetype = jQuery('select[data-name=\"filetype\"]').val();
    var filename = jQuery('#filename').val();

    return [
        {
            'id'        : 's#redooreports#' + reportID + '#' + filetype,
            'label'     : filetype.toUpperCase() + ' ' + ReportLabel,
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
        require_once vglobal('root_directory') . '/modules/RedooReports/autoload_wf.php';

        @mkdir(vglobal('root_directory') . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . 'Workflow');
        $tmpfile = vglobal('root_directory') . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . 'Workflow' . DIRECTORY_SEPARATOR . '' . date('Y-m-d-H-i-s') . '.' . $config['filetype'];

        $reportObj = new Report($reportId);
        $type = $reportObj->getType();

        if ($type == 'grid' || $type == 'sql') {
            if ($config['filetype'] == 'pdf') {
                $tmpfile = $reportObj->getParser()->generatePDF();
            } elseif ($config['filetype'] == 'xls') {
                $reportObj->getParser()->generateXLS($tmpfile);
            }
        } else {
            $reportObj->getParser()->generateStatic($tmpfile, $config['filetype']);
        }

        $filename = $value[2]['filename'];
        $filename = VTTemplate::parse($filename, $context);

        if (empty($filename)) {
            $filename = 'report-' . $reportId;
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

Attachment::register('redooreports', '\Workflow\Plugins\Mailattachments\RedooReports');
