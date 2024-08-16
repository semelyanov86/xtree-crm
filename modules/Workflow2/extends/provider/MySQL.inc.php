<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 20.09.14 23:15
 * You must not use this file without permission.
 */

namespace Workflow\Plugins\ConnectionProvider;

use Workflow\ConnectionProvider;

class MySQL extends ConnectionProvider
{
    private static $Cache = [];

    protected $_title = 'MySQL Connection';

    protected $configFields = [
        'server' => [
            'label' => 'MySQL Server',
            'type' => 'text',
            'description' => 'Leave empty to use VtigerCRM Server',
        ],
        'port' => [
            'label' => 'MySQL Serverport',
            'type' => 'text',
            'description' => 'Leave empty to use VtigerCRM Port',
        ],
        'mysql_username' => [
            'label' => 'MySQL Login Username',
            'type' => 'text',
            'description' => 'Leave empty to use VtigerCRM Login',
        ],
        'mysql_password' => [
            'label' => 'MySQL Login Password',
            'type' => 'password',
        ],
        'test_button' => [
            'label' => 'Test settings',
            'type' => 'test_button',
        ],
    ];

    protected $js4Editor = '';

    /**
     * @throws Exception
     */
    public function renderExtraBackend($data) {}

    /**
     * @return \PDO
     */
    public function getMySQLConnection()
    {
        $id = $this->get('_id');
        if (!empty($id) && isset(self::$Cache[$id])) {
            return self::$Cache[$id];
        }

        $server = $this->get('server');
        $port = $this->get('port');
        $mysql_username = $this->get('mysql_username');
        $mysql_password = $this->get('mysql_password');

        global $dbconfig;
        if (empty($server)) {
            $server = $dbconfig['db_server'];
        }
        if (empty($port)) {
            $port = trim($dbconfig['db_port'], ':');
        }
        if (empty($mysql_username)) {
            $mysql_username = $dbconfig['db_username'];
            $mysql_password = $dbconfig['db_password'];
        }

        $db = new \PDO('mysql:host=' . $server . ';port=' . $port . ';charset=utf8', $mysql_username, $mysql_password);
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        if ($id == 'vtigerdb') {
            $db->query('use `' . $dbconfig['db_name'] . '`;');
        }

        if (!empty($id)) {
            self::$Cache[$id] = $db;
        }

        return $db;
    }

    public function getColumns($tablenames)
    {
        $condb = $this->getMySQLConnection();

        // get column names
        $query = $condb->prepare("DESCRIBE {$tablenames}");
        $query->execute();
        $table_names = $query->fetchAll(\PDO::FETCH_ASSOC);
        $columns = [];
        foreach ($table_names as $col) {
            $columns[$col['Field']] = $col;
        }

        return $columns;
    }

    public function test()
    {
        try {
            $connection = $this->getMySQLConnection();
        } catch (\Exception $exp) {
            throw new \Exception($exp->getMessage());
        }

        return true;
    }

    public function database($newDatabase)
    {
        $connection = $this->getMySQLConnection();
        $connection->exec('USE `' . $newDatabase . '`;');
    }
}

ConnectionProvider::register('mysql', '\Workflow\Plugins\ConnectionProvider\MySQL');
