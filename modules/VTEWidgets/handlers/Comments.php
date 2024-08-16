<?php

class VTEWidgets_Comments_Handler extends VTEWidgets_Basic_Handler
{
    public $dbParams = ['relatedmodule' => 'ModComments'];

    public function getUrl()
    {
        if ($this->Data['limit'] == '') {
            $limit = 5;
        } else {
            $limit = $this->Data['limit'];
        }

        return 'module=VTEWidgets&view=SummaryWidget&record=' . $this->Record . '&mode=showCommentsWidget&page=1&limit=' . $limit;
    }

    public function getConfigTplName()
    {
        return 'CommentsConfig';
    }

    public function getWidget()
    {
        $widget = [];
        $modCommentsModel = Vtiger_Module_Model::getInstance('ModComments');
        if ($this->moduleModel->isCommentEnabled() && $modCommentsModel->isPermitted('EditView')) {
            $this->Config['url'] = $this->getUrl();
            $widget = $this->Config;
        }

        return $widget;
    }
}
