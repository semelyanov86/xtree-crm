<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 22.05.14 12:22
 * You must not use this file without permission.
 */

namespace Workflow\Filehandler;

class Csv extends Base
{
    /**
     * @var \SplFileObject
     */
    protected $filehandler;

    protected $_firstSkip = false;

    public function init()
    {
        if ($this->filehandler !== null) {
            return;
        }

        $this->filehandler = new \SplFileObject($this->filepath);
        $this->filehandler->setCsvControl($this->params['delimiter']);
        $this->filehandler->setFlags(\SplFileObject::READ_CSV);

        if ($this->_firstSkip == false) {
            $this->_firstSkip = true;

            //            if($this->position == 0) {
            if ($this->params['skipfirst']) {
                ++$this->position;
                header('SKIPFIRST:1');
            }
            //            }

            $check = $this->position;
            if ($this->position > 0) {
                $this->filehandler->seek(0);
                for ($i = 0; $i < $this->position; ++$i) {
                    $check .= '.';
                    $this->getNextRow(true);
                }
                // header('Check:'.$check);
            }
        }
    }

    public function getNextRow($skip = false)
    {
        if (!$skip) {
            $this->init();
        }
        if ($this->filehandler->eof()) {
            return false;
        }

        $return = $this->filehandler->fgetcsv();

        if ($this->params['encoding'] != 'UTF-8') {
            foreach ($return as $index => $value) {
                $return[$index] = mb_convert_encoding($return[$index], 'UTF-8', $this->params['encoding']);
            }
        }

        // header('HEAD'.rand(10000,99999).':'.($skip?'skip':'').$return[1]);

        return $return;
    }

    public function resetPosition()
    {
        $this->init();
        $this->position = 0;
        $this->filehandler->seek(0);
    }

    public function getTotalRows()
    {
        $linecount = 0;

        $handler = new \SplFileObject($this->filepath);
        $handler->setCsvControl($this->params['delimiter']);
        $handler->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE | \SplFileObject::READ_AHEAD);

        while ($handler->eof() != true) {
            $record = $handler->fgetcsv();

            if (!empty($record)) {
                ++$linecount;
            }
        }

        unset($handler);

        return $linecount;
    }
}
