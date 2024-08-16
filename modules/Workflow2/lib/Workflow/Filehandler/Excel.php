<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 22.05.14 12:22
 * You must not use this file without permission.
 */

namespace Workflow\Filehandler;

use Workflow\VtUtils;

class Excel extends Base
{
    /**
     * @var \PHPExcel_Reader_IReader
     */
    protected $filehandler;

    /**
     * @var \PHPExcel_Worksheet
     */
    protected $sheet;

    protected $highestCol;

    protected $_firstSkip = false;

    public function init()
    {
        require_once VtUtils::getAdditionalPath('phpexcel') . 'PHPExcel.php';

        if ($this->filehandler !== null) {
            return;
        }

        $inputFileType = \PHPExcel_IOFactory::identify($this->filepath);
        $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($this->filepath);

        $this->filehandler = $objPHPExcel;
        $this->sheet = $this->filehandler->getSheet(0);

        $this->highestCol = $this->sheet->getHighestDataColumn();

        if ($this->_firstSkip == false) {
            $this->_firstSkip = true;

            if ($this->params['skipfirst']) {
                ++$this->position;
                header('SKIPFIRST:1');
            }
        }
    }

    public function getNextRow($skip = false)
    {
        if (!$skip) {
            $this->init();
        }

        if ($this->position + 1 > $this->sheet->getHighestRow()) {
            return false;
        }

        $rowData = $this->sheet->rangeToArray(
            'A' . ($this->position + 1) . ':' . $this->highestCol . ($this->position + 1),
            null,
            true,
            false,
        );

        ++$this->position;

        return $rowData[0];
    }

    public function resetPosition()
    {
        $this->init();
        $this->position = 0;
    }

    public function getTotalRows()
    {
        require_once VtUtils::getAdditionalPath('phpexcel') . 'PHPExcel.php';
        $inputFileType = \PHPExcel_IOFactory::identify($this->filepath);
        $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($this->filepath);

        $filehandler = $objPHPExcel;
        $sheet = $filehandler->getSheet(0);

        return $sheet->getHighestRow();
    }
}
