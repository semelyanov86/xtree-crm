<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 16.08.14 13:11
 * You must not use this file without permission.
 */

namespace Workflow;

class RequestValuesException extends \Exception
{
    private $_reqValuesKey;

    private $_fields;

    private $_task;

    /**
     * @var null|VTEntity
     */
    private $_context;

    public function __construct($key, $fields, $message, Task $task, VTEntity $context)
    {
        $this->code = 100;

        $this->_reqValuesKey = $key;
        $this->_fields = $fields;
        $this->_task = $task;
        $this->_context = $context;

        $this->message = $message;
    }

    /**
     * @return null|VTEntity
     */
    public function getContext()
    {
        return $this->_context;
    }

    /**
     * @return Task
     */
    public function getTask()
    {
        return $this->_task;
    }

    public function getKey()
    {
        return $this->_reqValuesKey;
    }

    public function getFields()
    {
        return $this->_fields;
    }
}
