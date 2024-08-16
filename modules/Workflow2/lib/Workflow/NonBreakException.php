<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 12.06.2016
 * Time: 17:45.
 */

namespace Workflow;

class NonBreakException extends \Exception
{
    public function __construct($message)
    {
        parent::__construct($message, E_NONBREAK_ERROR);
    }
}
