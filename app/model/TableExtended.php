<?php

namespace App\Model;
use Nette;
use Tracy\Debugger;

abstract class TableExtended extends Table  { 

    public function insert($data)	{
        try {
            $data['created_by_user_id'] = $this->user->getIdentity()->id;
            
            return $this->getTable()
                        ->insert($data);

        } catch (Nette\Database\UniqueConstraintViolationException $e) {
            $exception = new DuplicateException($e->getMessage());
            $exception->foreign_key = substr($e->getMessage(), strpos($e->getMessage(), "for key '") + 9, -1);
            throw $exception;
		    }
	  }
 	   
    public function update($id, $data)  {
                
        // kontrola, zda se do některého cizího klíče nepřiřazuje prázdný řetězec, pokud ano nastaví se hodnota klíče na NULL                
        $references = $this->connection->getStructure()
                                       ->getBelongsToReference($this->tableName);
        
        foreach($references as $column => $table) {
           if(isset($data[$column]) && $data[$column] == "") {
               $data[$column] = NULL;
           }
        }
		
        return $this->getTable()->where(['id' => $id])
        			                  ->update($data);   			
    }
}

class DuplicateException extends \Exception {
    public $foreign_key;
}