<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 25.05.2016
 * Time: 08:45.
 */

namespace Workflow;

class ConfigHandler
{
    protected $data = [];

    public function loadData($data)
    {
        $this->data = $data;
    }

    public function getValue($key)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        return null;
    }

    public function setValue($key, $value)
    {
        $this->data[$key] = $value;
    }
}
