<?php
namespace  Core;


class Main{

    protected $fileInSession;
    protected $dataLoaded = false;
    protected $database;
    protected $isNew =  false;
    protected $docHeading;
    protected $table;


    function __construct(){
        session_start();
        $this->database = new \Core\Database('192.168.33.10','uniabuja_hostel','root','rootpass');
    }

    public function loadData(){
        if($_POST){
           $this->dataLoaded = $this->uploadFile();
            $this->setPersistTable($_POST['table']);
        }

    }

    public function getDataLoaded(){
        return $this->dataLoaded;
    }

    public function doDataExport(){
        if($_POST){
            $this->fileInSession = $this->getFileInSession();
            if(!$this->fileInSession)return;
            $chunkSize = 20;
            /**  Loop to read our worksheet in "chunk size" blocks  **/
            for ($startRow = 2; $startRow <= 240; $startRow += $chunkSize) {
               // echo 'Loading WorkSheet using configurable filter for headings row 1 and for rows ',$startRow,' to ',($startRow+$chunkSize-1),'<br />';
                /**  Create a new Instance of our Read Filter, passing in the limits on which rows we want to read  **/
                $chunkFilter = new ChunkReadFilter($this->fileInSession, $startRow,$chunkSize);
                $objReader =  \PhpOffice\PhpSpreadsheet\IOFactory::createReader($chunkFilter->getFileType());
                $objReader->setReadFilter($chunkFilter);
                /**  Load only the rows that match our filter from $inputFileName to a PHPExcel Object  **/
                $objPHPExcel = $objReader->load($this->fileInSession);
                //	Do some processing here
                $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);

                $postdata = $_POST['data'];
                var_dump($postdata);
                $arraykeys = array_keys($postdata);
                //exit;
                $table = $this->getTable();
                foreach($sheetData as $key => $datum){
                    if(($key >= $startRow) && ($key <=$startRow+$chunkSize-1)){
                        $data = $this->parseTableData($datum,$arraykeys,$postdata);
                        $this->database->insertParsedDataIntoTable($data, $table );
                    }
                }
            }

        }
    }

    protected function parseTableData($datum, $keys, $postdata){
        $tableData = array();
        foreach($keys as $key){
            //$doc_title = $postdata[$key]['doc_title'];
            $relation = $postdata[$key]['relation'];
            $columns  = $postdata[$key]['columns'];

            switch($relation){
                case "no_relation":
                    continue;
                break ;
                case "foreign_key" :
                    $foreignkey_table = $postdata[$key]['foreign_key_table'];
                    $display_field = $postdata[$key]['foreign_key_display_field'];
                    $id = $this->database->getForeignKeyId($foreignkey_table, $display_field, $datum[$key]);
                    $tableData[$columns] = $id;
                    break;
                default:
                    $tableData[$columns] = $datum[$key];
                    ;
            }

        }
        return $tableData;
    }


    protected function uploadFile(){
        $basepath = dirname(__FILE__);
        $uploaddir = dirname($basepath)."/dir/";
        $uploadfile = $uploaddir . basename($_FILES['file']['name']);
        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
            $this->persistFileInSession($uploadfile);
            return true;
        }

    }

    public function getFileHeader(){
        $this->fileInSession = $this->getFileInSession();
        $chunkFilter = new ChunkReadFilter($this->fileInSession);
        $objReader =  \PhpOffice\PhpSpreadsheet\IOFactory::createReader($chunkFilter->getFileType());
        $objReader->setReadFilter($chunkFilter);
        $objPHPExcel = $objReader->load($this->fileInSession);
        $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
        //var_dump($sheetData);
        return $sheetData[1];
    }

    public function getTable(){

        return $_SESSION['table'];
    }

    public function setPersistTable($table){
        $_SESSION['table'] = $table;
    }
    public function listTables(){
        return $this->database->getTables();
    }
    public function getColumns($table = ""){
        $table = ($table)? $table: $this->getTable();
        return $this->database->getColumns($table);
    }

    protected function persistFileInSession($file){
        $_SESSION['fileInSession'] = $file;
    }

    public function getFileInSession(){
        return $_SESSION['fileInSession'];
    }


}