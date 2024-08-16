<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 29.03.13
 * Time: 16:46.
 */

/**
 * These class is deprecated. Please use the full compatible Replacement Workflow_VTInventoryEntity instead.
 */
if (!class_exists('VTInventoryEntity')) {
    class VTInventoryEntity extends Workflow\VTInventoryEntity {}
}
