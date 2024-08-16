<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 20.09.14 23:15
 * You must not use this file without permission.
 */

namespace Workflow\Plugins\ConnectionProvider;

use it\thecsea\simple_caldav_client\CalDAVCalendar;
use it\thecsea\simple_caldav_client\CalDAVException;
use it\thecsea\simple_caldav_client\SimpleCalDAVClient;
use Workflow\ConnectionProvider;
use Workflow\VtUtils;

class CalDAV extends ConnectionProvider
{
    protected $_title = 'CalDAV Connection';

    protected $configFields = [
        /*'default' => array(
            'label' => 'Default method<br/>for all Workflow Mails',
            'type' => 'checkbox'
        )*/
    ];

    private $_connection;

    /**
     * @throws Exception
     */
    public function renderExtraBackend($data) {}

    public function getConfigFields()
    {
        return array_merge($this->configFields, [
            'server' => [
                'label' => 'CalDAV URL',
                'type' => 'text',
            ],
            'validcert' => [
                'label' => 'Validate SSL/TLS Cert',
                'type' => 'checkbox',
            ],
            'username' => [
                'label' => 'CalDAV Auth Username',
                'type' => 'text',
            ],
            'password' => [
                'label' => 'CalDAV Auth Password',
                'type' => 'password',
            ],
        ]);
    }

    /**
     * @return SimpleCalDAVClient
     * @throws CalDAVException
     */
    public function getCalDAVClient()
    {
        $path = VtUtils::getAdditionalPath('caldav');
        require_once $path . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

        $client = new SimpleCalDAVClient();

        $client->connect($this->get('server'), $this->get('username'), $this->get('password'));

        return $client;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function test()
    {
        if (extension_loaded('curl') === false) {
            throw new \Exception('php-curl Extension is required');
        }

        if (version_compare(phpversion(), '5.4.0') < 0) {
            throw new \Exception('PHP Version 5.4 is required');
        }

        try {
            $this->getCalDAVClient();
        } catch (\Exception $exp) {
            throw new \Exception($exp->getMessage());
        }

        return true;
    }

    /**
     * @return CalDAVCalendar[]
     * @throws CalDAVException
     */
    public function getAvailableCalendars()
    {
        $client = $this->getCalDAVClient();
        $calendars = $client->findCalendars();

        $return = [];
        foreach ($calendars as $cal) {
            $return[$cal->getCalendarID()] = $cal->getDisplayName();
        }

        return $return;
    }

    /**
     * @return CalDAVCalendar
     * @throws CalDAVException
     */
    public function getCalendar($calendarId)
    {
        $client = $this->getCalDAVClient();
        $calendars = $client->findCalendars();

        return $calendars[$calendarId];
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

ConnectionProvider::register('caldav', '\Workflow\Plugins\ConnectionProvider\CalDAV');
