<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 20.09.14 23:15
 * You must not use this file without permission.
 */

namespace Workflow\Plugins\ConnectionProvider;

use Ddeboer\Imap\Connection;
use Ddeboer\Imap\Server;
use Workflow\ConnectionProvider;
use Workflow\VtUtils;

class IMAP extends ConnectionProvider
{
    protected $_title = 'IMAP Server';

    protected $configFields = [
        /*'default' => array(
            'label' => 'Default method<br/>for all Workflow Mails',
            'type' => 'checkbox'
        )*/
    ];

    protected $js4Editor = '';

    private $_connection;

    /**
     * @throws Exception
     */
    public function renderExtraBackend($data) {}

    public function getConfigFields()
    {
        return array_merge($this->configFields, [
            'server' => [
                'label' => 'IMAP Server',
                'type' => 'text',
            ],
            'port' => [
                'label' => 'IMAP Port',
                'type' => 'text',
                'default' => 143,
            ],
            'ssl' => [
                'label' => 'SSL/TLS',
                'type' => 'select',
                'options' => [
                    'notls' => 'No encryption (not recommended)',
                    'ssl' => 'SSL',
                    'tls' => 'start-TLS (recommended)',
                ],
            ],
            'validcert' => [
                'label' => 'Validate SSL/TLS Cert',
                'type' => 'checkbox',
            ],
            'username' => [
                'label' => 'IMAP Auth Username',
                'type' => 'text',
            ],
            'password' => [
                'label' => 'IMAP Auth Password',
                'type' => 'password',
            ],
        ]);
    }

    /**
     * @return Connection|null
     */
    public function getImapConnection()
    {
        if ($this->_connection !== null) {
            return $this->_connection;
        }

        imap_timeout(IMAP_OPENTIMEOUT, 5);
        imap_timeout(IMAP_READTIMEOUT, 5);

        $path = VtUtils::getAdditionalPath('mailscanner');
        require_once $path . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

        if ($this->get('validcert') == '1') {
            $validateCert = 'validate-cert';
        } else {
            $validateCert = 'novalidate-cert';
        }

        $flags = '/imap/' . $this->get('ssl') . '/' . $validateCert;

        $server = new Server($this->get('server'), $this->get('port'), $flags);

        // $connection is instance of \Ddeboer\Imap\Connection
        $connection = $server->authenticate($this->get('username'), $this->get('password'));

        $this->_connection = $connection;

        return $connection;
    }

    public function test()
    {
        if (extension_loaded('imap') === false) {
            throw new \Exception('php-imap Extension is required');
        }

        if (version_compare(phpversion(), '5.4.0') < 0) {
            throw new \Exception('PHP Version 5.4 is required');
        }

        try {
            $connection = $this->getImapConnection();
            /*
            if($return == false) {
                throw new \Exception('Could not connect to SMTP Host');
            }*/
        } catch (\Exception $exp) {
            throw new \Exception($exp->getMessage());
        }

        return true;
    }

    public function getFolderObject($folder)
    {
        $connection = $this->getImapConnection();

        return $connection->getMailbox($folder);
    }

    public function getFolder()
    {
        $connection = $this->getImapConnection();

        $mailboxes = $connection->getMailboxes();

        $return = [];
        foreach ($mailboxes as $mailbox) {
            $return[] = [
                'name' => $mailbox->getName(),
                'messages' => $mailbox->count(),
            ];
        }

        return $return;
    }
}

ConnectionProvider::register('imap', '\Workflow\Plugins\ConnectionProvider\IMAP');
