<?php

use Workflow\DbCheck;
use Workflow\Repository;

if (function_exists('opcache_reset')) {
    opcache_reset();
}

$className = '\\Workflow\\SWExtension\\ca62d58e352291a30c165c444877b1c92c5d28d5c';
class_alias('\\Workflow\\SWExtension\\GenKey', $className);
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 15.10.14 11:04
 * You must not use this file without permission.
 */
if (!file_exists(dirname(__FILE__) . '/lib/Workflow/DbCheck.php')) {
    var_dump('dbcheck not found'); // Error Only

    return;
}

require_once dirname(__FILE__) . '/lib/Workflow/DbCheck.php';

$adb = PearDatabase::getInstance();
$adb->query('SET SESSION sql_mode = "NO_ENGINE_SUBSTITUTION";');

// $adb->query('SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO"');

if (!DbCheck::existTable('vtiger_wf_frontendtrigger')) {
    echo 'Create table vtiger_wf_frontendtrigger ... ok<br>';
    $adb->query('CREATE TABLE IF NOT EXISTS `vtiger_wf_frontendtrigger` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `workflow_id` int(10) unsigned NOT NULL,
  `active` tinyint(3) unsigned NOT NULL,
  `pageload` tinyint(4) NOT NULL,
  `condition` text NOT NULL,
  `conditiontext` text NOT NULL,
  `fields` text NOT NULL,
  `sort` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;');
}

if (!DbCheck::existTable('vtiger_wf_repository_core')) {
    echo 'Create table vtiger_wf_repository_core ... ok<br>';
    $adb->query('CREATE TABLE IF NOT EXISTS `vtiger_wf_repository_core` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `type` varchar(32) NOT NULL,
      `version` int(11) NOT NULL,
      PRIMARY KEY (`id`),
      KEY `type` (`type`)
    ) ENGINE=InnoDB;');
}

if (!DbCheck::existTable('vtiger_wf_folder')) {
    echo 'Create table vtiger_wf_folder ... ok<br>';
    $adb->query('CREATE TABLE IF NOT EXISTS `vtiger_wf_folder` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL,
  `color` varchar(9) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `title` (`title`)
) ENGINE=InnoDB;');
}

if (!DbCheck::existTable('vtiger_wf_types')) {
    echo 'Create table vtiger_wf_types ... ok<br>';
    $adb->query("CREATE TABLE IF NOT EXISTS `vtiger_wf_types` (
      `id` mediumint(8) unsigned NOT NULL,
      `type` varchar(32) NOT NULL,
      `handlerclass` varchar(32) NOT NULL,
      `file` varchar(256) NOT NULL,
      `module` varchar(32) NOT NULL DEFAULT 'Workflow2',
      `output` varchar(255) NOT NULL,
      `persons` varchar(255) NOT NULL,
      `text` varchar(255) NOT NULL,
      `input` tinyint(4) NOT NULL DEFAULT '1',
      `styleclass` varchar(64) NOT NULL,
      `background` varchar(32) NOT NULL,
      `category` varchar(16) NOT NULL,
      `singleModule` varchar(255) NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `type` (`type`)
    ) ENGINE=InnoDB;");
}
if (!DbCheck::existTable('vtiger_wf_frontendmanager')) {
    echo 'Create table vtiger_wf_frontendmanager ... ok<br>';
    $adb->query('CREATE TABLE IF NOT EXISTS `vtiger_wf_frontendmanager` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `workflow_id` int(11) NOT NULL,
      `position` varchar(12) NOT NULL,
      `color` varchar(12) NOT NULL,
      `label` varchar(48) NOT NULL,
      `order` tinyint(4) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB ;');
}
if (!DbCheck::existTable('vtiger_wf_frontend_config')) {
    echo 'Create table vtiger_wf_frontend_config ... ok<br>';
    $adb->query('CREATE TABLE IF NOT EXISTS `vtiger_wf_frontend_config` (
      `module` varchar(32) NOT NULL,
      `hide_listview` tinyint(4) NOT NULL,
      `show_labels` tinyint(4) NOT NULL,
      PRIMARY KEY (`module`)
    ) ENGINE=InnoDB;');
}

if (!DbCheck::existTable('vtiger_wf_userqueue')) {
    echo 'Create table vtiger_wf_userqueue ... ok<br>';
    $adb->query("CREATE TABLE IF NOT EXISTS `vtiger_wf_userqueue` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `parentKey` varchar(48) NOT NULL,
      `type` enum('requestValue') NOT NULL,
      `subject` varchar(64) NOT NULL,
      `queue_id` int(11) NOT NULL,
      `settings` text NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB;");
}

if (!DbCheck::existTable('vtiger_wf_trigger')) {
    echo 'Create table vtiger_wf_trigger ... ok<br>';
    $adb->query('CREATE TABLE IF NOT EXISTS `vtiger_wf_trigger` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `key` varchar(24) NOT NULL,
      `label` varchar(32) NOT NULL,
      `module` varchar(24) NOT NULL,
      `custom` tinyint(4) NOT NULL,
      `deleted` tinyint(4) NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `key` (`key`)
    ) ENGINE=InnoDB;');

    // Default Trigger
    $adb->query("INSERT INTO `vtiger_wf_trigger` (`id`, `key`, `label`, `module`) VALUES
    (1, 'WF2_EVERY_SAVE', 'LBL_START_EVERY', 'Workflow2'),
    (2, 'WF2_CREATION', 'LBL_START_CREATION', 'Workflow2'),
    (3, 'WF2_MANUELL', 'LBL_START_MANUELL', 'Workflow2'),
    (4, 'WF2_MAILSEND', 'LBL_START_MAIL_SEND', 'Workflow2'),
    (5, 'WF2_MODCOMMENT', 'LBL_START_CREATE_COMMENT', 'Workflow2');");
}
if (!DbCheck::existTable('vtiger_wf_auth')) {
    echo 'Create table vtiger_wf_auth ... ok<br>';
    $adb->query('CREATE TABLE `vtiger_wf_auth` (
    `workflow_id` INT UNSIGNED NOT NULL ,
    `key_id`  VARCHAR( 10 ) NOT NULL ,
    `auth_value` TINYINT NOT NULL ,
    `auth_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    UNIQUE (
    `workflow_id` ,
    `key_id`
    )
    ) ENGINE = InnoDB;');
}
if (!DbCheck::existTable('vtiger_wf_options')) {
    echo 'Create table vtiger_wf_options ... ok<br>';
    $adb->query('CREATE TABLE IF NOT EXISTS `vtiger_wf_options` (
      `workflow_id` int(10) unsigned NOT NULL,
      `key` varchar(32) NOT NULL,
      `value` text NOT NULL,
      `modify` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`workflow_id`,`key`)
    ) ENGINE = InnoDB;');
}

$adb->query('CREATE TABLE IF NOT EXISTS `vtiger_wf_types_seq` (
`id` INT UNSIGNED NOT NULL
) ENGINE = InnoDB;', false);

// echo "Reset table vtiger_wf_types_seq ... ok<br>";
$adb->query('TRUNCATE TABLE `vtiger_wf_types_seq`;', false);

$adb->query('INSERT INTO  `vtiger_wf_types_seq` (
    `id`
)
SELECT IFNULL(MAX(id),1) FROM vtiger_wf_types', false);

if (!DbCheck::existTable('vtiger_wf_log')) {
    echo 'Create table vtiger_wf_log ... ok<br>';
    $adb->query('CREATE TABLE IF NOT EXISTS `vtiger_wf_log` (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `workflow_id` smallint(5) unsigned NOT NULL,
      `execID` varchar(32) NOT NULL,
      `blockID` mediumint(8) unsigned NOT NULL,
      `lastBlockID` mediumint(8) unsigned NOT NULL,
      `lastBlockOutput` varchar(16) NOT NULL,
      `crmid` int(10) unsigned NOT NULL,
      `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      `durationms` int(11) unsigned NOT NULL,
      `data` BLOB NOT NULL,
      PRIMARY KEY (`id`),
      KEY `crmid` (`crmid`,`workflow_id`,`blockID`)
    ) ENGINE=InnoDB;');
}
if (!DbCheck::existTable('vtiger_wf_errorlog')) {
    echo 'Create table vtiger_wf_errorlog ... ok<br>';
    $adb->query('CREATE TABLE IF NOT EXISTS `vtiger_wf_errorlog` (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `workflow_id` int(10) unsigned NOT NULL,
      `block_id` int(10) unsigned NOT NULL,
      `text` varchar(255) NOT NULL,
      `datum_eintrag` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `workflow_id` (`workflow_id`)
    ) ENGINE=InnoDB;');
}
if (!DbCheck::existTable('vtiger_wf_http_logs')) {
    echo 'Create table vtiger_wf_http_logs ... ok<br>';
    $adb->query('CREATE TABLE IF NOT EXISTS `vtiger_wf_http_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(45) NOT NULL,
  `log` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;');
}
if (!DbCheck::existTable('vtiger_wf_scheduler')) {
    echo 'Create table vtiger_wf_scheduler ... ok<br>';
    $adb->query('CREATE TABLE IF NOT EXISTS `vtiger_wf_scheduler` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `active` TINYINT UNSIGNED NOT NULL,
          `hour` varchar(20) NOT NULL,
          `minute` varchar(20) NOT NULL,
          `dom` varchar(20) NOT NULL,
          `month` varchar(20) NOT NULL,
          `dow` varchar(20) NOT NULL,
          `year` varchar(20) NOT NULL,
          `next_execution` datetime NOT NULL,
          `workflow_id` int(11) NOT NULL,
          PRIMARY KEY (`id`),
          KEY `next_execution` (`next_execution`)
    ) ENGINE = INNODB');
}
if (!DbCheck::existTable('vtiger_wf_mailscanner')) {
    echo 'Create table vtiger_wf_mailscanner ... ok<br>';
    $adb->query('CREATE TABLE `vtiger_wf_mailscanner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `available_folder` text NOT NULL,
  `condition` text NOT NULL,
  `environment` text NOT NULL,
  `config` text NOT NULL,
  `workflow_id` int(10) UNSIGNED NOT NULL,
  `active` tinyint(4) NOT NULL,
  `last_check` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `provider_id` (`provider_id`)
) ENGINE=InnoDB;');
}

if (!DbCheck::existTable('vtiger_wf_mailscanner_done')) {
    echo 'Create table vtiger_wf_mailscanner_done ... ok<br>';
    $adb->query('CREATE TABLE `vtiger_wf_mailscanner_done` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `mailscanner_id` int(10) UNSIGNED NOT NULL,
  `messageid` varchar(255) NOT NULL,
  `done` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mailscanner_id` (`mailscanner_id`,`messageid`) USING BTREE
) ENGINE=InnoDB;');
}

if (!DbCheck::existTable('vtiger_wf_mailscanner_folder')) {
    echo 'Create table vtiger_wf_mailscanner_folder ... ok<br>';
    $adb->query('CREATE TABLE `vtiger_wf_mailscanner_folder` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `mailscanner_id` int(10) UNSIGNED NOT NULL,
  `folder` varchar(255) NOT NULL,
  `lastscan` datetime NOT NULL,
  `dirty` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mailscanner_id` (`mailscanner_id`)
) ENGINE=InnoDB;');
}
if (!DbCheck::existTable('vtiger_wf_messages')) {
    echo 'Create table vtiger_wf_messages ... ok<br>';
    $adb->query('CREATE TABLE IF NOT EXISTS `vtiger_wf_messages` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `crmid` int(10) unsigned NOT NULL,
      `type` varchar(24) NOT NULL,
      `position` varchar(12) NOT NULL,
      `subject` varchar(64) NOT NULL,
      `message` text NOT NULL,
      `show_once` tinyint(1) NOT NULL,
      `show_until` datetime NOT NULL,
      `created` datetime NOT NULL,
      `user_id` INT(11) NOT NULL,
      PRIMARY KEY (`id`),
      KEY `crmid` (`crmid`)
    ) ENGINE=InnoDB;');
}
if (!DbCheck::existTable('vtiger_wf_frontendtype')) {
    echo 'Create table vtiger_wf_frontendtype ... ok<br>';
    $adb->query('CREATE TABLE `vtiger_wf_frontendtype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `jsrender` tinyint(4) NOT NULL,
  `key` varchar(20) NOT NULL,
  `title` varchar(48) NOT NULL,
  `module` varchar(48) NOT NULL,
  `options` text NOT NULL,
  `handlerpath` varchar(255) NOT NULL,
  `handlerclass` varchar(80) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;');
}

if (!DbCheck::existTable('vtiger_wf_confirmation')) {
    echo 'Create table vtiger_wf_confirmation ... ok<br>';
    $adb->query("CREATE TABLE IF NOT EXISTS `vtiger_wf_confirmation` (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `execID` varchar(32) NOT NULL,
      `visible` tinyint(1) NOT NULL DEFAULT '1',
      `crmid` int(10) unsigned NOT NULL,
      `module` varchar(64) NOT NULL,
      `workflow_id` int(10) unsigned NOT NULL,
      `blockID` int(10) unsigned NOT NULL,
      `backgroundcolor` varchar(24) NOT NULL,
      `infomessage` varchar(255) NOT NULL,
      `result` varchar(10) NOT NULL,
      `result_user_id` int(11) NOT NULL,
      `from_user_id` int(10) unsigned NOT NULL,
      `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `timeout` datetime NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB;");
}

if (!DbCheck::existTable('vtiger_wf_confirmation_user')) {
    echo 'Create table vtiger_wf_confirmation_user ... ok<br>';
    $adb->query('CREATE TABLE IF NOT EXISTS `vtiger_wf_confirmation_user` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `confirmation_id` int(11) NOT NULL,
      `user_id` int(11) NOT NULL,
      PRIMARY KEY (`id`),
      KEY `user_id` (`user_id`)
    ) ENGINE=InnoDB;');
}

if (!DbCheck::existTable('vtiger_wf_entityddata')) {
    echo 'Create table vtiger_wf_entityddata ... ok<br>';
    $adb->query("CREATE TABLE IF NOT EXISTS `vtiger_wf_entityddata` (
          `dataid` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `crmid` int(10) unsigned NOT NULL,
          `key` varchar(64) NOT NULL,
          `value` text NOT NULL,
          `assigned_to` int(10) unsigned NOT NULL,
          `mode` enum('simple','multi') NOT NULL,
          `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`dataid`),
          KEY `crmid` (`crmid`,`key`)
    ) ENGINE=InnoDB");
}

$adb->query('ALTER TABLE `vtiger_wf_entityddata` CHANGE  `dataid`  `dataid` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT;');

if (!DbCheck::existTable('vtiger_wf_config')) {
    echo 'Create table vtiger_wf_config ... ok<br>';
    $adb->query("CREATE TABLE IF NOT EXISTS `vtiger_wf_config` (
      `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
      `version` float NOT NULL,
      `last_check` datetime NOT NULL,
      `available_update` VARCHAR( 10 ) NOT NULL,
      `license` varchar(64) NOT NULL DEFAULT 'demo',
      `license_for` varchar(64) NOT NULL,
      `last_hash` varchar(64) NOT NULL DEFAULT 'demo',
      `config` blob NOT NULL,
      `modify` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB;");
}
if (!DbCheck::existTable('vtiger_wf_logtbl')) {
    echo 'Create table vtiger_wf_logtbl ... ok<br>';
    $adb->query('CREATE TABLE  `vtiger_wf_logtbl` (
    `workflow` INT UNSIGNED NOT NULL ,
    `blockid` INT UNSIGNED NOT NULL ,
    `crmid` VARCHAR(16) NOT NULL ,
    `log` VARCHAR(32) NOT NULL ,
    `date` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE = INNODB;');
}

if (!DbCheck::existTable('vtiger_wf_http_limits')) {
    echo 'Create table vtiger_wf_http_limits ... ok<br>';
    $adb->query('CREATE TABLE IF NOT EXISTS `vtiger_wf_http_limits` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(256) NOT NULL,
      `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE = INNODB;');
}
if (!DbCheck::existTable('vtiger_wf_http_limits_ips')) {
    echo 'Create table vtiger_wf_http_limits_ips ... ok<br>';
    $adb->query('CREATE TABLE IF NOT EXISTS `vtiger_wf_http_limits_ips` (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `limit_id` int(10) unsigned NOT NULL,
      `ip` varchar(64) NOT NULL,
      PRIMARY KEY (`id`),
      KEY `ip` (`ip`)
    ) ENGINE = INNODB;');
}
if (!DbCheck::existTable('vtiger_wf_http_limits_value')) {
    echo 'Create table vtiger_wf_http_limits_value ... ok<br>';
    $adb->query("CREATE TABLE IF NOT EXISTS `vtiger_wf_http_limits_value` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `limit_id` int(10) unsigned NOT NULL,
      `mode` enum('all','trigger','id') NOT NULL,
      `value` varchar(64) NOT NULL,
      PRIMARY KEY (`id`),
      KEY `mode` (`mode`,`value`)
    ) ENGINE = INNODB;");
}
if (!DbCheck::existTable('vtiger_wf_objects')) {
    echo 'Create table vtiger_wf_objects ... ok<br>';
    $adb->query('CREATE TABLE IF NOT EXISTS `vtiger_wf_objects` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `x` int(11) NOT NULL,
      `y` int(11) NOT NULL,
      `type` VARCHAR( 12 ) NOT NULL,
      `content` text NOT NULL,
      `workflow_id` int(11) NOT NULL,
      PRIMARY KEY (`id`),
      KEY `workflow_id` (`workflow_id`)
    ) ENGINE=INNODB ;');
}
if (!DbCheck::existTable('vtiger_wf_provider')) {
    echo 'Create table vtiger_wf_provider ... ok<br>';
    $adb->query('CREATE TABLE IF NOT EXISTS `vtiger_wf_provider` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(48) NOT NULL,
  `title` varchar(64) NOT NULL,
  `settings` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=INNODB;');
}

// Repository check
if (!DbCheck::existTable('vtiger_wf_repository')) {
    echo 'Create table vtiger_wf_repository ... ok<br>';
    $adb->query('CREATE TABLE IF NOT EXISTS `vtiger_wf_repository` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `title` varchar(128) NOT NULL,
      `url` varchar(256) NOT NULL,
      `licenseCode` varchar(40) NOT NULL,
      `last_update` datetime NOT NULL,
      `messages` text NOT NULL,
      `available_status` varchar(64) NOT NULL,
      `status` varchar(12) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB;');

    $initRepository = true;
}

if (!DbCheck::existTable('vtiger_wf_repository_types')) {
    echo 'Create table vtiger_wf_repository_types ... ok<br>';
    $adb->query('CREATE TABLE IF NOT EXISTS `vtiger_wf_repository_types` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `repos_id` int(11) NOT NULL,
          `min_version` double NOT NULL,
          `name` varchar(64) NOT NULL,
          `version` int(10) unsigned NOT NULL,
          `last_update` datetime NOT NULL,
          `url` varchar(255) NOT NULL,
          `checksum` text NOT NULL,
          `mode` varchar(16) NOT NULL,
          `autoinstall` tinyint(4) NOT NULL,
          `status` varchar(12) NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `repos_id` (`repos_id`,`name`,`autoinstall`)
    ) ENGINE=InnoDB');
} else {
    $adb->query('ALTER TABLE `vtiger_wf_repository_types` DROP INDEX  `repos_id` , ADD UNIQUE  `repos_id` (  `repos_id` ,  `name`, `autoinstall` )');
}

if (!DbCheck::existTable('vtiger_wf_formulas')) {
    echo 'Create table vtiger_wf_formulas ... ok<br>';
    $adb->query('CREATE TABLE IF NOT EXISTS `vtiger_wf_formulas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `formula` text NOT NULL,
  `name` VARCHAR( 48 ),
  `variables` text NOT NULL,
  `modified` datetime NOT NULL,
  `modifiedby` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;');
}

if (!DbCheck::existTable('vtiger_wf_oauth')) {
    echo 'Create table vtiger_wf_oauth ... ok<br>';
    $adb->query('CREATE TABLE IF NOT EXISTS `vtiger_wf_oauth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL,
  `name` varchar(60) NOT NULL,
  `hash` varchar(40) NOT NULL,
  `done` tinyint(4) NOT NULL,
  `handler` varchar(128) NOT NULL,
  `data` text NOT NULL,
  `refresh` text NOT NULL,
  `created` datetime NOT NULL,
  `expire` date NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=InnoDB;');
}
if (!DbCheck::existTable('vtiger_wf_filestore')) {
    echo 'Create table vtiger_wf_filestore ... ok<br>';
    $adb->query('CREATE TABLE `vtiger_wf_filestore` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `execid` varchar(32) NOT NULL,
      `filestoreid` varchar(48) NOT NULL,
      `recordid` int(10) unsigned NOT NULL,
      `filename` varchar(255) NOT NULL,
      `orig_filename` varchar(255) NOT NULL,
      PRIMARY KEY (`id`),
      KEY `execid` (`execid`)
    ) ENGINE=InnoDB;');

    $sql = 'SELECT execId, environment, crmid FROM vtiger_wf_queue WHERE environment LIKE "%_tmpfiles%"';
    $result = $adb->query($sql);

    $sqlInsert = 'INSERT INTO vtiger_wf_filestore SET execid = ?, filestoreid = ?, recordid = ?, filename = ?, orig_filename = ?';

    while ($row = $adb->fetchByAssoc($result)) {
        $environment = unserialize(html_entity_decode($row['environment']));

        foreach ($environment['_tmpfiles'] as $filestoreId => $filestoreData) {
            $adb->pquery($sqlInsert, [$row['execid'], $filestoreId, $row['crmid'], basename($filestoreData['path']), html_entity_decode($filestoreData['name'])]);
        }
    }
}
DbCheck::clearTableCache();

DbCheck::lowercaseColumn('vtiger_wf_types', 'styleClass');
DbCheck::lowercaseColumn('vtiger_wf_types', 'singleModule');
DbCheck::lowercaseColumn('vtiger_wf_types', 'helpUrl');
DbCheck::lowercaseColumn('vtiger_wf_types', 'handlerClass');

$sql = 'SELECT * FROM vtiger_wf_config';
$result = $adb->query($sql);
if ($adb->num_rows($result) == 0) {
    $adb->query('INSERT INTO `vtiger_wf_config` (`id`, `version`, `license`, `last_hash`, `config`, `available_update`, `license_for`, `last_check`) VALUES (1, ' . Workflow2::VERSION . ", 'free', 'free', '', '', '', '0000-00-00');");
}
if ($adb->num_rows($result) > 1) {
    $adb->query('DELETE FROM `vtiger_wf_config` ORDER BY license LIMIT ' . ($adb->num_rows($result) - 1));
}

DbCheck::checkColumn('vtiger_wf_messages', 'target', "ENUM(  'record',  'user' )", 'record');
DbCheck::checkColumn('vtiger_wf_userqueue', 'type', "ENUM(  'requestValue',  'requestForm' )");
// \Workflow\DbCheck::checkColumn("vtiger_wf_messages", "user_id", "INT(11)");

$sql = 'DELETE FROM vtiger_wf_repository_types WHERE name = "pdfmaker_savedocument"';
$adb->query($sql);

DbCheck::checkColumn('vtiger_wf_provider', 'default', 'TINYINT', '0');

DbCheck::checkColumn('vtiger_wf_repository', 'messages', 'text');
DbCheck::checkColumn('vtiger_wf_repository', 'support_url', 'VARCHAR( 255 )');
DbCheck::checkColumn('vtiger_wf_repository', 'autoupdate', 'TINYINT', '1');
DbCheck::checkColumn('vtiger_wf_repository', 'available_status', 'VARCHAR( 128 )', false, true);
DbCheck::checkColumn('vtiger_wf_repository', 'status', 'VARCHAR( 12 )', 'stable');
DbCheck::checkColumn('vtiger_wf_repository', 'version', 'INT UNSIGNED', '1');
DbCheck::checkColumn('vtiger_wf_repository', 'deleted', 'TINYINT', '0');

DbCheck::checkColumn('vtiger_wf_repository_types', 'autoinstall', 'tinyint(4)');
DbCheck::checkColumn('vtiger_wf_repository_types', 'status', 'VARCHAR( 12 )');
DbCheck::checkColumn('vtiger_wf_repository_types', 'module_required', 'VARCHAR( 64 )');

DbCheck::checkColumn('vtiger_wf_confirmation', 'result_timestamp', 'datetime');
DbCheck::checkColumn('vtiger_wf_confirmation', 'backgroundcolor', 'varchar(24)');
DbCheck::checkColumn('vtiger_wf_confirmation', 'infomessage', 'varchar(255)');
DbCheck::checkColumn('vtiger_wf_confirmation', 'rundirect', 'tinyint(4)');

DbCheck::checkColumn('vtiger_wf_queue', 'environment', 'TEXT');
DbCheck::checkColumn('vtiger_wf_queue', 'delta', 'TEXT');
DbCheck::checkColumn('vtiger_wf_queue', 'hidden', 'TINYINT');

DbCheck::checkColumn('vtiger_wf_http_limits', 'url', 'VARCHAR( 128 )');

DbCheck::checkColumn('vtiger_wf_types', 'singleModule', 'VARCHAR( 255 )');
DbCheck::checkColumn('vtiger_wf_types', 'helpurl', 'VARCHAR( 255 )');

DbCheck::checkColumn('vtiger_wf_types', 'sort', 'int(10) UNSIGNED');
DbCheck::checkColumn('vtiger_wf_types', 'version', 'int(10) UNSIGNED');
DbCheck::checkColumn('vtiger_wf_types', 'repo_id', 'int(10) UNSIGNED');

DbCheck::checkColumn('vtiger_wf_oauth', 'provider', 'VARCHAR(48)');
DbCheck::checkColumn('vtiger_wf_oauth', 'expire', 'DATETIME', false, true);

DbCheck::checkColumn('vtiger_wf_frontendmanager', 'position', 'VARCHAR(64)', false, true);

DbCheck::checkColumn('vtiger_wf_settings', 'folder', 'VARCHAR(64)', false, false, static function () {
    $adb = PearDatabase::getInstance();

    $sql = 'UPDATE vtiger_wf_settings SET folder = module_name WHERE folder = ""';
    $adb->query($sql);
});
DbCheck::checkColumn('vtiger_wf_settings', 'sort', 'TINYINT(1)', false, false, static function () {
    $adb = PearDatabase::getInstance();

    $sql = 'SELECT id FROM vtiger_wf_settings ORDER BY module_name, active DESC, title';
    $result = $adb->pquery($sql);

    $counter = 1;

    while ($row = $adb->fetchByAssoc($result)) {
        $sql = 'UPDATE vtiger_wf_settings SET sort = ' . $counter . ' WHERE id = ' . $row['id'];
        $adb->query($sql);
        ++$counter;
    }
});

DbCheck::checkColumn('vtiger_wf_settings', 'nologging', 'tinyint(1)');
DbCheck::checkColumn('vtiger_wf_settings', 'invisible', 'tinyint(1)');
DbCheck::checkColumn('vtiger_wf_settings', 'last_modify_by', 'int(10) UNSIGNED');
DbCheck::checkColumn('vtiger_wf_settings', 'withoutrecord', 'tinyint(1)');
DbCheck::checkColumn('vtiger_wf_settings', 'once_per_record', 'tinyint(1)');
DbCheck::checkColumn('vtiger_wf_settings', 'revision', 'int(10) UNSIGNED', 1);    // unused
DbCheck::checkColumn('vtiger_wf_settings', 'authmanagement', 'tinyint(3) UNSIGNED', 0); // V 1.8
DbCheck::checkColumn('vtiger_wf_settings', 'startfields', 'TEXT'); // V 1.81
DbCheck::checkColumn('vtiger_wf_settings', 'options', 'TEXT'); // V 1.845
DbCheck::checkColumn('vtiger_wf_settings', 'trigger', 'VARCHAR( 24 )');
DbCheck::checkColumn('vtiger_wf_settings', 'view_condition', 'TEXT');
DbCheck::checkColumn('vtiger_wf_settings', 'collection_process', 'tinyint(1)');
DbCheck::checkColumn('vtiger_wf_settings', 'view_condition_lv', 'tinyint(1)');

DbCheck::checkColumn('vtiger_wf_settings', 'decimalconvert', 'tinyint(1)');

DbCheck::checkColumn('vtiger_wf_config', 'update_channel', 'VARCHAR(10)', 'stable');
DbCheck::checkColumn('vtiger_wf_config', 'error_handler', 'VARCHAR(10)', 'email');
DbCheck::checkColumn('vtiger_wf_config', 'error_handler_value', 'VARCHAR(256)', '');

DbCheck::checkColumn('vtiger_wf_config', 'minify_logs_after', 'int(11)', '30');
DbCheck::checkColumn('vtiger_wf_config', 'remove_logs_after', 'int(11)', '180');

DbCheck::checkColumn('vtiger_wf_config', 'log_handler', 'VARCHAR(10)', '');
DbCheck::checkColumn('vtiger_wf_config', 'log_handler_value', 'VARCHAR(256)', '');

DbCheck::checkColumn('vtiger_wfp_blocks', 'modified', 'DATETIME');
DbCheck::checkColumn('vtiger_wfp_blocks', 'modified_by', 'int(11) UNSIGNED', '0');

DbCheck::checkColumn('vtiger_wfp_blocks', 'colorlayer', 'VARCHAR(6)', '');
DbCheck::checkColumn('vtiger_wfp_blocks', 'env_vars', 'TEXT', '');

DbCheck::checkColumn('vtiger_wf_scheduler', 'timezone', 'VARCHAR(48)', 'UTC');
DbCheck::checkColumn('vtiger_wf_scheduler', 'enable_records', 'TINYINT', '0');
DbCheck::checkColumn('vtiger_wf_scheduler', 'condition', 'TEXT');

DbCheck::checkColumn('vtiger_wf_trigger', 'description', 'VARCHAR( 255 )', '');

DbCheck::checkColumn('vtiger_wfp_blocks', 'settings', 'mediumtext', false, true);

DbCheck::tableToUtf8('vtiger_wf_settings');
DbCheck::tableToUtf8('vtiger_wfp_blocks');

DbCheck::checkColumn('vtiger_wf_frontendmanager', 'config', 'text');
DbCheck::checkColumn('vtiger_wf_frontendmanager', 'module', 'VARCHAR(24)', '', false, static function () {
    $adb = PearDatabase::getInstance();

    $sql = 'UPDATE vtiger_wf_frontendmanager SET module = (SELECT module_name FROM vtiger_wf_settings WHERE vtiger_wf_settings.id = vtiger_wf_frontendmanager.workflow_id)';
    $adb->query($sql);
});
DbCheck::checkColumn('vtiger_wf_frontendmanager', 'order', 'tinyint(4)', '', true);
DbCheck::checkColumn('vtiger_wf_frontendmanager', 'color', 'VARCHAR(12)', '', true);
DbCheck::checkColumn('vtiger_wf_frontendmanager', 'listview', 'tinyint(4)', '1');

DbCheck::checkColumn('vtiger_wf_queue', 'checkMode', "enum('dynamic','static','running')", 'static', true);

$sql = "SELECT * FROM vtiger_wf_settings WHERE `trigger` = ''";
$result = $adb->query($sql);
if ($adb->num_rows($result) > 0) {
    echo '[update] Update ' . $adb->num_rows($result) . ' trigger';

    while ($row = $adb->fetchByAssoc($result)) {
        $sql = 'SELECT `key` FROM vtiger_wf_trigger WHERE id = ?';
        $result2 = $adb->pquery($sql, [$row['condition']]);

        $adb->pquery('UPDATE vtiger_wf_settings SET `trigger` = ? WHERE id = ?', [$adb->query_result($result2, 0, 'key'), $row['id']]);
    }
}

/* Repo Update */
$className = '\\Workflow\\SWExtension\\ca62d58e352291a30c165c444877b1c92c5d28d5c';
$moduleModel = Vtiger_Module_Model::getInstance('Workflow2');
$GenKey = new $className('Workflow2', $moduleModel->version);
$licenseHash = $GenKey->gb8d9a4f2e098e53aee15b6fd5f9456705f64f354();

$adb = PearDatabase::getInstance();
$sql = 'SELECT * FROM vtiger_wf_repository WHERE url LIKE ? AND deleted = 0';
$result = $adb->pquery($sql, ['%.redoo-networks.%']);

if ($adb->num_rows($result) == 0) {
    $repoId = Repository::register('https://repo.redoo-networks.com', $licenseHash, 'Redoo Networks Repository', true, '', $licenseHash);
} else {
    $repoId = $adb->query_result($result, 0, 'id');
}

$sql = 'SELECT * FROM vtiger_wf_repository WHERE url LIKE ?';
$result = $adb->pquery($sql, ['%repository.stefanwarnat.de%']);
$oldRepoId = $adb->query_result($result, 0, 'id');

if (!empty($oldRepoId)) {
    $sql = 'UPDATE vtiger_wf_types SET repo_id = ? WHERE repo_id = ? AND repo_id != 0';
    $adb->pquery($sql, [$repoId, $oldRepoId]);

    //    $sql = 'UPDATE vtiger_wf_types SET repo_id = ? WHERE repo_id = 0';
    //    $adb->pquery($sql, array($repoId));

    $sql = 'DELETE FROM vtiger_wf_repository_types WHERE repos_id = ?';
    $adb->pquery($sql, [$oldRepoId]);

    $sql = 'UPDATE vtiger_wf_repository SET deleted = 1 WHERE id = ?';
    $adb->pquery($sql, [$oldRepoId]);
}

$initRepository = true;
/* Repo Update */

if ($full) {
    DbCheck::checkColumn('vtiger_wf_queue', 'locked', 'tinyint(4)');
    DbCheck::checkColumn('vtiger_wf_queue', 'crmid', 'int(10) unsigned');
    DbCheck::checkColumn('vtiger_wf_queue', 'workflow_id', 'int(10) unsigned');
    DbCheck::checkColumn('vtiger_wf_queue', 'execID', 'varchar(32)');
    DbCheck::checkColumn('vtiger_wf_queue', 'execution_user', 'mediumint(9) unsigned');
    DbCheck::checkColumn('vtiger_wf_queue', 'block_id', 'int(10) unsigned');
    DbCheck::checkColumn('vtiger_wf_queue', 'nextStepTime', 'datetime');
    DbCheck::checkColumn('vtiger_wf_queue', 'nextStepField', 'varchar(64)');
    DbCheck::checkColumn('vtiger_wf_queue', 'timestamp', 'timestamp');

    DbCheck::checkColumn('vtiger_wf_types', 'type', 'varchar(32)');
    DbCheck::checkColumn('vtiger_wf_types', 'handlerclass', 'varchar(32)');
    DbCheck::checkColumn('vtiger_wf_types', 'file', ' varchar(256)');
    DbCheck::checkColumn('vtiger_wf_types', 'module', 'varchar(32)');
    DbCheck::checkColumn('vtiger_wf_types', 'output', 'varchar(255)');
    DbCheck::checkColumn('vtiger_wf_types', 'persons', 'varchar(255)');
    DbCheck::checkColumn('vtiger_wf_types', 'text', 'varchar(255)');
    DbCheck::checkColumn('vtiger_wf_types', 'input', ' tinyint(4)', '1');
    DbCheck::checkColumn('vtiger_wf_types', 'styleclass', 'varchar(64)');
    DbCheck::checkColumn('vtiger_wf_types', 'background', 'varchar(32)');
    DbCheck::checkColumn('vtiger_wf_types', 'category', 'varchar(16)');
    DbCheck::checkColumn('vtiger_wf_types', 'sort', 'int(10) unsigned');

    DbCheck::checkColumn('vtiger_wfp_blocks', 'workflow_id', 'smallint(5) unsigned');
    DbCheck::checkColumn('vtiger_wfp_blocks', 'active', 'tinyint(3) unsigned');
    DbCheck::checkColumn('vtiger_wfp_blocks', 'text', 'varchar(128)');
    DbCheck::checkColumn('vtiger_wfp_blocks', 'type', 'varchar(32)');
    DbCheck::checkColumn('vtiger_wfp_blocks', 'x', 'mediumint(9)');
    DbCheck::checkColumn('vtiger_wfp_blocks', 'y', 'mediumint(9)');

    DbCheck::checkColumn('vtiger_wfp_connections', 'deleted', 'tinyint(3) unsigned');
    DbCheck::checkColumn('vtiger_wfp_connections', 'workflow_id', 'int(11)');
    DbCheck::checkColumn('vtiger_wfp_connections', 'source_mode', "enum('block','person')");
    DbCheck::checkColumn('vtiger_wfp_connections', 'source_id', 'smallint(6)');
    DbCheck::checkColumn('vtiger_wfp_connections', 'source_key', 'varchar(32)');
    DbCheck::checkColumn('vtiger_wfp_connections', 'destination_id', 'smallint(6)');
    DbCheck::checkColumn('vtiger_wfp_connections', 'destination_key', 'varchar(32)');
    DbCheck::checkColumn('vtiger_wfp_connections', 'last_change', 'timestamp');
    DbCheck::checkColumn('vtiger_wfp_connections', 'last_changed_userid', 'mediumint(9)');
}

$sql = 'UPDATE vtiger_links SET linklabel = "Workflows" WHERE linklabel = "Workflow Designer" AND linktype LIKE "%SIDEBAR%"';
$adb->query($sql);

$sql = 'UPDATE vtiger_wf_repository SET url = "https://repo.redoo-networks.com" WHERE url LIKE "https://repository.redoo-networks.de"';
$result = $adb->query($sql);

$sql = 'SELECT id FROM vtiger_wf_repository WHERE url LIKE "%.redoo-networks%"';
$result = $adb->query($sql);
if ($adb->num_rows($result) == 0) {
    $initRepository = true;
}

if ($initRepository) {
    try {
        $className = '\\Workflow\\SWExtension\\ca62d58e352291a30c165c444877b1c92c5d28d5c';
        $moduleModel = Vtiger_Module_Model::getInstance('Workflow2');
        $GenKey = new $className('Workflow2', $moduleModel->version);
        $licenseHash = $GenKey->gb8d9a4f2e098e53aee15b6fd5f9456705f64f354();

        $newId = Repository::register('https://repo.redoo-networks.com', $licenseHash, 'Basic Repository', true, $licenseHash);
        // $repo = new \Workflow\Repository($newId);
        // $repo->installAll(\Workflow\Repository::INSTALL_ALL);

        // $adb->query('UPDATE vtiger_wf_types SET repo_id = ' . $newId);
    } catch (Exception $exp) {
        echo 'Repository Error:' . $exp->getMessage();
    }
}
Repository::clearDeletedRepositoryTypes();

$repoId = $adb->query_result($result, 0, 'id');
$className = '\\Workflow\\SWExtension\\ca62d58e352291a30c165c444877b1c92c5d28d5c';
$GenKey = new $className('Workflow2', $moduleModel->version);
$licenseHash = $GenKey->gb8d9a4f2e098e53aee15b6fd5f9456705f64f354();
$adb->query('UPDATE vtiger_wf_repository SET licensecode = "' . md5($licenseHash) . '" WHERE url LIKE "%repository.stefanwarnat.de"');

DbCheck::checkColumn('vtiger_wf_repository_types', 'mode', 'VARCHAR(16)');

$sql = 'SELECT * FROM vtiger_wf_repository WHERE url LIKE ? AND deleted = 0';
$result = $adb->pquery($sql, ['%.redoo-networks.%'], true);

if ($adb->num_rows($result) > 1) {
    while ($row = $adb->fetchByAssoc($result)) {
        $sql = 'SELECT * FROM vtiger_wf_types WHERE repo_id = ?';
        $resultCheck = $adb->pquery($sql, [$row['id']], true);

        if ($adb->num_rows($resultCheck) == 0) {
            $sql = 'UPDATE vtiger_wf_repository SET deleted = 1 WHERE id = ?';
            $adb->pquery($sql, [$row['id']], true);
        }
    }

    $sql = 'SELECT * FROM vtiger_wf_repository WHERE url = ? AND deleted = 0';
    $result = $adb->pquery($sql, ['https://repo.redoo-networks.com'], true);

    if ($adb->num_rows($result) == 0) {
        $sql = 'UPDATE vtiger_wf_repository SET deleted = 0 WHERE url = ? LIMIT 1';
        $adb->pquery($sql, ['https://repo.redoo-networks.com'], true);
    }
}

$sql = 'DELETE FROM vtiger_wf_repository_types WHERE repos_id IN (SELECT id FROM vtiger_wf_repository WHERE deleted = 1)';
$adb->query($sql);

$sql = 'UPDATE vtiger_wf_repository SET version = 2 WHERE url LIKE "%.redoo-networks.%" AND deleted = 0';
$result = $adb->query($sql, true);

if (!empty($licenseHash)) {
    $sql = 'UPDATE vtiger_wf_repository SET licenseCode = ? WHERE url LIKE "%.redoo-networks.%"';
    $adb->pquery($sql, [md5($licenseHash)]);

    $sql = 'SELECT * FROM vtiger_wf_repository WHERE url LIKE "%.redoo-networks.%" AND deleted = 0';
    $result = $adb->query($sql, true);

    $repository = new Repository($adb->query_result($result, 0, 'id'));
    $repository->pushPackageLicense(md5($licenseHash));
}
