<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 13.08.2016
 * Time: 10:55.
 */

namespace Workflow;

class AdminSidebar
{
    private static $INSTANCE;

    private $menu = [
        'Settings' => [
            [
                'url' => 'index.php?module=Workflow2&view=Index&parent=Settings',
                'label' => 'LBL_WORKFLOW2',
                'module' => 'Settings:Workflow2',
            ],
            [
                'url' => 'index.php?module=Workflow2&view=WorkflowGenerator&parent=Settings',
                'label' => 'Workflow Assistent',
                'module' => 'Settings:Workflow2',
                'inactive' => true,
            ],
            [
                'url' => 'index.php?module=Workflow2&view=FrontendManager&parent=Settings',
                'label' => 'Frontend Manager',
                'module' => 'Settings:Workflow2',
            ],
            [
                'url' => 'index.php?module=Workflow2&view=FrontendWorkflowConfig&parent=Settings',
                'label' => 'EditView Manager',
                'module' => 'Settings:Workflow2',
                'pro' => true,
            ],
            [
                'url' => 'index.php?module=Workflow2&view=ProviderManager&parent=Settings',
                'label' => 'Provider Manager',
                'module' => 'Settings:Workflow2',
            ],
            [
                'url' => 'index.php?module=Workflow2&view=SettingsScheduler&parent=Settings',
                'label' => 'LBL_SETTINGS_SCHEDULER',
                'module' => 'Settings:Workflow2',
            ],
            [
                'url' => 'index.php?module=Workflow2&view=Mailscanner&parent=Settings',
                'label' => 'Mailscanner',
                'module' => 'Settings:Workflow2',
            ],
            [
                'url' => 'index.php?module=Workflow2&view=HttpHandlerManager&parent=Settings',
                'label' => 'LBL_SETTINGS_HTTPHANDLER',
                'module' => 'Settings:Workflow2',
                'pro' => true,
            ],
            [
                'url' => 'index.php?module=Workflow2&view=SettingsLogging&parent=Settings',
                'label' => 'LBL_SETTINGS_LOGGING',
                'module' => 'Settings:Workflow2',
            ],
            [
                'url' => 'index.php?module=Workflow2&view=LicenseManager&parent=Settings',
                'label' => 'LBL_LICENSE_MANAGER',
                'module' => 'Settings:Workflow2',
            ],
            [
                'url' => 'index.php?module=Workflow2&view=SettingsDBCheck&parent=Settings',
                'label' => 'LBL_SETTINGS_DB_CHECK',
                'module' => 'Settings:Workflow2',
            ],
            [
                'url' => 'index.php?module=Workflow2&view=ErrorReport&parent=Settings',
                'label' => 'LBL_ERROR_REPORT',
                'module' => 'Settings:Workflow2',
            ],
        ],
        'LBL_TASK_MANAGEMENT' => [
            [
                'url' => 'index.php?module=Workflow2&view=TaskManagement&parent=Settings',
                'label' => 'LBL_TASK_MANAGEMENT',
                'module' => 'Settings:Workflow2',
            ],
            [
                'url' => 'index.php?module=Workflow2&view=TaskRepoManager&parent=Settings',
                'label' => 'LBL_TASK_REPO_MANAGEMENT',
                'module' => 'Settings:Workflow2',
            ],
        ],
    ];

    public function __construct() {}

    /**
     * @return AdminSidebar
     */
    public static function getInstance()
    {
        if (self::$INSTANCE === null) {
            self::$INSTANCE = new self();
            self::$INSTANCE->init();
        }

        return self::$INSTANCE;
    }

    public function init()
    {
        $alle = glob(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'extends' . DIRECTORY_SEPARATOR . 'sidebar' . DIRECTORY_SEPARATOR . '*.inc.php');

        foreach ($alle as $datei) {
            $this->menu = array_merge_recursive($this->menu, require (realpath($datei)));
        }
    }

    public function addMenu($group, $url, $label, $translationModule = 'Settings:Workflow2')
    {
        $this->menu[$group][] = [
            'url' => $url,
            'label' => $label,
            'module' => $translationModule,
        ];
    }

    public function getMenu()
    {
        $adb = \PearDatabase::getInstance();

        $moduleModel = \Vtiger_Module_Model::getInstance('Workflow2');
        $className = '\\Workflow\\SWExtension\\ca62d58e352291a30c165c444877b1c92c5d28d5c';
        $asdf = new $className('Workflow2', $moduleModel->version);
        $stage = $asdf->g1dd63e9ab62a68ac02f481ed3ba709207cb145ae();

        foreach ($this->menu as $cat => $items) {
            foreach ($items as $index => $item) {
                if (!empty($item['inactive'])) {
                    unset($this->menu[$cat][$index]);

                    continue;
                }
                if (!empty($item['pro']) && $item['pro'] == true && $stage != 'pro') {
                    unset($this->menu[$cat][$index]);

                    continue;
                }

                preg_match('/view=([a-zA-Z0-9-_]+)/', $item['url'], $matches);
                if ($matches[1] == 'HttpHandlerManager') {
                    $sql = 'SELECT COUNT(*) as num FROM vtiger_wf_http_logs WHERE created > "' . date('Y-m-d', time() - (86400 * 7)) . '"';
                    $result = $adb->query($sql);

                    $this->menu[$cat][$index]['errors'] = $adb->query_result($result, 0, 'num');
                }

                $this->menu[$cat][$index]['view'] = $matches[1];

                preg_match('/page=([a-zA-Z0-9-_]+)/', $item['url'], $matches);
                if (!empty($matches)) {
                    $this->menu[$cat][$index]['page'] = $matches[1];
                } else {
                    $this->menu[$cat][$index]['page'] = '';
                }
            }
        }

        return $this->menu;
    }
}
