<?php

namespace App\Model;
use Nette;
use Tracy\Debugger;

class BackupManager extends Nette\Object  { 
    /** @var Nette\Database\Connection */
    public $connection;

    public function __construct(Nette\Database\Context $db) {
        $this->connection = $db;
    }

    public function backupAll($table) {
        $columns = $this->connection->getStructure()
                                    ->getColumns($table);

        $rows = $this->connection->table($table);

        if($rows->count() == 0) 
            return "";

        $backup_string = "INSERT INTO `".$table."` (";
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