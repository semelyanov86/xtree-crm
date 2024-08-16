<?php

namespace Workflow\Plugins\RecordSource;

use Workflow\RecordSource;
use Workflow\VTEntity;
use Workflow\VtUtils;

class SelectionChain extends RecordSource
{
    public function getSource($moduleName)
    {
        $return = [];

        $return = [
            'id' => 'selectionchain',
            'title' => 'combine multiple Selectors',
            'sort' => 40,   // Sort Index. Condition = 10, others = 20
        ];

        return $return;
    }

    /**
     * @param $context \Workflow\VTEntity Context of current main Workflow Record
     * @param $sortField string|null [null] Query should sort by this field & direction, if != null
     * @param $limit integer|null [null] Add an limit to your query
     * @parem $includeAllModTables bool [false] You should add all Tables from Target Record
     *
     * @return string
     */
    public function getQuery(VTEntity $context, $sortField = null, $limit = null, $includeAllModTables = false)
    {
        $chainid = $this->_Data['recordsource']['chainid'];

        $chainid = md5($chainid);
        $environmentId = '__chain_' . $chainid;

        $chain = $context->getEnvironment($environmentId);

        if (empty($chain)) {
            throw new \Exception('You are select records by Selection chain, but don\'t add any record selection configuration to this chain "' . $this->_Data['recordsource']['chainid'] . '".');
        }

        $moduleSQL = VtUtils::getModuleTableSQL($this->_TargetModule);
        $sqlQuery = 'SELECT vtiger_crmentity.crmid /* Insert Fields */ ' . $moduleSQL . ' WHERE vtiger_crmentity.deleted = 0 ' . implode(PHP_EOL, $chain);

        return $sqlQuery;
    }

    public function getConfigHTML($data, $parameter)
    {
        $html = '<div class="alert alert-info">' . vtranslate('This method allows you to create more complex Record Selections and combine multiple selection methods.<br/>Use task "complexe Record Selection" to add Selection and define Chain ID.', 'Settings:Workflow2') . '</div>';

        $html .= '<div><label>' . vtranslate('Select records from this Selection Chain ID', 'Settings:Workflow2') . ':</label><div style="display:inline-block;width:50%;"><div class="insertTextfield" data-name="task[recordsource][chainid]" data-id="subject">' . $this->_Data['recordsource']['chainid'] . '</div></div></div>';

        return $html;
    }

    public function getConfigInlineJS()
    {
        // Return JavaScript, which will executed one time if Record Selector is used
        return '';
    }

    public function getConfigInlineCSS()
    {
        // Return CSS, which will only be applied to your Record Selector Configuration
        return '...';
    }
}

// Register your Selection method
RecordSource::register('selectionchain', '\Workflow\Plugins\RecordSource\SelectionChain');
