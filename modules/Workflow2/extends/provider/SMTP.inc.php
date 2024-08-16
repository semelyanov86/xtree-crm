<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 20.09.14 23:15
 * You must not use this file without permission.
 */

namespace Workflow\Plugins\ConnectionProvider;

use Workflow\ConnectionProvider;

class SMTP extends ConnectionProvider
{
    protected $_title = 'Mail Delivery Method';

    protected $configFields = [
        /*'default' => array(
            'label' => 'Default method<br/>for all Workflow Mails',
            'type' => 'checkbox'
        )*/
    ];

    protected $js4Editor = '';

    private $_phpmailer;

    public static function getDefaultMailer()
    {
        if (!class_exists('Workflow_PHPMailer')) {
            require_once 'modules/Workflow2/phpmailer/class.phpmailer.php';
        }

        $_phpmailer = new \Workflow_PHPMailer(true);
        $_phpmailer->IsSMTP();
        $_phpmailer->CharSet = 'utf-8';
        $_phpmailer->Timeout = 60;

        setMailServerProperties($_phpmailer);

        $adb = \PearDatabase::getInstance();
        $query = 'select from_email_field from vtiger_systems where server_type=?';
        $params = ['email'];
        $result = $adb->pquery($query, $params);
        $from_email_field = $adb->query_result($result, 0, 'from_email_field');

        if (!empty($from_email_field)) {
            $_phpmailer->From = $from_email_field;
        }

        return $_phpmailer;
    }

    /**
     * @throws Exception
     */
    public function renderExtraBackend($data) {}

    public function getConfigFields()
    {
        switch ($this->getSubProvider()) {
            case 'mail':
                return array_merge($this->configFields, [
                    'sender_mail' => [
                        'label' => 'Force Sender Email',
                        'type' => 'text',
                    ],
                ]);
                break;
            case 'sendmail':
                return array_merge($this->configFields, [
                    'sender_mail' => [
                        'label' => 'Force Sender Email',
                        'type' => 'text',
                    ],
                ]);

                break;
            case 'SMTP':
                return array_merge($this->configFields, [
                    'server' => [
                        'label' => 'SMTP Server',
                        'type' => 'text',
                    ],
                    'smtpauth_username' => [
                        'label' => 'SMTP Auth Username',
                        'type' => 'text',
                    ],
                    'smtpauth_password' => [
                        'label' => 'SMTP Auth Password',
                        'type' => 'password',
                    ],
                    'sender_mail' => [
                        'label' => 'Force Sender Email',
                        'type' => 'text',
                    ],
                ]);
                break;
        }
    }

    public function getAvailableSubProvider()
    {
        $plugins =         $plugins = [
            'SMTP' => 'SMTP',
            'mail' => 'mail() PHP Method',
            'Sendmail' => 'Sendmail',
        ];

        return $plugins;
    }

    public function getPHPMailer()
    {
        if (!class_exists('Workflow_PHPMailer')) {
            require_once 'modules/Workflow2/phpmailer/class.phpmailer.php';
        }

        $this->_phpmailer = new \Workflow_PHPMailer(true);
        $this->_phpmailer->CharSet = 'utf-8';
        $this->_phpmailer->Timeout = 60;

        $senderMail = $this->get('sender_mail');
        if (!empty($senderMail)) {
            $this->_phpmailer->From = $senderMail;
        }

        switch ($this->getSubProvider()) {
            case 'mail':
                $this->_phpmailer->IsMail();
                break;
            case 'sendmail':
                $this->_phpmailer->IsSendmail();
                break;
            case 'SMTP':
                $this->_phpmailer->IsSMTP();

                $this->_phpmailer->Host = $this->get('server');
                $smtpauth_username = $this->get('smtpauth_username');

                if (!empty($smtpauth_username)) {
                    $this->_phpmailer->Username = $this->get('smtpauth_username');
                    $this->_phpmailer->Password = $this->get('smtpauth_password');
                    $this->_phpmailer->SMTPAuth = true;
                }

                $serverinfo = explode('://', $this->_phpmailer->Host);
                $smtpsecure = $serverinfo[0];

                if ($smtpsecure == 'tls') {
                    $this->_phpmailer->SMTPSecure = $smtpsecure;
                    $this->_phpmailer->Host = $serverinfo[1];
                }

                break;
        }

        return $this->_phpmailer;
    }

    public function loadSystemConfiguration()
    {
        $adb = \PearDatabase::getInstance();

        $result = $adb->pquery('select * from vtiger_systems where server_type=?', ['email']);
        $data = $adb->fetchByAssoc($result);
        $config = [
            'server' => $data['server'],
            'smtpauth_username' => $data['server_username'],
            'smtpauth_password' => $data['server_password'],
        ];

        $this->setConfiguration($config);
    }

    public function test()
    {
        try {
            $this->_phpmailer = $this->getPHPMailer();

            $return = $this->_phpmailer->SmtpConnect();

            if ($return == false) {
                throw new \Exception('Could not connect to SMTP Host');
            }
        } catch (\Exception $exp) {
            throw new \Exception($exp->getMessage());
        }

        return true;
    }
}

ConnectionProvider::register('smtp', '\Workflow\Plugins\ConnectionProvider\SMTP');
