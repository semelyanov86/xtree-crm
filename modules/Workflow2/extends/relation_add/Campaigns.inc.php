<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 20.09.14 23:15
 * You must not use this file without permission.
 */

namespace Workflow\Plugins\RelationAddExtend;

use Workflow\RelationAddExtend;

require_once 'DefaultRelation.php';

class Campaigns extends DefaultRelation
{
    protected $_relatedModule = 'Campaigns';

    protected $_title = 'Campaigns';
}

RelationAddExtend::register(str_replace('.inc.php', '', basename(__FILE__)), '\Workflow\Plugins\RelationAddExtend\Campaigns');
