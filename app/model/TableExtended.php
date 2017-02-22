<?php

namespace App\Model;
use Nette;
use Tracy\Debugger;

abstract class TableExtended extends Table  { 
    public function insert($data)	{
        try {
            $columns = $this->connection->getStructure()
                                        ->getColumns($this->tableName);

            foreach($columns as $column) {
                if($column['name'] == "created_by_user_id") {
                    $data['created_by_user_id'] = $this->user->getIdentity()->id;
                }
            }
            
            return $this->getTable()
                        ->insert($data);

        } catch (Nette\Database\UniqueConstraintViolationException $e) {
            $exception = new DuplicateException($e->getMessage());
            $exception->foreign_key = substr($e->getMessage(), strpos($e->getMessage(), "for key '") + 9, -1);
            throw $exception;
		}
	}
 	   
    public function update($id, $data) {
        // kontrola, zda se do některého cizího klíče nepřiřazuje prázdný řetězec, pokud ano nastaví se hodnota klíče na NULL                
        $references = $this->connection->getStructure()
                                       ->getBelongsToReference($this->tableName);
        
        foreach($references as $column => $table) {
           if(isset($data[$column]) && $data[$column] == "") {
               $data[$column] = NULL;
           }
        }

        try {
            return parent::update($id, $data);

        } catch (Nette\Database\UniqueConstraintViolationException $e) {
            $exception = new DuplicateException($e->getMessage());
            $exception->foreign_key = substr($e->getMessage(), strpos($e->getMessage(), "for key '") + 9, -1);
            throw $exception;
        }
    }

    public function delete($id) {
        try {
            return parent::delete($id);

        } catch (Nette\Database\ForeignKeyConstraintViolationException $e) {
            return false;
        }
        
    }    

    public function backupAll() {
        $columns = $this->connection->getStructure()
                                    ->getColumns($this->tableName);
        $rows = $this->getTable();

        if($rows->count() == 0) 
            return "";

        $backup_string = "INSERT INTO `".$this->tableName."` (";
        $column_types = [];

        foreach($columns as $column) {
            $backup_string .= "`".$column['name']."`, ";
            $column_types[] = $column['nativetype'];
        }                         

        $backup_string = substr($backup_string, 0, -2);
        $backup_string .= ") VALUES\r\n";

        foreach ($rows as $row) {
            $backup_string .= "(";
            $col_index = 0;            
            
            foreach($row as $column_value) {
                if($column_types[$col_index] == "VARCHAR" || $column_types[$col_index] == "CHAR" || $column_types[$col_index] == "ENUM")
                    $column_value = "'".$this->escape($column_value)."'";
                elseif($column_value == "")
                    $column_value = "NULL";

                $backup_string .= $column_value.", ";
                $col_index++;
            }
            
            $backup_string = substr($backup_string, 0, -2);
            $backup_string .= "),\r\n";
        }

        $backup_string = substr($backup_string, 0, -3);
        $backup_string .= ";";

        return $backup_string;
    }

    
    private function escape($value) {
        $return = '';
        for($i = 0; $i < strlen($value); ++$i) {
            $char = $value[$i];
            $ord = ord($char);
            if($char !== "'" && $char !== "\"" && $char !== '\\')
                $return .= $char;
            else
                $return .= '\\' . $char;
        }
        return $return;
    }
}

class DuplicateException extends \Exception {
    public $foreign_key;
}