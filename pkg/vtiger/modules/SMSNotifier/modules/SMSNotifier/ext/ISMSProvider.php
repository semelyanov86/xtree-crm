<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 */

interface ISMSProvider
{
    public const MSG_STATUS_DISPATCHED = 'Dispatched';
    public const MSG_STATUS_PROCESSING = 'Processing';
    public const MSG_STATUS_DELIVERED = 'Delivered';
    public const MSG_STATUS_FAILED = 'Failed';
    public const MSG_STATUS_ERROR = 'ERR: ';
    public const SERVICE_SEND = 'SEND';
    public const SERVICE_QUERY = 'QUERY';
    public const SERVICE_PING = 'PING';
    public const SERVICE_AUTH = 'AUTH';

    /**
     * Get required parameters other than (username, password).
     */
    public function getRequiredParams();

    /**
     * Get service URL to use for a given type.
     *
     * @param string $type like SEND, PING, QUERY
     */
    public function getServiceURL($type = false);

    /**
     * Set authentication parameters.
     *
     * @param string $username
     * @param string $password
     */
    public function setAuthParameters($username, $password);

    /**
     * Set non-auth parameter.
     *
     * @param string $key
     * @param string $value
     */
    public function setParameter($key, $value);

    /**
     * Handle SMS Send operation.
     *
     * @param string $message
     * @param mixed $tonumbers One or Array of numbers
     */
    public function send($message, $tonumbers);

    /**
     * Query for status using messgae id.
     *
     * @param string $messageid
     */
    public function query($messageid);
}
