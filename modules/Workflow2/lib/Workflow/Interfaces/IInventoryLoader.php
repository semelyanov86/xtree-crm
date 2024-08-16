<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 28.09.2016
 * Time: 19:05.
 */

namespace Workflow\Interfaces;

use Workflow\VTEntity;

interface IInventoryLoader
{
    public function getAvailableLoader();

    public function getItems($config, VTEntity $context);
}
