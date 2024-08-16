<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 09.04.14 13:24
 * You must not use this file without permission.
 */

namespace Workflow;

class VTEntityMap extends \ArrayIterator
{
    public function get($key)
    {
        $returns = [];

        foreach ($this as $value) {
            $returns[] = $value->get($key);
        }

        return $returns;
    }
}
