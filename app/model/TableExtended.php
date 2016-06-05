<?php

namespace App\Model;
use Nette;

abstract class TableExtended extends Table
{   
	public function insert($data)	{
		try {
	    	return $this->getTable()
	                    ->insert($data);
	    } catch (Nette\Database\UniqueConstraintViolationException $e) {
		    ob_start();
			var_dump($data);
			$result = ob_get_clean();
			throw new DuplicateException($result);
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
		
        return $this->getTable()
        			->where(['id' => $id])
        			->update($data);   			
    }
}

class DuplicateException extends \Exception
{}