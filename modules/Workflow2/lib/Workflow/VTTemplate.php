<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 12.02.14 23:36
 * You must not use this file without permission.
 */

namespace Workflow;

class VTTemplate
{
    protected static $_TranslateModule = '';

    /**
     * @var VTEntity
     */
    protected $_context = '';

    /**
     * @param $context VTEntity
     */
    public function __construct($context)
    {
        $this->_context = $context;
        self::$_TranslateModule = $context->getModuleName();
    }

    public static function setTranslationModule($moduleName)
    {
        self::$_TranslateModule = $moduleName;
    }

    public static function parse($template, $context)
    {
        if (is_array($template)) {
            foreach ($template as $index => $value) {
                $template[$index] = self::parse($value, $context);
            }

            return $template;
        }

        if (strpos($template, '$') !== false || strpos($template, '?') !== false) {
            $objTemplate = new VTTemplate($context);
            $template = $objTemplate->render($template);
        }

        return $template;
    }

    public function render($template)
    {
        if (is_array($template)) {
            foreach ($template as $index => $value) {
                $template[$index] = $this->render($value);
            }

            return $template;
        }

        $template = html_entity_decode($template);

        // Variable in Brackets
        $return = preg_replace_callback('/{\$([^}]*?)\|(.*?)}/s', [$this, 'modifiedHandler'], $template);

        // VTexpressions NEW
        $return = preg_replace_callback('/\${(.*?)}}>/s', [$this, 'functionHandler'], $return);

        // VTexpressions
        $return = preg_replace_callback('/\<\?p?h?p?(.*?)\?\>/s', [$this, 'functionHandler'], $return);

        // Variable in Brackets
        $return = preg_replace_callback('/{\$([a-zA-Z0-9_]*?)}/s', [$this, 'matchHandler'], $return);

        if (is_array($return) || is_object($return)) {
            throw new \Exception('Wrong input VTTemplate::render: $return=' . serialize($return));
        }

        if (strpos($return, '$env') !== false) {
            $return = $this->repairEnvironment($return);
            $return = preg_replace_callback('/\${(.*?)}}>/s', [$this, 'functionHandler'], $return);
        }

        // $asdf or $(assigned_user_id : (Users) signature)
        $return = preg_replace_callback('/{?\$(\w+|(\[([a-zA-Z0-9]*)((,(.*?))?)\])|({(.*?)}}>)|\((\w+) ?: \(([_\w]+)\) (\w+)\))}?/', [$this, 'matchHandler'], $return);

        $return = preg_replace_callback('/href=["\']([^"\']+%[^"\']+)["\']/', static function ($match) {
            return 'href="' . str_replace('%', '--@@--', $match[1]) . '"';
        }, $return);
        $return = preg_replace_callback('/src=["\']([^"\']+%[^"\']+)["\']/', static function ($match) {
            return 'src="' . str_replace('%', '--@@--', $match[1]) . '"';
        }, $return);

        $return = preg_replace_callback('/%([A-Za-z0-9-_]+)%/', [$this, 'translateHandler'], $return);
        $return = str_replace('--@@--', '%', $return);

        return $return;
    }

    public function repairEnvironment($value)
    {
        $count = substr_count($value, '$env');
        $lastPos = 0;

        $newString = '';

        if (is_array($value) || is_object($value)) {
            throw new \Exception('Wrong input VTTemplate::243 $value=' . serialize($value));
        }

        for ($i = 0; $i < $count; ++$i) {
            $pos = strpos($value, '$env', $lastPos);

            $newString .= substr($value, $lastPos, $pos - $lastPos);
            $completeString = '$env';
            $newKey = false;
            $keys = [];

            $started = false;
            for ($j = $pos + 4; $j < strlen($value); ++$j) {
                $char = $value[$j];

                if ($char == '[') {
                    $newKey = '';
                    $completeString .= $char;
                    $started = true;
                } elseif ($char == ']') {
                    $keys[] = trim($newKey, '"\'');
                    $newKey = false;
                    $completeString .= $char;
                    $started = true;
                } elseif ($newKey === false) {
                    if ($started === false) {
                        break;
                    }

                    $newString .= '${ return ' . $completeString . '; }}>';

                    $value  = VtUtils::str_replace_first($completeString, str_repeat('#', strlen($completeString)), $value);
                    $started = false;
                    break;
                } else {
                    $newKey .= $char;
                    $completeString .= $char;
                }
            }

            $lastPos = $j;

            if ($started !== false) {
                $newString .= '${ return ' . $completeString . '; }}>';

                $value  = VtUtils::str_replace_first($completeString, str_repeat('#', strlen($completeString)), $value);
            }
        }

        $newString .= substr($value, $lastPos, strlen($value) - $i - 1);

        return $newString;
    }

