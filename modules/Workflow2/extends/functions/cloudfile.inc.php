<?php

use CloudFile\Connection;
use Workflow2\Autoload;

function getFolderId($folder, $connectionID)
{
    Autoload::register('CloudFile', '~/modules/CloudFile/lib');

    $adapter = Connection::getAdapter($connectionID);

    $adapter->chdir($folder);

    return $adapter->getCurrentPathKey();
}
