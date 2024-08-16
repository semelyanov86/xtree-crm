<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 01.03.14 17:57
 * You must not use this file without permission.
 */

namespace Workflow\Preset;

use Workflow\ExpressionParser;
use Workflow\Preset;

class SyntaxHighlighter extends Preset
{
    protected $_JSFiles = [PATH_CODEMIRROR];

    public function init()
    {
        $this->_CSSFiles = ['~' . PATH_CODEMIRROR . '/lib/codemirror.css', '~' . PATH_CODEMIRROR . '/theme/eclipse.css', '~' . PATH_CODEMIRROR . '/addon/hint/show-hint.css'];
        $this->_JSFiles = [
            '~' . PATH_CODEMIRROR . '/lib/codemirror.js',
            '~' . PATH_CODEMIRROR . '/mode/clike/clike.js',
            '~' . PATH_CODEMIRROR . '/mode/css/css.js',
            '~' . PATH_CODEMIRROR . '/mode/htmlmixed/htmlmixed.js',
            '~' . PATH_CODEMIRROR . '/mode/javascript/javascript.js',
            '~' . PATH_CODEMIRROR . '/mode/php/php.js',
            '~' . PATH_CODEMIRROR . '/addon/edit/closebrackets.js',
            '~' . PATH_CODEMIRROR . '/addon/edit/matchbrackets.js',
            '~' . PATH_CODEMIRROR . '/addon/hint/show-hint.js',
            // '~'.PATH_CODEMIRROR.'/addon/hint/anyword-hint.js',
        ];
    }

    public function beforeGetTaskform($data)
    {
        $expression = array_merge(ExpressionParser::getDefinedCustomFunctions(), ExpressionParser::$WhitelistPHPfunctions);
        $script = 'var expressionWhitelisted = "' . implode(' ', $expression) . '"';

        $this->addInlineJS($script);
    }
}
