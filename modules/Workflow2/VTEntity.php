<?php
/**
 * This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>.
 *
 * It belongs to the Workflow Designer and must not be distributed without complete extension
 */

/**
 * These class is deprecated. Please use the full compatible Replacement \Workflow\VTEntity instead.
 */
if (!class_exists('VTEntity')) {
    class VTEntity extends Workflow\VTEntity {}
}
