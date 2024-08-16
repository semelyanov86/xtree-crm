<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 14.12.14 19:54
 * You must not use this file without permission.
 */

namespace Workflow;

interface UserQueueInterface
{
    /**
     * Should Return the HTML Form, which will be displayed to the User.
     */
    public static function generateUserQueueHTML($config, $context);

    /**
     * Return the Config, which could be used to build the frontend.
     */
    public function exportUserQueue($data, $context);
}