    protected function modifiedHandler($match)
    {
        $value = $this->render('$' . $match[1]);

        $matches = [];
        preg_match_all('/[^|}]+/', $match[2], $matches);

        foreach ($matches[0] as $match) {
            preg_match_all('/"[^"]+"|[a-zA-Z0-9_]+/', $match, $functionParts);
            if (empty($functionParts)) {
                continue;
            }

            $functionParts = $functionParts[0];

            $smartyFolder = vglobal('root_directory') . DIRECTORY_SEPARATOR .
                'libraries' . DIRECTORY_SEPARATOR .
                'Smarty' . DIRECTORY_SEPARATOR .
                'libs' . DIRECTORY_SEPARATOR .
                'plugins' . DIRECTORY_SEPARATOR;

            $mofifierFile = 'modifier.' . strtolower($functionParts[0]) . '.php';

            if (ExpressionParser::IsAllowedFunction($functionParts[0]) == true || file_exists($smartyFolder . $mofifierFile)) {
                $args = [];
                for ($i = 1; $i < count($functionParts); ++$i) {
                    $tmp = $functionParts[$i];
                    if (substr($functionParts[$i], 0, 1) == '"' && substr($functionParts[$i], -1) == '"') {
                        $tmp = substr($tmp, 1, -1);
                    }
                    $args[] = $tmp;
                }
                array_unshift($args, $value);

                if (file_exists($smartyFolder . $mofifierFile)) {
                    require_once realpath($smartyFolder . $mofifierFile);
                    $modifierFunction = 'smarty_modifier_' . strtolower($functionParts[0]);
                    $value = call_user_func_array($modifierFunction, $args);
                } else {
                    $value = call_user_func_array($functionParts[0], $args);
                }
                /*
                switch($functionParts[0]) {
                    case 'replace':
                        $value = str_replace($args[1], $args[2], $value);
                        break;
                    default:
                        $value = call_user_func_array($functionParts[0], $args);
                }
                */
            }
        }

        // self::IsAllowedFunction($nextToken[1])
        return $value;
    }

    protected function translateHandler($match)
    {
        return vtranslate($match[1], self::$_TranslateModule);
    }

    protected function functionHandler($match)
    {
        $parser = new ExpressionParser($match[1], $this->_context, false); // Last Parameter = DEBUG

        try {
            $parser->run();
        } catch (ExpressionException $exp) {
            Workflow2::error_handler(E_EXPRESSION_ERROR, $exp->getMessage(), '', '');
        }

        return $newValue = $parser->getReturn();
    }

