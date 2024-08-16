<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 12.06.2016
 * Time: 20:05.
 */
if (!empty($_GET['repository_url']) && $_GET['module'] == 'Workflow2' && $_GET['view'] == 'AddRemoteRepository') {
    require_once '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config.inc.php';

    echo '<meta http-equiv="refresh" content="0; url=' . $site_URL . '/index.php?module=Workflow2&view=AddRemoteRepository&parent=Settings&repository_url=' . $_GET['repository_url'] . '"/>';
    exit;
}
