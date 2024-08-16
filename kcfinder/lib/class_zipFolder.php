<?php

/** This file is part of KCFinder project. The class are taken from
 * http://www.php.net/manual/en/function.ziparchive-addemptydir.php.
 *
 *      @desc Directory to ZIP file archivator
 *   @version 2.21
 *    @author Pavel Tzonkov <pavelc@users.sourceforge.net>
 * @copyright 2010 KCFinder Project
 *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
 *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
 *      @see http://kcfinder.sunhater.com
 */
class zipFolder
{
    protected $zip;

    protected $root;

    protected $ignored;

    public function __construct($file, $folder, $ignored = null)
    {
        $this->zip = new ZipArchive();

        $this->ignored = is_array($ignored)
            ? $ignored
            : ($ignored ? [$ignored] : []);

        if ($this->zip->open($file, ZipArchive::CREATE) !== true) {
            throw new Exception("cannot open <{$file}>\n");
        }

        $folder = rtrim($folder, '/');

        if (strstr($folder, '/')) {
            $this->root = substr($folder, 0, strrpos($folder, '/') + 1);
            $folder = substr($folder, strrpos($folder, '/') + 1);
        }

        $this->zip($folder);
        $this->zip->close();
    }

    public function zip($folder, $parent = null)
    {
        $full_path = "{$this->root}$parent$folder";
        $zip_path = "{$parent}{$folder}";
        $this->zip->addEmptyDir($zip_path);
        $dir = new DirectoryIterator($full_path);
        foreach ($dir as $file) {
            if (!$file->isDot()) {
                $filename = $file->getFilename();
                if (!in_array($filename, $this->ignored)) {
                    if ($file->isDir()) {
                        $this->zip($filename, "{$zip_path}/");
                    } else {
                        $this->zip->addFile("{$full_path}/{$filename}", "{$zip_path}/{$filename}");
                    }
                }
            }
        }
    }
}
