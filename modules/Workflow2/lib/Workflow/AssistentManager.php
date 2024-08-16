<?php

namespace Workflow;

class AssistentManager
{
    public function getAvailableAssistents()
    {
        $filePath = MODULE_ROOTPATH . DS . 'extends' . DS . 'assistents' . DS . '*' . DS . 'Assistent.php';

        $files = glob($filePath);

        $assistents = [];

        foreach ($files as $file) {
            require_once $file;

            $key = basename(dirname($file));

            $className = '\Workflow\Plugins\Assistents\\' . $key;

            $assistent = new $className($key);

            $assistents[$key] = [
                'name' => $assistent->getName(),
                'description' => $assistent->getDescription(),
            ];
        }

        return $assistents;
    }
}
