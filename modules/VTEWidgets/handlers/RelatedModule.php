<?php

class VTEWidgets_RelatedModule_Handler extends VTEWidgets_Basic_Handler
{
    public function getUrl()
    {
        $moduleName = Vtiger_Functions::getModuleName($this->Data['relatedmodule']);

        return 'module=VTEWidgets&view=SummaryWidget&record=' . $this->Record . '&mode=showRelatedWidget&relatedModule=' . $moduleName . '&page=1&limit=' . $this->Data['limit'] . '&vtewidgetid=' . $this->Config['id'];
    }

    public function getWidget()
    {
        $widget = [];
        $moduleName = Vtiger_Functions::getModuleName($this->Data['relatedmodule']);
        $model = Vtiger_Module_Model::getInstance($moduleName);
        if ($model->isPermitted('DetailView')) {
            $this->Config['url'] = $this->getUrl();
            $this->Config['tpl'] = 'Basic.tpl';
            if ($this->Data['action'] == 1) {
                $createPermission = $model->isPermitted('EditView');
                $this->Config['action'] = $createPermission == true ? 1 : 0;
                if ($model->isQuickCreateSupported()) {
                    $this->Config['actionURL'] = $model->getQuickCreateUrl();
                } else {
                    $this->Config['actionURL'] = 'index.php?module=' . $moduleName . '&view=Edit';
                }
            }
            if (isset($this->Data['filter'])) {
                $filterArray = explode('::', $this->Data['filter']);
                [$this->Config['column_name'], $this->Config['field_name']] = $filterArray;
            }
            $widget = $this->Config;
        }

        return $widget;
    }

    public function getConfigTplName()
    {
        return 'RelatedModuleConfig';
    }
}
