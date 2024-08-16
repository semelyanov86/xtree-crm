<?php
/**
 * @copyright 2016-2017 Redoo Networks GmbH
 * @see https://redoo-networks.com/
 * This file is part of a vTigerCRM module, implemented by Redoo Networks GmbH and must not used without permission.
 */
abstract class Workflow2_EnvironmentHandlerAbstract_Model
{
    /**
     * @param array $environment
     * @parem int $crmid
     * @return array
     */
    abstract public function retrieve($environment, $crmid);
}
