<?php
namespace Core;

class Database {

    protected $connect;
    
    function __construct($host, $dbname, $username, $password)
    {
        try {
            $this->connect = new \PDO('mysql:host='.$host.';port=3306;dbname='.$dbname.'', $username, $password);
            $this->connect->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch(\PDOException $e) {
            echo 'ERROR: ' . $e->getMessage();
        }
    }


    public function getColumns($table){
        $columns = array();
        $statement = $this->connect->query('DESCRIBE '.$table);
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $statement->closeCursor();
        foreach($result as $column){
           $columns[] = $column['Field'];
        }
        return $columns;
    }

    public function getTables()
    {
        $sql = 'SHOW TABLES';
        $statement = $this->connect->query($sql);
        $stmt = $statement->fetchAll(\PDO::FETCH_COLUMN);
        $statement->closeCursor();
        return $stmt;

    }

    public function selectAllFromTable($table){
        $statement = $this->connect->query('SELECT * FROM '.$table);
        $stmt = $statement->fetchAll(\PDO::FETCH_OBJ);
        $statement->closeCursor();
        return $stmt;
    }

    public function getForeignKeyId($table, $column, $value){
        try{
            $statement = $this->connect->prepare('SELECT * FROM '.$table.' WHERE '.$column.'=:column');

            $statement->bindValue( ":column", $value, \PDO::PARAM_STR );
            $statement->execute();
            $result = $statement->fetchColumn();
            $statement->closeCursor();
            if($result) return $result;
        else return $this->insertNewForeignKey($table, $column, $value);
        } catch (\PDOException $e) {
            echo 'ERROR: ' . $e->getMessage()." -- Getting Foreign Key ---<br />";
        }
    }

    public function insertNewForeignKey($table, $column, $value)
    {
        $statement = $this->connect->prepare("INSERT INTO " . $table . " (" . $column . ") VALUES(?)");
        try {
            $this->connect->beginTransaction();
            $statement->execute(array($value));
            $statement->closeCursor();
            $this->connect->commit();
            return $this->connect->lastInsertId();
        } catch (\PDOException $e) {
            echo 'ERROR: ' . $e->getMessage()." -- Inserting New Foreign Key ---<br />";
        }
    }

    public function insertParsedDataIntoTable($data, $table ){
        $keys = array_keys($data);
        $values = array_values($data);
        $binds = array_fill(1, count($values), '?');
        $columns = implode(',', $keys);
        $imploded_bind = implode(',', $binds);

        $statement = $this->connect->prepare("INSERT INTO " . $table . " (" . $columns . ") VALUES(".$imploded_bind.")");
        try {
            $this->connect->beginTransaction();
            $statement->execute($values);
            $statement->closeCursor();
            $this->connect->commit();
        } catch (\PDOException $e) {
            echo 'ERROR: ' . $e->getMessage()." -- Inserting Parsed Data ---<br />";
        }
    }

}