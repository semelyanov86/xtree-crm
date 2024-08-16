<?php
/**
 * Created by Stefan Warnat
 * User: Stefan
 * Date: 29.04.2017
 * Time: 17:30.
 */

namespace Workflow;

class PresetManager
{
    private $_presets = [];

    /**
     * @var Preset[]
     */
    private $_presetObj = [];

    /**
     * @var null|Task
     */
    private $_task;

    public function __construct(Task $task)
    {
        $this->_task = $task;
    }

    /**
     * Register Preset for this TaskType
     * Currently available presets: Condition, FieldSetter.
     *
     * @param string $preset
     * @param string $configName name of configuration variable
     * @param array $extraParameter Parameter transfer to the preset
     * @see https://support.stefanwarnat.de
     */
    public function addPreset($preset, $configName, $extraParameter = [])
    {
        $this->_presets[] = [$configName, $preset, $extraParameter];
        $index = count($this->_presets) - 1;

        if (!isset($this->_presetObj['preset_' . $index])) {
            $className = 'Workflow\\Preset\\' . $preset;
            $this->_presetObj['preset_' . $index] = new $className($configName, $this->_task->getWorkflow(), $extraParameter, $this->_task);
        }

        return $this->_presetObj['preset_' . $index];
    }

    public function getInlineJavaScript()
    {
        $inlineJS = '';
        foreach ($this->_presets as $index => $preset) {
            if (isset($this->_presetObj['preset_' . $index])) {
                $inlineJS .= $this->_presetObj['preset_' . $index]->getInlineJS();
            }
        }

        return $inlineJS;
    }

    public function getJavaScriptFiles()
    {
        $jsFiles = [];
        foreach ($this->_presets as $index => $preset) {
            if (isset($this->_presetObj['preset_' . $index])) {
                $jsFiles = array_merge($jsFiles, $this->_presetObj['preset_' . $index]->getJSFiles());
            }
        }

        return $jsFiles;
    }

    public function getCSSFiles()
    {
        $cssFiles = [];
        foreach ($this->_presets as $index => $preset) {
            if (isset($this->_presetObj['preset_' . $index])) {
                $cssFiles = array_merge($cssFiles, $this->_presetObj['preset_' . $index]->getCSSFiles());
            }
        }

        return $cssFiles;
    }

    public function beforeTaskform() {}

    public function trigger($event, $values)
    {
        foreach ($this->_presets as $index => $preset) {
            $obj = $this->_presetObj['preset_' . $index];

            $newValues = $obj->{$event}($values);
            if (!empty($newValues)) {
                $values = $newValues;
            }
        }

        return $values;
    }

    public function getTaskForm() {}
}
