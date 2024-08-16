<?php
/**
 * @copyright 2016-2017 Redoo Networks GmbH
 * @see https://redoo-networks.com/
 * This file is part of a vTigerCRM module, implemented by Redoo Networks GmbH and must not used without permission.
 */

namespace Workflow\RequestValuesForm;

use Workflow\RequestValuesForm;
use Workflow\VTEntity;

class Row
{
    /**
     * @var null|RequestValuesForm
     */
    private $_Form;

    /**
     * @var Field[]
     */
    private $_Fields = [];

    private $_HTML = '';

    private $_JS = '';

    public function __construct(RequestValuesForm $form)
    {
        $this->_Form = $form;
    }

    public function addField()
    {
        $field = new Field($this);

        $this->_Fields[] = $field;

        return $field;
    }

    public function render(VTEntity $context)
    {
        $this->_HTML = '';

        $this->_HTML .= '<div class="ReqValRow">';

        foreach ($this->_Fields as $field) {
            if ($field->getType() != 'hidden') {
                $this->_HTML .= '<div class="ReqValField">';
            }

            $field->render($context);

            try {
                $this->_HTML .= $field->getHTML();
            } catch (\Exception $exp) {
            }

            $this->_JS .= $field->getJS();

            if ($field->getType() != 'hidden') {
                $this->_HTML .= '</div>';
            }
        }

        $this->_HTML .= '</div>';
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getHTML()
    {
        if (empty($this->_HTML)) {
            throw new \Exception('Execute render Function before get HTML');
        }

        return $this->_HTML;
    }

    /**
     * @return Field[]
     */
    public function getFieldList()
    {
        return $this->_Fields;
    }

    /**
     * @return string
     */
    public function getJS()
    {
        return $this->_JS;
    }
}