    // $((account_id: (Accounts)) cf_661)
    protected function matchHandler($match)
    {
        // Wenn count($match) == 2, dann nur $email und keine referenzierten Felder
        if (count($match) == 2) {
            // Special Variables
            if ($match[0] == '$current_user_id') {
                global $current_user, $adb, $oldCurrentUser;
                $sql = "SELECT id FROM vtiger_ws_entity WHERE name = 'Users'";
                $result = $adb->query($sql);
                $wsTabId = $adb->query_result($result, 0, 'id');

                if (!empty($oldCurrentUser)) {
                    return $oldCurrentUser->id;
                }

                return $current_user->id;
            }

            if (
                (
                    $this->_context->getModuleName() == 'Calendar'
                    || $this->_context->getModuleName() == 'Events'
                ) && (
                    $match[0] == '$full_date_start' || $match[0] == '$full_due_date'
                )
            ) {
                if ($match[0] == '$full_date_start') {
                    $fullDate = $this->_context->get('date_start') . ' ' . $this->_context->get('time_start');
                } else {
                    $fullDate = $this->_context->get('due_date') . ' ' . $this->_context->get('time_end');
                }

                $fullDate = \Vtiger_Util_Helper::convertDateTimeIntoUsersDisplayFormat($fullDate, $this->_context->getOwner());

                return $fullDate;
            }

            $fieldname = $match[1];
            $fieldvalue = $this->_context->get($fieldname);

            if ($fieldvalue === false) {
                return '$' . $fieldname;
            }

            if (!empty($fieldvalue)) {
                return $fieldvalue;
            }

            // it is a global function
        } elseif (substr($match[0], 0, 2) == '$[') {
            $function = strtolower($match[3]);

            if (count($match) > 4 && $match[4] != '') {
                $parameter = explode(',', $match[6]);
                for ($i = 0; $i < count($parameter); ++$i) {
                    $parameter[$i] = trim($parameter[$i], "'\" ");
                    $parameter[$i] = preg_replace('/[\x{200B}-\x{200D}]/u', '', $parameter[$i]);
                }
            } else {
                $parameter = false;
            }

            // Exception is thrown if no ShortFunction was found
            try {
                return Shortfunctions::call($function, $parameter, $this->_context);
            } catch (\Exception $exp) {
                $donothing = true;
                // do nothing
            }

            switch ($function) {
                case 'blockid':
                    return \Workflow2::$currentBlockObj->getBlockId();
                    break;
                case 'workflowid':
                    return \Workflow2::$currentWorkflowObj->getId();
                    break;
                case 'execid':
                    return \Workflow2::$currentWorkflowObj->getLastExecID();
                    break;
                case 'url':
                    if ($parameter != false && count($parameter) > 0) {
                        $parameter[0] = VTTemplate::parse($parameter[0], $this->_context);
                        $parameter[0] = intval($parameter[0]);

                        $objTMP = VTEntity::getForId($parameter[0]);
                        global $site_URL;

                        return $site_URL . '/index.php?module=' . $objTMP->getModuleName() . '&view=Detail&record=' . $parameter[0];
                    }
                    break;
                case 'round':
                    $parameter[0] = VTTemplate::parse($parameter[0], $this->_context);

                    if (count($parameter) == 3 && $parameter[2] == '1') {
                        $return = \CurrencyField::convertToUserFormat($parameter[0]);
                    } else {
                        $return = round($parameter[0], $parameter[1]);
                    }

                    return $return;
                    break;
                case 'timezone':
                    $parameter[0] = VTTemplate::parse($parameter[0], $this->_context);
                    $return = \DateTimeField::convertTimeZone($parameter[0], \DateTimeField::getDBTimeZone(), $parameter[1]);

                    return $return->format('H:i:s');
                    break;
                case 'utctz':
                    if (!empty($parameter[0])) {
                        $parameter[0] = VTTemplate::parse($parameter[0], $this->_context);
                    } else {
                        $parameter[0] = date('Y-m-d H:i:s');
                    }

                    $return = \DateTimeField::convertToDBTimeZone($parameter[0]);

                    return $return->format('H:i:s');
                    break;
                case 'dateformat':
                    $parameter[0] = VTTemplate::parse($parameter[0], $this->_context);
                    $time = strtotime($parameter[0]);

                    $format = $parameter[1];

                    return date($format, $time);
                    break;
                case 'nl2br':
                    $parameter[0] = VTTemplate::parse($parameter[0], $this->_context);

                    return nl2br($parameter[0]);
                    break;
                case 'formula':
                    $obj = new Formula($parameter[0], $this->_context);

                    return $obj->getResult();
                    break;
                case 'nowutc':
                    $format = 'Y-m-d H:i:s';
                    $date = \DateTimeField::convertToUserTimeZone(date('Y-m-d H:i:s'));
                    $time = strtotime($date->format('Y-m-d H:i:s'));

                    if ($parameter != false) {
                        if (!empty($parameter[0])) {
                            $time += (intval($parameter[0]) * 86400);
                        }

                        if (!empty($parameter[1])) {
                            $format = $parameter[1];
                        }
                    }

                    $date = date($format, $time);

                    return \DateTimeField::convertToDBTimeZone($date)->format('Y-m-d H:i:s');
                    break;
                    /*                case "now":
                                        $format = "Y-m-d";
                                        $date = \DateTimeField::convertToUserTimeZone(date('Y-m-d H:i:s'));
                                        $time = strtotime($date->format('Y-m-d H:i:s'));

                                        if($parameter != false) {
                                            if(!empty($parameter[0])) {
                                                if(is_numeric($parameter[0])) {
                                                    $time += (intval($parameter[0]) * 86400);
                                                } else {
                                                    $time = strtotime($parameter[0], $time);
                                                }
                                            }

                                            if(!empty($parameter[1])) {
                                                $format = $parameter[1];
                                            }
                                        }
                                        return date($format, $time);
                                        break;*/
                case 'link':
                    if ($parameter != false && count($parameter) > 0) {
                        $parameter[0] = VTTemplate::parse($parameter[0], $this->_context);
                        $parameter[0] = intval($parameter[0]);

                        $objTMP = VTEntity::getForId($parameter[0]);

                        global $site_URL;
                        $url = $site_URL . '/index.php?module=' . $objTMP->getModuleName() . '&view=Detail&record=' . $parameter[0];

                        return '<a href="' . $url . '">' . $objTMP->getCRMRecordLabel() . '</a>';
                    }
                    break;
                case 'recordlabel':
                    if ($parameter != false && count($parameter) > 0) {
                        $parameter[0] = VTTemplate::parse($parameter[0], $this->_context);
                        $parameter[0] = intval($parameter[0]);

                        return \Vtiger_Functions::getCRMRecordLabel($parameter[0]);
                    }
                    break;
                case 'entityname':
                    if ($parameter != false) {
                        $parameter[0] = VTTemplate::parse($parameter[0], $this->_context);

                        if (is_array($parameter[0]) || is_object($parameter[0])) {
                            throw new \Exception('Wrong input entityname: $parameter=' . serialize($parameter[0]));
                        }

                        if (strpos($parameter[0], 'x') !== false) {
                            $crmid = explode('x', $parameter[0]);
                            $crmid = intval($crmid[1]);
                        } else {
                            $crmid = intval($parameter[0]);
                        }
                        global $adb;

                        $sql = 'SELECT setype FROM vtiger_crmentity WHERE crmid=?';
                        $result = $adb->pquery($sql, [$crmid]);
                        $data = $adb->fetchByAssoc($result);
                        $return = getEntityName($data['setype'], [$crmid]);

                        return $return[$crmid];
                    }

                    return '';
                    break;
            }
        } else {
            preg_match('/\((\w+) ?: \(([_\w]+)\) (\w+)\)/', $match[1], $matches);

            [$full, $referenceField, $referenceModule, $fieldname] = $matches;
            if ($referenceField == 'smownerid') {
                $referenceField = 'assigned_user_id';
            }

            if ($referenceModule === '__VtigerMeta__') {
                return $this->_getMetaValue($fieldname);
                //            } elseif($referenceModule === 'ModuleName'){
                //                return \Vtiger_Functions::getCRMRecordType($this->_context->get($referenceField));
            }
            if ($referenceField != 'current_user') {
                $referenceId = $this->_context->get($referenceField);
                if ($referenceModule == 'Users' && $referenceField == 'assigned_user_id') {
                    $owner = $this->_context->getOwners();
                    $values = $owner->get($fieldname);

                    return implode(',', $values);
                }

                if (empty($referenceId)) {
                    return '';
                }
            } else {
                global $current_user;
                $referenceId = $current_user->id;
            }

            $entity = VTEntity::getForId($referenceId, $referenceModule == 'Users' ? 'Users' : false);

            return $entity->get($fieldname);
        }
    }

