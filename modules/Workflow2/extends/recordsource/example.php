<?php

namespace Workflow\Plugins\RecordSource;

use Workflow\RecordSource;
use Workflow\VTEntity;

class CustomClassName extends RecordSource
{
    public function getSource($moduleName)
    {
        $return = [];

        $return = [
            'id' => 'unique id of method',
            'title' => 'Headline of selection method',
            'sort' => 30,   // Sort Index. Condition = 10, others = 20
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
        $sqlQuery = 'SELECT vtiger_crmentity.crmid /* Insert Fields */ FROM ...';

        return $sqlQuery;
    }

    public function getConfigHTML()
    {
        $html = '... HTML of configuration. Store anything within the task[...] or better task[recordsource][...] array';

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
RecordSource::register('unique id of method', '\Workflow\Plugins\RecordSource\CustomClassName');
