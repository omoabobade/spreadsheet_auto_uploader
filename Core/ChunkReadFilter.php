<?php

namespace Core;



class ChunkReadFilter implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter
{
    private $_startRow = 0;
    private $_endRow   = 0;
    protected $fileType;
    const TYPE_XLSX = 'XLSX';
    const TYPE_XLS = 'XLS';
    const TYPE_CSV = 'CSV';
    const TYPE_ODS = 'ODS';


    function __construct($filename,$startRow = "", $chunkSize=""){
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $this->setFileType($ext);
        $this->_startRow	= $startRow;
        $this->_endRow		= $startRow + $chunkSize;
    }

    public function readCell($column, $row, $worksheetName = '') {
        //  Only read the heading row, and the rows that were configured in the constructor
        if (($row == 1) || ($row >= $this->_startRow && $row < $this->_endRow)) {
            return true;
        }
        return false;
    }

    protected function setFileType($ext){
        if (!$this->fileType)
        {
            switch ($ext)
            {
                case 'xlsx':
                case 'xltx': // XLSX template
                case 'xlsm': // Macro-enabled XLSX
                case 'xltm': // Macro-enabled XLSX template
                    $this->fileType = self::TYPE_XLSX;
                    break;
                case 'xls':
                case 'xlt':
                    $this->fileType = self::TYPE_XLS;
                    break;
                case 'ods':
                case 'odt':
                    $this->fileType = self::TYPE_ODS;
                    break;
                default:
                    $this->fileType = self::TYPE_CSV;
                    break;
            }
        }
    }

    public function getFileType(){
        return $this->fileType;
    }
}