    protected function _getMetaValue($fieldname)
    {
        global $site_URL, $PORTAL_URL, $current_user;

        switch ($fieldname) {
            case 'date'					:	return getNewDisplayDate();
            case 'time'					:	return date('h:i:s');
            case 'dbtimezone'			:	return DateTimeField::getDBTimeZone();
            case 'crmdetailviewurl'		:	$recordId = $this->_context->getId();
                $moduleName = $this->_context->getModuleName();

                return $site_URL . '/index.php?action=DetailView&module=' . $moduleName . '&record=' . $recordId;
            case 'portaldetailviewurl'	: 	$recordId = $this->_context->getId();
                $moduleName = $this->_context->getModuleName();
                $recorIdName = 'id';
                if ($moduleName == 'HelpDesk') {
                    $recorIdName = 'ticketid';
                }
                if ($moduleName == 'Faq') {
                    $recorIdName = 'faqid';
                }
                if ($moduleName == 'Products') {
                    $recorIdName = 'productid';
                }

                return $PORTAL_URL . '/index.php?module=' . $moduleName . '&action=index&' . $recorIdName . '=' . $recordId . '&fun=detail';
            case 'siteurl'				: return $site_URL;
            case 'portalurl'			: return $PORTAL_URL;

            default: '';
        }
    }
}
