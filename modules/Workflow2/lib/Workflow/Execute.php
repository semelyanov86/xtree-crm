<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 12.09.13
 * Time: 22:36.
 */

namespace Workflow;

class Execute
{
    protected $_environment = [];

    protected $_record = 0;

    /**
     * @var \Users
     */
    protected $_user = false;

    protected $_files = [];

    public function setUser($userObj)
    {
        if (is_int($userObj)) {
            $userObj = \CRMEntity::getInstance('Users');
            $userObj->id = $userObj;
            $userObj = $userObj->retrieve_entity_info($userObj, 'Users');
        }

        $this->_user = $userObj;
    }

    /**
     * @param array $environment    Environment Variables you want to set
     */
    public function setEnvironment($environment)
    {
        $this->_environment = $environment;
    }

    public function addFile($filename, $filepath, $filestoreid)
    {
        $this->_files[] = [
            'filename' => $filename,
            'filepath' => $filepath,
            'filestoreid' => $filestoreid,
        ];
    }

    /*
     * @param int $record        RecordID, which will use to execute the Record (It will be onle Workflows from the Module of this Record executed)
     */
    public function setRecord($record)
    {
        $this->_record = $record;
    }

    /**
     * @param string $trigger              Name of a trigger you want to execute
     */
    public function runByTrigger($trigger)
    {
        if ($this->_user === false) {
            return 'User not found (use: setUser(userid) )';
        }

        VTEntity::setUser($this->_user);

        if (!empty($this->_record)) {
            $context = VTEntity::getForId($this->_record);
        } else {
            $context = VTEntity::getDummy();
        }

        foreach ($this->_files as $file) {
            $context->addTempFile($file['filepath'], $file['filestoreid'], $file['filename']);
        }

        $wfManager = new Manager();
        $workflows = $wfManager->GetWorkflows($context->getModuleName(), $trigger);
        $context->loadEnvironment($this->_environment);

        foreach ($workflows as $wf) {
            if (!$context->isAvailable()) {
                break;
            }
            // $context->setEnvironment();

            $wf->setContext($context);

            $wf->start();
        }

        return count($workflows);
    }

    public function runById($workflow_id)
    {
        if ($this->_user === false) {
            return 'User not found (use: setUser(userid) )';
        }

        VTEntity::setUser($this->_user);

        if (!empty($this->_record)) {
            $context = VTEntity::getForId($this->_record);
        } else {
            $context = VTEntity::getDummy();
        }

        $sql = 'SELECT module_name FROM vtiger_wf_settings WHERE vtiger_wf_settings.id = ' . $workflow_id . ' AND active = 1';
        $result  = VtUtils::query($sql);
        if (VtUtils::num_rows($result) == 0) {
            return;
        }

        if (!empty($this->_environment)) {
            $context->loadEnvironment($this->_environment);
        }

        $obj = new Main($workflow_id, false, VTEntity::getUser());

        foreach ($this->_files as $file) {
            $context->addTempFile($file['filepath'], $file['filestoreid'], $file['filename'], $obj->getLastExecID());
        }

        $obj->setExecutionTrigger('WF2_MANUELL');
        $obj->isSubWorkflow(true);

        $obj->setContext($context);

        $obj->start();
    }
}
