<?php
if (!empty($_GET['stefanDebug'])) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    $adb->dieOnError = true;
}

if (!empty($_POST['send_report'])) {
    require_once 'Workflow2.php';
    require_once 'modules/Emails/class.phpmailer.php';

    require_once 'modules/Emails/mail.php';

    $mailtext = 'ERROR REPORT WORKFLOW EXTENSION ' . Workflow2::VERSION . ' - vtiger VERSION ' . $vtiger_current_version . "\n\n";
    $mailtext .= "PHPINFO:\n" . $_POST['system']['phpinfo'] . "\n\n";
    $mailtext .= "TABLES:\n" . $_POST['system']['table'] . "\n\n";
    $mailtext .= "CurrentUser:\n" . $_POST['system']['currentUser'] . "\n\n";
    $mailtext .= "FEHLERBESCHREIBUNG:\n" . $_POST['errorRecognization'] . "\n\n";

    $mail = new PHPMailer();
    $mail->IsSMTP();
    setMailServerProperties($mail);
    $mail->FromName = 'Fehlerbericht';
    $mail->Sender = 'errorreporting@stefanwarnat.de';
    $mail->Subject = 'Workflow Designer Error Report';
    $mail->Body = $mailtext;
    $mail->AddAddress('support@stefanwarnat.de', 'Stefan Warnat');
    $mailReturn = MailSend($mail);
    // setMailerProperties($mail,$subject,$contents,$from_email,$from_name,trim($to_email,","),$attachment,$emailid,$module,$logo);

    // $mail_return = send_mail("Accounts", "kontakt@stefanwarnat.de", "Fehlerbereicht", "errorreport@stefanwarnat.de","",$mailtext);
    /* ONLY DEBUG */ var_dump($mailReturn);
}

$extended = !empty($_GET['extend']);
$extendedGroups = ['PHP Variables', 'HTTP Headers Information', 'Apache Environment'];
// Source: http://php.net/manual/de/function.phpinfo.php (Ken)
function phpinfo_array()
{
    ob_start();
    phpinfo(INFO_ALL);
    $info_arr = [];
    $info_lines = explode("\n", strip_tags(ob_get_clean(), '<tr><td><h2>'));
    $cat = 'General';
    foreach ($info_lines as $line) {
        // new cat?
        preg_match('~<h2>(.*)</h2>~', $line, $title) ? $cat = trim($title[1]) : null;
        if (preg_match('~<tr><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td></tr>~', $line, $val)) {
            $info_arr[$cat][$val[1]] = trim($val[2]);
        } elseif (preg_match('~<tr><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td></tr>~', $line, $val)) {
            $info_arr[$cat][$val[1]] = ['local' => $val[2], 'master' => $val[3]];
        }
    }

    return $info_arr;
}

$debug = ['phpinfo' => [], 'table' => ''];
$phpinfo = phpinfo_array();
foreach ($phpinfo as $groupKey => $group) {
    if (in_array($groupKey, $extendedGroups) && $extended == false) {
        continue;
    }

    $debug['phpinfo'][] = 'Group: ' . $groupKey;

    if ($groupKey == 'Apache Environment' && $extended == false) {
        continue;
    }

    foreach ($group as $index => $value) {
        if (!is_string($value) && !empty($value['local'])) {
            $debug['phpinfo'][] = '  `' . $index . "` = '" . $value['local'] . "'";
        } else {
            $debug['phpinfo'][] = '  `' . $index . "` = '" . $value . "'";
        }
    }
}

$tables = $adb->get_tables();

foreach ($tables as $table) {
    if (substr($table, 0, 9) == 'vtiger_wf') {
        $debug['table'][] = 'Table: ' . $table;
        $cols = $adb->query('SHOW FULL COLUMNS FROM `' . $table . '`');

        while ($row = $adb->fetchByAssoc($cols)) {
            $debug['table'][] = '   `' . $row['field'] . '` - ' . $row['type'] . ' - ' . $row['collation'];
        }
    }
}

?>
<br>
<link rel="stylesheet" href="modules/Workflow2/adminStyle.css" type="text/css" media="all" />
<h2 style="margin-left:40px;">Workflow Designer - Debug</h2>
<table cellspacing="0" cellpadding="0" border="0" align="center" width="98%">
<tr>
       <td valign="top"></td>
        <td width="100%" valign="top" style="padding: 10px;" class="showPanelBg">
            <br>
            <div class="settingsUI" style="width:95%;padding:10px;margin-left:10px;">
                <form method="POST" action="#">
                    <?php echo getTranslatedString('LBL_DEBUG_HEAD'); ?>
                    <textarea name="system[phpinfo]" style="height:300px;"><?php echo implode("\n", $debug['phpinfo']); ?></textarea><br>
                    <br>
                    <?php echo getTranslatedString('LBL_DEBUG_MIDDLE'); ?>
                    <textarea name="system[table]" style="height:300px;"><?php echo implode("\n", $debug['table']); ?></textarea>
                    <br>
                    <br>
                    Current User Settings: (Passwords are removed!)
                    <textarea name="system[currentUser]" style="height:300px;"><?php $cU = $current_user;
unset($cU->db, $cU->column_fields['user_password'], $cU->column_fields['confirm_password'], $cU->column_fields['accesskey'], $cU->user_password, $cU->confirm_password, $cU->accesskey);

                        var_dump($cU); ?></textarea>
                    <br>
                    <br>
                    <?php echo getTranslatedString('LBL_DEBUG_BOTTOM'); ?>
                    <textarea name="errorRecognization" style="height:100px;"></textarea><br>
                    <br>
                    <input type="submit" name="send_report" class="crmbutton small edit" value="<?php echo getTranslatedString('SEND_DEBUG_REPORT'); ?>">
                </form>
            </div>
    </td></tr>
</table>

