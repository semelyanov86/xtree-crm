<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 06.04.2016
 * Time: 19:06.
 */

namespace Workflow;

class Formula
{
    private static $EvalAllowed = -1;

    private $_formulaId = 0;

    /**
     * @var VTEntity
     */
    private $_context;

    private $_data = [];

    private $_formula = '';

    private $_variables = [];

    public function __construct($formulaId, VTEntity $context)
    {
        $this->_formulaId = intval($formulaId);
        $this->_context = $context;

        $adb = \PearDatabase::getInstance();

        $sql = 'SELECT * FROM vtiger_wf_formulas WHERE id = ?';
        $result = $adb->pquery($sql, [$this->_formulaId]);

        if ($adb->num_rows($result) == 0) {
            throw new \Exception('Formula ' . $this->_formulaId . ' not found!');
        }

        $this->_data = $adb->fetchByAssoc($result);

        $this->_formula = $this->_data['formula'];
        $this->_variables = unserialize(html_entity_decode($this->_data['variables']));
    }

    public function getResult()
    {
        foreach ($this->_variables as $var => $value) {
            $this->_variables[$var] = floatval(VTTemplate::parse($value, $this->_context));
        }

        $this->_prepareFunction();
        $this->_checkSyntax();

        $result = eval('return ' . $this->_formula . ';');

        return $result;
    }

    private function isEvalAllowed()
    {
        if (self::$EvalAllowed !== -1) {
            return self::$EvalAllowed;
        }

        if (!function_exists('ini_get')) {
            self::$EvalAllowed = false;

            return self::$EvalAllowed;
        }

        if (ini_get('suhosin.executor.disable_eval') == '1') {
            self::$EvalAllowed = false;

            return self::$EvalAllowed;
        }

        $check = ini_get('disable_functions') . ' ' . ini_get('suhosin.executor.func.blacklist');

        if (strpos($check, 'eval') !== false) {
            self::$EvalAllowed = false;

            return self::$EvalAllowed;
        }

        return true;
    }

    private function _checkSyntax()
    {
        if (substr_count($this->_formula, '(') != substr_count($this->_formula, ')')) {
            throw new \Exception('Opening and Closing Brakets are not correct in this Formula: ' . $this->_formula);
        }

        $inString = @ini_set('log_errors', false);
        $token = @ini_set('display_errors', true);
        ob_start();

        // If $braces is not zero, then we are sure that $code is broken.
        // We run it anyway in order to catch the error message and line number.

        // Else, if $braces are correctly balanced, then we can safely put
        // $code in a dead code sandbox to prevent its execution.
        // Note that without this sandbox, a function or class declaration inside
        // $code could throw a "Cannot redeclare" fatal error.

        // $code = html_entity_decode(htmlspecialchars_decode($code, ENT_NOQUOTES), ENT_NOQUOTES, "UTF-8");
        // var_dump(htmlentities($code, ENT_QUOTES, "UTF-8"), htmlentities( ' return $env[\'url\'];  ', ENT_QUOTES, "UTF-8"));
        $code = "if(0){{$this->_formula};\n}";

        // If eval not allowed, don't execute this
        if (!$this->isEvalAllowed()) {
            throw new \Exception('Formula calculations require the eval feature of PHP. Ask your system administrator!');
        }

        if (eval($code) === false) {
            // var_dump(str_replace("&", "-", $code), htmlentities($code, ENT_NOQUOTES, "UTF-8"));exit();
            if ($braces) {
                $braces = PHP_INT_MAX;
            } else {
                // Get the maximum number of lines in $code to fix a border case
                strpos($code, "\r") !== false && $code = strtr(str_replace("\r\n", "\n", $code), "\r", "\n");
                $braces = substr_count($code, "\n");
            }

            $code = ob_get_clean();
            $code = strip_tags($code);

            // Get the error message and line number
            if (preg_match("'syntax error, (.+) in .+ on line (\\d+)'s", $code, $code)) {
                $code[2] = (int) $code[2];
                $code = $code[2] <= $braces
                    ? [$code[1], $code[2]]
                    : ['unexpected $end' . substr($code[1], 14), $braces];
            } else {
                $code = ['syntax error', 0];
            }

            $oldHandler = set_error_handler('var_dump', 0);
            @$undef_var;
            if (!empty($oldHandler)) {
                set_error_handler($oldHandler);
            }
        } else {
            ob_end_clean();
            $code = false;
        }

        @ini_set('display_errors', $token);
        @ini_set('log_errors', $inString);

        if ($code !== false) {
            throw new \Exception('Error in Formular ' . $this->_formula);
        }
    }

    private function _prepareFunction()
    {
        $this->_formula = preg_replace_callback('/[a-zA-Z0-9]+/', [$this, '_replaceFormula'], $this->_formula);

        $this->_formula = preg_replace('/[^0-9\.\+\-\*\/%\*\(\)]/', '', $this->_formula);
    }

    private function _replaceFormula($match)
    {
        if (is_numeric($match[0])) {
            return $match[0];
        }

        if (!isset($this->_variables[$match[0]])) {
            return '';
        }

        return $this->_variables[$match[0]];
    }
}
